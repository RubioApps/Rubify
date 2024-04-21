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

class modelUpload extends Model
{
    public function display()
    {
        $this->data = $this->_data();        
        $this->page->title = Text::_('PROFILE');
        $this->page->data       = $this->data;
        parent::display();
    }

    public function form()
    {        
        $result   = ['error' => true , 'message' => Text::_('UPLOAD_ERROR') ];
        
        if(isset($_FILES['file']) && Factory::checkToken())
        {            
            $filename = basename($_FILES['file']['name']);
            $ext      = strtolower(pathinfo($_FILES['file']['name'],PATHINFO_EXTENSION));
            $allowed = ['mp3','m4a','webm','mp4'];

            if (in_array($ext, $allowed)) 
            {
                //Register the upload
                $upload = new Upload($this->user);

                if (move_uploaded_file($_FILES['file']['tmp_name'], $upload->getFolder() . DIRECTORY_SEPARATOR . $filename)) {

                    $result = ['error' => false , 'message' => Text::_('UPLOAD_SUCCESS') ];

                    //Register the upload
                    $upload->register($filename);
                } 
            }     
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($result);
            exit(0);
        }
        $this->page->setFile('upload.form.php');    
    }

    public function remove()
    {
        $file = Request::getVar('file','','GET');

        $upload = new Upload($this->user);        

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($upload->remove($file));
        exit(0);          
    }

    public function empty()
    {
        $upload = new Upload($this->user);        

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($upload->empty());
        exit(0);          
    }    

    protected function _data()
    {      
        $upload = new Upload($this->user); 
        $array  = $upload->getEntries();
        $model  = Factory::getModel('Track');

        foreach($array as &$item)
        {
            $sql = "SELECT `OB`.`OBJECT_ID` FROM `OBJECTS` `OB` , `DETAILS` `DT`
                WHERE `OB`.`DETAIL_ID` = `DT`.`ID` 
                AND `OB`.`CLASS` = '" . RBFY_CLASS_TRACK . "' 
                AND `DT`.`PATH` = '" . $upload->getFolder() . DIRECTORY_SEPARATOR . $item->file . "';";
                
            $this->database->query($sql);
            if($row = $this->database->loadRow())
            {                                
                $item->oid = $row['OBJECT_ID'];
                $item->link = Factory::Link('track','oid=' . $row['OBJECT_ID']);
                if($track = $model->get($row['OBJECT_ID']))
                {
                    foreach(get_object_vars($track) as $key => $value)
                    {                        
                        $item->$key = $value;
                    }
                }                
            }
        }
        return $array;
    }  
    
    /**
     * Remove the orphan files found in the upload folders    
     */
    protected function _purge()
    {
        $upload = new Upload($this->user);         
        
        if ($folder = opendir($upload->getFolder()))
        {
            while (false !== ($file = readdir($folder)))
            {        
                if ($file != "." && $file != "..") 
                {
                    $sql = "SELECT `OB`.`OBJECT_ID` FROM `OBJECTS` `OB` , `DETAILS` `DT`
                    WHERE `OB`.`DETAIL_ID` = `DT`.`ID` 
                    AND `DT`.`PATH` = '" . $upload->getFolder() . DIRECTORY_SEPARATOR . $file . "'
                    AND `OB`.`CLASS` = '" . RBFY_CLASS_TRACK . "';";

                    $this->database->query($sql);
                    $row = $this->database->loadRow();
                    if($row === false)
                    {   
                        $upload->remove($file);
                        unlink($upload->getFolder() . DIRECTORY_SEPARATOR . $file);
                    }
                }
            }        
            closedir($folder);
        }       
    }

}