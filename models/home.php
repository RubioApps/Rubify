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

use Rubify\Framework\Language\Text;

class modelHome extends Model
{
    public function display()
    {
        //Menu 
        $allowed = [
            'Album'     => 'albums',
            'All Music' => 'tracks',
            'Artist'    => 'artists',
            'Genre'     => 'genres',
            'Folders'   => 'folders'
        ];  

        $this->page->title      = Text::_('HOME');
        $this->_stats();        
    
        $sql = "SELECT `OBJECT_ID` FROM `OBJECTS` 
            WHERE `CLASS` = 'container.storageFolder'
            AND `PARENT_ID`='0' AND `NAME`= 'Music' 
            ";

        $this->database->query($sql);
        $music = $this->database->loadResult();     
        
        $sql = "SELECT `ID`,`OBJECT_ID`,`NAME`,`CLASS` FROM `OBJECTS` WHERE `PARENT_ID`= '$music'";     
        $this->database->query($sql);
        $rows = $this->database->loadRows();   

        $array  = [];                    
        foreach($rows as $row)
        {
            if(!key_exists($row['NAME'],$allowed))
                continue;

            $item = new \stdClass();
            $item->id       = $row['ID'] ;
            $item->oid      = $row['OBJECT_ID'] ;
            $item->label    = $allowed[$row['NAME']];
            $item->name     = $row['NAME'];
            $item->key      = preg_replace('/[\W]/','',strtolower($item->name));
            $item->alias    = Helpers::encode($item->name);
            $item->link     = Factory::Link('browse','oid=' . $item->oid .':' . $item->alias);
            $item->image    = Factory::getAssets() . '/images/' . $item->alias . '.png';

            //Bind stats
            if(isset($this->data['stats'][$item->key]))
                $item->stats = $this->data['stats'][$item->key];
            else
                $item->stats = '';

            $array[$item->name] = $item;
        }        
        $this->data['menu'] = $array;

        //Recent tracks
        $className = RBFY_CLASS_TRACK;
        $sql = "SELECT `OB`.`OBJECT_ID` FROM `OBJECTS` `OB` 
        LEFT JOIN `DETAILS` `DT` ON `OB`.`DETAIL_ID`=`DT`.`ID` 
        WHERE `OB`.`CLASS` = '$className' 
        AND `OB`.`REF_ID` IS NOT NULL
        GROUP BY `DT`.`PATH`
        ORDER BY `DT`.`TIMESTAMP` DESC LIMIT 24"
        ;
        $this->database->query($sql);
        $rows = $this->database->loadRows(); 
        $track = Factory::getModel('track');
        $array  = []; 
        foreach($rows as $row)
        {
            $array[] = $track->get($row['OBJECT_ID']);
        }
        $this->data['recent_tracks'] = $array;
  
               
        $this->page->data       = $this->data;
        parent::display();
    }

    protected function _stats()
    {
        if(!isset($this->data['stats']))
            $this->data['stats'] = [];
        //Albums
        $sql = "SELECT COUNT(*) `TOTAL` FROM `OBJECTS` WHERE `CLASS`='" . RBFY_CLASS_ALBUM_MUSIC . "'";
        $this->database->query($sql);
        $row = $this->database->loadRow();
        $this->data['stats']['album'] = $row['TOTAL'];

        //Tracks
        $sql = "SELECT COUNT(`OB`.`ID`) `TOTAL` FROM `OBJECTS` `OB` , `DETAILS` `DT` 
            WHERE `OB`.`DETAIL_ID` = `DT`.`ID`
            AND `OB`.`CLASS`='" . RBFY_CLASS_TRACK . "'
            GROUP BY `OB`.`REF_ID`
            ";
        $this->database->query($sql);
        $row = $this->database->loadRow();
        $this->data['stats']['allmusic'] = $row['TOTAL'];        

        //Artists
        $sql = "SELECT COUNT(*) `TOTAL` FROM `OBJECTS` WHERE `CLASS`='" . RBFY_CLASS_ARTIST . "'";
        $this->database->query($sql);
        $row = $this->database->loadRow();        
        $this->data['stats']['artist'] = $row['TOTAL'];

        //Genres
        $sql = "SELECT COUNT(*) `TOTAL` FROM `OBJECTS` WHERE `CLASS`='" . RBFY_CLASS_GENRE . "'";
        $this->database->query($sql);
        $row = $this->database->loadRow();          
        $this->data['stats']['genre'] = $row['TOTAL'];

        //Folders
        $sql = "SELECT COUNT(*) `TOTAL` FROM `OBJECTS` WHERE `CLASS`='" . RBFY_CLASS_FOLDER . "'";
        $this->database->query($sql);
        $row = $this->database->loadRow();          
        $this->data['stats']['folders'] = $row['TOTAL'];        
    }


}