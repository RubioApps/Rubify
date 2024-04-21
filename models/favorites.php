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

use Rubify\Framework\Playlist;
use Rubify\Framework\Language\Text;

class modelFavorites extends Model
{
    public function display()
    {
        $this->page->title      = Text::_('FAVORITES');
        parent::display();
    }

    public function json()
    {
        $playlist  = new Playlist( $this->user );
        $favid     = $playlist->favoritesID();

        $playlist->select($favid);
        $array = $playlist ->load();
        
        header('Content-Type: application/json; charset=utf-8');  
        echo json_encode($array);
        exit(0);
    }

    public function find()
    {
        list($oid) = Helpers::getId();  

        $playlist  = new Playlist( $this->user );
        $favid     = $playlist->favoritesID();

        $playlist->select($favid);
        
        $result = ['success' => false , 'message' => Text::_('FAV_NOTFOUND')];         
        if($playlist->exists($oid))
            $result = ['success' => true , 'message' => Text::_('FAV_FOUND') ];        
        
        header('Content-Type: application/json; charset=utf-8');  
        echo json_encode( $result );
        exit(0);
    }

    public function push()
    {
        list($oid) = Helpers::getId();
        
        $playlist  = new Playlist( $this->user );
        $favid     = $playlist->favoritesID();

        $playlist->select($favid);  

        $result = ['success' => false , 'message' => Text::_('FAV_ADDED_ERROR')];         
        if($playlist->push($oid))
            $result = ['success' => true , 'message' => Text::_('FAV_ADDED_SUCCESS') ];        
        
        header('Content-Type: application/json; charset=utf-8');  
        echo json_encode( $result );
        exit(0);
    }
    public function pop()
    {
        list($oid) = Helpers::getId();

        $playlist  = new Playlist( $this->user );
        $favid     = $playlist->favoritesID();
        
        $playlist->select($favid);  
        
        $result = ['success' => false , 'message' => Text::_('FAV_REMOVED_ERROR')];         
        if($playlist->pop($oid))
            $result = ['success' => true , 'message' => Text::_('FAV_REMOVED_SUCCESS') ];              

        header('Content-Type: application/json; charset=utf-8');  
        echo json_encode( $result );
        exit(0);
    }    
}