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

use Rubify\Framework\Language\Text;

require dirname(__DIR__) . '/vendor/autoload.php';

class Remote
{
    protected $api;
    protected $track;

    public function __construct($track)
    {
        $this->track = $track;
    }


    public function __destruct()
    {
        unset($this->track);
    }    
    /**
     * Collects the lyrics from http://openapi.music.163.com/api based on the title and artist
     * 
     * @return mixed JSON string that contains the timestamped phrases of the lyrics or false if not found
     */
    public function lyrics()
    {
        $api = 'http://openapi.music.163.com/api';

        if(file_exists(RBFY_CACHE . DIRECTORY_SEPARATOR . 'lyrics' . DIRECTORY_SEPARATOR . $this->track->oid . '.json'))
        {
            return file_get_contents(RBFY_CACHE . DIRECTORY_SEPARATOR . 'lyrics' . DIRECTORY_SEPARATOR . $this->track->oid . '.json');
        }

        $array = ['success' => false  , 'lyrics' => []];

        //Any data?
        if ($this->track) {

            //Get the song       
            $params = ['api' => $api, 's' => urlencode($this->track->title . ' ' . $this->track->artist), 'type' => 1, 'limit' => 1];
            $response = json_decode($this->_request('/search/get/', $params), false);

            //Found?
            if ($response->code == 200) {
                if(!($list = $response->result->songs)) return; 
                if(!isset($list[0])) return;

                //Get the index
                $song = $list[0]->id;

                //Get the lyrics
                $params = ['api' => $api, 'id' => $song, 'lv' => -1];
                $response = json_decode($this->_request('/song/lyric', $params), false);

                //Found?
                if ($response->code == 200) {
                    if ($lyrics = $response->lrc->lyric)
                    {                    
                        $lines = explode("\n", $lyrics);
                        //Add a start line with time zero
                        foreach ($lines as $line) {
                            if (!strlen($line))
                                continue;
                            if (preg_match('/(\[[0-9]{2}:[0-9]{2}\.[0-9]{2}\])/', $line, $matches, PREG_OFFSET_CAPTURE)) {
                                $item = new \stdClass;
                                $item->time = preg_replace('/[\[\]]/', '', $matches[0][0]);
                                $item->time = Helpers::durationToMilliseconds($item->time);
                                $text = substr($line, strlen($matches[0][0]));
                                $item->text = $text;
                                $array['lyrics'][] = $item;
                            }
                        }

                        //Fix
                        if(count($array['lyrics'])>0){
                            if( intval($array['lyrics'][0]->time) > 0 )
                            {
                                $item = new \stdClass;
                                $item->time = 0;
                                $item->text = $this->track->title;
                                array_unshift($array['lyrics'] , $item);                          
                            }
                        }
                    }
                }
            }
        }

        //If any lyrics found, set success
        if(count($array['lyrics'])>0) 
        {
            $array['success'] = true;
            file_put_contents(RBFY_CACHE . DIRECTORY_SEPARATOR . 'lyrics' . DIRECTORY_SEPARATOR . $this->track->oid . '.json', json_encode($array, JSON_UNESCAPED_SLASHES));
        }

        //Serve the JSON object
        return json_encode($array, JSON_UNESCAPED_SLASHES);
    }

    /**
     * This function collects the picutes of an album based in the musicbrainz ID (MBID)
     * Cover: 'https://coverartarchive.org/release/' . $this->track->tags['mb_album_id'],  
     */
    public function banner()
    {
        $config = Factory::getConfig();
        $array = ['success' => false, 'images' => [] , 'response' => null];

        if(!$this->track || !($this->track->tags = $this->getTags())){
            return json_encode($array);          
        }
        
        if(isset($this->track->tags['mb_album_id']) && $this->track->tags['mb_album_id'] !== '')
        {
            $path = RBFY_CACHE . DIRECTORY_SEPARATOR . 'banners' . DIRECTORY_SEPARATOR . $this->track->tags['mb_album_id'];            

            if($config->use_cache){
                //Create the local path
                if(!file_exists($path)) mkdir($path);        

                if (file_exists($path) && $handle = opendir($path)) {
                    while (false !== ($filename = readdir($handle))) {
                        if ($filename != '.' && $filename != '..')
                            $array['images'][] = $config->live_site . '/cache/banners/' . $this->track->tags['mb_album_id'] . '/' . $filename;
                    }
                    closedir($handle);
                }        
            }
        }
        //If not image found so far, search for them!
        if(!count($array['images']))
        {            
            //If any data, any tag and music brainz album id (MBID) exists
            if ($this->track && isset ($this->track->tags) && isset($this->track->tags['mb_album_id']) && $this->track->tags['mb_album_id'] !== '') 
            {
                $mbid = $this->track->tags['mb_album_id'];

                //Look for the banner
                $params = ['api'  => 'https://coverartarchive.org'];
                $response = $this->_request('/release/' . $mbid , $params);

                //If no error
                if ($response !== false)
                {
                    $array['response'] = $response;
                    $rows = json_decode($response);

                    //If any result
                    if ($rows && isset($rows->images))
                    {
                        //Fulfill the list of images
                        foreach ($rows->images as $row)
                        {
                            //Use the front image only
                            //if(!$row->front) continue;

                            //If cache is enabled,store it
                            if($config->use_cache)
                            {
                                $parts = explode('/', parse_url($row->image, PHP_URL_PATH));
                                $filename = $parts[count($parts) - 1];

                                if(!strlen($filename)) continue;

                                //if the file does not exist yet
                                if (!file_exists($path . DIRECTORY_SEPARATOR . $filename)) {                                                                        
                                    if($content = file_get_contents($row->image))
                                    {
                                        $err = false;
                                        $fp = fopen($path . DIRECTORY_SEPARATOR . $filename, 'wb');
                                        $err = fwrite($fp, $content);
                                        fclose($fp);                                    
                                    }
                                }
                                if($err) continue;
                                $array['images'][] = $config->live_site . '/cache/banners/' . $mbid . '/' . $filename;

                            //Otherwise, check the content and push to the result
                            } else {
                                if($content = file_get_contents($row->image))
                                    $array['images'][] = $row->image;                                  
                                else
                                    $array['images'][] = Factory::getAssets() . '/images/all-music.png';
                            }
                        }
                    }
                }
            }
        }

        //If any image collected, set success to true
        if(count($array['images'])) $array['success'] = true;

        //Serve the response
        return json_encode($array);
    }

    /**
     * This function collects the information about a track based on the Musicbrainz ID
     * Musicbrainz entities:
     * area, artist, event, genre, instrument, label, place, recording, release, release-group, series, work, url
     * lookup:   /<ENTITY_TYPE>/<MBID>?inc=<INC>
     * browse:   /<RESULT_ENTITY_TYPE>?<BROWSING_ENTITY_TYPE>=<MBID>&limit=<LIMIT>&offset=<OFFSET>&inc=<INC>
     * search:   /<ENTITY_TYPE>?query=<QUERY>&limit=<LIMIT>&offset=<OFFSET>
     * Example:
     * Track: 'https://musicbrainz.org/ws/2/release?track=' . $this->track->tags['mb_track_id'] . '&inc=url-rels&fmt=json',    
     */
    public function info()
    {
        $array = ['success' => false, 'tags' => [] , 'relations' => [] , 'response' => null];

        if(!$this->track || !$this->getTags()){
            return json_encode($array);
        }

        //If any data, any tag and music brainz track id (MBID) exists
        if ($this->track && isset ($this->track->tags) && isset($this->track->tags['mb_track_id']) && $this->track->tags['mb_album_id'] !== '')
        {
            //Store the tags
            $array['tags'] = $this->track->tags;

            //Look for the info
            $params = [
                'api'   => 'https://musicbrainz.org/ws/2',
                'track' => $this->track->tags['mb_track_id'],
                'inc'   => 'url-rels',
                'fmt'   => 'json'
            ];

            $response = $this->_request('/release',$params);
            $array['response'] = $response;
            $item = json_decode($response);
            if(isset($item->releases[0]))
            {
                $relations = $item->releases[0]->relations;
                foreach($relations as $rel)
                {
                    $array['relations'][] = $rel->url->resource;
                }
            }
        }
        //If any relation collected, set success to true
        if(count($array['tags']) || count($array['relations'])) $array['success'] = true;  

        //Serve the response
        return json_encode($array);
    }

    /**
     * Get ID3 tags in an unified JSON format
     * The remote file can be pointed through the MiniDLNA UPNP server: http://{serverip}:{port}/MediaItems/{id}.{extension}
     * 
     * Do not forget to allow a reading access for www-data apache user
     * chmod -R 0775 /path/to/music
     * 
     * @return string JSON object
     */
    public function getTags()
    {   
        //If any data and no tag set yet
        if ($this->track && empty($this->track->tags))        
        {     
            $config = Factory::getConfig();
            $array = [];       
            $ext = substr($this->track->path, strrpos( $this->track->path, '.' ));

            //Copy and paste the original file
            $source = $config->minidlna['http'] . '/MediaItems/' . $this->track->id . $ext;
            $temp = tempnam(sys_get_temp_dir(), 'getID3') . $ext;
            copy($source , $temp);
            
            // Initialize getID3 engine
            $id3 = new \getID3;
            $info = $id3->analyze($temp);             
            
            //Destroy the temporary file
            unlink($temp);                     

            if (isset($info['tags'])) 
            {
                switch ($ext) {
                    case 'flac':
                        $tags = $info['tags']['vorbiscomment'];
                        break;
                    case 'mp3':
                    default:
                        $v1 = isset($info['tags']['id3v1']) ? $info['tags']['id3v1'] : [];
                        $v2 = isset($info['tags']['id3v2']) ? $info['tags']['id3v2'] : [];
                        $tags = array_merge($v1,$v2);
                }
                //Unify the tags                
                $array['artist'] = $this->_unify($tags, ['artist', 'artists', 'artistsort'], $this->track->artist);
                $array['track_number'] = $this->_unify($tags, ['track_number', 'tracknumber'], 1);
                $array['total_tracks'] = $this->_unify($tags, ['tracktotal', 'totaltracks', 'total_tracks'], 1);
                $array['total_discs'] = $this->_unify($tags, ['totaldiscs', 'disctotal'], 1);
                $array['disc_number'] = $this->_unify($tags, ['disc_number', 'discnumber', 'text|disc'], 1);
                $array['media_type'] = $this->_unify($tags, ['media_type', 'media'], 'CD');
                $array['release_type'] = $this->_unify($tags, ['releasetype', 'text|MusicBrainz Album Type'], Text::_('ALBUM'));
                $array['year'] = $this->_unify($tags, ['original_year', 'text|originalyear', 'year'], '--');
                $array['ASIN'] = $this->_unify($tags, ['asin', 'text|ASIN'], '');
                $array['publisher'] = $this->_unify($tags, ['publisher', 'label'], Text::_('UNKNOWN'));
                $array['country'] = $this->_unify($tags, ['releasecountry', 'MusicBrainz Album Release Country'], Text::_('UNKNOWN'));
                $array['part_collection'] = $this->_unify($tags, ['part_of_a_set', 'set'], '1/1');
                $array['contributions'] = $this->_unify($tags, ['involved_people_list', 'set'], '1/1');
                $array['album_artist'] = $this->_unify($tags, ['albumartist', 'album_artist_sort_order', 'albumartistsort'], '');
                $array['mb_track_id'] = $this->_unify($tags, ['musicbrainz_trackid', 'text|MusicBrainz Release Track Id'], '');
                $array['mb_album_id'] = $this->_unify($tags, ['musicbrainz_albumid', 'text|MusicBrainz Album Id'], '');
                $array['mb_artist_id'] = $this->_unify($tags, ['musicbrainz_artistid', 'text|MusicBrainz Artist Id'], '');
                $array['mb_album_artist_id'] = $this->_unify($tags, ['musicbrainz_albumartistid', 'text|MusicBrainz Album Artist Id'], '');
                $array['mb_acoustid_id'] = $this->_unify($tags, ['acoustid_id', 'text|Acoustid Id'], '');

                foreach ($array as $k => $v)
                    $array[$k] = is_array($v) ? $v[0] : $v;                   

                if(count($array)>0) 
                {
                    return $array;
                }                
            }
        }
        //If any tag collected, send true
        
        return false;
    }        

    /**
     * Unify the tags in a unique version independently of the ID3 version
     * @param array $tags       Array of tags found from the ID3 parser
     * @param array $labels     Array of candidate labels to be merged
     * @param string $default   Default value if the merge did not succeeded
     * 
     * @return string   Returns the unified tag value
     */
    protected function _unify(&$tags, $labels, $default = '')
    {
        while (count($labels)) {
            $item   = &$tags;
            $key    = $labels[0];

            if (strstr($labels[0], '|')) {
                $parts = explode('|', $labels[0]);
                if (!isset ($tags[$parts[0]]))
                    return $default;

                $item = &$tags[$parts[0]];
                $key = $parts[1];
            }

            if (isset ($item[$key])) {
                $value = $item[$key];
                while (count($labels)) {
                    unset($item[$key]);
                    $key = array_shift($labels);
                }
                return $value;
            }
            array_shift($labels);
            unset($item[$key]);
        }
        return $default;
    }

    /**
     * Send a request to a given endpoint
     * @param string $endpoint
     * @param array $params
     * @param array $headers
     * 
     * @return mixed Returns the remote response or false if it fails
     */
    private function _request($endpoint, $params = null , $headers = null)
    {
        if (!empty ($params)) {
            if (is_array($params)) {
                $api = $params['api'] ?: $this->api;
                unset($params['api']);
                $url = $api . $endpoint . (count($params) ? '?' . http_build_query($params) : '');
            } else {
                $url = $this->api . $endpoint . '/' . $params;
            }
        } else {
            $url = $this->api . $endpoint;
        }

        $basics = [
            'accept' => 'application/json',
            'user-agent' => 'Rubify/1.0'
        ];

        if(!empty($headers)){
            array_walk($headers,'strtolower');            
            $headers = array_merge($basics,$headers);   
        } else {
            $headers = $basics;
        }
             
        $http_headers = [];
        foreach($headers as $k=>$v)
            $http_headers[] = $k . ': ' . $v;
        
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 2,
            CURLOPT_TIMEOUT => 30,             
            CURLOPT_HTTPHEADER => $http_headers
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        return $err ? false : $response;
    }        
}