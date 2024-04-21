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

class Playlist 
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
        $this->filename = RBFY_PLAYLIST . DIRECTORY_SEPARATOR . $this->user . '.xml';

        if(!file_exists($this->filename))
        {
            file_put_contents($this->filename, '<?xml version = "1.0" encoding = "UTF-8" standalone = "yes" ?><collection></collection>');    
            $this->data     = file_get_contents($this->filename);
            $this->xml      = new SimpleXMLExtended($this->data);  
            $this->create('favorites',true);    
        }
        
        $this->data     = file_get_contents($this->filename);
        $this->xml      = new SimpleXMLExtended($this->data);             
        $this->select('favorites');        
        $this->array    = [];
    }

    public function __destruct()
    {

    }

    public function getSelected()
    {
        return $this->selected;
    }
        
    /**
     * Return the list of tracks in a selected playlist
     * @param mixed $name Name of the playlist to select
     * 
     * @return bool Playlist ID if found, false if not
     */
    public function favoritesID()
    {
        $playlist = $this->xml->xpath("//collection/playlist[@name='favorites']"); 
        if(count($playlist)){
            $attr = $playlist[0]->attributes();
            return $attr->id;
        } 
        return false;        
    }

    /**
     * Return the list of tracks in a selected playlist
     * @param mixed $name Name of the playlist to select
     * 
     * @return bool True if found, false if not
     */
    public function select($id)
    {
        $playlist = $this->xml->xpath("//collection/playlist[@id='$id']"); 
        if(count($playlist)){
            $this->selected = $id;
            return true;    
        } 
        return false;        
    }

    /**
     * Create a playlist
     * @param string    $name       Name of the playlist
     * @param bool      $default    Create the default playlist (favorites)
     * 
     * @return bool     True if created, false if not    
     */
    public function create($name , $default = false)
    {
        $root   = $this->xml->xpath("//collection"); 
        $now    = new DateTime();
        $id     = uniqid();

        //Cannot create a playlist named
        if($name == 'favorites' && !$default) return false;

        //Cannot create a playlist with an existing name
        foreach($this->list() as $n)
            if($n->name == $name) return false;

        //Create the new node
        $track = $root[0]->addChild('playlist');
        $track->addAttribute('id' , $id);          
        $track->addAttribute('name' , $name );        
        $track->addAttribute('created' , $now->format('c') ); 
        $track->addAttribute('updated' , $now->format('c') ); 
        $this->xml->asXML($this->filename);

        $this->selected = $id;
        return true;       
    }

    public function delete($id)
    {
        if($id == $this->favoritesID()) return false;

        $playlist = $this->xml->xpath("//collection/playlist[@id='$id']"); 
        unset($playlist[0][0]);
        return $this->xml->asXML($this->filename);   
    }    

    public function list(){
        $root   = $this->xml->xpath("//collection/playlist"); 
        $array=[];
        foreach($root as $p)
        {
            $item = new \stdClass();
            $attr = $p->attributes();
            foreach($attr as $key => $value)
                $item->$key = $value->__toString(); 

            //Check if it is the favorites                
            $item->isfavorites = ($attr->name == 'favorites');
            
            //Number of tracks within the playlist
            $item->count = count($p->children());;         

            $array[] = $item;
        }
        return $array;
    }

    public function get()
    {
        $root = $this->xml->xpath("//collection/playlist[@id='{$this->selected}']");        
        if(count($root))
        {
            $item = new \stdClass();

            //Get attributes
            $attr = $root[0]->attributes();
            foreach($attr as $key => $value)
                $item->$key = $value->__toString();        

            //Load the tracks
            $tracks = $this->load();
            $item->tracks = count($tracks);

            //Duration
            $duration = 0;
            foreach($tracks as $t)
                $duration += $t->duration_ms;
            $item->duration_fm = Helpers::formatMilliseconds($duration);  
            
            //Return the result
            return $item;
        }
        return false;     
    }    
    public function load()
    {
        $config = Factory::getConfig();
        $tracks = $this->xml->xpath("//collection/playlist[@id='{$this->selected}']/track");        
        $array=[];
        foreach($tracks as $t)
        {
            $item = new \stdClass();

            $attr = $t->attributes();
            foreach($attr as $key => $value)
                $item->$key = $value->__toString();
        
            $children = $t->children();            
            foreach($children as $key => $value)
                $item->$key = $value->__toString();

            $item->title        = html_entity_decode($item->title);
            $item->name         = html_entity_decode($item->name);
            $item->alias        = Helpers::encode($item->title);       
            $item->link         = Factory::Link('track', 'oid=' . $item->oid . ':' . $item->alias);
            $item->isfavorite  = $this->isFavorite($item->oid); 

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
    public function getTrack($oid){

        if($this->exists($oid))
            return $this->array[$oid];

        $this->array = $this->load();
        if($this->exists($oid))
            return $this->array[$oid];
        else        
            return false;                             
    }
    

    /**
     * Push a list of tracks to a given playlist
     * 
     * @param array $array  Array of objects tracks
     * 
     * @return array Array of the current playlist
     */
    public function pushArray( $array )
    {
        $current    = $this->load();
        $now        = new DateTime();     
        $playlist   = $this->xml->xpath("//collection/playlist[@id='{$this->selected}']");

        if(isset($playlist[0]))
        {
            foreach($array as $row)
            {   
                //Do not push a track twice to the same playlist
                if(in_array($row->oid,$current))
                    continue;
                
                ///Add the new node child
                $track = $playlist[0]->addChild('track');

                //Put the attributes and children to the brand new node
                $this->_build($row,$track);
            }                    

            //Reorder
            $this->_reorder();

            //Update 
            $playlist[0]->attributes()->updated = $now->format('c');

            //Save
            $this->xml->asXML($this->filename);
        }
        return $this->load();
    }

    /**
     * Push a track to a given playlist
     */
    public function push( $oid )
    {
        if($this->exists($oid))
            return $this->array[$oid];

        $now    = new DateTime();            

        //Get ths playlist
        $playlist = $this->xml->xpath("//collection/playlist[@id='{$this->selected}']");

        $sql = "SELECT `OB`.`ID` , `OB`.`OBJECT_ID` `OID`, `OB`.`NAME` , `DT`.*, `AA`.`PATH` `PICTURE`
            FROM `OBJECTS` `OB` 
            LEFT JOIN `DETAILS` `DT` ON `OB`.`DETAIL_ID`=`DT`.`ID`
                LEFT JOIN `ALBUM_ART` `AA` ON `DT`.`ALBUM_ART`=`AA`.`ID`
            WHERE `OB`.`OBJECT_ID` = '$oid'
            ;";
            
        $this->database->query($sql);
        $row   = $this->database->loadRow();   
        if($row && isset($playlist[0]))
        {    
            $track = $playlist[0]->addChild('track');
            $item  = new \stdClass();
            foreach($row as $k=>$v)
            {
                $k = strtolower($k);
                $item->$k = $v;                
            }

            //Build
            $this->_build($item,$track); 

            //Reorder
            $this->_reorder();

            //Update 
            $playlist[0]->attributes()->updated= $now->format('c');            
        }        
        $success = $this->xml->asXML($this->filename);
        $this->load();

        return $success;
    }

    /**
     * Remove a track from a given playlist
     */
    public function pop( $oid )
    {        
        $now  = new DateTime();

        $this->load();        
        if($this->exists($oid))
        {
            //Get the playlist
            $playlist = $this->xml->xpath("//collection/playlist[@id='{$this->selected}']");

            if(isset($playlist[0]))
            {
                //Get the tracks from the playlist            
                $tracks = $playlist[0]->xpath("//track[@oid='$oid']");
                if(!empty($tracks))
                {
                    //Remove the track
                    unset($tracks[0][0]); 
                
                    //Update                 
                    $playlist[0]->attributes()->updated = $now->format('c');                  

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
            $playlist = $this->xml->xpath("//collection/playlist[@id='{$this->selected}']");
            if(isset($playlist[0]))
            {
                $tracks = $playlist[0]->xpath("//collection/playlist[@id='{$this->selected}']/track");
                foreach($tracks as $t)
                    unset($t[0][0]); 

                //Update                 
                $playlist[0]->attributes()->updated = $now->format('c');      

                $success = $this->xml->asXML($this->filename);
            }                               
        }
        return $success;
    }

    public function isFavorite($oid)
    {
        $favid     = $this->favoritesID();
        $track = $this->xml->xpath("//collection/playlist[@id='$favid']/track[@oid='$oid']"); 
        return isset($track[0]);
    }    
    /**
     * Fulfill the SimpleXMLElement with the data from the database
     * @param mixed $data stdClass object from the database
     * @param mixed $track Object SimpleXML
     *      
     */    
    protected function _build($data,&$node)
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

}