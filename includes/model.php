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
use Rubify\Framework\Request;
use Rubify\Framework\Pagination;
use Rubify\Framework\Language\Text;

class Model
{
    protected $config;
    protected $params;
    protected $user;
    protected $database;
    protected $page;
    protected $data;
    protected $link;
    protected $pagination;
    public function __construct($params = null)
    {
        // Get database
        $this->database = Factory::getDatabase();

        if(!$this->database){
            @trigger_error(
                    'this function needs to set a database to the mode. Please, use setDatabase first.',
                    E_USER_ERROR
                );
            die();
        }

        // Get the parameters from the router
        if(!empty($params))
        {
            $this->params   = new \stdClass;
            foreach($params as $k=>$p)
            {
                if(is_object($p) && $k === 'config'){
                    $this->config = $p;

                } else {
                    $this->params->$k = $p['value'];
                }
            }
        }

        // Set the user
        $this->user = $this->params->user ?? null;

        // Get the query string
        $input = Request::get('GET');
        foreach($input as $k=>$p)
        {
            if(empty($this->params->$k))
            {
                if(strstr($p , ':') !== false)
                {
                    $alias = $k . '_alias';
                    $parts = explode(':' , $p);
                    $this->params->$k = $parts[0];
                    $this->params->$alias = $parts[1];
                } else {
                    $this->params->$k = $p;
                }
            }
        }

        // Get the page
        $this->page                 = Factory::getPage();
        $this->page->title          = $this->config->sitename;
    }

    public function __destruct()
    {
        unset($this->page);
    }

    public function display()
    {
        $this->page->menu   =$this->_menu();
        return true;
    } 

    protected function _data()
    {
        return $this->data;
    }

    protected function _link()
    {
        return $this->link;
    }

    protected function _menu()
    {
        //Menu 
        $allowed = [
            'Album'     => 'albums',
            'All Music' => 'tracks',
            'Artist'    => 'artists',
            'Genre'     => 'genres',
            'Folders'   => 'folders'
        ]; 
                
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
            $item->id = $row['ID'] ;
            $item->oid = $row['OBJECT_ID'] ;
            $item->label    = $allowed[$row['NAME']];
            $item->name     = $row['NAME'];
            $item->alias = Helpers::encode($item->name);
            $item->link = Factory::Link('browse','oid=' . $item->oid .':' . $item->alias);
            $item->image= Factory::getAssets() . '/images/' . $item->alias . '.png';
            $array[$item->name] = $item;
        }    

        //Add Playlists
        $item = new \stdClass();
        $item->id = 0;
        $item->oid = '$0' ;
        $item->label = 'playlists';
        $item->name = 'playlists';        
        $item->alias = Helpers::encode($item->name);
        $item->link = Factory::Link('playlist');
        $item->image= Factory::getAssets() . '/images/' . $item->alias . '.png';
        $array[] = $item;    

        //Add History
        $item = new \stdClass();
        $item->id = 0;
        $item->oid = '$0' ;
        $item->label = 'history';
        $item->name = 'history';
        $item->alias = Helpers::encode($item->name);
        $item->link = Factory::Link('history');
        $item->image= Factory::getAssets() . '/images/' . $item->alias . '.png';
        $array[] = $item;  
        
        //Add User Profile
        $item = new \stdClass();
        $item->id = 0;
        $item->oid = '$0' ;
        $item->label = 'profile';
        $item->name = 'profile';
        $item->alias = Helpers::encode($item->name);
        $item->link = Factory::Link('user');
        $item->image= Factory::getAssets() . '/images/' . $item->alias . '.png';
        $array[] = $item;         

        return $array;
    }

    protected function _pagination()
    {
        $offset = Request::getInt('offset',0,'GET');
        $limit  = Request::getInt('limit' , $this->config->list_limit ,'GET');

        if($this->data){
            $total  = count($this->data);            
            $this->page->data = array_slice($this->data , (int) $offset , (int) $limit);
            $this->pagination = new Pagination( $total , (int) $offset, (int) $limit);

            // Clean-up redondant parameters (join id and alias)
            $array = get_object_vars($this->params);
            foreach($array as $key => $value)
            {
                if(isset($array[$key . '_alias']))
                {
                    $array[$key] .= ':' . $array[$key. '_alias'];
                    unset($array[$key. '_alias']);
                }
            }

            // Add the parameters to the pagination (except offset and limit)
            foreach($array as $key => $value)
            {
                if($key !== 'offset' && $key !== 'limit')
                    $this->pagination->setAdditionalUrlParam( $key ,$value);
            }

        } else {
            $this->page->data = [];
            $this->pagination = new Pagination( 0 , (int) $offset, (int) $limit);
        }     
        return $this->pagination;
    }

    protected function _term( $task = null)
    {
        if(empty($task))
            $task = Factory::getTask();
        
        switch($task){
            case 'all-music':
                $task = 'track';
                break;
            case 'album':
                $task = 'album';
                break;
            case 'artist':
                $task = 'browse';
                break;
            case 'genre':
                $task = 'genre';                
                break;                
            default:            
        }

        $term    = $this->params->term;
        $result = [];
        if($term){
            foreach($this->data as $item)
            {
                if(preg_match("/$term/im" , $item->name , $match))
                {
                    $item->link    = Factory::Link($task , 'oid=' . $item->oid . ':' . $item->alias);
                    $result[] = $item;
                }
            }
        } else {
            $result = null;
        }
        return json_encode($result);
    }

}

