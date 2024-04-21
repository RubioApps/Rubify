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

class User
{
    protected $uid;
    protected $pwd;
    protected $config;
    protected $filename;
    protected $content;    
    protected $data;
    protected $logged;

    public function __construct()
    {
        $this->config = Factory::getConfig();
        $this->filename = RBFY_USERS . DIRECTORY_SEPARATOR . '.users';

        //For the first install, create an admin with a simple password
        if(!file_exists($this->filename)) {
            $this->content = 'admin:' . md5('0123456789') . ':admin' . PHP_EOL;
            file_put_contents($this->filename, $this->_encrypt($this->content));   
        }
           
        $this->_load();  
        $this->logged   = $this->isLogged(); 
    }

    public function __destruct()
    {

    }

    /**
     * Get the user object
     * @param string $uid Username. If empty, it will the logged username
     */
    public function get($uid = null)
    {
        if(empty($uid)) 
            $uid = Factory::getParam('user');  

        if(!isset($this->data[$uid])) 
            return null;            

        return $this->data[$uid];
    }

    /**
     * Get the list of users
     */
    public function getList()
    {
        $array = [];
        if($this->isAdmin()) $array = $this->data;
        return $array;
    }

    /**
     * Get the use hashed password
     * @param string $uid Username. If empty, it will the logged username
     */    
    public function getPasswordHash($uid = null)
    {
        if(empty($uid)) 
            $uid = Factory::getParam('user');        

        if(!isset($this->data[$uid])) 
            return null;

        return $this->data[$uid]->hash;
    }

    /**
     * Get the user level 
     * @param string $uid Username. If empty, it will the logged username
     */
    public function getUserLevel($uid = null)
    {
        if(empty($uid)) 
            $uid = Factory::getParam('user');  

        if(!isset($this->data[$uid])) 
            return null;

        return $this->data[$uid]->level;
    }    

    /**
     * Checks whether a user is logged
     * @param string $uid Username. If empty, it will the logged username
     */    
    public function isLogged($uid = null)
    {
        if(empty($uid))
             $uid = Factory::getParam('user'); 

        if(isset($this->data[$uid]) && (isset($_SESSION['sid']) || $this->autoLogged()))
        {
            $safe   = $this->data[$uid]->hash;
            if($_SESSION['sid'] == md5(session_id() . $safe))
                return true;
        }
        return false;
    }

    /**
     * Checks whether a user is an administrator
     * @param string $uid Username. If empty, it will the logged username
     */
    public function isAdmin($uid = null)
    {
        if(empty($uid)) 
            $uid = Factory::getParam('user');  

        if(!isset($this->data[$uid])) 
            return false;
        
        return $this->data[$uid]->level == RBFY_ADMIN;
    }       

    /**
     * Depending on the configuration, we can automatically log the user from the cookies
     * This function does not work fine yet
     * 
     */
    public function autoLogged()
    {
        if(!$this->config->use_autolog)
            return false;

        // Get client ip address
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        // Check the local ip address
        if ( ! filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) )
        {
            $uid    = Factory::getParam('user') || "autologged";
            $hash   = $this->getPasswordHash($uid);
            $_SESSION['sid'] = md5(session_id() .  $hash);
            return true;
        }
        return false;
    }    

    /**
     * Savee the session of the identified user
     * 
     * @param string $uid   Username
     */

    public function Logon($uid)
    {
        if(!isset($this->data[$uid])) return null;

        $_SESSION['sid'] = md5(session_id() . $this->data[$uid]->hash);  

        Factory::setParam('user',$uid,'string');
        Factory::saveParams();
    }    

    /**
     * Log off the current user
     */
    public function Logoff()
    {
        unset($_SESSION['sid']);
        //session_destroy();
        header('Location:' . Factory::Link());
        die();        
    }

    /**
     * Checks whether a password fulfills the requirements
     * 
     * @param string $password The password to check
     * @param integer $min      Minimum length
     * @param integer $max      Maximum length
     * @param integer $digit    At least x digits
     * @param integer $lower    At least x lower case
     * @param integer $upper    At least x upper case
     * @param integer $symbol   At leasr x symbols
     * 
     * @return boolean True if the password commits the requirements. False if not.
     */
    public function validPassword($password, $min = 8, $max = 32, $digit = 1, $lower = 1, $upper = 1, $symbol = 1)
    {
        $regex = '/^';
        if ($digit == 1) { $regex .= '(?=.*\d)'; }              
        if ($lower == 1) { $regex .= '(?=.*[a-z])'; }           
        if ($upper == 1) { $regex .= '(?=.*[A-Z])'; }           
        if ($symbol == 1) { $regex .= '(?=.*[^a-zA-Z\d])'; }    
        $regex .= '.{' . $min . ',' . $max . '}$/';
    
        if(preg_match($regex, $password)) {
            return true;
        } else {
            return false;
        }
    }        

    /**
     * Compares a given password to the saved one
     * @param string $uid   Username
     * @param string $password  Password
     */
    public function checkPassword( $uid , $password)
    {
        if(isset($this->data[$uid]) && strlen(trim($password)) >= 8) 
        {
            if(strcmp( md5($password) , $this->data[$uid]->hash) == 0) return true;
        }
        return false;
    }

    /**
     * Updates the password of a given user
     * @param string $uid   Username
     * @param string $password  Password* 
     */
    public function update($uid , $password)
    {
        if(!$this->data)
            $this->data = $this->_parse();

        if(!isset($this->data[$uid])) return false;            

        $this->data[$uid]->hash = md5($password);
        return $this->_save();
    }    

    /**
     * Add a nuew user to the database
     * 
     * @param string $username Username
     * @param string $password  Password
     * @param string $level     Level of the user. This is 'user' or 'admin'
     */

    public function add($username , $password , $level = 'user')
    {
        if(!$this->data)
            $this->data = $this->_parse();

        if(!$this->isAdmin()) return false;

        $hash = md5($password);
        $item = new \stdClass;
        $item->uid = $username;
        $item->hash = $hash;
        $item->level = $level;
        $this->data[$username] = $item;        
        return $this->_save();
    }

    /**
     * Remove a user
     * 
     * @param string $username Username
     */
    public function remove($username)
    {
        if(!$this->data)
            $this->data = $this->_parse();

        if(!$this->isAdmin()) return false;
        unset($this->data[$username]); 
        return $this->_save();
    }    

    /**
     * Load the decrypted content of the users file
     */
    protected function _load()
    {
        $this->content = $this->_decrypt(file_get_contents($this->filename));     
        $this->_parse();         
    }


    /**
     * Saves the content of the floating arrray into an encrypted file
     */
    protected function _save()
    {
        if(($fp = fopen($this->filename, 'r+')) !== false) 
        {         
            //Empty the file and close it                    
            ftruncate($fp,0);
            fclose($fp);

            //Re-open in exclusive mode
            $fp = fopen($this->filename, 'a');                    
            flock($fp,LOCK_EX);

            //Save the new content and close
            fwrite($fp , $this->_encrypt($this->_dump()));
            fclose($fp);
            return true;
        }
        return false;            
    }

    /**
     * Put the content of the floating array into a string, ready to be encrypted
     */
    protected function _dump()
    {
        if(!$this->data)
            $this->data = $this->_parse();

        $buffer = [];            
        foreach($this->data as $item)
        {
            $line   = [];
            foreach(get_object_vars($item) as $key=>$value) $line[] = $value;
            $buffer[] = implode(':',$line);
        }
        $this->content = implode(PHP_EOL,$buffer);        
        return $this->content;
    }

    /**
     * Parse the decrypted content and put it into the floating array
     */
    protected function _parse()
    {
        $array =[];        
        if(preg_match_all('/^(.*)$/im' , $this->content , $lines))
        {
            foreach($lines[0] as $line){
                $item = new \stdClass;
                if(strlen(trim($line))>0 && strstr($line,':'))
                {
                    $parts = explode(':',$line);
                    $item->uid = $parts[0];
                    $item->hash = $parts[1];
                    $item->level = $parts[2];
                    $array[$item->uid] = $item;
                }
            }
        } 
        $this->data = $array;
        return $array;        
    }

    /**
     * Encrypt a string.  This uses the encryption key defined in the configuration
     * @param string $string The text to be encrypted
     */
    protected function _encrypt($string)
    {
        $method = 'AES-256-CBC';
        $key    = hash('sha256', $this->config->key);
        $iv     = substr(hash('sha256', IV_KEY ), 0, 16);

        return base64_encode(openssl_encrypt($string, $method, $key, 0, $iv)) ?? null;        
    }

    /**
     * Decrypt a string. This uses the encryption key defined in the configuration
     * @param string $string The text to be encrypted
     */    
    protected function _decrypt($string)
    {
        $method = 'AES-256-CBC';
        $key    = hash('sha256', $this->config->key);
        $iv     = substr(hash('sha256', IV_KEY ), 0, 16);        

        return openssl_decrypt(base64_decode($string), $method, $key, 0, $iv);        
    }
}