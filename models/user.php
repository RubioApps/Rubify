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

use Rubify\Framework\Helpers;
use Rubify\Framework\User;
use Rubify\Framework\Language\Text;

class modelUser extends Model
{
    public function display()
    {
        $this->data = [];
        $this->data['playlists'] = $this->_playlists();
        $this->data['history'] = $this->_history();
        $this->data['downloads'] = $this->_downloads();
        $this->data['uploads'] = $this->_uploads();        

        $this->page->title = Text::_('PROFILE');
        $this->page->data       = $this->data;
        parent::display();
    }

    public function list()
    {
        $this->data = [];
        $this->data = $this->_users();   
        $this->page->setFile('user.admin.php');
        $this->page->title = Text::_('ADMIN_USERS');
        $this->page->data  = $this->data;
        parent::display();
    }    

    public function view()
    {
        $uid  = Request::getVar('uid',null,'GET');
        if($uid)
        {
            $user = new User(); 
            $this->page->data = $user->get($uid);       
        } else {
            $this->page->data = new \stdClass;
            $this->page->data->uid = null;
        }
        $this->page->setFile('user.view.php');
        
        parent::display();
    }    

    public function add()
    {
        $result = ['error' => true , 'message' => Text::_('ADMIN_USER_ADDED_ERROR')];        

        if(is_array($_POST) && isset($_POST['user']) && isset($_POST['password']) && isset($_POST['level']))
        {   
            $user   = new User();         
            $uid    = $_POST['user'];
            $pwd    = $_POST['password'];            
            $lvl    = $_POST['level'];  
                        
            //Check the password
            if(Factory::checkToken() && $user->validPassword($pwd))
            {                
                if($user->add($uid,$pwd,$lvl))
                {
                    $result = ['error' => false , 'message' => Text::_('ADMIN_USER_ADDED_SUCCESS') ];
                }                  
            }
        }  
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($result);
        exit(0);              
    }

    public function update()
    {
        $result = ['error' => true , 'message' => Text::_('CREDENTIALS_ERROR')];        

        if(is_array($_POST) && isset($_POST['user']) && isset($_POST['password']))
        {   
            $user   = new User();         
            $uid    = $_POST['user'];
            $pwd    = $_POST['password'];            
                        
            //Check the password
            if(Factory::checkToken() && $user->validPassword($pwd))
            {                
                if($user->update($uid,$pwd))
                {
                    $result = ['error' => false , 'message' => Text::_('CREDENTIALS_SUCCESS') ];
                }                  
            }
        }  
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($result);
        exit(0);              
    }

    public function remove()
    {
        $result = ['error' => true , 'message' => Text::_('ADMIN_USER_REMOVED_ERROR')];        

        if(is_array($_POST) && isset($_POST['user']))
        {   
            $user   = new User();         
            $uid    = $_POST['user'];
                        
            //Check the password
            if(Factory::checkToken())
            {                
                if($user->remove($uid))
                {
                    $result = ['error' => false , 'message' => Text::_('ADMIN_USER_REMOVED_SUCCESS') ];
                }                  
            }
        }  
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($result);
        exit(0);              
    }


    protected function _users()
    {
        $user = new User();        
        return $user->getList();          
    }

    protected function _playlists()
    {
        $playlist = new Playlist($this->user);
        $array = $playlist->list();
        return $array;

    }

    protected function _history()
    {
        $history = new History($this->user);
        $array = $history->load();

        $day = new \DateInterval('P1D');
        $scope = new \DateInterval('P1M');
        $scope->invert = true;

        $start  = (new \DateTime())->add($scope);
        $end    = new \DateTime();
        $data   = [];

        while($start <= $end)
        {
            $key = $start->format('d M');
            $data[$key] = 0;
            foreach($array as $event)
            {
                $point = new \DateTime($event->date);
                if($key == $point->format('d M')) {
                    $data[$key]++;
                }
            }
            $start->add($day);
        }        
        return $data;
    }

    protected function _downloads()
    {
        $register   = RBFY_USERS . DIRECTORY_SEPARATOR . 'exports' . DIRECTORY_SEPARATOR . 'registry.xml';
        $array      = [];

        if(file_exists($register) && ($content=file_get_contents($register))) 
        {                                          
            $xml    = new \SimpleXMLElement($content);                
            $nodes  = $xml->xpath("//entry[@user='" . $this->user . "']");  
            if($nodes)
            {                
                foreach($nodes as $node)
                {
                    $attr = $node->attributes();
                    $item = new \stdClass();
                    foreach($attr as $k=>$v)                    
                        $item->$k = $v;

                    $item->created   = date_create($item->created);
                    $item->expiry    = date_create($item->expiry);
                    $array[] = $item;
                }                
            }
        }
        return $array;
    }      

    protected function _uploads()
    {      
        $upload = new Upload($this->user); 
        $registry = $upload->getRegistry();
        return $registry['summary'];
    }    

}