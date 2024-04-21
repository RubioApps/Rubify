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
use Rubify\Framework\Helpers;

?>
<section class="mb-1">
    <div class="d-md-none">
        <div class="row">
            <div class="album-title text-truncate">
                <?=  Text::_($page->title); ?>
            </div>
        </div>  
        <div class="row d-flex text-center">
            <button class="play-history col me-2 btn btn-primary bi bi-play"></button>
            <button class="queue-history col me-2 btn btn-success bi bi-plus"></button>
            <button class="empty-history col me-2 btn btn-danger bi bi-trash3"></button>
        </div>               
    </div>
    <div class="d-none d-md-flex row m-0 p-0 mb-5">
        <div class="album-info">
            <div class="row row-cols-2">
                <div class="album-title mb-1"><?= Text::_($page->title);?></div>
                <div class="col text-end">
                    <button class="play-history btn btn-primary bi bi-play"><i><?= Text::_('PLAY');?></i></button>
                    <button class="queue-history btn btn-success bi bi-plus"><i><?= Text::_('QUEUE');?></i></button>
                    <button class="empty-history btn btn-danger bi bi-trash3"><i><?= Text::_('EMPTY');?></i></button>
                </div>
            </div>
        </div>         
    </div>
</section>  
<section> 
    <div class="fs-4 ms-3"><?= Text::_('HISTORY_THISWEEK');?></div>
    <div class="tracks-list pt-3 pb-5" data-source="history">
        <?php foreach ($page->data['thisweek'] as $row): ?>
        <div class="track row row-cols-3  p-0 m-0 pt-2 pb-2" data-link="<?= $row->link;?>" data-src="<?= $row->oid; ?>">
            <div class="track-art d-none d-sm-inline col-1 text-center">
                <img class="img-fluid" src="<?= $row->thumbnail; ?>" />
                <button class="d-none btn bi bi-play p-0 m-0" data-action="play"></button>
            </div>
            <div class="track-info col-10 col-sm-9 text-truncate ps-3 text-start ps-1 pe-1">
                <div class="track-title text-truncate scroll-box">
                    <?= $row->title; ?>
                    <span class="track-percent"><?php if($row->percent<100) echo '('.$row->percent.'%)'; ?></span>
                </div>
                <div class="track-artist fw-lighter text-muted"><?= $row->artist; ?></div>
                <div class="track-progress mt-1" data-progress="<?= $row->percent; ?>"></div>
            </div>
            <div class="track-duration col-2 text-nowrap text-end my-auto"><?= $row->duration_fm; ?></div>            
        </div>                   
        <?php endforeach;?>
    </div>
</section>
<section> 
    <div class="fs-4 ms-3"><?= Text::_('HISTORY_LASTWEEK');?></div>
    <div class="tracks-list pt-3 pb-5" data-source="history">
        <?php foreach ($page->data['lastweek'] as $row): ?>
        <div class="track-wrapper row border-bottom m-0 p-0 w-100">
            <div class="track row row-cols-3 m-0 p-0 my-auto flex-nowrap float-left w-100" 
                data-link="<?= $row->link;?>" 
                data-src="<?= $row->oid; ?>">
                <div class="track-art d-none d-sm-inline col-1 text-center">
                    <img class="img-fluid" src="<?= $row->thumbnail; ?>" />
                    <button class="d-none btn bi bi-play p-0 m-0" data-action="play"></button>
                </div>
                <div class="track-info col-10 col-sm-9 text-truncate ps-3 text-start ps-1 pe-1">
                    <div class="track-title text-truncate scroll-box">
                        <?= $row->title; ?>
                        <span class="track-percent"><?php if($row->percent<100) echo '('.$row->percent.'%)'; ?></span>
                    </div>
                    <div class="track-artist fw-lighter text-muted"><?= $row->artist; ?></div>
                    <div class="track-progress mt-1" data-progress="<?= $row->percent; ?>"></div>
                </div>
                <div class="track-duration col-2 text-nowrap text-end my-auto"><?= $row->duration_fm; ?></div>            
            </div>                   
        </div>
        <?php endforeach;?>
    </div>
</section>
<section> 
    <div class="fs-4 ms-3"><?= Text::_('HISTORY_THISMONTH');?></div>
    <div class="tracks-list pt-3 pb-5" data-source="history">
        <?php foreach ($page->data['thismonth'] as $row): ?>
        <div class="track row row-cols-3  p-0 m-0 pt-2 pb-2" data-link="<?= $row->link;?>" data-src="<?= $row->oid; ?>">
            <div class="track-art d-none d-sm-inline col-1 text-center">
                <img class="img-fluid" src="<?= $row->thumbnail; ?>" />
                <button class="d-none btn bi bi-play p-0 m-0" data-action="play"></button>
            </div>
            <div class="track-info col-10 col-sm-9 text-truncate ps-3 text-start ps-1 pe-1">
                <div class="track-title text-truncate scroll-box">
                    <?= $row->title; ?>
                    <span class="track-percent"><?php if($row->percent<100) echo '('.$row->percent.'%)'; ?></span>
                </div>
                <div class="track-artist fw-lighter text-muted"><?= $row->artist; ?></div>
                <div class="track-progress mt-1" data-progress="<?= $row->percent; ?>"></div>
            </div>
            <div class="track-duration col-2 text-nowrap text-end my-auto"><?= $row->duration_fm; ?></div>            
        </div>                   
        <?php endforeach;?>
    </div>
</section>
<section> 
    <div class="fs-4 ms-3"><?= Text::_('HISTORY_LASTMONTH');?></div>
    <div class="tracks-list pt-3 pb-5" data-source="history">
        <?php foreach ($page->data['lastmonth'] as $row): ?>
        <div class="track-wrapper row border-bottom m-0 p-0 w-100 mb-2">
            <div class="track row row-cols-3 m-0 p-0 my-auto flex-nowrap float-left w-100" 
                data-link="<?= $row->link;?>" 
                data-src="<?= $row->oid; ?>">
                <div class="track-art d-none d-sm-inline col-1 my-auto">
                    <img class="img-fluid" src="<?= $row->thumbnail; ?>" />
                    <button class="d-none btn bi bi-play p-0 m-0" data-action="play"></button>
                </div>
                <div class="track-info col-10 col-sm-9 text-truncate text-start"> 
                    <div class="track-title text-truncate scroll-box">
                        <?= $row->title; ?>
                        <span class="track-percent"><?php if($row->percent<100) echo '('.$row->percent.'%)'; ?></span>
                    </div>
                    <div class="track-artist fw-lighter text-muted"><?= $row->artist; ?></div>
                    <div class="track-progress p-1 m-1 mt-2" data-progress="<?= $row->percent; ?>"></div>
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

        $('.track-progress').each(function(){
            let percent = $(this).attr('data-progress');
            $(this).css({'background-color': 'red', 'width': percent + '%'});
        });

        $('button.queue-history').on('click',function(){
            $.rbfy.queue.add($.rbfy.livesite + '/?task=queue.history');
        });

        $('button.play-history').on('click',function(){
            $.rbfy.queue.addandplay($.rbfy.livesite + '/?task=queue.history&method=unshift');
        });

        $('button.empty-history').on('click',function(){
            $.get($.rbfy.livesite + '/?task=history.empty',function(){    
                $.rbfy.toast($.rbfy.labels['history_emptied_success'],false);                                  
                $('.track').fadeOut('slow',function(){$(this).remove()});
            });
        });        

        $.rbfy.track.bind();                    
    });    
</script>