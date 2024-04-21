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
<section>
    <!--
    <div class="d-md-none mb-3">
        <div class="album-art-banner mb-1" style="background-image: url('<?= $page->data['album']->thumbnail; ?>')"></div>
        <div class="row">
            <div class="album-title text-truncate"><?= $page->title; ?></div>
        </div>
        <div class="row pb-3">
            <?php if($page->data['album']->artist != ''): ?>
            <div class="album-artist text-truncate mb-1">
                <?= $page->data['album']->artist; ?>                
                <a class="bi bi-box-arrow-up-right framed" href="<?= $page->data['album']->alink; ?>"></a>
            </div>
            <?php endif;?>            
        </div>
        <div class="row row-cols-2 mb-5">
            <div class="album-info col-8 text-start text-nowrap">
                <ul class="nav">
                    <li class="nav-item bi bi-collection p-1 me-3 ">
                        <a class="framed" href="<?= $page->data['album']->glink;?>">
                            <span><?= Text::_($page->data['album']->genre); ?></span>
                        </a>
                    </li>
                    <li class="nav-item bi bi-disc p-1 me-3">
                        <span><?= $page->data['album']->discs; ?></span>
                    </li>
                    <li class="nav-item bi bi-file-music p-1 me-3">
                        <span><?= $page->data['album']->tracks; ?></span>
                    </li>
                    <li class="nav-item bi bi-clock p-1 me-3">
                        <span><?= $page->data['album']->duration_fm; ?></span>
                    </li>
                </ul>              
            </div>
        </div> 
        <div class="row d-flex">
            <button class="play-album col me-2 btn btn-primary bi bi-play"></button>
            <button class="queue-album col me-2 btn btn-success bi bi-plus"></button>
        </div>                  
    </div>
    -->
    <div class="d-flex row m-0 p-0 mb-3">
        <div class="col-12 col-md-4 text-center">
            <img class="w-100 " src="<?= $page->data['album']->thumbnail; ?>" />
        </div>
        <div class="col-12 col-md-8 text-start">
            <div class="album-title mb-2">
                <div class="fs-1"><?= $page->title; ?></div>
            </div>
            <?php if($page->data['album']->artist != ''): ?>
            <div class="album-artist mb-2 fs-2">
                <?= $page->data['album']->artist; ?>                
                <a class="bi bi-box-arrow-up-right framed" href="<?= $page->data['album']->alink; ?>"></a>
            </div>
            <?php endif;?>                                 
            <div class="album-info mt-2">
                <ul class="nav flex-column flex-md-row fs-5">
                    <li class="nav-item bi bi-collection p-1 me-3 ">
                        <a class="framed" href="<?= $page->data['album']->glink;?>">
                            <span><?= Text::_($page->data['album']->genre); ?></span>
                        </a>
                    </li>
                    <li class="nav-item bi bi-disc p-1 me-3">
                        <span><?= $page->data['album']->discs; ?></span>
                    </li>
                    <li class="nav-item bi bi-file-music p-1 me-3">
                        <span><?= $page->data['album']->tracks; ?></span>
                    </li>
                    <li class="nav-item bi bi-clock p-1 me-3">
                        <span><?= $page->data['album']->duration_fm; ?></span>
                    </li>
                </ul>                           
            </div>                     
        </div>
    </div>
    <div class="row text-center d-flex mb-5 d-none d-md-flex">
        <button class="play-album col me-2 btn btn-primary bi bi-play"><i><?= Text::_('PLAY');?></i></button>
        <button class="queue-album col me-2 btn btn-success bi bi-plus"><i><?= Text::_('QUEUE');?></i></button>
    </div>    
</section>  
<section>
    <?php
    if ($page->data['album']->discs > 1) {
        $first = true
            ?>
        <ul class="nav nav-pills mt-3 mb-3" id="disc-tab" role="tablist">
            <?php
            foreach ($page->data['tracks'] as $k => $disc) {
                ?>
                <li class="nav-item" role="presentation">
                    <button class="nav-link <?= ($first ? ' active' : ''); ?>" id="<?= $k; ?>-tab" data-bs-toggle="pill"
                        data-bs-target="#<?= $k; ?>" role="tab" aria-controls="<?= $k; ?>"><?= $k; ?>
                    </button>
                </li>
                <?php
                $first = false;
            }
            ?>
        </ul>
        <?php
    }
    ?>    
    <div class="tab-content tracks-list pt-3 pb-5" data-source="album">
        <?php
        $first = true;
        foreach ($page->data['tracks'] as $k => $disc) {
            ?>
            <div class="tab-pane fade<?= ($first ? ' show active' : ''); ?>" id="<?= $k; ?>" role="tabpanel" aria-labelledby="<?= $k; ?>-tab">
                <?php
                foreach ($disc as $row) {
                    ?>
                    <div class="track-wrapper row border-bottom m-0 p-0 w-100 mb-2">
                        <div class="track row row-cols-3 m-0 p-0 my-auto flex-nowrap float-left w-100" 
                            data-playlist="" 
                            data-link="<?= $row->link;?>" 
                            data-src="<?= $row->oid; ?>" 
                            data-favorite="<?= $row->isfavorite ? 'true' : 'false';?>" >
                            <div class="track-art d-none d-sm-inline col-1 my-auto">
                                <img class="img-fluid" src="<?= $row->thumbnail; ?>" />
                                <button class="d-none btn bi bi-play p-0 m-0" data-action="play"></button>
                            </div>
                            <div class="track-info col-10 col-sm-9 text-start">                            
                                <div class="track-title scroll-box text-truncate">
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
                    <?php
                }
                ?>
            </div>
            <?php
            $first = false;
        }
        ?>
    </div>
</section>
<!-- JS -->
<script type="text/javascript">
    jQuery(document).ready(function () {

        let oid = '<?= $page->data['album']->oid;?>';

        $('button.play-album').on('click',function(){
            $.rbfy.queue.addandplay($.rbfy.livesite + '/?task=queue.album&oid='+oid);
        }); 

        $('button.queue-album').on('click',function(){
            $.rbfy.queue.add($.rbfy.livesite + '/?task=queue.album&oid='+oid);
        });        

        $.rbfy.track.bind();  
    });
</script>