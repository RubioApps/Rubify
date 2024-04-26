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

require_once RBFY_VENDOR . DIRECTORY_SEPARATOR . 'autoload.php';

use Rubify\Framework\Helpers;
use Rubify\Framework\Remote;
use Rubify\Framework\Model;

class modelTrack extends Model
{
    protected $oid;

    public function display()
    {
        if(!$this->oid)
            list($this->oid) = Helpers::getId();

        //Object not found
        if(!($this->data = $this->_getTrack())) die();

        //Get ID3 tags
        $remote = new Remote($this->data);
        $this->data->tags = $remote->getTags();

        $this->page->data = $this->data;
        parent::display();

    }

    public function json()
    {
        if(!$this->oid)
            list($this->oid) = Helpers::getId();

        if (!$this->data)
            $this->data = $this->_getTrack();

        if ($this->data) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($this->data);
            die (0);
        }
    }

    public function get( $oid = null)
    {
        $this->oid = $oid;
        return $this->_getTrack();
    }

    public function lyrics()
    {
        if(!$this->oid)
            list($this->oid) = Helpers::getId();

        //Object not found
        if(!($this->data = $this->_getTrack())) die();        

        header('Content-Type: application/json; charset=utf-8');
        $remote = new Remote($this->data);
        echo $remote->lyrics();
        exit(0);        
    }

    public function banner()
    {
        if(!$this->oid)
            list($this->oid) = Helpers::getId();

        //Object not found
        if(!($this->data = $this->_getTrack())) die();

        header('Content-Type: application/json; charset=utf-8');
        $remote = new Remote($this->data);
        echo $remote->banner();
        exit(0);    
    }

    public function info()
    {
        if(!$this->oid)
            list($this->oid) = Helpers::getId();

        //Object not found
        if(!($this->data = $this->_getTrack())) die();

        header('Content-Type: application/json; charset=utf-8');
        $remote = new Remote($this->data);
        echo $remote->info();
        exit(0);  
    }

    /**
     * Public method called by task=track.audio that serrves the audio streaming
     * @return mixed
     */
    public function audio()
    {
        $CHUNK_SIZE = 1024 * 1024; //1024KBytes

        ob_end_clean();

        if(!$this->oid)
            list($this->oid) = Helpers::getId();

        if (!$this->data)
            $this->data = $this->_getTrack();

        if ($this->data) {
            if (file_exists($this->data->path))
            {                
                $filename = pathinfo($this->data->path, PATHINFO_FILENAME);
                $size = filesize($this->data->path);
                $time = date('r', filemtime($this->data->path));
                $offset = 0;
                $end = $size - 1;
                $fp = fopen($this->data->path, 'rb');
                stream_set_chunk_size($fp, $CHUNK_SIZE);
                stream_set_read_buffer($fp, $CHUNK_SIZE);
                if (!$fp) {
                    header("HTTP/1.1 505 Internal server error");
                    die();
                }

                if (isset($_SERVER['HTTP_RANGE'])) {
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
                header('Content-Type: ' . $this->data->mime);                                
                header('Content-Length:' . (($end - $offset) + 1));
                header('Content-Disposition: inline; filename=' . $filename);                

                $cur = $offset;
                fseek($fp, $offset);
                while (!feof($fp) && $cur <= $end && !connection_aborted()) {
                    print fread($fp, min( $CHUNK_SIZE , ($end - $cur) + 1));
                    $cur += $CHUNK_SIZE;
                }
                fclose($fp);
            }
        }
    }
    /**
     * Public method called by task=track.thumbnail to get the thumbnail of a track
     */
    public function thumbnail()
    {
        $config = Factory::getConfig();

        if(!$this->oid)
            list($this->oid) = Helpers::getId();
        
        if (!$this->data)
            $this->data = $this->_getTrack();

        if ($this->data) {
            if (file_exists($this->data->artwork))
            {
                //If not using the symbolic link, copy the artwork in a local folder of the www and send it
                if (!$config->use_symlink) {
                    $path   = RBFY_CACHE . DIRECTORY_SEPARATOR . 'thumbnails';
                    $ext    = pathinfo($this->data->artwork, PATHINFO_EXTENSION);

                    if(!file_exists($path . DIRECTORY_SEPARATOR . $this->oid . '.' . $ext)) {                    
                        $this->data->artwork = Helpers::createThumbnail($this->data->artwork , $path . DIRECTORY_SEPARATOR . $this->oid . '.' . $ext);
                    }
                }

                $time = date('r', filemtime($this->data->artwork));
                header('Last-Modified: ' . $time);
                header('Cache-Control: max-age=86400');
                header('Pragma: Public');
                header('Content-Type: ' . mime_content_type($this->data->artwork));
                header('Content-Length: ' . filesize($this->data->artwork));                
                readfile($this->data->artwork);
                exit (0);                

            }
        }
        header('HTTP/1.1 301 Moved Permanently');
        header('Location: ' . Factory::getAssets() . '/images/all-music.png');
        exit(0);
    }

    /**
     * Build the track object based on the database and ID3 tags
     * @param mixed $oid Object Id
     * @return mixed stdClass object
     */
    protected function _getTrack()
    {
        if ($this->data)
            return $this->data;

        if(!$this->oid)
            list($this->oid) = Helpers::getId();

        $config = Factory::getConfig();

        //Get the object
        $className = RBFY_CLASS_TRACK;
        $sql = "SELECT `OB`.`ID` , `OB`.`REF_ID` , `OB`.`OBJECT_ID`, `OB`.`NAME` , `DT`.*, `AA`.`PATH` `PICTURE`
            FROM `OBJECTS` `OB` 
            LEFT JOIN `DETAILS` `DT` ON `OB`.`DETAIL_ID`=`DT`.`ID`
                LEFT JOIN `ALBUM_ART` `AA` ON `DT`.`ALBUM_ART`=`AA`.`ID`
            WHERE `OB`.`OBJECT_ID` = '{$this->oid}'
            AND `OB`.`CLASS`= '$className'
            ;";

        $this->database->query($sql);

        if ($row = $this->database->loadRow())
        {          
            $item = new \stdClass();
            $item->id           = $row['ID'];
            $item->oid          = $row['OBJECT_ID'];            
            $item->title        = html_entity_decode($row['TITLE']);
            $item->name         = html_entity_decode($row['NAME']);
            $item->alias        = Helpers::encode($item->title);
            $item->path         = $row['PATH'];
            $item->genre        = $row['GENRE'];
            $item->artist       = html_entity_decode($row['ARTIST']);
            $item->creator      = html_entity_decode($row['CREATOR']);
            $item->artwork      = $row['PICTURE'];
            $item->duration     = $row['DURATION'];
            $item->duration_ms  = Helpers::durationToMilliseconds($item->duration);
            $item->duration_fm  = Helpers::formatMilliseconds($item->duration_ms);
            $item->bitrate      = $row['BITRATE'];
            $item->samplerate   = $row['SAMPLERATE'];
            $item->channels     = $row['CHANNELS'];
            $item->mime         = $row['MIME'];
            $item->link         = Factory::Link('track','oid=' . $item->oid . ':' . $item->alias);    
            $item->glink        = Helpers::getGenreLink($item->genre);
            $item->alink        = Helpers::getArtistLink($item->artist);
            $item->isfavorite   = $this->_isfavorite();

            //Get the parent
            $className = RBFY_CLASS_ALBUM_MUSIC;
            $sql = "SELECT `PR`.* , `OB`.`OBJECT_ID` AS `OID` 
                FROM `OBJECTS` `OB`  , `OBJECTS` `PR` 
                WHERE `OB`.`PARENT_ID` = `PR`.`OBJECT_ID` 
                AND `PR`.`CLASS`= '$className' 
                AND (`OB`.`REF_ID` = '" . $row['OBJECT_ID'] . "' OR `OB`.`OBJECT_ID` = '" . $row['OBJECT_ID'] . "' )
                ORDER BY LENGTH(`PR`.`PARENT_ID`)
                ";
            $this->database->query($sql);                    
            if($ref = $this->database->loadRow())
            {
                $parent = new \stdClass();
                $parent->oid    = $ref ['OBJECT_ID'] ;
                $parent->title  = Helpers::getFolderName($ref ['NAME']);
                $parent->name   = Helpers::getFolderName($ref ['NAME']);
                $parent->alias  = Helpers::encode($parent->name);                
                $parent->link   = Factory::Link('album','oid=' . $parent->oid . ':' . $parent->alias);    
                $item->parent = $parent;
            }

            if ($config->use_symlink) {

                if (file_exists($item->path)) {
                    $item->audio = Helpers::getRelAudio($item->path);
                } else {
                    $item->audio = '';
                }

                if (file_exists($item->artwork)) {
                    $item->thumbnail = Helpers::getRelArtwork($item->artwork);
                } else {
                    $item->thumbnail = Factory::getAssets() . '/images/all-music.png';
                }
            } else {
                $item->audio = Factory::Link('track.audio', 'oid=' . $item->oid);
                $item->thumbnail = Factory::Link('track.thumbnail', 'oid=' . $item->oid);
            }

            $item->extra = [
                'lyrics' => Factory::Link('track.lyrics', 'oid=' . $item->oid, 'format=json'),
                'info' => Factory::Link('track.info', 'oid=' . $item->oid, 'format=json'),
                'banner' => Factory::Link('track.banner', 'oid=' . $item->oid, 'format=json'),
            ];
            return $item;
        }
        return false;
    }

    private function _isfavorite()
    {
        $playlist = new Playlist($this->user);
        return $playlist->isFavorite($this->oid);
    }       
   

}