<?php
/**
 +-------------------------------------------------------------------------+
 | Rubify  - An MiniDLNA Webapp                                            |
 | Version 1.0.0                                                           |
 |                                                                         |
 | This program is free software: you can redistribute it and/or modify    |
 | it under the terms of the GNU General Public License as published by    |
 | the Free Software Foundation.                                           |
 |                                                                         |
 | This file forms part of the Rubify software.                            |
 |                                                                         |
 | If you wish to use this file in another project or create a modified    |
 | version that will not be part of the Rubify Software, you               |
 | may remove the exception above and use this source code under the       |
 | original version of the license.                                        |
 |                                                                         |
 | This program is distributed in the hope that it will be useful,         |
 | but WITHOUT ANY WARRANTY; without even the implied warranty of          |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the            |
 | GNU General Public License for more details.                            |
 |                                                                         |
 | You should have received a copy of the GNU General Public License       |
 | along with this program.  If not, see http://www.gnu.org/licenses/.     |
 |                                                                         |
 +-------------------------------------------------------------------------+
 | Author: Jaime Rubio <jaime@rubiogafsi.com>                              |
 +-------------------------------------------------------------------------+
*/
namespace Rubify\Framework;
use DateTime;

defined('_RBFYEXEC') or die;

use Rubify\Framework\Factory;
use Rubify\Framework\SimpleXMLExtended;
use Rubify\Framework\Helpers;

class History 
{
    protected $user;
    protected $list;
    protected $selected;
    protected $filename;
    protected $database;
    protected $data;    
    protected $xml;
    protected $array;

    public function __construct( $user )
    {
        $this->database = Factory::getDatabase();
        $this->user     = $user;
        $this->filename = RBFY_HISTORY . DIRECTORY_SEPARATOR . $this->user . '.xml';

        if(!file_exists($this->filename))
        {
            file_put_contents($this->filename, '<?xml version = "1.0" encoding = "UTF-8" standalone = "yes" ?><history></history>');    
        }
        
        $this->data     = file_get_contents($this->filename);
        $this->xml      = new SimpleXMLExtended($this->data);                    
        $this->array    = [];
    }

    public function __destruct()
    {

    }

    public function load()
    {
        $config = Factory::getConfig();
        $tracks = $this->xml->xpath("//history/track");        
        $array=[];
        foreach($tracks as $t)
        {
            $item = new \stdClass();

            $attr = $t->attributes();
            foreach($attr as $key => $value)
                $item->$key = $value->__toString();
        

            $children           = $t->children();            
            foreach($children as $key => $value)
                $item->$key = $value->__toString();

            $item->alias        = Helpers::encode($item->title);       
            $item->link         = Factory::Link('track', 'oid=' . $item->oid . ':' . $item->alias);            

            if($config->use_symlink){

                if(file_exists($item->path))
                {
                    $item->audio = Helpers::getRelAudio($item->path);
                } else {
                    $item->audio = '';
                } 

                if(file_exists($item->picture))
                {
                    $item->thumbnail = Helpers::getRelArtwork($item->picture);
                } else {
                    $item->thumbnail = Factory::getAssets() . '/images/all-music.png';
                }
            } else {
                $item->audio        = Factory::Link('track.audio','oid=' . $item->oid);  
                $item->thumbnail    = Factory::Link('track.thumbnail','oid=' . $item->oid);                  
            }                     

            $item->audio        = Factory::Link('track.audio','oid=' . $item->oid);                        
            $item->duration_ms  = Helpers::durationToMilliseconds($item->duration);
            $item->duration_fm  = Helpers::formatMilliseconds($item->duration_ms);  
            $item->percent      = intval(100*$item->progress / $item->duration_ms);
            $item->extra = [
                'lyrics'=> Factory::Link('track.lyrics', 'oid=' . $item->oid, 'format=json'),
                'info'  => Factory::Link('track.info', 'oid=' . $item->oid,'format=json'),
                'banner' => Factory::Link('track.banner', 'oid=' . $item->oid,'format=json'),
            ];                                                                          

            $array[$item->oid] = $item;
        }
        $this->array = $array;
        return $this->array;        
    }

    public function exists( $oid)
    {
        if(!$this->array)        
            $this->array = $this->load();

        return array_key_exists($oid , $this->array);
    }

    /**
     * Get an XML object fulfilled with the data from the database
     * @param string $oid   Reference Object ID from miniDLNA
     * 
     * @return mixed Returns the XML object or false if the database object isnot found
     */
    public function get($oid){

        if($this->exists($oid))
            return $this->array[$oid];

        $this->array = $this->load();
        if($this->exists($oid))
            return $this->array[$oid];
        else        
            return false;                             
    }
    

    public function pushArray( $array )
    {
        $current    = $this->load();  
        $history   = $this->xml->xpath("//history");

        if(isset($history[0]))
        {
            foreach($array as $row)
            {   
                if(in_array($row->oid,$current))
                    continue;
                
                $track = $history[0]->addChild('track');
                $this->_build($row,$track);
            }                    

            //Save
            $this->xml->asXML($this->filename);
        }
        return $this->load();
    }

    public function push( $oid , $time = 0)
    {
        if($this->exists($oid))
            return $this->array[$oid];

        //Get the history
        $history = $this->xml->xpath("//history");

        $sql = "SELECT `OB`.`ID` , `OB`.`OBJECT_ID` `OID`, `OB`.`NAME` , `DT`.*, `AA`.`PATH` `PICTURE`
            FROM `OBJECTS` `OB` 
            LEFT JOIN `DETAILS` `DT` ON `OB`.`DETAIL_ID`=`DT`.`ID`
                LEFT JOIN `ALBUM_ART` `AA` ON `DT`.`ALBUM_ART`=`AA`.`ID`
            WHERE `OB`.`OBJECT_ID` = '$oid'
            ;";
            
        $this->database->query($sql);
        $row   = $this->database->loadRow();   
        if($row && isset($history[0]))
        {   
            $track  = $history[0]->addChild('track');
            $item   = new \stdClass();
            $item->progress = $time;

            foreach($row as $k=>$v)
            {
                $k = strtolower($k);
                $item->$k = $v;                
            }
            //Build
            $this->_build($item,$track);         
        }        
        $success = $this->xml->asXML($this->filename);
        $this->load();

        return $success;
    }

    public function pop( $oid )
    {        
        $now  = new DateTime();

        $this->load();        
        if($this->exists($oid))
        {
            //Get the history
            $history = $this->xml->xpath("//history");

            if(isset($history[0]))
            {
                //Get the tracks from the history            
                $tracks = $history[0]->xpath("//track[@oid='$oid']");
                if(!empty($tracks))
                {
                    //Remove the track
                    unset($tracks[0][0]); 
                
                    //Update                 
                    $history[0]->attributes()->updated = $now->format('c');                  

                    return $this->xml->asXML($this->filename);
                }     
            }            
        } 
        return false;                
    }    

    public function empty()
    {
        $now     = new DateTime();
        $success = true;

        if(file_exists($this->filename))   
        {     
            $history = $this->xml->xpath("//history");
            if(isset($history[0]))
            {
                $tracks = $history[0]->xpath('//track');
                foreach($tracks as $t)
                    unset($t[0][0]); 
   
                $success = $this->xml->asXML($this->filename);
            }                               
        }
        return $success;
    }

    /**
     * Fulfill the SimpleXMLElement with the data from the database
     * @param mixed $data stdClass object from the database
     * @param mixed $track Object SimpleXML
     *      
     */    
    protected function _build($data,&$node)
    {
        $now        = new DateTime();   

        $node->addAttribute('id' , $data->id );
        $node->addAttribute('oid' , $data->oid );
        $node->addAttribute('date' , $now->format('c'));
        $node->addAttribute('progress' , $data->progress);

        $node->addChild('title')->addCData($data->title);
        $node->addChild('album')->addCData($data->album);
        $node->addChild('name')->addCData($data->name);
        $node->addChild('path')->addCData($data->path);
        $node->addChild('genre')->addCData($data->genre);
        $node->addChild('artist')->addCData($data->artist);
        $node->addChild('creator')->addCData($data->creator);
        $node->addChild('picture')->addCData($data->picture);
        $node->addChild('duration',$data->duration);
        $node->addChild('bitrate',$data->bitrate);
        $node->addChild('samplerate',$data->samplerate);
        $node->addChild('channels',$data->channels);
        $node->addChild('mime',$data->mime);  
    }

}