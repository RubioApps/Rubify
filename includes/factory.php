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
use SimpleXMLElement;

defined('_RBFYEXEC') or die;

// Pre-Load configuration. Don't remove the Output Buffering due to BOM issues
ob_start();
require_once RBFY_CONFIGURATION . '/configuration.php';
ob_end_clean();

// Load the include needed classes
if ($folder = opendir(RBFY_INCLUDES))
{
    while (false !== ($file = readdir($folder)))
    {        
        if ($file != "." && $file != ".." && $file != 'factory.php') 
        {
            require_once RBFY_INCLUDES . DIRECTORY_SEPARATOR . $file;
        }
    }        
    closedir($folder);
}

class SimpleXMLExtended extends SimpleXMLElement
{
    public function addCData( $text )
    {
        $dom    = dom_import_simplexml($this); 
        $owner  = $dom->ownerDocument;          
        $dom->appendChild( $owner->createCDATASection( $text ));      
    }          

    public function prependChild($name)
    {
        $dom    = dom_import_simplexml($this); 
        $owner  = $dom->ownerDocument;   
        $node   = $owner->createElement($name);
        $new    = $dom->insertBefore($node , $dom->firstChild);

        return simplexml_import_dom($new,get_class($this));        
    }    
}

class Factory
{
    protected static $startTime;
    protected static $database;
    protected static $language;
    protected static $locale;
    protected static $config;
    protected static $params;
    protected static $assets;
    protected static $task;
    protected static $action;
    protected static $user;
    protected static $router;
    protected static $theme;
    protected static $page;    
    protected static $xhr;

    public function __construct()
    {
        static::$startTime = microtime(1);
        static::$params = [];
        static::$config = new RbfyConfig();
        static::$xhr    = self::isAjax();

        // Restore the params from the cookies
        self::_restoreParams();

    }

    public function __destruct() {
        //print "Destroying " . __CLASS__ . "\n";
    }

    public static function getConfig()
    {
        if(!static::$config){
            static::$config = new RbfyConfig();
        }
        return static::$config;
    }

    public static function getDatabase()
    {

        if(!static::$database)
        {
            static::$database = new Database(static::$config->minidlna['dir'] . DIRECTORY_SEPARATOR . 'files.db');
        }

        return static::$database;
    }

    public static function getUser()
    {

        if(!static::$user)
        {
            static::$user = new User();
        }
        return static::$user;
    }    


    public static function isAjax()
    {
        if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strcasecmp($_SERVER['HTTP_X_REQUESTED_WITH'], 'xmlhttprequest') == 0)
        {
            return true;
        }
        return false;
    }    

    public static function setParam( $name , $default , $type)
    {
        if(!is_array(static::$params)){
            static::$params = array();
        }

        $key = strtolower($name);

        $array = array();
        $array['default'] = $default;
        $array['type'] = $type;
        $array['value'] = null;
        
        static::$params[$key] = $array;           

        return static::$params[$key];
    }

    public static function getParam( $name )
    {
        if(!is_array(static::$params)){
            static::$params = array();
        }

        if(!isset(static::$params[$name])){
            return null;
        }
        return static::$params[$name]['value'] ?? static::$params[$name]['default'];
    }

    protected static function _restoreParams()
    {
        $cookie = filter_input(INPUT_COOKIE,'__rbfy');
        if($cookie){
            static::$params = json_decode(self::_decrypt($cookie) , true);
        } else {
            static::$params = [];
        }

        if(!count(static::$params))
        {
            // Basic param task
            if(empty(static::$params['task']))
                self::setParam('task','home','string');                                                          

            if(empty(static::$params['layout']))
                self::setParam('layout', 'grid' ,'string');                 

            // Add config
            static::$params['config'] = self::$config;
        }
    }

    public static function saveParams()
    {
 
        if(!is_array(static::$params)){
            static::$params = [];
        }

        // SAFETY: Exclude the config from the saved params
        if(isset(static::$params['config'])){
            unset(static::$params['config']);
        }      

        // Ensure all the basic parameters are loaded
        foreach(static::$params as $k=>$array)
        {       
            // When a basic parameter is not provided through the query, set the default value
            if(!(static::$params[$k]['value'] = filter_input(INPUT_GET, $k)))
            {
                switch (static::$params[$k]['type'])
                {
                    case 'int':
                        static::$params[$k]['value'] = (int) $array['default'];
                        break;
                    case 'string':
                    default:
                        static::$params[$k]['value'] = $array['default'];
                        break;
                }               
            }            
        }       

        // Store the params only if logged
        $user = new User();
        if($user->isLogged())
            setcookie('__rbfy', self::_encrypt(json_encode(static::$params)), static::$startTime + 3600 , '/');

        // Reload the config
        static::$params['config'] = self::$config;

    }

    public static function getLanguage( $lang = null )
    {        
        if(!static::$language){
            static::$language= new Language($lang);
        }
        return static::$language;
    }

    public static function setLanguage( $lang  )
    {        
        //static::$language= new Language($lang);        
        return static::$language;
    }    

    public static function getLangTag()
    {
        if(!static::$locale){
            $language = self::getLanguage();
            static::$locale = $language->getTag();
        }
        return static::$locale;
    }

    public static function getAssets()
    {
        if(!static::$assets){
            $config = self::getConfig();
            static::$assets = $config->live_site . '/templates/' . $config->theme . '/assets';
        }
        return static::$assets;
    }

    public static function getTheme()
    {
        if(!static::$theme){
            $config = self::getConfig();
            static::$theme = RBFY_THEMES . DIRECTORY_SEPARATOR . $config->theme;
        }
        return static::$theme;
    }

    public static function getTask()
    {
        if(!static::$task){
            static::$task = Request::getVar('task','home','GET');
        }

        if(strstr(static::$task,'.'))
        {            
            $parts = explode('.' , static::$task );
            static::$task   = array_shift($parts);                
            static::$action =  join('.', $parts);            
        }        
        static::$params['task']['value'] =  static::$task;      

        return static::$task;
    }

    public static function getAction()
    {
        if(!static::$action)
            static::$action = 'display';
        
        if(strstr(static::$task,'.'))
        {            
            $parts = explode('.' , static::$task );
            static::$task = array_shift($parts);                
            static::$action =  join('.', $parts);
        }                      
        return static::$action;
    }    

    public static function setTask( $task = null)
    {
        if(empty($task) || $task === null)
            static::$task = 'home';

        if(strstr($task,'.'))
        {
            $parts  = explode('.',static::$task );
            $task   = array_shift($parts);
            static::$action =  join('.',$parts);
            Request::setVar('action' , static::$action , 'GET');           
        }
        static::$task = $task;
        static::$params['task']['value'] = static::$task;

        return static::$task;
    }

    public static function getRouter( $task = null)
    {
        if(!$task){
            $task = self::getTask();
        }

        if(!static::$router){
            static::$router= new Router(static::$params);
        }

        return static::$router;
    }

    public static function getModel ($name)
    {
        if(file_exists(RBFY_MODELS . DIRECTORY_SEPARATOR . strtolower($name) . '.php'))
        {
            require_once(RBFY_MODELS . DIRECTORY_SEPARATOR . strtolower($name) . '.php');

            $classname  = '\Rubify\Framework\model' . ucfirst($name);

            if(class_exists($classname))        
                return new $classname (self::$params);
        } else {
            return false;
        }        
    }

    public static function getPage( $pagename = null)
    {
        if(!static::$page){
            static::$page= new Page();
        }

        foreach(static::$params as $key => $p)
        {
            if(is_object($p))
                static::$page->$key = $p;
            else
                static::$page->$key = $p['value'];
        }

        return static::$page;
    }

    public static function Link()
    {
        if(!func_num_args())
            return static::$config->live_site;

        $props      = ['task'];
        $ret        = static::$config->live_site  . '/';

        // For the protected parameters ($props)
        for($counter = 0; $counter <  func_num_args(); $counter++)
        {
            if($counter >= count($props))
                break;

            if(func_get_arg($counter))
            {
                $key    = $props[$counter];
                $value  = func_get_arg($counter);

                $ret .= $counter > 0 ? '&' : '?';
                $ret .= $key . '='. Helpers::encode($value);
            } else
                break;
        }

        // For the rest of the arguments
        for($i = $counter ; $i <  func_num_args(); $i++)
        {
            if(func_get_arg($i))
            {
                $ret .= $ret !=='' ? '&' : '?';
                $ret .= func_get_arg($i);
            }
        }

        //Add layout
        $ret .= $ret !=='' ? '&' : '?';
        $ret .= 'layout=' . self::getParam('layout');

        // Return
        return $ret;
    }

    /**
     * Serve the labels for Javascript framework
     */
    public static function jsBridge()
    {     
        $task = self::getTask();   
        switch($task){
            case 'labels':
                $source = static::$language->getStrings();
                break;
            case 'token':
                $source = self::getToken(true);
                break;
            default:
                return;
        }        
        header('Content-Type: application/json; charset=utf-8');        
        echo json_encode( $source, JSON_UNESCAPED_SLASHES);
        die();        
    }  

    public static function getToken( $raw = false)
    {
        $method = 'AES-256-CBC';
        $key    = hash('sha256', static::$config->key);
        $iv     = substr(hash('sha256', IV_KEY ), 0, 16);

        $token = substr(bin2hex(openssl_random_pseudo_bytes(16)), 0, 32);
        $_SESSION['_token'] = $token;        

        $value = base64_encode(openssl_encrypt($token, $method, $key, 0, $iv)) ?? null;
        if($raw)
        {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['token' => $value , 'sid' => session_id()]);
            exit(0);
        }            
        return '<input type="hidden" name="' . $value . '" id="token" value="' . session_id() . '" />';    
    }

    public static function checkToken()
    {
        $method = 'AES-256-CBC';
        $key    = hash('sha256', static::$config->key);
        $iv     = substr(hash('sha256', IV_KEY ), 0, 16);

        $id     =  $_SESSION['_token'] ?: null;
        unset($_SESSION['_token']);

        foreach($_POST as $k => $v)
        {                
            $token  = openssl_decrypt(base64_decode($k), $method, $key, 0, $iv);
            if( $id == $token && $v == session_id())
            {
                return true;
            }
        }
        return false;
        
    }

    protected static function _encrypt($string)
    {
        $method = 'AES-256-CBC';
        $key    = hash('sha256', static::$config->key);
        $iv     = substr(hash('sha256', IV_KEY ), 0, 16);

        return base64_encode(openssl_encrypt($string, $method, $key, 0, $iv)) ?? null;        
    }

    protected static function _decrypt($string)
    {
        $method = 'AES-256-CBC';
        $key    = hash('sha256', static::$config->key);
        $iv     = substr(hash('sha256', IV_KEY ), 0, 16);        

        return openssl_decrypt(base64_decode($string), $method, $key, 0, $iv);        
    }

    /**
     * Valid a token for direct access to an audio track
     */
    public static function validToken()
    {
        if(!isset($_GET['_t'])) return false;

        $config= static::$config;
        $token = $_GET['_t'];
        $register = RBFY_USERS . DIRECTORY_SEPARATOR . 'exports' . DIRECTORY_SEPARATOR . 'registry.xml';

        if(!file_exists($register) || !($content=file_get_contents($register))) return false;
              
        $xml    = new SimpleXMLElement($content);                
        $node   = $xml->xpath("//entry[@token='" . $token . "']");  
        if($node)
        {
            //First we take the token
            $attr = $node[0]->attributes();        
            if($attr['token'] == $token)
            {
                $path   = parse_url($config->live_site,PHP_URL_PATH);
                $uri    = $config->live_site . substr($_SERVER['REQUEST_URI'],strlen($path));
                //Then we valid the link                
                $links = $xml->xpath("//entry[@token='" . $token . "']/link"); 
                foreach($links as $link)
                    if($uri == $link->__toString()) return true;                
            }
        }
        return false;
    }

}

