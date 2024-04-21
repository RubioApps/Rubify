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
use Rubify\Framework\Language\Text;

class modelLogin extends Model
{
    public function display()
    {
        $user = new User();

        if(is_array($_POST) && isset($_POST['user']) && isset($_POST['password']))
        {            
            $uid    = $_POST['user'];
            $pwd    = $_POST['password'];

            if( Factory::checkToken() && $user->checkPassword($uid , $pwd) )
            {                    
                //Log the user                
                $user->Logon($uid);

                //Serve the JSON
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['error' => false , 'message' => Text::_('LOGIN_SUCCESS') ]);
                exit(0);
            }
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => true , 'message' => Text::_('LOGIN_ERROR')]);
            exit(0);
        } 

        if($user->isLogged())
        {
            $config = Factory::getConfig();
            header('Location:' . $config->live_site);
            die();            
        }              
    }

    public function token()
    {
        header('Content-Type: application/json; charset=utf-8');
        echo Factory::getToken(true);
        exit(0);
    }

    public function off()
    {
        $user = new User();
        $user->Logoff();
    }

}