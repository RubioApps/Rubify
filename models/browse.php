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

use Rubify\Framework\Factory;
use Rubify\Framework\Helpers;
use Rubify\Framework\Language\Text;

class modelBrowse extends Model
{
    var $task;

    public function display()
    {
        $this->page->title      = Text::_('HOME');
        $this->page->data       = $this->_data();

        // Build pagination
        $this->page->pagination = $this->_pagination();     
        parent::display();
    }

    public function search()
    {
        list($oid , $this->task) = Helpers::getId();

        if($this->_data() && $this->params->format==='json'){   
            header('Content-Type: application/json; charset=utf-8');    
            echo $this->_term($this->task);
            exit(0);                
        }           
    }    

    public function _data()
    {
        list($oid , $this->task) = Helpers::getId();    

        //Get current object's parent ID
        $sql = "SELECT `PARENT_ID` FROM `OBJECTS` WHERE `OBJECT_ID` = '$oid';";
        $this->database->query($sql);
        $row = $this->database->loadRow();
        $pid = $row['PARENT_ID'];

        //Get the parent
        $sql = "SELECT `ID` , `OBJECT_ID` , `NAME` , `CLASS` FROM `OBJECTS` WHERE `OBJECT_ID` = '$pid';";
        $this->database->query($sql);
        $row = $this->database->loadRow();
        
        $parent = new \stdClass();
        $parent->oid    = $row['OBJECT_ID'] ;
        $parent->title  = Helpers::getFolderName($row['NAME']);
        $parent->name   = Helpers::getFolderName($row['NAME']);
        $parent->alias  = Helpers::encode($parent->name);                
        $parent->link   = Factory::Link('browse','oid=' . $parent->oid . ':' . $parent->alias);        

        //Get children
        $sql = "SELECT `ID` , `OBJECT_ID` , `NAME` , `CLASS` FROM `OBJECTS` WHERE `PARENT_ID` = '$oid' ORDER BY `REF_ID`, `NAME`;";
        $this->database->query($sql);        
        $rows  = $this->database->loadRows();  

        //If no children, we are in a song level: redirect
        if(!count($rows))   
        {
            $oid    = Request::getVar('oid',0,'GET');  
            header('Location: ' . Factory::Link('track','oid=' . $oid));
            exit(0);
        } 
        
        $className  = $rows[0]['CLASS'];
        $array      = [];
        foreach($rows as $row)
        {
            $item = new \stdClass();
            $item->id       = $row['ID'] ;
            $item->oid      = $row['OBJECT_ID'] ;
            $item->title    = Helpers::getFolderName($row['NAME']);
            $item->name     = Helpers::getFolderName($row['NAME']);
            $item->alias    = Helpers::encode($item->name);
            $item->link     = '#';
            $item->thumbnail= Factory::getAssets() . '/images/folders.png';
            $array[] = $item;
        }        
        
        switch($className)
        {
            case RBFY_CLASS_ALBUM:
            case RBFY_CLASS_ALBUM_MUSIC:
                $this->_getAlbums($array);
                break;
            case RBFY_CLASS_TRACK: 
                $this->_getSongs($array);   
                break;
            case RBFY_CLASS_FOLDER:
                $this->_getFolders($array);
                $path = explode('$',$oid);
                if(count($path)>2)
                    $this->page->parent = $parent;                                  
                break;
            case RBFY_CLASS_GENRE:
                $this->_getGenres($array);                  
                break;  
            case RBFY_CLASS_ARTIST:
                $this->_getArtists($array);                                       
                break;                                  
            case RBFY_CLASS_PLAYLIST:
                $this->_getPlaylists($array);                
                break;                 
            default:                                       
        }        
        
        $this->data = $array;        
        return $this->data;
    }

    protected function _getAlbums( &$array )
    {
        $config = Factory::getConfig();
        
        foreach($array as $item)
        {
            $sql = "SELECT `OB`.`OBJECT_ID` , `DT`.`GENRE` , `DT`.`ARTIST`, `AA`.`PATH` `PICTURE` FROM `OBJECTS` `OB` 
                LEFT JOIN `DETAILS` `DT` ON `OB`.`DETAIL_ID`=`DT`.`ID`
                    LEFT JOIN `ALBUM_ART` `AA` ON `DT`.`ALBUM_ART`=`AA`.`ID`
                WHERE `OB`.`ID` = '" . $item->id . "';";
            
            $this->database->query($sql);
            $data = $this->database->loadRow(); 
            
            $item->oid      = $data['OBJECT_ID'];
            $item->genre    = $data['GENRE'];
            $item->artist   = $data['ARTIST'];
            $item->artwork  = $data['PICTURE'];
            $item->link     = Factory::Link('album','oid=' . $item->oid .':' . $item->alias);

            if($config->use_symlink){
                if(file_exists($item->artwork))
                {
                    $item->thumbnail = Helpers::getRelArtwork($item->artwork);
                } else {
                    $item->thumbnail = Factory::getAssets() . '/images/album.png';
                }
            } else {
                $item->thumbnail    = Factory::Link('album.thumbnail','oid=' . $item->oid);                  
            }          
        }
    }

    protected function _getSongs( &$array )
    {
        $config = Factory::getConfig();

        foreach($array as $item)
        {
            $sql = "SELECT `OB`.`OBJECT_ID`, `OB`.`NAME` , `DT`.* , `AA`.`PATH` `PICTURE`
            FROM `OBJECTS` `OB` 
            LEFT JOIN `DETAILS` `DT` ON `OB`.`DETAIL_ID`=`DT`.`ID`
                LEFT JOIN `ALBUM_ART` `AA` ON `DT`.`ALBUM_ART`=`AA`.`ID`
            WHERE `OB`.`ID` = '" . $item->id . "';";
            
            $this->database->query($sql);
            $data = $this->database->loadRow(); 

            $item->oid          = $data['OBJECT_ID'];
            $item->title        = Helpers::getFolderName($data['TITLE']);
            $item->name         = Helpers::getFolderName($data['NAME']);         
            $item->alias        = Helpers::encode($item->title);            
            $item->genre        = trim($data['GENRE']);
            $item->artist       = trim($data['ARTIST']);
            $item->artwork      = $data['PICTURE'];
            $item->duration     = $data['DURATION'];
            $item->duration_ms  = Helpers::durationToMilliseconds($data['DURATION']);
            $item->duration_fm  = Helpers::formatMilliseconds($item->duration_ms);
            $item->bitrate      = $data['BITRATE'];
            $item->samplerate   = $data['SAMPLERATE'];
            $item->channels     = $data['CHANNELS'];
            $item->mime         = $data['MIME'];                    
            $item->link         = Factory::Link('track','oid=' . $item->oid .':' . $item->alias);        

            if($config->use_symlink){
                if(file_exists($item->artwork))
                {
                    $item->thumbnail = Helpers::getRelArtwork($item->artwork);
                } else {
                    $item->thumbnail = Factory::getAssets() . '/images/all-music.png';
                }
            } else {
                $item->thumbnail    = Factory::Link('track.thumbnail','oid=' . $item->oid);                  
            }      
        }
    }    

    protected function _getFolders( &$array )
    {    
        foreach($array as $item)
        {                
            $item->link         = Factory::Link('browse','oid=' . $item->oid .':' . $item->alias);
            $item->thumbnail    = Factory::getAssets() . '/images/folders.png';        
        }
    }

    protected function _getArtists( &$array )
    {    
        foreach($array as $item)
        {                
            $item->link         = Factory::Link('browse','oid=' . $item->oid .':' . $item->alias);
            $item->thumbnail    = Factory::Link('artist.thumbnail','name=' . urlencode($item->title));
        }
    }

    protected function _getGenres( &$array )
    {    
        $config = Factory::getConfig();
        $icons  = RBFY_THEMES . DIRECTORY_SEPARATOR . $config->theme . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'images';
        foreach($array as $item)
        {                      
            $item->link     = Factory::Link('genre','oid=' . $item->oid .':' . $item->alias);
            $list = [];
            if(strstr($item->name,'/'))
                $list = explode('/' , $item->name);
            else
                $list[] = $item->name;

            foreach($list as $label)
            {                
                $label = trim(strtolower($label));
                $icon_name = Helpers::encode($label);
                if(file_exists($icons . DIRECTORY_SEPARATOR . $icon_name . '.png'))
                {
                    $item->thumbnail    = Factory::getAssets() . '/images/' . $icon_name . '.png';
                    break;
                } else {
                    $item->thumbnail    = Factory::getAssets() . '/images/genre.png';
                }
            }
        }
    }    


    protected function _getPlaylists( &$array )
    {
        foreach($array as $item)
        {
            $parts = explode('$',$item->oid);
            $index = array_pop($parts);

            $sql = "SELECT * FROM `PLAYLISTS` WHERE `ID` = '" . $index . "';";            
            $this->database->query($sql);
            $data = $this->database->loadRow(); 

            if(!$data['FOUND']){
                unset($item);                
            }
            /*
            [ID] => 1
            [NAME] => Reggatta De Blanc
            [PATH] => /storage/samba/dlna/music/Rock/The Police/1979 The Police - Reggatta De Blanc/Reggatta De Blanc.m3u
            [ITEMS] => 11
            [FOUND] => 0
            [TIMESTAMP] => 1707590406            
            */
        }
    }    

}