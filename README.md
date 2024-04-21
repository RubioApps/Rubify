# Rubify
Web application written in PHP8.x for MiniDLNA, Spotify-like.
It allows to browse your audio files and play them.

You can browse by Album, Artist, Genre, Folder, create your playlists, check the history and upload audio files to your server directly from the app.

This web apps uses a default template based on [Bootstrap 5](https://getbootstrap.com) and [JQuery](https://jquery.com/), which is fully responsive for mobile devices.

It is recommended to run this web app under Apache2 for domestic purposes

## Table of contents

- [Requirements](#requirements)
- [Build your folder](#build-your-folders)
- [Apache2 setup](#apache-setup)
- [Configuration](#configuration)
- [License](#license)

## Requirements

- miniDLNA running as a daemon service in your server. Further info at https://help.ubuntu.com/community/MiniDLNA
- Apache2 Web Server
- PHP8.x
- getID3. Further info at https://www.getid3.org>

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
## Configuration

Rubify can be fully configured by overriding the file [configuration.php](https://github.com/RubioApps/Rubify/blob/main/configuration.php).

## Legal

No video files are stored in this repository. The repository simply contains user-submitted links to publicly available video stream URLs, which to the best of our knowledge have been intentionally made publicly by the copyright holders. If any links in these playlists infringe on your rights as a copyright holder, they may be removed by sending a [pull request](https://github.com/RubioApps/Rubify/pulls) or opening an [issue](https://github.com//RubioApps/Rubify/issues/new?assignees=freearhey&labels=removal+request&template=--removal-request.yml&title=Remove%3A+). However, note that we have **no control** over the destination of the link, and just removing the link from the playlist will not remove its contents from the web. Note that linking does not directly infringe copyright because no copy is made on the site providing the link, and thus this is **not** a valid reason to send a DMCA notice to GitHub. To remove this content from the web, you should contact the web host that's actually hosting the content (**not** GitHub, nor the maintainers of this repository).

## License

GNU GENERAL PUBLIC LICENSE

Version 3, 29 June 2007

Copyright (C) 2007 Free Software Foundation, Inc.
<https://fsf.org/>

Everyone is permitted to copy and distribute verbatim copies of this
license document, but changing it is not allowed.