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

use Rubify\Framework\Helpers;
use Rubify\Framework\Request;
use Rubify\Framework\Language\Text;

class modelArtist extends Model
{

    public function display()
    {
        $this->page->data       = $this->_data();
        parent::display();
    }

    public function thumbnail()
    {
        $config  = Factory::getConfig();
        $name   = Request::getString('name',null,'GET');
        if(!$name)
            return Factory::getAssets() . '/images/artist.png';  

        $name = urldecode($name); 
        $name = trim(strtolower($name));
        $filename = preg_replace('/[^a-zA-Z0-9\-\._]/', '', $name);

        //If the file exists, return it
        if (file_exists(RBFY_CACHE . DIRECTORY_SEPARATOR . 'artists' . DIRECTORY_SEPARATOR . $filename . '.webp'))
        {
            header('HTTP/1.1 301 Moved Permanently');
            header('Location: ' . $config->live_site . '/cache/artists/' . $filename . '.webp');
            exit(0);            
        }

        if(file_exists(Helpers::getArtwork($name))){
            $data = file_get_contents(Helpers::getArtwork($name));        
            header('Content-type: image/webp');
            echo $data;        
            exit(0);
        } else {
            header('HTTP/1.1 301 Moved Permanently');
            header('Location: ' . Factory::getAssets() . '/images/artist.png');
            exit(0);
        }
    }

    protected function _data()
    {
        return [];    
    }

}