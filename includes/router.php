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

class Router
{
    protected $params;
    protected $model;
    protected $format;

    public function __construct( &$params )
    {
        $this->params = &$params;
    }

    public function __destruct()
    {

    }

    public function dispatch()
    {         
        $config =   Factory::getConfig();
        $user   =   Factory::getUser();
        $task   =   Factory::getTask();
        $func   =   Factory::getAction();       
        $page   =   Factory::getPage();
        $format =   Factory::getParam('format');
        $layout =   Factory::getParam('layout');                        

        //Clean task & funciton name
        $task = preg_replace('/[^a-zA-Z]+/','',$task);    
        $func = preg_replace('/[^a-zA-Z]+/','',$func); 

        //For direct access without login, check the validity of the token
        if(!$user->isLogged())
        {
            switch($task)
            {
                case 'track':                     
                    if(!Factory::validToken())
                    {
                        header('HTTP/1.0 403 Forbidden');
                        die('Access forbidden');            
                    }
                    break;
                default:
                    Factory::setTask('login');            
                    $task = 'login';
            }        
        }

        if(file_exists(RBFY_MODELS . DIRECTORY_SEPARATOR . strtolower($task) . '.php'))
        {
            require_once(RBFY_MODELS . DIRECTORY_SEPARATOR . strtolower($task) . '.php');

            $classname  = '\Rubify\Framework\model' . ucfirst($task);
            
            if(class_exists($classname) && method_exists($classname , $func))
            {                
                $this->model = new $classname ($this->params);
                if($this->model->$func() === false)
                    $this->_notfound();
            } else {
                error_log('Router::dispatch() : ' . $classname . '::' . $func . ' does not exist');
                $this->_notfound();
            }
        } else {
            error_log('Router::dispatch() : ' . $task . ' does not exist');
            $this->_notfound();
        }        

        //If not called by Ajax, use the index
        if(!Factory::isAjax()){
            $template = 'index.php';           
        //For Ajax
        } else {                                  
            //If the page is forced            
            if($page->whichFile() != '')
            {                
                $template = $page->whichFile(); 
            //Automatic page depending on the task
            } else {                  
                $template = Factory::getTask();
                if($layout != 'grid' && file_exists(Factory::getTheme() . DIRECTORY_SEPARATOR . $template . '.' . $layout . '.php')){
                    $template .= '.' . $layout;            
                }
                $template .= '.php';                    
            }            
        }

        return Factory::getTheme() . DIRECTORY_SEPARATOR . $template;
    }

    protected function _notfound()
    {
        $page   =   Factory::getPage();      
        $this->model = new \Rubify\Framework\Model($this->params);
        $this->model->display();
        $page->setFile(null);
    }


}