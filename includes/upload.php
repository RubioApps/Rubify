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

use DateTime;
use Rubify\Framework\Factory;
use Rubify\Framework\SimpleXMLExtended;
use Rubify\Framework\Helpers;

require_once dirname(__DIR__) . '/vendor/autoload.php';

class Upload 
{
    protected $user;    
    protected $folder;
    protected $registry;
    protected $xml;    
    protected $data;   
    protected $nodes; 
    protected $header;    
    protected $entries;  
    protected $tags;  

    public function __construct( $user )
    {
        $this->user     = $user;
        $this->folder   = RBFY_USERS . DIRECTORY_SEPARATOR . 'uploads';
        $this->registry = $this->folder . DIRECTORY_SEPARATOR . 'registry.xml';

        if(!file_exists($this->registry))
        {
            file_put_contents($this->registry, '<?xml version = "1.0" encoding = "UTF-8" standalone = "yes" ?><uploads></uploads>');    
        }
        
        $this->xml      = new SimpleXMLExtended(file_get_contents($this->registry));                   
        $this->data     = $this->getRegistry();
        $this->header   = $this->data['summary'];
        $this->entries  = $this->data['entries'];
    }

    public function __destruct()
    {

    } 

    public function getFolder()
    {
        return $this->folder;
    }

    public function getRegistry()
    {        
        if(!empty($this->data))
            return $this->data;

        $now = new DateTime;
        $summary = new \stdClass;        
        $summary->user = $this->user;
        $summary->count= 0;
        $summary->size = 0;
        $summary->first = $now;
        $summary->last = $now;
        
        $array = [
            'summary' => $summary,
            'entries' => $this->getEntries()
        ];    
   
        //Read the current registry       
        $this->nodes  = $this->xml->xpath("//uploads/entry[@user='".$this->user."']");            
        if($this->nodes)
        {
            foreach($this->nodes as $node)
            {
                $attr = $node->attributes();
                $array['summary']->size += intval($attr->size);
                $array['summary']->count++;

                if($attr->uploaded < $array['summary']->first)
                    $array['summary']->first = $attr->uploaded;

                if($attr->uploaded >= $array['summary']->last)
                    $array['summary']->last = $attr->uploaded;                    
            }        
        }        
        $this->data = $array;          
        return $this->data;
    }      

    public function getEntries()
    {
        $array = [];
        $nodes = $this->xml->xpath("//uploads/entry[@user='{$this->user}']");        
        if(count($nodes))
        {
            foreach($nodes as $node)
            {
                $item = new \stdClass();

                //Get children
                foreach($node->children() as $key => $value)
                    $item->$key = $value->__toString(); 

                //Get attributes
                $attr = $node->attributes();
                foreach($attr as $key => $value)
                    $item->$key = $value->__toString();                                  
            
                $array[] = $item;
            }
        }
        return $array;     
    }    
    
    public function getScannedEntries()
    {
        $array  = $this->getEntries();
        $result = [];
        foreach($array as $item)
        {
            $track = $this->getTrack($item->file);
            if($track) $result[$track->oid] = $track;
        }
        return $result;
    }      

    public function getTrack( $filename )
    {
        $database   = Factory::getDatabase();
        $config     = Factory::getConfig();

        $sql = "SELECT `OB`.`OBJECT_ID`, `DT`.* FROM `OBJECTS` `OB` , `DETAILS` `DT`
        WHERE `OB`.`DETAIL_ID` = `DT`.`ID` 
        AND `DT`.`PATH` = '" . $this->folder . DIRECTORY_SEPARATOR . $filename . "'
        AND `OB`.`CLASS` = '" . RBFY_CLASS_TRACK . "';";

        $database->query($sql);
        if($row = $database->loadRow()){
            
            $item = new \stdClass;
            $item->oid = $row['OBJECT_ID'];

            foreach($row as $key => $value)
            {
                $key = strtolower($key);
                $item->$key = html_entity_decode($value);
            }

            $item->alias        = Helpers::encode($item->title);       
            $item->link         = Factory::Link('track', 'oid=' . $item->oid . ':' . $item->alias);            
    
            if($config->use_symlink)
            {    
                if(file_exists($item->path))
                {
                    $item->audio = Helpers::getRelAudio($item->path);
                } else {
                    $item->audio = '';
                }     
                $item->thumbnail = Factory::getAssets() . '/images/all-music.png';
            } else {
                $item->audio        = Factory::Link('track.audio','oid=' . $item->oid);  
                $item->thumbnail    = Factory::getAssets() . '/images/all-music.png';             
            }                     
    
            $item->audio        = Factory::Link('track.audio','oid=' . $item->oid);                        
            $item->duration_ms  = Helpers::durationToMilliseconds($item->duration);
            $item->duration_fm  = Helpers::formatMilliseconds($item->duration_ms);  
            $item->extra = [
                'lyrics'=> Factory::Link('track.lyrics', 'oid=' . $item->oid, 'format=json'),
                'info'  => Factory::Link('track.info', 'oid=' . $item->oid,'format=json'),
                'banner' => Factory::Link('track.banner', 'oid=' . $item->oid,'format=json'),
            ];                                                                          
            return $item;                            
        } else {
            return false;
        }
    }
    
    public function register($filename)
    {
        $token  = Helpers::UUID();
        $now    = new DateTime();    
        $source = $this->folder . DIRECTORY_SEPARATOR . $filename;            
        $ext    = strtolower(pathinfo($source , PATHINFO_EXTENSION));
        $target = $this->folder . DIRECTORY_SEPARATOR . $token . '.mp3';

        //If the extension if other than mp3, then convert
        if($ext != 'mp3')
        {
            $output = $this->folder . DIRECTORY_SEPARATOR . pathinfo($source,PATHINFO_FILENAME) . '.mp3';
            switch($ext)
            {
                case 'm4a':                        
                    $cmd = "ffmpeg -i '$source' -vn -c:a libmp3lame -q:a 4 -y '$output'";                    
                    break;
                case 'webm':
                    $cmd = "ffmpeg -i '$source' -vn -b:a 128k -ar 44100 -y '$output'";
                    break;
                case 'mp4':
                    $cmd = "ffmpeg -i '$source' -vn -b:a 128k -ar 44100 -y '$output'";
                    break;
            }
            exec($cmd);
            unlink($source);
            $source = $output;
        }

        //Tag the file
        $this->_tagFile($source);   

        //Rename the source to a token-based name
        rename($source,$target);            

        //Register the upload
        $root   = $this->xml->xpath('//uploads');
        $node   = $root[0]->addChild('entry');            
        $node->addAttribute('file' , $token . '.mp3');            
        $node->addAttribute('user' , $this->user);
        $node->addAttribute('size' , filesize($target));
        $node->addAttribute('uploaded', $now->format('c'));

        $node->addChild('original')->addCData($filename);
        foreach($this->tags as $key => $tag)
            $node->addChild($key)->addCData(join(',',$this->tags[$key]));
            
        return $this->xml->asXML($this->registry); 
    }      

    public function remove( $filename)
    {        
        foreach($this->nodes as &$node)
        {     
            $attr = $node->attributes();   
            if($attr->file == $filename)
            {
                if(file_exists($this->folder . DIRECTORY_SEPARATOR . $filename))
                    unlink($this->folder . DIRECTORY_SEPARATOR . $filename);

                unset($node[0]);
                break;
            }
        }
        return $this->xml->asXML($this->registry);         
    }

    public function empty()
    {        
        foreach($this->nodes as &$node)
        {     
            $attr = $node->attributes();   
            if(file_exists($this->folder . DIRECTORY_SEPARATOR . $attr->file))
                unlink($this->folder . DIRECTORY_SEPARATOR . $attr->file);

            unset($node[0]);            
        }
        return $this->xml->asXML($this->registry);         
    }


    protected function _tagFile ($source)
    { 
        $token  = Helpers::UUID();
        $now    = new DateTime();
     
        // Initialize getID3 engine        
        $getID3 = new \getID3();
        $getID3->setOption(['encoding'=>'UTF-8']);
        \getid3_lib::IncludeDependency( GETID3_INCLUDEPATH . 'write.php', __FILE__, true);                        

        //Write ID3 tags
        $writer = new \getid3_writetags;                        
        $writer->filename       = $source;
        $writer->tagformats = ['id3v1', 'id3v2.3'];;
        $writer->tag_encoding   = 'UTF-8';            
        $writer->overwrite_tags    = true; 
        $writer->remove_other_tags = true;  
            
        $title  = $_POST['title'] ?: Helpers::encode(pathinfo($source , PATHINFO_FILENAME));            
        $album  = $_POST['album'] ?: ucfirst($this->user); 
        $artist = $_POST['artist'] ?: ucfirst($this->user); 
        $genre  = $_POST['genre'] ?: 'Uploads';            

        $tags = [
            'title'                  => [$title],
            'artist'                 => [$artist],
            'album'                  => [$album],
            'genre'                  => [$genre],
            'year'                   => [$now->format('Y')],                
            'track_number'           => [++$this->header->count],
            'unique_file_identifier' => ['ownerid' => $this->user , 'data' => $token]
        ];
        $this->tags = $tags;
        $writer->tag_data = $this->tags ;
        return $writer->WriteTags(); 
    }           

}