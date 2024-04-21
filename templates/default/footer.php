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

use Rubify\Framework\Language\Text;

?> 
<footer class="rbfy-footer fixed-bottom p-0">    
    <audio id="audioFile"></audio>
    <div class="container-xxl row row-cols-2 row-cols-md-3 mx-auto h-100">        
        <!-- Player buttons -->
        <div class="col-6 col-md-3 text-nowrap p-1 my-auto">
            <div class="player-controls text-center my-auto p-0">
                <button id="prev" class="btn" data-bs-toggle="tooltip" title="<?=Text::_('PLAYER_PREV');?>">
                    <i class="bi bi-rewind"></i>
                </button>
                <button id="play" class="btn" data-bs-toggle="tooltip" title="<?=Text::_('PLAYER_PLAY');?>">
                    <i class="bi bi-play"></i>
                </button>
                <button id="next" class="btn" data-bs-toggle="tooltip" title="<?=Text::_('PLAYER_NEXT');?>">
                    <i class="bi bi-fast-forward"></i>
                </button>
            </div>
        </div>
        <!-- Artwork -->
        <div class="d-none col-md-1 d-md-inline p-0 my-auto">
            <img class="img-fluid album-art" 
                src="<?= $factory->getAssets() . '/images/all-music.png'; ?>"  
                data-default="<?= $factory->getAssets() . '/images/all-music.png'; ?>" />
        </div>
        <!-- Currently playing -->
        <div id="song-info" class="col-6 col-md-8 p-1 my-auto">
            <div id="player-bar" class="row row-cols-3 ps-2 pe-2 me-1 me-md-0">
                <div class="d-none d-md-inline col-1 p-1 text-center" id="currentTime"></div>
                <div class="col-12 col-md-10 p-0" id="progress-bar">
                    <div id="progress-value"><i id="progress-marker" class="bi bi-circle-fill"></i></div>
                </div>
                <div class="d-none d-md-inline col-1 p-1 text-start" id="duration"></div>
            </div>
            <div id="loading-song" class="d-flex justify-content-center p-0">
                <div class="spinner-border text-light" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
            <div id="current-song" class="d-none row ps-2">
                <div class="col">
                    <div class="song-title text-truncate m-0 p-0" data-default="<?= Text::_('PLAYER_NO_SONG'); ?>">
                        <?= Text::_('PLAYER_NO_SONG'); ?>
                    </div>
                    <div class="song-artist text-truncate m-0 p-0" data-default="<?= Text::_('PLAYER_UNKOWN_ARTIST'); ?>">
                        <?= Text::_('PLAYER_UNKOWN_ARTIST'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div> 
</footer>
