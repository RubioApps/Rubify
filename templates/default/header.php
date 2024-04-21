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
<header class="navbar navbar-expand-xxl navbar-dark rbfy-navbar sticky-top" data-type="header">    
    <nav class="container-xxl flex-wrap flex-xxl-nowrap">
        <div class="d-flex">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainmenu">
                <span class="navbar-toggler-icon"></span>
            </button>
        </div>
        <a class="navbar-brand rbfy-brand framed text-center text-truncate" href="<?= $config->live_site; ?>">
            <div class="text-center">
                <img class="p-0" src="<?= $factory->getAssets() . '/favicons/rubify.png';?>" width="30" />
                <span class="h4 fw-bold" style="position:relative;top:3px"><?= $config->sitename; ?></span>
            </div>
        </a>               
        <div id="player-queue-toggle" class="rbfy-navbar-toggler">
            <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#queue">
                <div class="h1 align-top top p-0">...</div>
            </button>
        </div>
        <div class="collapse navbar-collapse bg-dark" id="mainmenu">
            <ul class="navbar-nav me-auto mb-xxl-0">               
                <?php if(is_array($page->menu)) {
                    foreach ($page->menu as $item): 
                ?>
                <li class="nav-item">
                    <a class="nav-link framed" href="<?= $item->link; ?>"><?= Text::_($item->label) ?></a>                    
                </li>
                <?php 
                endforeach; 
                }?>
                <?php if ($user->isLogged() && !$config->use_autolog): ?>
                    <li class="nav-item p-0 mt-3 mt-xxl-0 ms-0 ms-xxl-3">
                        <a class="nav-link p-0" href="<?= $factory->Link('login.off'); ?>">
                            <div class="btn btn-secondary">
                                <?= ucfirst($factory->getParam('user'));?>
                                <span class="bi bi-power"></span>
                            </div>
                        </a>                        
                    </li>
                <?php endif; ?>                 
            </ul>
        </div>   
    </nav>
</header>
