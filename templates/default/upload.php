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
<?php if ($user->isLogged() && !$config->use_autolog): ?>
<section>
    <div class="row m-0 p-0 mb-1">        
        <div class="col fs-3"><?= Text::_('USER_UPLOADS');?></div>
    </div>
    <div class="row m-0 p-0 mb-3">        
        <div class="col text-end">
            <button id="button-upload-form" class="btn btn-info bi bi-upload">
                <span class="d-none d-sm-inline">&nbsp;<?= Text::_("UPLOAD_BUTTON");?>&nbsp;</span>
            </button>
            <button class="play-upload btn btn-primary bi bi-play">
                <span class="d-none d-sm-inline">&nbsp;<?= Text::_("PLAY");?>&nbsp;</span>
            </button>             
            <button class="queue-upload btn btn-success bi bi-plus">
                <span class="d-none d-sm-inline">&nbsp;<?= Text::_("QUEUE");?>&nbsp;</span>
            </button>              
            <button class="empty-upload btn btn-danger bi bi-trash3">
                <span class="d-none d-sm-inline">&nbsp;<?= Text::_("EMPTY");?>&nbsp;</span>
            </button>            
        </div>
    </div>    
    <div class="tracks-list pt-3 pb-5" data-source="upload">
        <?php foreach($page->data as $row):?> 
        <?php if($row->oid): ?>
        <div class="track-wrapper row border-bottom m-0 p-0 w-100" source="">
            <div class="track row row-cols-3 m-0 p-0 my-auto flex-nowrap float-left w-100 mb-2" 
                data-playlist=""
                data-link="<?= $row->link;?>" 
                data-src="<?= $row->oid; ?>" 
                data-favorite="<?= $row->isfavorite ? 'true' : 'false';?>"
                data-file="<?= $row->file;?>">            
                <div class="track-art d-none d-sm-inline col-1 my-auto mb-2">
                    <img class="img-fluid" src="<?= $row->thumbnail; ?>" />
                    <button class="d-none btn bi bi-play p-0 m-0" data-action="play"></button>
                </div>
                <div class="track-info col-10 col-sm-9 text-truncate text-start">                             
                    <div class="track-title text-truncate">
                        <?= $row->title; ?>
                        <div class="track-fav <?=($row->isfavorite ? 'd-inline' : 'd-none');?>">
                            <span class="badge rounded-pill bg-danger" style="position:relative;top:-2px;font-size:10px">
                                <span class="bi bi-heart-fill"></span>
                            </span>
                        </div>
                    </div>
                    <div class="track-artist fw-lighter text-muted"><?= $row->artist; ?></div>
                </div>
                <div class="track-duration col-2 text-nowrap text-end my-auto"><?= $row->duration_fm; ?></div>         
            </div>                   
        </div>   
        <?php else:?>
        <div class="track-wrapper row border-bottom m-0 p-0 w-100">
            <div class="no-scanned row row-cols-3 m-0 p-0 my-auto flex-nowrap float-left w-100 mb-2" data-file="<?= $row->file;?>">        
                <div class="track-art d-none d-sm-inline col-1 my-auto mb-2">
                    <img class="img-fluid" src="<?= $factory->getAssets() . '/images/all-music.png'; ?>" />
                </div>
                <div class="track-info col-10 col-sm-9 text-truncate text-start">                             
                    <div class="track-title text-truncate">
                        <?= $row->title; ?>
                    </div>
                    <div class="track-artist fw-lighter text-muted"><?= $row->artist; ?></div>
                </div>
                <div class="track-duration col-2 text-end my-auto">
                    <button type="button" class="btn btn-remove bi bi-x-circle"></button>
                </div>              
            </div>
            <div class="text-start text-danger w-100"><?= Text::_('UPLOAD_NOT_SCANNED'); ?></div>                                                            
        </div>
        <?php endif;?>
        <?php endforeach;?>
    </div>    
</section>
<?php endif; ?>
<!-- JS -->
<script type="text/javascript">
    jQuery(document).ready(function () {

        $.rbfy.upload.reload = true;
        $.rbfy.upload.bind();
        $.rbfy.track.bind();

        $('button.play-upload').on('click',function(){
            $.rbfy.queue.addandplay($.rbfy.livesite + '/?task=queue.upload');
        }); 

        $('button.queue-upload').on('click',function(){
            $.rbfy.queue.add($.rbfy.livesite + '/?task=queue.upload');
        });  

        $('button.btn-remove').on('click',function(){
            const row  = $(this).parents('.track-wrapper');
            const file = $(this).parents('.no-scanned').attr('data-file');
            $.get($.rbfy.livesite + '/?task=upload.remove&file='+file,function(){    
                $.rbfy.toast($.rbfy.labels['upload_removed_success'],false);                                  
                row.fadeOut('slow',function(){$(this).remove()});
            });
        });                  

        $('button.empty-upload').on('click',function(){
            $.get($.rbfy.livesite + '/?task=upload.empty',function(){    
                $.rbfy.toast($.rbfy.labels['upload_emptied_success'],false);                                  
                $('.track').fadeOut('slow',function(){$(this).remove()});
            });
        });                  

    });    
</script>