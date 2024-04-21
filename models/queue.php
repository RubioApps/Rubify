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

use Rubify\Framework\Request;
use Rubify\Framework\Queue;
use Rubify\Framework\Helpers;
use Rubify\Framework\Playlist;
class modelQueue extends Model
{    
    /**
     * Display the content of the queue in HTML
     */
    public function display()
    {
        $autoplay     = Request::getVar('autoplay',false,'GET','bool');        
        $queue  = new Queue( $this->user );

        $this->page->data = $queue->load();
        $this->page->params['autoplay'] = ($autoplay ? 'true' : 'false');
        $this->page->json = json_encode($this->page->data);

        parent::display();
    }
    /**
     * Provide the content of the queue in a JSON format
     */

    public function json()
    {
        $queue  = new Queue( $this->user );

        $array = $queue->load();
        
        header('Content-Type: application/json; charset=utf-8');  
        echo json_encode($array);
        die();
    }

    /**
     * Push a track at the end of the queue
     */
    public function push()
    {
        list($oid)  = Helpers::getId();
        $method     = Request::getVar('method','','GET');                      
        $queue      = new Queue( $this->user );        
        $success    = $queue->push($oid,$method);
        $result     = $queue->get($oid);

        $array = ['success' => $success , 'result' => $result];

        header('Content-Type: application/json; charset=utf-8');  
        echo json_encode($array);
        die();
    }

    /**
     * Remove a track from the queue
     */
    public function pop()
    {
        list($oid)  = Helpers::getId();        
        $queue      = new Queue( $this->user );                
        $success     = false;

        //Cannot pop a missing track from the queue
        if($track = $queue->get($oid)){
            $success    = $queue->pop($oid);
            $result     = $track;
        }

        $array = ['success' => $success , 'result' => $result];    

        header('Content-Type: application/json; charset=utf-8');  
        echo json_encode($array);
        die();      
    }    

    /**
     * Push a track to the beginning of the queue
     */
    public function unshift()
    {
        list($oid)  = Helpers::getId();                     
        $queue      = new Queue( $this->user );        
        $success    = $queue->push($oid,'unshift');
        $result     = $queue->get($oid);

        $array = ['success' => $success , 'result' => $result];

        header('Content-Type: application/json; charset=utf-8');  
        echo json_encode($array);        
        die();
    }    

    /**
     * Move a track to the end of the queue
     */
    public function popandpush()
    {
        list($oid)  = Helpers::getId();        
        $queue      = new Queue( $this->user ); 
        $success    = false; 

        if($queue->pop($oid))
            $success = $queue->push($oid);
      
        $result  = $queue->get($oid);
        $array = ['success' => $success , 'result' => $result];

        header('Content-Type: application/json; charset=utf-8');  
        echo json_encode($array);
        die();
    }

    /**
     * Move a track at the beginning of the queue
     */
    public function popandunshift()
    {
        list($oid)  = Helpers::getId();        
        $queue      = new Queue( $this->user ); 
        $success    = false;

        if($queue->pop($oid))
            $success = $queue->push($oid,'unshift');            
              
        $result = $queue->get($oid);  
        $array = ['success' => $success , 'result' => $result];

        header('Content-Type: application/json; charset=utf-8');  
        echo json_encode($array);
        die();
    }    

    /**
     * Empty the queue
     */
    public function empty()
    {        
        $queue  = new Queue( $this->user );

        $array = ['success' => $queue->empty(), 'result' => []];

        header('Content-Type: application/json; charset=utf-8');  
        echo json_encode($array);
        die();
    }

    /**
     * Add the tracks of an album to the queue
     */
    public function album()
    {
        list($oid)  = Helpers::getId();     
        $method     = Request::getVar('method','','GET');        
        $album      = Factory::getModel('Album');        
        $array      = $album->getTracks($oid);   
        
        $queue = new Queue($this->user);
        $success    = $queue->pushArray($array,$method);
        $result     = $queue->load();

        $array = ['success' => $success , 'result' => $result];       
        header('Content-Type: application/json; charset=utf-8');  
        echo json_encode($array);
        die();
    }

    /**
     * Add the tracks of a playlist to the queue
     */
    public function playlist()
    {
        $id     = Request::getVar('id',null,'GET');        
        $method = Request::getVar('method','','GET'); 
        
        $playlist = new Playlist($this->user);
        $playlist->select($id);
        $array = $playlist->load();   
        
        $queue      = new Queue($this->user);
        $success    = $queue->pushArray($array,$method);
        $result     = $queue->load();

        $array = ['success' => $success , 'result' => $result];          
        header('Content-Type: application/json; charset=utf-8');  
        echo json_encode($array);
        die();
    }

    /**
     * Add the tracks of the user uploads to the queue
     */
    public function upload()
    {        
        $upload = new Upload($this->user);
        $array = $upload->getScannedEntries();
        
        $queue      = new Queue($this->user);
        $success    = $queue->pushArray($array);
        $result     = $queue->load();

        $array = ['success' => $success , 'result' => $result];          
        header('Content-Type: application/json; charset=utf-8');  
        echo json_encode($array);
        die();
    }    

    /**
     * Add the tracks of a playlist to the queue
     */
    public function history()
    {      
        $method = Request::getVar('method','','GET'); 
        
        $history = new History($this->user);
        $array = $history->load();   
        
        $queue      = new Queue($this->user);
        $success    = $queue->pushArray($array,$method);
        $result     = $queue->load();

        $array = ['success' => $success , 'result' => $result];          
        header('Content-Type: application/json; charset=utf-8');  
        echo json_encode($array);
        die();
    }

    /**
     * Export a m3u faile containing the tracks
     * #EXTM3U
     * #EXTINF: duration, artist - title
     * https//path_to_the_file.mp3
     */
    public function export()
    {
        $queue  = new Queue($this->user);
        $array  = $queue->load();
        $token  = Helpers::UUID();
        $now    = new \DateTime();
        $expiry = new \DateTime();
        $expiry = $expiry->add(new \DateInterval('P1Y'));
     
        if(count($array))
        {
            //Create the m3u file
            $m3u    = tempnam(sys_get_temp_dir(), 'rbfy-queue') . '.m3u';

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

            //Register the download
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
     * Sort the tracks in the queue
     */
    public function sort()
    {
        $ids        = Request::getVar('ids', [] ,'POST');  
        $queue      = new Queue($this->user);
        $success    = $queue->sort($ids);
        $result     = $queue->load();

        $array = ['success' => $success , 'result' => $result ];  

        header('Content-Type: application/json; charset=utf-8');  
        echo json_encode($array);
        die();        

    }


}