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

if(empty($page->data->oid)) die('No track selected');
?>
<section class="pb-5"> 
    <div class="row d-flex row-cols-2">
        <div class="col-12 col-md-4">
            <div id="track-carousel" class="carousel carousel-fade slide">
                <div class="carousel-inner align-middle">
                    <div class="carousel-item active">
                        <img src="<?= $page->data->thumbnail; ?>" class="d-block w-100" />
                    </div>                      
                </div>
                <button class="carousel-control-prev" type="button" data-bs-target="#track-carousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#track-carousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                </button>
            </div>
        </div>
        <div class="col-12 col-md-8">    
            <!-- Title -->
            <div class="track-title row">
                <div class="fs-1"><?= $page->data->title; ?></div>
            </div>
            <!-- Artist -->
            <div class="track-artist row">
                <div class="fs-3">
                    <?= $page->data->artist; ?>
                    <a class="bi bi-box-arrow-up-right framed" href="<?= $page->data->alink; ?>"></a>
                </div>
            </div>  
            <!-- Buttons -->                                     
            <div class="track-buttons row">
                <div class="pb-3 mt-3 text-center text-md-start">
                    <button id="track-to-play" class="btn btn-primary bi bi-play">
                        <i class="d-none d-sm-inline"><?= Text::_('PLAY'); ?></i>
                    </button>
                    <button id="track-to-queue" class="btn btn-success bi bi-plus">
                        <i class="d-none d-sm-inline"><?= Text::_('QUEUE'); ?></i>
                    </button>
                    <button id="track-to-favorites" class="btn btn-secondary bi bi-heart">
                        <i class="d-none d-sm-inline"><?= Text::_('FAVORITES'); ?></i>
                    </button>
                </div>
            </div>             
        </div>        
    </div>    
    <!-- Info -->
    <div class="row mt-3">
        <div class="col">
            <ul class="nav flex-column flex-sm-row fs-4 text-truncate">
                <?php if(isset($page->data->parent)): ?>
                <li class="nav-item bi bi bi-disc p-1 me-3">                        
                    <a class="framed" href="<?= $page->data->parent->link;?>">
                        <span><?= $page->data->parent->title;?></span>
                    </a>                        
                </li>   
                <?php endif; ?>                     
                <li class="nav-item bi bi-collection p-1 me-3">
                    <a class="framed" href="<?= $page->data->glink; ?>">
                        <span><?= Text::_($page->data->genre); ?></span>
                    </a>
                </li>
                <?php if (isset ($page->data->tags['part_of_a_set']) && $page->data->tags['part_of_a_set'] != '1/1'): ?>
                <li class="nav-item bi bi-disc p-1 me-3">
                    <span><?= $page->data->tags['part_of_a_set']; ?></span>
                </li>
                <?php endif; ?>
                <?php if (isset ($page->data->tags['total_discs']) && $page->data->tags['total_discs'] != ''): ?>
                <li class="nav-item bi bi-disc p-1 me-3">
                    <span><?= $page->data->tags['total_discs']; ?></span>
                </li>
                <?php endif; ?>
                <?php if (isset ($page->data->tags['track_number']) && $page->data->tags['track_number'] != ''): ?>
                <li class="nav-item bi bi-file-music p-1 me-3">
                    <span><?= $page->data->tags['track_number']; ?>/<?= $page->data->tags['total_tracks']; ?></span>
                </li>
                <?php endif; ?>
                <?php if (isset ($page->data->tags['year']) && $page->data->tags['year'] != ''): ?>
                <li class="nav-item bi bi-calendar p-1 me-3">
                    <span><?= $page->data->tags['year']; ?></span>
                </li>
                <?php endif; ?>
                <?php if (isset ($page->data->tags['publisher']) && $page->data->tags['publisher'] != ''): ?>
                <li class="nav-item bi bi-postage p-1 me-3">
                    <span><?= $page->data->tags['publisher']; ?></span>
                </li>
                <?php endif; ?>
            </ul>
        </div>            
        <!-- Contributors -->
        <?php if (isset ($page->data->tags['contributors']) && is_array($page->data->tags['contributors'])): ?>
        <div class="row">
            <?php foreach ($page->data->tags['contributors'] as $coworker): ?>
            <div class="pe-2 fs-6 d-flex">
                <?= ucfirst($coworker['position']); ?>:&nbsp;
                <?= $coworker['person']; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        <!-- Links -->
        <div id="track-links" class="row mt-5 pb-5"></div> 
    </div>        
</section>
<section class="pb-5 d-none">
    <div class="row" class="track-tags">
        <?php
        if (isset ($page->data->tags) && count($page->data->tags)) {
            foreach ($page->data->tags as $k => $v) {
                if(substr($k,0,3) != 'mb_') continue;
                echo '<div class="mb-tag row">';
                echo '<div class="col">' . $k . '</div>';
                echo '<div class="col">' . (is_array($v) ? $v[0] : $v) . '</div>';
                echo '</div>';
            }
        } ?>
    </div>
</section>

<script type="text/javascript">
    jQuery(document).ready(function () {
        $.rbfy.track.setup({ oid: '<?= $page->data->oid; ?>' , title: '<?= htmlentities($page->data->title); ?>'});
    });
</script>
