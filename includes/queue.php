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
use stdClass;

defined('_RBFYEXEC') or die;

use Rubify\Framework\Factory;
use Rubify\Framework\Playlist;
use Rubify\Framework\Helpers;

class Queue 
{
    protected $user;
    protected $filename;
    protected $database;
    protected $data;    
    protected $xml;
    protected $array;

    public function __construct( $user )
    {
        $this->database = Factory::getDatabase();
        $this->user     = $user;
        $this->filename = RBFY_QUEUE . DIRECTORY_SEPARATOR . $this->user . '.xml';

        if(!file_exists($this->filename))
            file_put_contents($this->filename, '<?xml version = "1.0" encoding = "UTF-8" standalone = "yes" ?><queue></queue>');        

        $this->data = file_get_contents($this->filename);
        $this->xml  = new SimpleXMLExtended($this->data);             
        $this->array= [];
    }

    public function __destruct()
    {

    }

    /**
     * Load the complete set of tracks in the current queue
     * 
     * @return array    Array of XML Elements containing the details of the track
     */
    public function load()
    {
        $config = Factory::getConfig();
        $tracks = $this->xml->xpath('//queue/track');        
        $array=[];
        foreach($tracks as $t)
        {
            $item = new stdClass();

            $attr = $t->attributes();
            foreach($attr as $key => $value)
                $item->$key = $value->__toString();
    

            $children           = $t->children();            
            foreach($children as $key => $value)
                $item->$key = $value->__toString();

            $item->title        = html_entity_decode($item->title);
            $item->name         = html_entity_decode($item->name);
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
            $item->isfavorite   = $this->_isfavorite($item->oid);
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

    /**
     * Checks whether a tracks exists in the queue
     * @param string $oid Reference Object ID from miniDLNA
     * 
     * @return boolean True if the track exists. False if not
     */
    public function exists($oid)
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

    /**
     * Push an array of objects from the database
     * @param array $array  Array of objects from the database
     * @param string $method Select a position of the new node. By default, this is pushed to the last. Otherwise, use unshift to the 1st position
     */
    public function pushArray( $array , $method = '' )
    {
        $current= $this->load();       
        $queue  = $this->xml->xpath('//queue');

        foreach($array as $row)
        {   
            if(in_array($row->oid,$current))
                continue;
                
            if($method != 'unshift')
                $node = $queue[0]->addChild('track');
            else
                $node = $queue[0]->prependChild('track');

            $this->_build($row , $node);                  
        }                              
        $this->_reorder();
        $success = $this->xml->asXML($this->filename);
        $this->load();
        return $success;
    }
  
    /**
     * Remove a given track from the queue
     * @param string $oid   Reference OBject ID (miniDLNA)
     * @param mixed $method Select a position of the new node. By default, this is pushed to the last. Otherwise, use unshift to the 1st position
     */
    public function push( $oid , $method = '' )
    {
        if($this->exists($oid))
            return $this->array[$oid];

        $sql = "SELECT `OB`.`ID` , `OB`.`OBJECT_ID` `OID`, `OB`.`NAME` , `OB`.`REF_ID`, `DT`.*, `AA`.`PATH` `PICTURE`
            FROM `OBJECTS` `OB` 
            LEFT JOIN `DETAILS` `DT` ON `OB`.`DETAIL_ID`=`DT`.`ID`
                LEFT JOIN `ALBUM_ART` `AA` ON `DT`.`ALBUM_ART`=`AA`.`ID`
            WHERE `OB`.`OBJECT_ID` = '$oid'
            ;";
            
        $this->database->query($sql);
        $row   = $this->database->loadRow();   
        $success = false;
        if($row)
        {   
            $queue  = $this->xml->xpath('//queue');
            $data   = new stdClass(); 
            
            foreach($row as $k=>$v)
            {
                $k = strtolower($k);
                $data->$k = html_entity_decode($v);
            }

            //Get the parent
            $className = RBFY_CLASS_ALBUM_MUSIC;
            $sql = "SELECT `PR`.* 
                FROM `OBJECTS` `OB` , `OBJECTS` `PR` 
                WHERE `OB`.`PARENT_ID` = `PR`.`OBJECT_ID` 
                AND `PR`.`CLASS`= '$className' 
                AND `OB`.`REF_ID` = '" . $data->ref_id . "' 
                ORDER BY LENGTH(`PR`.`PARENT_ID`) ;";

            $this->database->query($sql);
            if ($ref = $this->database->loadRow())
            {
                $parent = new stdClass();
                $parent->oid = $ref['OBJECT_ID'];
                $parent->title = Helpers::getFolderName($ref['NAME']);
                $parent->name = Helpers::getFolderName($ref['NAME']);
                $parent->alias = Helpers::encode($parent->name);
                $parent->link = Factory::Link('album', 'oid=' . $parent->oid . ':' . $parent->alias);
                $data->parent = $parent;
            }

            if($method != 'unshift')
                $track = $queue[0]->addChild('track');
            else
                $track = $queue[0]->prependChild('track');

            $this->_build($data,$track);            
            $this->_reorder();
            $success = $this->xml->asXML($this->filename);
            $this->load(); 
        }                                      
        return $success;
    }

    /**
     * Remove a given track from the queue
     * @param string $oid   Reference OBject ID (miniDLNA)
     */
    public function pop( $oid )
    {        
        $result = false;
        $this->load();
        if($this->exists($oid))
        {
            if($tracks = $this->xml->xpath("//queue/track[@oid='$oid']"))
            {                           
                unset($tracks[0][0]); 
                unset($this->array[$oid]);         
                $this->_reorder();
                $result = $this->xml->asXML($this->filename);
                $this->array = $this->load();   
            }            
        } 
        return $result;                
    }    

    /**
     * Removes all the tracks in the queue
     */
    public function empty()
    {
        $result = false;
        if(file_exists($this->filename))   
        {     
            $tracks = $this->xml->xpath("//queue/track");
            if(!empty($tracks))
            {
                foreach($tracks as $t)
                    unset($t[0][0]); 

                $this->_reorder();
                $result = $this->xml->asXML($this->filename);
                $this->array = $this->load();   
            }                               
        }
        return $result;
    }

    /**
     * Sort the tracks in the queue based on the new order in a array of objects ID
     */
    public function sort($array)
    {
        if(!$this->array)
            $this->load();

        $result = [];        
        foreach($array as $key)
            $result[] = $this->array[$key];    

        $this->empty();
        return $this->pushArray($result);
    }

    /**
     * Fulfill the SimpleXMLElement with the data from the database
     * @param stdClass $data stdClass object from the database
     * @param mixed $track Object SimpleXML
     *      
     */
    private function _build($data, &$node)
    {
        $node->addAttribute('id' , $data->id );
        $node->addAttribute('oid' , $data->oid );
        $node->addAttribute('order' , 0 );
        $node->addChild('title')->addCData($data->title);    
        $node->addChild('album')->addCData($data->album);    
        $node->addChild('name')->addCData($data->name);
        $node->addChild('path')->addCData($data->path);
        $node->addChild('genre')->addCData($data->genre);
        $node->addChild('artist')->addCData($data->artist);
        $node->addChild('creator')->addCData($data->creator);
        $node->addChild('picture')->addCData($data->picture);
        $node->addChild('duration', $data->duration);
        $node->addChild('bitrate',$data->bitrate);
        $node->addChild('samplerate',$data->samplerate);
        $node->addChild('channels',$data->channels);
        $node->addChild('mime',$data->mime);    
    }
    /**
     * Reassign the order of the tracks in a queue
     */
    private function _reorder()
    {
        //Reorder
        $tracks  = $this->xml->xpath('//queue/track');
        $order = 1;
        foreach($tracks as $t)
        {
            $attr = $t->attributes();
            $attr->order = $order;
            $order++;
        }
        $this->xml->asXML($this->filename);       
    }

    /**
     * Check if the track is the favorites
     */
    private function _isfavorite($oid)
    {
        $playlist = new Playlist($this->user);
        return $playlist->isFavorite($oid);
    }       
}