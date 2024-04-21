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

defined('_RBFYEXEC') or die;

use Rubify\Framework\Request;
use Rubify\Framework\Helpers;

class modelAllmusic extends Model
{
    public $album;
    public $discs;
    public $tracks;
    public $playlist;

    public function display()
    {
        $id     = Request::getVar('id',0,'GET');
        $parts  = explode(':',$id);
        $aid    = (int) $parts[0];
        $format = $this->params->format;
             
        $this->album    = $this->_getAlbum($aid);
        $this->discs    = $this->_getDiscs($this->album->oid);
        $this->album->discs  = count($this->discs);
        $this->album->tracks = 0;   
        $this->album->duration_ms = 0;      
        $this->tracks   = [];

        $this->playlist = new \stdClass();
        $this->playlist->songs = [];        
        $index = 0;
        foreach($this->discs as $k=>$v)
        {   
            $disc = $v['DISC'];        
            $this->tracks['CD'.($k+1)] = $this->_getTracks($this->album->oid , $disc);           
            $this->album->tracks += count($this->tracks['CD'.($k+1)]);
            
            foreach($this->tracks['CD'.($k+1)] as $track)
            {
                $this->playlist->songs[$index++] = $track;
                $this->album->duration_ms += $track->duration_ms;
            }
        }
        $this->data['album']    = $this->album; 
        $this->data['tracks']   = $this->tracks;         

        $this->page->data       = $this->data;
        $this->page->title      = $this->album->name;        

        parent::display();
    }

    public function json()
    {
        $aid     = Request::getVar('id',0,'GET');       
        $this->album    = $this->_getAlbum($aid);
        $this->tracks   = $this->_getTracks($this->album->oid);

        $this->playlist = new \stdClass();
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

    /**
     * Return the bitstream of an album thumbnail
     */
    public function thumbnail()
    {
        $aid            = Request::getVar('id',0,'GET');       
        $this->album    = $this->_getAlbum($aid);      

        if(file_exists($this->album->artwork))
        {
            header('Content-Type: ' .  mime_content_type($this->album->artwork));
            header('Content-Length: ' . filesize($this->album->artwork));
            readfile($this->album->artwork);
        } 
        exit;
    }

    /**
     * Get the properties of an album object
     * 
     * @param mixed $aid    album id
     * 
     * @return mixed        array of stdClass items containing the properties of the album
     */    
    protected function _getAlbum( $aid = null)
    {        
        $sql = "SELECT `OB`.`ID` , `OB`.`OBJECT_ID`, `OB`.`NAME` , `DT`.`GENRE` , `DT`.`ARTIST`, `AA`.`PATH` 
            FROM `OBJECTS` `OB` 
            LEFT JOIN `DETAILS` `DT` ON `OB`.`DETAIL_ID`=`DT`.`ID`
                LEFT JOIN `ALBUM_ART` `AA` ON `DT`.`ALBUM_ART`=`AA`.`ID`
            WHERE `OB`.`ID` = '" . $aid . "' LIMIT 1;";
            
        $this->database->query($sql);
        $rows = $this->database->loadRows(); 
        $data = $rows[0];
            
        $item = new \stdClass();
        $item->id       = $data['ID'] ;
        $item->oid      = $data['OBJECT_ID'] ;
        $item->name     = html_entity_decode($data['NAME']);
        $item->alias    = Helpers::encode($item->name);
        $item->genre    = $data['GENRE'];
        $item->artist   = $data['ARTIST'];
        $item->artwork  = $data['PATH'];        
        $item->link     = Factory::Link('album','id=' . $item->id .':' . $item->alias);
        $item->thumbnail= Factory::Link('album.thumbnail','id=' . $aid);
      
            
        return $item;
    }


    /**
     * Get the properties of discs from an album object
     * 
     * @param mixed $oid    album object id
     * 
     * @return array        array of disc numbers
     */
    protected function _getDiscs( $oid = null)
    {        
        $sql = "SELECT `DT`.`DISC` FROM `OBJECTS` `OB` 
                LEFT JOIN `DETAILS` `DT` ON `OB`.`DETAIL_ID`=`DT`.`ID`
            WHERE `OB`.`PARENT_ID` = '$oid'
            GROUP BY `DT`.`DISC`
            ORDER BY `DT`.`DISC`;";
            
        $this->database->query($sql);
        return $this->database->loadRows(); 
    }    

    /**
     * Get the list of tracks for a given album object
     * 
     * @param int $oid      album object id
     * @param mixed $disc   disc number
     * 
     * @return array        array of stdClass items containing the properties of each track
     */
    protected function _getTracks( $oid , $disc = null)
    {
        $config = Factory::getConfig();
        $className = RBFY_CLASS_TRACK;

        $sql = "SELECT `OB`.`ID` , `OB`.`OBJECT_ID`, `OB`.`NAME` , `DT`.* , `AA`.`PATH` `PICTURE`
            FROM `OBJECTS` `OB` 
            LEFT JOIN `DETAILS` `DT` ON `OB`.`DETAIL_ID`=`DT`.`ID`
                LEFT JOIN `ALBUM_ART` `AA` ON `DT`.`ALBUM_ART`=`AA`.`ID`
            WHERE `OB`.`PARENT_ID` = '$oid'
            AND `OB`.`CLASS` = '$className' " . (!empty($disc)? " AND `DT`.`DISC` = '$disc' " : "") . " 
            ORDER BY `DT`.`DISC` ASC , `DT`.`TRACK` ASC ;";
            
        $this->database->query($sql);
        $rows = $this->database->loadRows();     
        $array = [];
        foreach($rows as $row)
        {
            $item = new \stdClass();
            $item->id           = $row['ID'] ;
            $item->oid          = $row['OBJECT_ID'] ;
            $item->name         = html_entity_decode($row['NAME']);
            $item->alias        = Helpers::encode($item->name);
            $item->track        = $row['TRACK'];
            $item->path         = $row['PATH'];
            $item->genre        = $row['GENRE'];
            $item->artist       = $row['ARTIST'];
            $item->creator      = $row['CREATOR'];
            $item->duration     = $row['DURATION'];
            $item->duration_ms  = Helpers::durationToMilliseconds($item->duration);
            $item->duration_fm  = Helpers::formatMilliseconds($item->duration_ms);
            $item->bitrate      = $row['BITRATE'];
            $item->samplerate   = $row['SAMPLERATE'];
            $item->channels     = $row['CHANNELS'];
            $item->mime         = $row['MIME'];            
            $item->link         = Factory::Link('track','oid=' . $item->oid);
            $item->audio        = Factory::Link('track.audio','oid=' . $item->oid);            
            $item->json         = Factory::Link('track.json','oid=' . $item->oid);  

            if(!empty($row['PICTURE']))
                $item->thumbnail = $config->live_site . '/artwork' . substr($row['PICTURE'] , strlen($config->minidlna['dir'] . '/art_cache'));
            else
                $item->thumbnail = $this->album->thumbnail;   
                                      
            $array[] = $item;
        }
        return $array;
    }

}