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
defined('_RBFYEXEC') or die;

// Global definitions
$parts = explode(DIRECTORY_SEPARATOR, RBFY_BASE);

// Paths
define('RBFY_ROOT', implode(DIRECTORY_SEPARATOR, $parts));
define('RBFY_SITE', RBFY_ROOT);
define('RBFY_CONFIGURATION', RBFY_ROOT);
define('RBFY_INCLUDES', RBFY_ROOT . DIRECTORY_SEPARATOR . 'includes');
define('RBFY_MODELS', RBFY_ROOT . DIRECTORY_SEPARATOR . 'models');
define('RBFY_STATIC', RBFY_ROOT . DIRECTORY_SEPARATOR . 'includes');
define('RBFY_THEMES', RBFY_BASE . DIRECTORY_SEPARATOR . 'templates');
define('RBFY_VENDOR', RBFY_BASE . DIRECTORY_SEPARATOR . 'vendor');
define('RBFY_USERS', RBFY_BASE . DIRECTORY_SEPARATOR . 'users');
define('RBFY_QUEUE', RBFY_USERS . DIRECTORY_SEPARATOR . 'queue');
define('RBFY_PLAYLIST', RBFY_USERS . DIRECTORY_SEPARATOR . 'playlist');
define('RBFY_HISTORY', RBFY_USERS . DIRECTORY_SEPARATOR . 'history');
define('RBFY_CACHE', RBFY_BASE . DIRECTORY_SEPARATOR . 'cache');
define('RBFY_SEF', RBFY_BASE . DIRECTORY_SEPARATOR . 'sef');

// Container classes
define('RBFY_CLASS_FOLDER','container.storageFolder');
define('RBFY_CLASS_ALBUM','container.album');
define('RBFY_CLASS_ALBUM_MUSIC','container.album.musicAlbum');
define('RBFY_CLASS_TRACK','item.audioItem.musicTrack');
define('RBFY_CLASS_ARTIST','container.person.musicArtist');
define('RBFY_CLASS_GENRE','container.genre.musicGenre');
define('RBFY_CLASS_PLAYLIST','container.playlistContainer');
define('RBFY_CLASS_RECENT','container.playlistContainer');

// Errors
define('ERR_NONE', 0);
define('ERR_INVALID_TOKEN', 500);

// Security
define('RBDY_USER', 'user');
define('RBFY_ADMIN', 'admin');
define('IV_KEY', '8w)kz^r71Z^V]*X');


