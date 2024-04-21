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

use Rubify\Framework\Helpers;
use Rubify\Framework\Language\Text;

list($oid , $alias) = Helpers::getId();
?>
<div id="track-menu" class="offcanvas offcanvas-bottom container">
    <div id="track-menu-btn" class="d-none col-2 text-end my-auto p-0 m-0 pe-2">
        <a class="bi bi-three-dots-vertical fs-2" data-bs-toggle="offcanvas" data-bs-target="#track-menu" aria-expanded="false"></a>
    </div>    
    <div class="row row-cols-2 border-bottom mt-1">
        <div class="col-11 p-1">
            <div class="track-menu-title text-truncate"></div>
        </div>
        <div class="col-1 p-1">
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" data-bs-target="#track-menu" aria-label="Close"></button>
        </div>
    </div>
    <ul class="track-menu-options nav justify-content-center mt-3">
        <li class="nav-item m-1">
            <button class="btn btn-primary bi bi-play" data-action="play" data-option="false">
                <span class="d-none d-sm-inline">&nbsp;<?= Text::_("PLAY");?>&nbsp;</span>
            </button>
        </li>
        <li class="nav-item m-1">
            <button class="btn btn-warning bi bi-info-circle" data-action="view" data-option="false">
                <span class="d-none d-sm-inline">&nbsp;<?= Text::_("VIEW");?>&nbsp;</span>
            </button>
        </li>
        <li class="nav-item m-1">
            <button class="btn btn-success bi bi-plus-circle" data-action="queue" data-option="false">
                <span class="d-none d-sm-inline">&nbsp;<?= Text::_("QUEUE_ADD");?>&nbsp;</span>
            </button>
        </li>
        <li class="nav-item m-1">
            <button class="btn btn-secondary bi bi-heart" data-action="fav-add" data-option="true">
                <span class="d-none d-sm-inline">&nbsp;<?= Text::_("FAV_ADD");?>&nbsp;</span>
            </button>
        </li>
        <li class="nav-item m-1">
            <button class="btn btn-danger bi bi-heart-fill" data-action="fav-remove" data-option="true">
                <span class="d-none d-sm-inline">&nbsp;<?= Text::_("FAV_REMOVE");?>&nbsp;</span>
            </button>
        </li>  
        <li class="nav-item m-1">
            <button class="btn btn-info bi bi-music-note-list" data-action="playlist-remove" data-option="true">
                <span class="d-none d-sm-inline">&nbsp;<?= Text::_("PLAYLIST_REMOVE");?>&nbsp;</span>
            </button>
        </li>  
        <li class="nav-item m-1">
            <button class="btn btn-info bi bi-music-note-list" data-action="playlist-add" data-option="true">
                <span class="d-none d-sm-inline">&nbsp;<?= Text::_("PLAYLIST_ADD");?>&nbsp;</span>
            </button>
        </li> 
        <li class="nav-item m-1">
            <button class="btn btn-danger bi bi-clock-history" data-action="history-remove" data-option="true">
                <span class="d-none d-sm-inline">&nbsp;<?= Text::_("HISTORY_REMOVE");?>&nbsp;</span>
            </button>
        </li>  
        <li class="nav-item m-1">
            <button class="btn btn-danger bi bi-trash3" data-action="upload-remove" data-option="true">
                <span class="d-none d-sm-inline">&nbsp;<?= Text::_("UPLOAD_REMOVE");?>&nbsp;</span>
            </button>
        </li>                                        
    </ul>
</div>

