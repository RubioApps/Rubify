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
use ZipArchive;

defined('_RBFYEXEC') or die;

use Rubify\Framework\Helpers;
use Rubify\Framework\Request;
use Rubify\Framework\Playlist;
use Rubify\Framework\Language\Text;

class modelPlaylist extends Model
{

    /** 
     * Display the list of playlists 
    */
    public function display()
    {
        $playlist   = new Playlist($this->user);
        $this->data = $playlist->list();
        $this->page->title  = Text::_('PLAYLIST');
        $this->page->data   = $this->data;    
        parent::display();         
    }

    /**
     * View the content of a playlist
     */
    public function view()
    {
        $id         = Request::getVar('id',null,'GET');
        $playlist   = new Playlist($this->user);
        $favid      = $playlist->favoritesID();

        $source = ($id == $favid) ? 'favorites' : 'playlist';

        if($playlist->select($id))        
            $this->data = ['playlist' => $playlist->get(), 'source' => $source , 'tracks' => $playlist->load()];                        


        $this->page->setFile('playlist.view.php');
        $this->page->title  = $this->data['playlist']->name;
        $this->page->data   = $this->data;    
        parent::display();          
    }    

    /**
     * Empty a playlist
     */
    public function empty()
    {
        $id         = Request::getVar('id',null,'GET');
        $playlist   = new Playlist($this->user);

        if($playlist->select($id))                   
            $array = $playlist->empty();
        else
            $array = ["success" => false];
               
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($array);
        die (0);                        
    }  

    /**
     * Create a playlist
     */
    public function create()
    {
        $result = ['success' => false , 'message' => Text::_('PLAYLIST_CREATED_ERROR')];

        if(is_array($_POST) && isset($_POST['name']) && $_POST['name'] != 'favorites')
        {            
            $name   = $_POST['name'];
            if( Factory::checkToken() && $name != '')
            {        
                //Create the playlist
                $playlist = new Playlist($this->user);
                if($playlist->create($name,false))
                    $result = ['success' => true , 'message' => Text::_('PLAYLIST_CREATED_SUCCESS') ];                
            }
        }    
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($result);
        exit(0);            
    }    

    /**
     * Delete a playlist
     */
    public function delete()
    {       
        $id         = Request::getVar('id','','GET');
        $playlist   = new Playlist($this->user);
        $result     = ['success' => false , 'message' => Text::_('PLAYLIST_DELETED_ERROR')];

        if($id != $playlist->favoritesID())
        {            
            if($playlist->delete($id))
                $result = ['success' => true , 'message' => Text::_('PLAYLIST_DELETE_SUCCESS') ];
        }    
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($result);
        exit(0);            
    }    

    /**
     * Select a playlist as current
     */
    public function select(){
        $playlist   = new Playlist($this->user);        
        $this->data = $playlist->list();
        $this->page->data = $this->data;
        $this->page->setFile('playlist.select.php');        
    }

    /**
     * Create a playlist
     */
    public function save()
    {
        $result = ['success' => false , 'message' => Text::_('PLAYLIST_CREATED_ERROR')];

        if(is_array($_POST) && isset($_POST['name']) && ($_POST['name'] != 'favorites' || $_POST['name']==''))
        {            
            $id = $_POST['select'];
            $name   = $_POST['name'];
            
            if( Factory::checkToken() )
            {
                //Get the current queue
                $queue = new Queue($this->user);
                $array = $queue->load();

                //Get the playlist
                $playlist = new Playlist($this->user);

                //If the name of a new list is given
                if($name != '')
                {                                        
                    if($playlist->create($name,false))
                    {
                        $id = $playlist->getSelected();
                    }                
                }

                if($playlist->select($id))
                {
                    $playlist->pushArray($array);
                    $result = ['success' => true , 'message' => Text::_('PLAYLIST_CREATED_SUCCESS') ];  
                }                
            }
        }    
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($result);
        exit(0);            
    }      
    
    /**
     * Remove track from a playlist
     */
    public function pop()
    {   
        list($oid)  = Helpers::getId();
        $id         = Request::getVar('id',null,'GET');
        $playlist   = new Playlist($this->user);

        $result = ['success' => false , 'message' => Text::_('PLAYLIST_REMOVED_ERROR')];        
        if($id != $playlist->favoritesID())
        {           
            $playlist->select($id);
            if($playlist->pop($oid))
                $result = ['success' => true , 'message' => Text::_('PLAYLIST_REMOVED_SUCCESS') ];
        }    
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($result);
        exit(0);            
    }        

    /**
     * Push a track to a playlist
     */
    public function push()
    {   
        list($oid)  = Helpers::getId();
        $id         = Request::getVar('id',null,'GET');
        $playlist   = new Playlist($this->user);

        $result = ['success' => false , 'message' => Text::_('PLAYLIST_ADDED_ERROR')];

        if(is_array($_POST) && isset($_POST['name']) && ($_POST['name'] != 'favorites' || $_POST['name']==''))
        {            
            $id = $_POST['select'];
            $name   = $_POST['name'];
            
            if( Factory::checkToken() )
            {
                //Get the current queue
                $queue = new Queue($this->user);
                $array = $queue->load();

                //Get the playlist
                $playlist = new Playlist($this->user);

                //If the name of a new list is given
                if($name != '')
                {                                        
                    if($playlist->create($name,false))
                    {
                        $id = $playlist->getSelected();
                    }                
                }

                if($playlist->select($id))
                {
                    $playlist->push($oid);
                    $result = ['success' => true , 'message' => Text::_('PLAYLIST_ADDED_SUCCESS') ];  
                }                
            }
        }    
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($result);
        exit(0);           
    }   
    
    /**
     * Export a m3u file containing the tracks
     * #EXTM3U
     * #EXTINF: duration, artist - title
     * https//path_to_the_file.mp3
     */
    public function export()
    {
        $id         = Request::getVar('id',null,'GET');
        $token      = Helpers::UUID();
        $now        = new \DateTime();
        $expiry     = new \DateTime();
        $expiry     = $expiry->add(new \DateInterval('P1Y'));

        $playlist   = new Playlist($this->user);
        $playlist->select($id);
        $array      = $playlist->load();        
     
        if(count($array))
        {
            //Create the m3u file
            $m3u    = tempnam(sys_get_temp_dir(), 'rbfy-playlist') . '.m3u';

            $content = '#EXTM3U' . PHP_EOL;
            foreach($array as $item)
            {            
                $content .= '#EXTINF:' . intval($item->duration_ms/1000) . ' logo="' . $item->thumbnail . '&_t=' . $token . '",' .
                    $item->artist .' - '. $item->title . PHP_EOL;
                $content .= '#PLAYLIST: ' . $token. PHP_EOL;
                $content .= '#EXTALB: ' . $item->album . PHP_EOL;
                $content .= '#EXTART: ' . $item->artist . PHP_EOL;
                $content .= '#EXTGENRE: ' . $item->genre . PHP_EOL;
                $content .= $item->audio . '&_t=' . $token . PHP_EOL;
            }
            file_put_contents($m3u , $content);

            //Register the export
            $register = RBFY_USERS . DIRECTORY_SEPARATOR . 'exports' . DIRECTORY_SEPARATOR . 'registry.xml';
            if(!file_exists($register)){  
                file_put_contents($register,'<?xml version="1.0" encoding="UTF-8" standalone="yes"?><exports></exports>');
            }
            $xml    = new SimpleXMLExtended(file_get_contents($register));
            $root   = $xml->xpath('//exports');
            $node   = $root[0]->addChild('entry');            
            $node->addAttribute('token',$token);
            $node->addAttribute('user',$this->user);
            $node->addAttribute('size', filesize($m3u));
            $node->addAttribute('created', $now->format('c'));
            $node->addAttribute('expiry' , $expiry->format('c'));
            $node->addAttribute('tracks' , count($array));
            foreach($array as $item)
                $node->addChild('link')->addCData($item->audio . '&_t=' . $token); 
            $xml->asXML($register);

            header('Content-type: audio/mpegurl'); 
            header('Content-Disposition: attachment; filename=' . microtime(true) . '.m3u');
            header('Content-length: ' . filesize($m3u));
            header('Pragma: no-cache'); 
            header('Expires: 0'); 
            readfile($m3u);                      
        }
    }    

    /**
     * Download a zip file containing the tracks
     */
    public function download()
    {
        $CHUNK_SIZE = 512 * 1024; //512KBytes

        ob_end_clean();

        $id         = Request::getVar('id',null,'GET');
        $playlist   = new Playlist($this->user);
        $playlist->select($id);
        $array      = $playlist->load();        
     
        if(count($array))
        {
            //Create the zip file
            $temp   = tempnam(sys_get_temp_dir(), 'rbfy-playlist') . '.zip';
            $zip        = new ZipArchive;  
            if($zip->open($temp,  ZipArchive::CREATE))
            {
                foreach($array as $item)
                {
                    $filename = pathinfo($item->path , PATHINFO_BASENAME);
                    $zip->addFile($item->path , 
                        Helpers::sanitize($item->artist) . '-' . 
                        Helpers::sanitize($item->title) . '.mp3'
                    );
                }                
                $zip->close();

                //Serve the file
                $filename = pathinfo($temp, PATHINFO_FILENAME);
                $size = filesize($temp);
                $time = date('r', filemtime($temp));
                $offset = 0;
                $end = $size - 1;
                $fp = fopen($temp, 'rb');
                stream_set_chunk_size($fp, $CHUNK_SIZE);
                stream_set_read_buffer($fp, $CHUNK_SIZE);
                if (!$fp) {
                    header("HTTP/1.1 505 Internal server error");
                    die();
                }

                if (false && isset($_SERVER['HTTP_RANGE'])) {
                    if (preg_match('/bytes=\h*(\d+)-(\d*)[\D.*]?/i', $_SERVER['HTTP_RANGE'], $matches)) {
                        $offset = intval($matches[1]);
                        if (!empty ($matches[2]))
                            $end = intval($matches[2]);
                    }
                    header('HTTP/1.1 206 Partial Content');
                    header('Content-Range: bytes ' . $offset . '-' . $end . '/' . $size);
                    header("Content-Transfer-Encoding: chunked");
                } else {
                    header('HTTP/1.1 200 OK');
                    header("Content-Transfer-Encoding: binary");
                }

                header('Last-Modified: ' . $time);
                header('Accept-Ranges: bytes');
                header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
                header('Cache-Control: post-check=0, pre-check=0', false);
                header('Pragma: no-cache');
                header('Content-Type: application/zip');                                
                header('Content-Length:' . (($end - $offset) + 1));
                header('Content-Disposition: inline; filename=' . $filename);                

                $cur = $offset;
                fseek($fp, $offset);
                while (!feof($fp) && $cur <= $end && !connection_aborted()) {
                    print fread($fp, min( $CHUNK_SIZE , ($end - $cur) + 1));
                    $cur += $CHUNK_SIZE;
                }
                fclose($fp);
                exit(0);                     
            } 
        }
        return false;
    }       

}