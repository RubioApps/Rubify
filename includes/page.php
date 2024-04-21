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

class Page
{
    public $title;
    public $menu;
    public $data;
    public $params;
    public $pagination;
    protected static $metatags;
    protected static $file;
    protected static $js;

    public function __construct()
    {
        static::$js     = [];
    }

    public function __destruct(){

    }

    public static function getFile( $pagename = null )
    {
        if(!$pagename)
        {
            if(!static::$file)
            {
                $filename= ( Factory::getTheme() . DIRECTORY_SEPARATOR . Factory::getTask() . '.php');
                if(file_exists($filename))
                {
                    static::$file = $filename;
                } else {
                    static::$file = ( Factory::getTheme() . DIRECTORY_SEPARATOR . '404.php');
                }
            }
            return static::$file;
        } else {

            $filename= ( Factory::getTheme() . DIRECTORY_SEPARATOR . $pagename . '.php');

            if(file_exists($filename)){
               return $filename;
            } else {
               return ( Factory::getTheme() . DIRECTORY_SEPARATOR . '404.php');
            }
        }
    }

    public static function setFile( $pagename )
    {
        static::$file = $pagename;
    }

    public static function whichFile(){
        return static::$file;
    }

    public static function sendError( $title , $content)
    {
        self::sendMessage( $title , $content, 'danger');
    }

    public static function sendSuccess( $title , $content)
    {
        self::sendMessage( $title , $content , 'success');
    }

    public static function sendMessage( $title , $content, $type = 'info')
    {
        $notify = "$.rbfy.toast('<b>". addslashes($title) ."</b><br />" . addslashes($content) . "');";
        self::registerScript($notify);
    }

    public static function addStatic( $type = null)
    {
        $config = Factory::getConfig();
        $list = [];
        if ($handle = opendir(RBFY_STATIC))
        {
            while (false !== ($file = readdir($handle)))
            {
                if ($file != "." && $file != "..")
                {
                    $filepath   = RBFY_STATIC . DIRECTORY_SEPARATOR . $file;
                    $extension  = pathinfo($filepath , PATHINFO_EXTENSION);
                    if(!empty($type) && $type === $extension){
                        $list[$file]= self::addCDN($extension , $config->live_site . '/static/' . $file );
                    }
                }
            }
            closedir($handle);
            ksort($list);
        }
        return join( "\n", $list);
    }

    public static function addCDN ($type , $url, $hash = null, $cors = null)
    {
        switch($type){
            case 'js':
                $tag  = 'script';
                $attr['type']   = 'text/javascript';
                $attr['src']    = $url ;
                $attr['integrity'] = $hash;
                $attr['crossorigin'] = $cors;
                break;
            case 'css':
                $tag = 'link';
                $attr['href'] = $url ;
                $attr['rel'] = 'stylesheet';
                $attr['integrity'] = $hash;
                $attr['crossorigin'] = $cors;
                break;
            default:
                return;
        }
        $ret = '<' . $tag . ' ';
        foreach($attr as $k => $v){
            if($v !== null)
                $ret .= $k . '="' . $v. '" ';
        }
        $ret .= '></' . $tag . '>' .  PHP_EOL;
        return $ret;
    }

    public static function registerScript( $code )
    {
        self::$js[] = $code;
    }

    public static function getJScripts()
    {
        $ret = "<script type=\"text/javascript\"> \n";
        $ret .= ";jQuery(document).ready(function(){\n ";
        if(count(self::$js)){
            foreach(self::$js as $code){
                $ret .= $code." \n";
            }
        }
        $ret .= "}); \n";
        $ret .= "</script> \n";
        echo $ret;
    }

    public static function addMeta( $name, $value , $separator = ',')
    {
        if(empty(static::$metatags))
            static::$metatags = array();

        $key = strtolower($name);
        if(!empty(static::$metatags[$key]))
            static::$metatags[$key] .= $separator . htmlspecialchars($value);
        else
            static::$metatags[$key] = htmlspecialchars($value);
    }

    public static function getMeta( $name )
    {
        if(empty(static::$metatags))
            static::$metatags = array();

        return static::$metatags[strtolower($name)] ?? '';
    }

}

