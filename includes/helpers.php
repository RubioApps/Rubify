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
use Rubify\Framework\Language\Text;

class Helpers
{
    /**
     * Get the Object ID from the Query String and return it, splitted in the oid and the alias if it exists
     */
    public static function getId()
    {
        $oid = Request::getVar('oid','','GET');

        if(strstr($oid,':')){
            $parts  = explode(':',$oid);
            $id     = array_shift($parts);
            $alias  = join(':',$parts);
        } else {
            $id     = $oid;
            $alias  = '';
        }    
        return array($id , $alias)    ;
    }
    /**
     * Get the user password hash MD5
     * @param string $uid  Username
     * 
     * @return string The retrieved MD5 hash of the user password
     */
    public static function getPasswordHash($uid)
    {
        $hash  = null;
        if(($fp = fopen(RBFY_USERS . DIRECTORY_SEPARATOR . '.users', 'r')) !== false) 
        {                    
            while (!feof($fp))
            {
                $line = fgets($fp);
                if(strstr($line ,':')){                        
                    $parts = explode(':',$line);
                    if( strcmp( $parts[0] , $uid ) == 0)
                    {
                        $hash = trim($parts[1]);
                        break;
                    }
                }
            }                                              
        }
        fclose($fp);   
        return $hash;        
    }

    /**
     * Get the user level (user or admin)
     * @param string $uid  Username
     * 
     * @return string The retrieved user level
     */
    public static function getUserLevel($uid)
    {
        $level  = RBFY_USERS;
        if(($fp = fopen(RBFY_USERS . DIRECTORY_SEPARATOR . '.users', 'r')) !== false) 
        {                    
            while (!feof($fp))
            {
                $line = fgets($fp);
                if(strstr($line ,':')){                        
                    $parts = explode(':',$line);
                    if( strcmp( $parts[0] , $uid ) == 0)
                    {
                        $level = trim($parts[2]);
                        break;
                    }
                }
            }                                              
        }
        fclose($fp);   
        return $level;        
    }    

    /**
     * Get the folders from the MiniDLNA server
     */
    public static function getFolders()
    {
        $database = Factory::getDatabase();
        $sql = "SELECT * FROM SETTINGS";
        $database->query($sql);
        $rows = $database->loadRows();

        $ret = [];
        foreach ($rows as $row) {
            $item = new \stdClass();
            $item->key = $row['KEY'];
            $item->value = $row['VALUE'];
            $ret[] = $item;
        }
        return $ret;
    }   

    /**
     * Encode a string
     */
    public static function encode ( $string)
    {        
        $encoding = mb_detect_encoding($string);
        $string = mb_convert_encoding($string,'UTF-8',$encoding);          

        $string = htmlentities($string, ENT_NOQUOTES, 'UTF-8');
        $string = preg_replace('#&([A-za-z])(?:acute|cedil|caron|circ|grave|orn|ring|slash|th|tilde|uml);#', '\1', $string);
        $string = preg_replace('#&([A-za-z]{2})(?:lig);#', '\1', $string); 
        $string = preg_replace('#&[^;]+;#', '', $string); 
        $string = preg_replace('/[\s\%\'\"]+/','-', $string);           

        return strtolower($string);
    }

    /**
     * Decode a string
     */
    public static function decode ( $string)
    {        
        $encoding = mb_detect_encoding($string);
        $string = mb_convert_encoding($string,'UTF-8',$encoding);
        $array= preg_split('/[\s,\-]+/',$string);     
        $array = array_map('ucfirst' , $array);
        return join(' ',$array);
    }

    /**
     * Sanitize a filename, removing any character which is not a letter, number of space
     */
    public static function sanitize( $string)
    {
        $string = mb_ereg_replace("([^\w\s\d\-_~,;\[\]\(\).])", '', $string);
        $string = mb_ereg_replace("([\.]{2,})", '', $string);        
        return $string;
    }

    public static function formatFileSize($bytes , $precision =2 )
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB'); 
   
        $bytes = max($bytes, 0); 
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024)); 
        $pow = min($pow, count($units) - 1); 
       
        // Uncomment one of the following alternatives
        $bytes /= pow(1024, $pow);
        // $bytes /= (1 << (10 * $pow)); 
       
        return round($bytes, $precision) . $units[$pow];         
    }


    /**
     * Translate the generic labels used by MiniDLNA and the labels used in Rubify
     * 
     * @param string $string Folder name
     * 
     * @return string Label used in the .ini locals
     */
    public static function getFolderName($string)
    {
        $special = [
            '- All Albums -' => Text::_('ALL_ALBUMS'),
            '- All Artists -' => Text::_('ALL_ARTISTS'),
            '- All Songs -' => Text::_('ALL_SONGS'),
        ];        

        foreach($special as $k => $v)
            $string = str_replace($k,$v,$string);
        $string = html_entity_decode($string);
        return $string;
    }

    /**
     * Transform duration obtained from MiniDLNA (e.g. 0:04:15.026) and convert it into milliseconds
     * 
     * @param string $string Formatted MiniDLNA track duration
     * 
     * @return integer Milliseconds
     * 
     */
    public static function durationToMilliseconds($string)
    {
        if(!strlen($string) || !strstr($string,':'))
            return 0;

        $parts = explode(':', $string);
        $n = count($parts);
        $H = 0;

        $ms = 1000 * (float) $parts[$n-1];        
        $m = (int) $parts[$n-2];
        if($n>2) $H = (int) $parts[$n-3];        

        $time = (3600 * $H + 60 * $m) * 1000 + $ms;
        return $time;
    }

    /**
     * Formats milliseconds into a format hours minutes seconds (e.g. 5h 36m 4s)
     * 
     * @param integer $milliseconds 
     * 
     * @return string Formatted string
     * 
     */
    public static function formatMilliseconds($milliseconds)
    {

        if (!is_numeric($milliseconds)) return '';

        $seconds = floor($milliseconds / 1000);
        $minutes = floor($seconds / 60);
        $hours = floor($minutes / 60);
        $milliseconds = $milliseconds % 1000;
        $seconds = $seconds % 60;
        $minutes = $minutes % 60;

        if (!$hours) {
            if (!$minutes) {
                $format = '%us';
                $time = sprintf($format, $seconds);
            } else {
                $format = '%um %02us';
                $time = sprintf($format, $minutes, $seconds);
            }
        } else {
            $format = '%uh %um %02us';
            $time = sprintf($format, $hours, $minutes, $seconds);
        }

        return rtrim($time, '0');
    }

    public static function getGenreLink($genre)
    {
        $database = Factory::getDatabase();
        $sql = "SELECT `OB`.* FROM `OBJECTS` `OB` 
        WHERE `OB`.`NAME` = '$genre'
        AND `OB`.`CLASS` = '" . RBFY_CLASS_GENRE . "';";          

        $database->query($sql);
        $row = $database->loadRow();  
        
        if($row){
            $item = new \stdClass();
            $item->oid = $row['OBJECT_ID'] ;
            $item->name = $row['NAME'];
            $item->alias = Helpers::encode($item->name);
            $item->link = Factory::Link('genre','oid=' . $item->oid .':' . $item->alias);
            return $item->link ;
        }
        return false;
    }

    public static function getArtistLink($artist)
    {
        $database = Factory::getDatabase();
        $sql = "SELECT `OB`.* FROM `OBJECTS` `OB` 
        WHERE `OB`.`NAME` = '$artist'
        AND `OB`.`CLASS` = '" . RBFY_CLASS_ARTIST . "';";          

        $database->query($sql);
        $row = $database->loadRow();  
        
        if($row){
            $item = new \stdClass();
            $item->oid = $row['OBJECT_ID'] ;
            $item->name = $row['NAME'];
            $item->alias = Helpers::encode($item->name);
            $item->link = Factory::Link('browse','oid=' . $item->oid .':' . $item->alias);
            return $item->link ;
        }
        return false;
    }

    public static function getRelAudio($path)
    {
        $config = Factory::getConfig();
        $source = str_replace(DIRECTORY_SEPARATOR, '/' , $path);
        return $config->live_site . '/audio' . $source;
        
    }

    public static function getRelArtwork($path)
    {
        $config = Factory::getConfig();
        $root   = $config->minidlna['dir'] . DIRECTORY_SEPARATOR . 'art_cache';                
        $source = str_replace(DIRECTORY_SEPARATOR, '/' , substr($path , strlen($root)));
        return $config->live_site . '/cache/thumbnails' . $source;
        
    }

    public static function getLyrics($track)
    {
        $curl = curl_init();

        $api = 'https://sridurgayadav-chart-lyrics-v1.p.rapidapi.com/apiv1.asmx';
        $endpoint = 'SearchLyricDirect';
        $artist = urlencode($track->artist);
        $song = urlencode($track->name);

        curl_setopt_array($curl, [
            CURLOPT_URL => $api . '/' . $endpoint . '?artist=' . $artist . '&song=' . $song,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => [
                    "X-RapidAPI-Host: sridurgayadav-chart-lyrics-v1.p.rapidapi.com",
                    "X-RapidAPI-Key: SIGN-UP-FOR-KEY"
                ],
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
            return $response;
        }
    }

    public static function getArtwork($string)
    {
        $config = Factory::getConfig();        
        $source = 'https://www.allmusic.com/search/artists/%s';
        $path = '//div[@class="artist"]/div[@class="photo"]/a/img';

        //Sanitize string
        if(strstr($string,';')){
            $parts = explode(';',$string);
            $string = array_shift($parts);
        }

        if(preg_match('/([\s]+)feat([\s\.]+)/i',$string,$matches)){            
            $parts = explode($matches[0],$string);
            $string = array_shift($parts);
        }

        $string = trim(strtolower($string));
        $filename = preg_replace('/[^a-zA-Z0-9\-\._]/', '', $string);

        //If the file exists, return it
        if (file_exists(RBFY_CACHE . DIRECTORY_SEPARATOR . 'artists' . DIRECTORY_SEPARATOR . $filename . '.webp'))
            return $config->live_site . '/cache/artists/' . $filename . '.webp';

        //Get the image from the remote source
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => sprintf($source, urlencode($string)),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET"
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        //If there was an error, return the default image
        if ($err)
            return Factory::getAssets() . '/images/artist.png';

        //Browse the document and seek for the first available image
        $document = new \DOMDocument();
        libxml_use_internal_errors(true);
        $document->loadHTML($response);
        libxml_clear_errors();
        $xpath = new \DOMXpath($document);
        $images = $xpath->query($path);

        //If any image found, copy it locally then return the local file
        if ($images->length) {

            //Import the remote content
            $data = file_get_contents($images->item(0)->getAttribute('data-src'));

            //Source file
            $source = RBFY_CACHE . DIRECTORY_SEPARATOR . 'artists' . DIRECTORY_SEPARATOR . $filename . '.jpg';           
            $target = RBFY_CACHE . DIRECTORY_SEPARATOR . 'artists' . DIRECTORY_SEPARATOR . $filename . '.webp';

            //Save the remote content into the source file
            $fp = fopen($source, 'w');
            fwrite($fp, $data);
            fclose($fp);
            
            //Resize to square
            if(extension_loaded('mbstring') && extension_loaded('exif') && extension_loaded('gd'))
            {                
                if(!self::createThumbnail($source , $target))
                {
                    return Factory::getAssets() . '/images/artist.png';
                } else {
                    unlink($source);                
                    return $config->live_site . '/cache/artists/' . $filename. '.webp';
                }
            } else {
                return Factory::getAssets() . '/images/artist.png';
            }

            //Otherwise, return the default image            
        } else {
            return Factory::getAssets() . '/images/artist.png';
        }
    }

    public static function createThumbnail($filename, $target = null , $size = 150, $quality = 90)
    {
        // Deals only with jpeg
        if (exif_imagetype($filename) != IMAGETYPE_JPEG) {
            return false;
        }

        if (empty($target))
            $target = $filename;

        // Convert old file into img
        $orig   = imagecreatefromjpeg($filename);
        $w      = imageSX($orig);
        $h      = imageSY($orig);

        // Create new image
        $new    = imagecreatetruecolor($size, $size);

        // The image is square, just issue resampled image with adjusted square sides and image quality
        if ($w == $h) {
            imagecopyresampled($new , $orig , 0 , 0 , 0 , 0 , $size , $size , $w , $w);
        
        // The image is vertical, use x side as initial square side
        } elseif ($w < $h) {
            $x = 0;
            $y = round(($h - $w) / 2);
            imagecopyresampled($new , $orig , 0 , 0 , $x , $y , $size , $size , $w , $w);
        
        // The image is horizontal, use y side as initial square side
        } else {
            $x = round(($w - $h) / 2);
            $y = 0;
            imagecopyresampled($new , $orig , 0 , 0 , $x , $y , $size , $size , $h , $h);
        }        

        // Save it to the filesystem
        imagewebp($new , $target , $quality );

        // Destroys the images
        imagedestroy($orig);
        imagedestroy($new);

        return $target;

    }

    public static function UUID()
    {
        return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        // 32 bits for "time_low"
        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),

        // 16 bits for "time_mid"
        mt_rand( 0, 0xffff ),

        // 16 bits for "time_hi_and_version",
        // four most significant bits holds version number 4
        mt_rand( 0, 0x0fff ) | 0x4000,

        // 16 bits, 8 bits for "clk_seq_hi_res",
        // 8 bits for "clk_seq_low",
        // two most significant bits holds zero and one for variant DCE1.1
        mt_rand( 0, 0x3fff ) | 0x8000,

        // 48 bits for "node"
        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
        );
    }    

}
