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
<!DOCTYPE html>
<html lang="<?= $language->getTag();?>" dir="<?= ($language->isRtl() ? 'rtl' : 'ltr'); ?>" data-bs-theme="dark">
    <head>
        <meta charset="utf-8">
        <title><?= $config->sitename;?></title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="robots" content="noindex,nofollow" />
        <meta name="keywords" content="brave, search" />
        <meta name="description" content="brave, search" />
        <meta name="referrer" content="strict-origin-when-cross-origin">        

        <!-- Icons @see: https://github.com/audreyr/favicon-cheat-sheet -->
        <link rel="shortcut icon" href="<?= $factory->getAssets() ;?>/favicons/rubify.png" type="image/png">
        <link rel="icon" href="<?= $factory->getAssets() ;?>/favicons/rubify.png">
        <!-- Basic Jquery -->
        <?= $page->addCDN('js','https://code.jquery.com/jquery-3.7.1.min.js','sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=','anonymous');?>        
        <?= $page->addCDN('js','https://code.jquery.com/ui/1.13.2/jquery-ui.min.js','sha256-lSjKY0/srUM9BE3dPm+c4fBo1dky2v27Gdjm2uoZaL0=','anonymous');?>
        <?= $page->addCDN('js', $factory->getAssets() . '/jquery.ui.touch-punch.min.js');?>        
        <?= $page->addCDN('js', $factory->getAssets() . '/rubify.js');?>
        <?= $page->addCDN('js', $factory->getAssets() . '/player.js');?>              
        <!-- Bootstrap v5 -->
        <?= $page->addCDN('css','https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css','sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN','anonymous');?>
        <?= $page->addCDN('js','https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js','sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL','anonymous');?>
        <?= $page->addCDN('css','https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css');?>
        <!-- Additional styles -->
        <?= $page->addCDN('css',$factory->getAssets() . '/default.css');?>      
        <script type="text/javascript">
        jQuery(document).ready(function(){
            $.rbfy.init('<?= $config->live_site;?>');
            $.rbfy.logged = <?= $user->isLogged()? 'true':'false';?>;
        });        
        </script>        
    </head>
    <body>                          
        <?php if($user->isLogged()):?>  
        <?php require_once $page->getFile('header'); ?> 
        <div class="rbfy-layout container-xl">
            <main class="rbfy-main mt-3 p-3 mb-5 pb-5 mt-xxl-5">  
                <?php require_once $page->getFile(); ?>  
            </main>             
            <aside id="queue" class="rbfy-queue offcanvas-xxl offcanvas-start" data-bs-backdrop="true" tabindex="-1" aria-labelledby="queueLabel">                       
                <div class="offcanvas-header p-0">
                    <div id="player-toolbar" class="col-4 text-center p-0 my-auto">
                        <button id="repeat" class="btn p-1" style="color:grey"><i class="bi bi-repeat"></i></button>
                        <button id="shuffle" class="btn p-1 ps-2" style="color:grey"><i class="bi bi-shuffle"></i></button>
                    </div>
                    <div id="queue-tools" class="col-6 text-center p-0 my-auto">
                        <button id="queue-play" type="button" class="btn btn-primary bi bi-play" data-bs-toggle="tooltip"
                            data-bs-placement="bottom" title="<?= Text::_('QUEUE_PLAY'); ?>">
                        </button>                       
                        <button id="queue-save" type="button" class="btn btn-success bi bi-music-note-list" title="<?= Text::_('QUEUE_SAVE'); ?>">
                        </button>
                        <button id="queue-export" type="button" class="btn btn-info bi bi-file-earmark-arrow-down" title="<?= Text::_('QUEUE_EXPORT'); ?>">
                        </button>                         
                        <button id="queue-empty" type="button" class="btn btn-danger bi bi-trash3" data-bs-toggle="tooltip"
                            data-bs-placement="bottom" title="<?= Text::_('QUEUE_EMPTY'); ?>">
                        </button>
                    </div>    
                    <div class="col-2 ps-2">                
                        <button type="button" class="btn-close text-reset d-xxl-none" data-bs-dismiss="offcanvas" data-bs-target="#queue" aria-label="Close"></button>
                    </div>
                </div>
                <div class="offcanvas-body"> 
                    <div id="queue-tracks" class="w-100 p-1"></div>
                </div>
            </aside>                                       
        </div>   
        <?php require_once $page->getFile('info'); ?>                 
        <?php require_once $page->getFile('footer'); ?>      
        <?php else: ?> 
        <div class="container mx-auto">
            <?php require_once $page->getFile(); ?>               
        </div>         
        <?php endif;?>                                
        <?php require_once $page->getFile('menu'); ?> 
        <?php require_once $page->getFile('modal'); ?>    
        <?php require_once $page->getFile('toast'); ?>     
        <?php $page->getJScripts();?>                
    </body>    
</html>
