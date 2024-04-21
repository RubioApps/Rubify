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

class modelGenre extends Model
{

    public function display()
    {
        $this->page->title      = Text::_('GENRES');
        $this->page->data       = $this->_data();
        $this->page->pagination = $this->_pagination();
        parent::display();
    }

    public function json()
    {        
        $sql = "SELECT `OB`.`OBJECT_ID` , `DT`.`TITLE`
        FROM `OBJECTS` `OB`, `DETAILS` `DT`
        WHERE `OB`.`DETAIL_ID` = `DT`.`ID`
        AND `OB`.`CLASS` = '" . RBFY_CLASS_GENRE . "'";

        $term = Request::getVar('term',null,'GET');
        if($term) $sql .= " AND `DT`.`TITLE` LIKE '" . $term . "%'";        
            
        $this->database->query($sql);
        $rows   = $this->database->loadRows(); 

        $array = [];
        foreach($rows as $row)
        {
            $item = new \stdClass();
            $item->id = $row['OBJECT_ID'];
            $item->value = $row['TITLE'];
            $array[] = $item;     
        }
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($array);
        exit(0);              
    }

    protected function _data()
    {
        list($oid,$alias) = Helpers::getId();

        $sql = "SELECT `OB`.`ID` , `OB`.`OBJECT_ID`, `OB`.`NAME` , `DT`.`GENRE` , `DT`.`ARTIST`, `AA`.`PATH` `PICTURE`
            FROM `OBJECTS` `OB` 
                LEFT JOIN `DETAILS` `DT` ON `OB`.`DETAIL_ID`=`DT`.`ID`
                    LEFT JOIN `ALBUM_ART` `AA` ON `DT`.`ALBUM_ART`=`AA`.`ID`
            WHERE `OB`.`PARENT_ID` = '" . $oid . "';";
            
        $this->database->query($sql);
        $rows   = $this->database->loadRows(); 
        $array  = [];
        foreach($rows as $row)
        {
            $item = new \stdClass();
            $item->id       = $row['ID'] ;
            $item->oid      = $row['OBJECT_ID'] ;
            $item->name     = Helpers::getFolderName($row['NAME']);
            $item->alias    = Helpers::encode($item->name);
            $item->genre    = $row['GENRE'];
            $item->artist   = $row['ARTIST'];
            $item->artwork  = $row['PICTURE'];        
            $item->link     = Factory::Link('browse','oid=' . $item->oid .':' . $item->alias);
            $item->thumbnail = Factory::Link('artist.thumbnail','name=' . $item->name);
            $array[] = $item;
        }

        $this->data = $array;
        return $this->data; 
    }

}