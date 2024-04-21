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

use \Rubify\Framework\Language\Text;
?>
<div id="pane-track">
    <div class="pane-tab-up active"></div>
    <div class="pane-content">        
        <div id="song-controls" class="row">        
            <div class="col-1 d-none d-sd-inline"></div>
            <div class="text-sd-center col-10 text-start">
                <button id="rewind" class="btn bi bi-arrow-counterclockwise" data-bs-toggle="tooltip" title="<?=Text::_('PLAYER_REWIND');?>">
                    <i class="d-none d-md-inline">5s</i>
                </button>
                <button id="loop" class="btn bi bi-repeat" style="color:grey" data-bs-toggle="tooltip" title="<?=Text::_('PLAYER_LOOP');?>">
                </button>
                <button id="forward" class="btn bi bi-arrow-clockwise" data-bs-toggle="tooltip" title="<?=Text::_('PLAYER_FORWARD');?>">
                    <i class="d-none d-md-inline">5s</i>
                </button>
                <button id="volume" class="btn bi bi-volume-up" data-bs-toggle="tooltip" title="<?=Text::_('PLAYER_VOLUME');?>">
                </button>
                <button id="favorite" class="btn bi bi-heart" style="color:grey" data-bs-toggle="tooltip" title="<?=Text::_('PLAYER_FAV');?>">
                </button>
                <button id="view-track" class="btn bi bi-info-circle" data-bs-toggle="tooltip" title="<?=Text::_('PLAYER_INFO');?>">
                </button>                
            </div>
            <div class="pane-tab-down col-2"></div>
        </div>
        <div id="song-view">
            <canvas id="equalizer" class="d-none"></canvas>
            <div id="song-lyrics" class="text-center mx-auto p-3"></div>                
            <div id="pane-info"></div>        
        </div>
    </div>
</div>
