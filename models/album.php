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

use Rubify\Framework\Helpers;
use Rubify\Framework\Playlist;

class modelAlbum extends Model
{
    protected $oid = null;
    public $album;
    public $discs;
    public $tracks;
    public $playlist;

    public function display()
    {
        if(!$this->oid)
            list($this->oid) = Helpers::getId();
             
        $this->album    = $this->_getAlbum();
        $this->discs    = $this->_getDiscs();
        $this->album->discs  = count($this->discs);
        $this->album->tracks = 0; 
        $this->album->duration_ms = 0;
        $this->tracks   = [];
        foreach($this->discs as $k=>$v)
        {   
            $disc = $v['DISC'];        
            $this->tracks['CD'.($k+1)] = $this->_getTracks($disc);           
            $this->album->tracks += count($this->tracks['CD'.($k+1)]);

            foreach($this->tracks['CD'.($k+1)] as $track)
                $this->album->duration_ms += $track->duration_ms;            
        }
        $this->album->duration_fm = Helpers::formatMilliseconds($this->album->duration_ms);

        $this->data['album']    = $this->album; 
        $this->data['tracks']   = $this->tracks;         

        $this->page->data       = $this->data;
        $this->page->title      = $this->album->name;        

        parent::display();
    }

    public function json()
    {
        if(!$this->oid)
            list($this->oid) = Helpers::getId();
        
        $this->album    = $this->_getAlbum();
        $this->tracks   = $this->_getTracks();

        $this->playlist = new stdClass();
        $this->playlist->songs = [];        
        $index = 0;            
        foreach($this->tracks as $track)
        {
            $this->playlist->songs[$index++] = $track;
        }  
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($this->playlist,JSON_UNESCAPED_SLASHES);
        exit(0);                      
    }

    public function getTracks()
    {    
        if(!$this->oid)
            list($this->oid) = Helpers::getId();

        $this->album    = $this->_getAlbum();
        $this->tracks   = $this->_getTracks();
        $array = [];        
        $index = 0;            
        foreach($this->tracks as $track)
        {
            $array[$index++] = $track;
        }  
        return $array;                   
    }

    /**
     * Return the bitstream of an album thumbnail
     */
    public function thumbnail()
    {
        if(!$this->oid)
            list($this->oid) = Helpers::getId();

        $config    = Factory::getConfig();

        if (!$this->album)
            $this->album = $this->_getAlbum();            

        if($this->album)
        {
            if(file_exists($this->album->artwork))
            {        
                //If not using the symbolic link, copy the artwork in a local folder of the www and send it
                if(!$config->use_symlink)
                {
                    $path   = RBFY_CACHE . DIRECTORY_SEPARATOR . 'thumbnails';
                    $ext    = pathinfo($this->album->artwork,PATHINFO_EXTENSION);                

                    if(!file_exists($path . DIRECTORY_SEPARATOR . $this->oid . '.' . $ext)) {                    
                        $this->album->artwork = Helpers::createThumbnail($this->album->artwork , $path . DIRECTORY_SEPARATOR . $this->oid . '.' . $ext);
                    }

                    $time = date('r', filemtime($this->album->artwork));
                    header('Cache-Control: max-age=604800');
                    header('Last-Modified: ' . $time);
                    header('Content-Type: ' . mime_content_type($this->album->artwork));
                    header('Content-Length: ' . filesize($this->album->artwork));
                    readfile($this->album->artwork);
                    exit (0);                            
                }    
            }
        }
        header('HTTP/1.1 301 Moved Permanently');
        header('Location: ' . Factory::getAssets() . '/images/all-music.png');
        exit(0);        
    }

    /**
     * Get the properties of an album object
     * 
     * @param mixed $aid    album id
     * 
     * @return mixed        array of stdClass items containing the properties of the album
     */    
    protected function _getAlbum()
    {        
        if(!$this->oid)
            list($this->oid) = Helpers::getId();

        $config = Factory::getConfig();

        $sql = "SELECT `OB`.`ID` , `OB`.`OBJECT_ID`, `OB`.`NAME` , `DT`.`ID` `DID` , `DT`.`GENRE` , `DT`.`ARTIST`, `AA`.`PATH`
            FROM `OBJECTS` `OB` 
            LEFT JOIN `DETAILS` `DT` ON `OB`.`DETAIL_ID`=`DT`.`ID`
                LEFT JOIN `ALBUM_ART` `AA` ON `DT`.`ALBUM_ART`=`AA`.`ID`
            WHERE `OB`.`OBJECT_ID` = '" . $this->oid . "' LIMIT 1;";
            
        $this->database->query($sql);
        $rows = $this->database->loadRows(); 
        $data = $rows[0];

        $item = new stdClass();
        $item->id       = $data['ID'] ;
        $item->oid      = $data['OBJECT_ID'] ;
        $item->name     = Helpers::getFolderName($data['NAME']);
        $item->alias    = Helpers::encode($item->name);        
        $item->genre    = $data['GENRE'];
        $item->glink    = Helpers::getGenreLink($item->genre) ?: '#';
        $item->artist   = html_entity_decode($data['ARTIST']);
        $item->alink    = Helpers::getArtistLink($item->artist) ?: '#';
        $item->artwork  = $data['PATH'];
        $item->link     = Factory::Link('album','oid=' . $item->oid .':' . $item->alias);

        if($config->use_symlink){
            if(file_exists($item->artwork))
            {
                $item->thumbnail = Helpers::getRelArtwork($item->artwork);
            } else {
                $item->thumbnail = Factory::getAssets() . '/images/album.png';
            }
        } else {
            $item->thumbnail = Factory::Link('album.thumbnail','oid=' . $item->oid);                   
        }   
        return $item;
    }

    /**
     * Get the properties of discs from an album object     
     * 
     * @return array        array of disc numbers
     */
    protected function _getDiscs()
    {        
        if(!$this->oid)
            list($this->oid) = Helpers::getId();

        $sql = "SELECT `DT`.`DISC` FROM `OBJECTS` `OB` 
                LEFT JOIN `DETAILS` `DT` ON `OB`.`DETAIL_ID`=`DT`.`ID`
            WHERE `OB`.`PARENT_ID` = '$this->oid'
            GROUP BY `DT`.`DISC`
            ORDER BY `DT`.`DISC`;";
            
        $this->database->query($sql);
        return $this->database->loadRows(); 
    }    

/**
     * Get the list of tracks for a given album object
     * 
     * @param mixed $disc   disc number
     * 
     * @return array        array of stdClass items containing the properties of each track
     */
    private function _getTracks( $disc = null)
    {
        if(!$this->oid)
            list($this->oid) = Helpers::getId();
                
        $config     = Factory::getConfig();
        $className  = RBFY_CLASS_TRACK;

        $sql = "SELECT `OB`.`ID` , `OB`.`OBJECT_ID` , `OB`.`REF_ID` , `OB`.`NAME` , `DT`.* , `AA`.`PATH` `PICTURE` 
            FROM `OBJECTS` `OB`             
            LEFT JOIN `DETAILS` `DT` ON `OB`.`DETAIL_ID`=`DT`.`ID`
                LEFT JOIN `ALBUM_ART` `AA` ON `DT`.`ALBUM_ART`=`AA`.`ID`
            WHERE `OB`.`PARENT_ID` = '$this->oid'
            AND `OB`.`CLASS` = '$className' " . (!empty($disc)? " AND `DT`.`DISC` = '$disc' " : "") . " 
            ORDER BY `DT`.`DISC` ASC , `DT`.`TRACK` ASC ;";        

        $this->database->query($sql);
        $rows = $this->database->loadRows();   

        $array = [];
        foreach($rows as $row)
        {
            $item = new stdClass();
            $item->id           = $row['ID'] ;
            $item->oid          = $row['OBJECT_ID'] ;
            $item->title        = html_entity_decode($row['TITLE']);
            $item->album        = html_entity_decode($row['ALBUM']);
            $item->name         = html_entity_decode($row['NAME']);
            $item->alias        = Helpers::encode($item->title);
            $item->track        = $row['TRACK'];
            $item->path         = $row['PATH'];
            $item->genre        = $row['GENRE'];
            $item->picture      = $row['PICTURE'];
            $item->artist       = html_entity_decode($row['ARTIST']);
            $item->creator      = html_entity_decode($row['CREATOR']);
            $item->duration     = $row['DURATION'];
            $item->duration_ms  = Helpers::durationToMilliseconds($row['DURATION']);
            $item->duration_fm  = Helpers::formatMilliseconds($item->duration_ms);
            $item->bitrate      = $row['BITRATE'];
            $item->samplerate   = $row['SAMPLERATE'];
            $item->channels     = $row['CHANNELS'];
            $item->mime         = $row['MIME'];            
            $item->isfavorite   = $this->_isfavorite($item->oid);
            $item->link         = Factory::Link('track','oid=' . $item->oid);            
            $item->json         = Factory::Link('track.json','oid=' . $item->oid);  
        
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
            $array[] = $item;
        }
        return $array;
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