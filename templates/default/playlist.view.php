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
<section class="mb-1">
    <div class="row">
        <div class="album-title text-truncate">
            <?= Text::_('Playlist'); ?>&nbsp;
            <?= Text::_($page->data['playlist']->name); ?>
        </div>
    </div>
    <div class="row row-cols-2 mb-3">
        <div class="album-info col text-start text-nowrap">
            <ul class="nav">
                <li class="nav-item bi bi-file-music p-1 me-3">
                    <span>
                        <?= $page->data['playlist']->tracks; ?>
                    </span>
                </li>
                <li class="nav-item bi bi-clock p-1 me-3">
                    <span>
                        <?= $page->data['playlist']->duration_fm; ?>
                    </span>
                </li>
            </ul>
        </div>
    </div>
    <div class="row d-flex text-center">
        <button class="play-playlist col me-2 btn btn-primary bi bi-play">
            <span class="d-none d-sm-inline">
                <?= Text::_('PLAY'); ?>
            </span> </button>
        <button class="queue-playlist col me-2 btn btn-success bi bi-plus">
            <span class="d-none d-sm-inline">
                <?= Text::_('QUEUE'); ?>
            </span> </button>
        <button class="empty-playlist col me-2 btn btn-danger bi bi-trash3">
            <span class="d-none d-sm-inline">
                <?= Text::_('EMPTY'); ?>
            </span> </button>
        <button class="export-playlist col me-2 btn btn-info bi bi-file-earmark-arrow-down">
            <span class="d-none d-sm-inline">
                <?= Text::_('EXPORT'); ?>
            </span> </button>
        <button class="download-playlist col me-2 btn btn-warning bi bi-download">
            <span class="d-none d-sm-inline">
                <?= Text::_('DOWNLOAD'); ?>
            </span> </button>
    </div>
</section>  
<section> 
    <div class="tracks-list pt-3 pb-5" data-source="<?= $page->data['source'];?>">
        <?php foreach ($page->data['tracks'] as $row): ?>
        <div class="track-wrapper row border-bottom m-0 p-0 w-100 mb-2">
            <div class="track row row-cols-3 m-0 p-0 my-auto flex-nowrap float-left w-100" 
                data-playlist="<?= $page->data['playlist']->id;?>" 
                data-link="<?= $row->link;?>" 
                data-src="<?= $row->oid; ?>"
                data-favorite="<?= ($row->isfavorite ? 'true' : 'false');?>">
                <div class="track-art d-none d-sm-inline col-1 my-auto">
                    <img class="img-fluid" src="<?= $row->thumbnail; ?>" />
                    <button class="d-none btn bi bi-play p-0 m-0" data-action="play"></button>
                </div>
                <div class="track-info col-10 col-sm-9 text-truncate text-start">  
                    <div class="track-title text-truncate scroll-box"><?= $row->title; ?></div>
                    <div class="track-artist fw-lighter text-muted"><?= $row->artist; ?></div>
                </div>
                <div class="track-duration col-2 text-nowrap text-end my-auto"><?= $row->duration_fm; ?></div>
            </div>
        </div>
        <?php endforeach;?>
    </div>
</section>
<!-- JS -->
<script type="text/javascript">
    jQuery(document).ready(function () {

        let id = '<?= $page->data['playlist']->id;?>';

        $('button.queue-playlist').on('click',function(){
            $.rbfy.queue.add($.rbfy.livesite + '/?task=queue.playlist&id='+id);
        });

        $('button.play-playlist').on('click',function(){
            $.rbfy.queue.addandplay($.rbfy.livesite + '/?task=queue.playlist&method=unshift&id='+id);
        });

        $('button.empty-playlist').on('click',function(){
            $.get($.rbfy.livesite + '/?task=playlist.empty&id='+id,function(){    
                $.rbfy.toast($.rbfy.labels['playlist_emptied_success'],false);                                  
                $('.track').fadeOut('slow',function(){$(this).remove()});
            });
        });    
        
        $('button.export-playlist').on('click',function(e){
            e.preventDefault();
            $.rbfy.playlist.export(id);
            return false; 
        });   
        
        $('button.download-playlist').on('click',function(e){
            e.preventDefault();
            $.rbfy.playlist.download(id);
            return false;
        });            

        $.rbfy.track.bind();                    
    });    
</script>