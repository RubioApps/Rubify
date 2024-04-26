# Rubify
Rubify is a web application written in PHP8.x for MiniDLNA, Spotify-like.
MiniDLNA is a light DLNA UPnP server largely based on MusicBrainz, that creates a gallery of media sources. When used for audio files, it allows to class them automatically based on the tags of the audio files.

Rubify is an interface that allows you to browse your audio files and play them, to manage custom playlists and favorites, put the tracks in a queue, get the lyrics remotely,... 
You can navigate through the menus without stopping the music. It's is not obstrusive, all the operations are done asynchronously to let you enjoy your favorite tracks.

- Demo: https://famillerubio.com/rubify/
- User: guest
- Password: Guest#1234

![image](https://github.com/RubioApps/Rubify/assets/155658204/672b93ae-64b1-4124-b24d-cf2e66f58057)
![image](https://github.com/RubioApps/Rubify/assets/155658204/bb5d01e3-9787-447a-bbdb-b84f24a59949)
![image](https://github.com/RubioApps/Rubify/assets/155658204/0ce593a5-73de-48c1-93a0-47181a0d1cd6)
![image](https://github.com/RubioApps/Rubify/assets/155658204/19a9573f-d2bc-4d51-9a47-124e10ffbffc)
![image](https://github.com/RubioApps/Rubify/assets/155658204/fcd9d8e4-f4b9-4826-85e3-71f18f32fe53)
![image](https://github.com/RubioApps/Rubify/assets/155658204/671c8ba0-0504-4e37-837f-e82bd6de07fc)

You can also browse by Album, Artist, Genre, Folder, create your own playlists, check the history and upload your audio files to the server directly from the web application.
This web app uses a default template based on [Bootstrap 5](https://getbootstrap.com) and [JQuery](https://jquery.com/), which is fully responsive for mobile devices.

This is NOT an Android APK but a Web Application (like a Website) and you can access to it with any browser like Chrome or Firefox.
From server side, it is recommended to run this web app under Apache2 for domestic purposes

## Table of contents

- [Requirements](#requirements)
- [Build your folder](#build-your-folders)
- [Apache2 setup](#apache-setup)
- [MiniDLNA setup](#minidlna-setup)
- [Configuration](#configuration)
- [License](#license)

## Requirements

- miniDLNA running as a daemon service in your server. Further info at https://help.ubuntu.com/community/MiniDLNA
- Apache2 Web Server
- PHP8.x, fpm preferred
- PHP8.x SQLite Extension
- getID3 installer with composer. Further info at https://www.getid3.org>

## Build your folders

The most efficient and secured structure to run this web app is

```
yoursite.com 
│   configuration.php
│   index.php
│   README.md
|   ...
└───cache
└───includes
└───local
└───models
└───static
└───templates
└───vendor    
```

## Apache2 setup

You can create a directory in your main Apache web application that points to */yoursite.com* folder.
The whole Apache2 setup (mysite.conf) would be like this
```
    Alias "/rubify" "/path/to/your/site/public"
    <Directory "/path/to/your/site/public">  
      DirectoryIndex index.php index.html
      Options FollowSymLinks
      AllowOverride All
      Require all granted            
      <IfModule "mod_headers.c">        
        Header always set Access-Control-Allow-Origin "*"
        Header always set Access-Control-Allow-Methods "POST, GET, OPTIONS, DELETE, PUT"
        Header always set Access-Control-Allow-Headers "x-requested-with, Content-Type, origin, authorization, accept, client-security-token"
        Header always set Access-Control-Expose-Headers "Content-Security-Policy, Location"
        Header always set Access-Control-Max-Age "600"        
      </IfModule>        
      <FilesMatch "\.php$">
        SetHandler "proxy:unix:/run/php/php8.1-fpm.sock|fcgi://localhost/"
      </FilesMatch>        
    </Directory>
    <Directory "/path/to/your/site/users"> 
      Order deny,allow
      Deny from all
    </Directory>
```
## MiniDLNA setup
You must setup the minidlna.conf file to force the refresh. Hereinafter a functional example:
```
user=minidlna
media_dir=A,/path/to/your/uploads
media_dir=A,/path/to/your/dlna/storage
db_dir=/var/lib/minidlna
log_dir=/var/log/minidlna
root_container=M
port=8200
presentation_url=https://yoursite.com
inotify=yes
notify_interval=60
```

## Configuration

Rubify can be fully configured by overriding the file [configuration.php](https://github.com/RubioApps/Rubify/blob/main/configuration.php).

```
class RbfyConfig
{
        public $sitename  = 'Rubify';
        public $live_site = 'https://yoursite.com';     
        public $use_cache = true;
        public $use_symlink = false;        
        public $use_autolog = false;
        public $enable_upload = true;        
        public $key      = 'put-here-a-long-encryption-key';
	    public $list_limit = 60;
        public $minidlna = [
                'dir'   => '/var/lib/minidlna',
                'http'  => 'http://192.168.2.1:8200'
                ];
	public $theme = 'default';
}
```
- sitename: (string) Your site name
- live_site: (string) Your host URL
- use_cache: (bool) If you want to store the thumbnails into a local directory under your webserver root. This will avoid to use symbolic links
- use_symlink: (bool) Not recommended. If your webserver runs on the same machine than minidlna, you can create a symlink at /webroot/cache/thumbnails that points to /var/lib/minidlna/art_cache. 
- use_autolog: (bool) Not recommended. This allow to login to Rubify automatically if the client is within the same network than the webserver. For instance, if your host IP is 192.168.1.1, all the clients connected from 192.168.1.0/24 will be logged without using a user/password. This will disable the use of a user profile. 
- enable_upload: (bool) This allows a user to upload his own music files. The allowed formats are webm, m4a, mp3. All of them will be transcoded into mp3 and the tags ID3 will be added conveniently. MiniDLNA will detect them and classify in the gallery
- key: (string) This is an encryption key used for securtiy purposes. The user's databases and cookies are all encrypted with. The more the key is complex, the more the security is enhanced.
- list_limit: (integer) This is the number of items to display on a page. Default is 60
- minidlna: (array) This contains the details of your minidlna server
-     dir: (string) Directory of the files.db database
-     http: (string) URL as written in the /etc/minidlna.conf file.

## Legal

No video files are stored in this repository. The repository simply contains user-submitted links to publicly available video stream URLs, which to the best of our knowledge have been intentionally made publicly by the copyright holders. If any links in these playlists infringe on your rights as a copyright holder, they may be removed by sending a [pull request](https://github.com/RubioApps/Rubify/pulls) or opening an [issue](https://github.com//RubioApps/Rubify/issues/new?assignees=freearhey&labels=removal+request&template=--removal-request.yml&title=Remove%3A+). However, note that we have **no control** over the destination of the link, and just removing the link from the playlist will not remove its contents from the web. Note that linking does not directly infringe copyright because no copy is made on the site providing the link, and thus this is **not** a valid reason to send a DMCA notice to GitHub. To remove this content from the web, you should contact the web host that's actually hosting the content (**not** GitHub, nor the maintainers of this repository).

## License

GNU GENERAL PUBLIC LICENSE

Version 3, 29 June 2007

Copyright (C) 2007 Free Software Foundation, Inc.
<https://fsf.org/>

Everyone is permitted to copy and distribute verbatim copies of this
license document, but changing it is not allowed.
