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
<?php require_once('search.php'); ?>
<section class="mb-5">
    <div class="d-flex justify-content-end pb-1 mb-3">                
        <button type="button" id="layout-grid" class="btn bi bi-grid me-1">
            <span class="d-none d-sm-inline"><?= Text::_('GRID');?></span>
        </button>             
        <button type="button" id="layout-list" class="btn bi bi-list">                
            <span class="item-link d-none d-sm-inline"><?= Text::_('LIST');?></span>
        </button>                           
    </div>    
    <div class="rbfy-tracks-list">           
        <?php if(isset($page->parent)):?>                
        <a class="framed" href="<?= $page->parent->link;?>">            
            <div class="d-flex border rounded p-3 g-2 mt-1 mb-2">                                                    
                <img src="<?= $factory->getAssets() . '/images/up.png';?>" class="rbfy-img-folder me-2" title="<?= $page->parent->title;?>" alt="<?= $page->parent->title;?>">            
                <div class="fs-4 my-auto scroll-box text-truncate"><?= $page->parent->title;?></div>
            </div>                             
        </a>
        <?php endif;?>        
        <?php foreach ($page->data as $row): ?>                   
        <a class="framed" href="<?= $row->link;?>">            
            <div class="d-flex border rounded p-3 g-2 mt-1 mb-2">                                    
                <img src="<?= $row->thumbnail;?>" class="rbfy-img-folder me-2" title="<?= $row->title;?>" alt="<?= $row->title;?>">            
                <div class="fs-4 my-auto scroll-box text-truncate"><?= $row->title;?></div>                        
            </div> 
        </a>        
        <?php endforeach; ?>        
    </div>
</section>
<section class="mb-5">
    <?= $page->pagination->getPagesLinks(); ?>
</section>

<!-- JS -->
<script type="text/javascript">
jQuery(document).ready(function () {  

    /*
    $('.scroll-box').mouseenter(function () {
        $(this).stop();
        var boxWidth = $(this).width();
        var textWidth = $('.scroll-text', $(this)).width();
        if (textWidth > boxWidth) {
            var animSpeed = textWidth * 2;
            $(this).animate({scrollLeft: (textWidth - boxWidth)}, animSpeed, function () {
                $(this).animate({scrollLeft: 0}, animSpeed, function () {
                    $(this).trigger('mouseenter');
                    });
                });
            }
        });

    $('.scroll-box').mouseleave(function () {
        var animSpeed = $(this).scrollLeft() * 2;
        $(this).stop().animate({scrollLeft: 0}, animSpeed);
    }); 
    */
                                              
});
</script>    