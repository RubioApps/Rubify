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
    <div class="rbfy-tracks-grid row row-cols-2 row-cols-sm-4 row-cols-md-5 row-cols-lg-6 g-2 g-lg-3 mt-1">           
        <?php if(isset($page->parent)):?>
        <div class="col">
            <div class="rbfy-card card p-3 border text-center">                
                <a class="framed" href="<?= $page->parent->link;?>">
                    <img src="<?= $factory->getAssets() . '/images/up.png';?>" class="card-img-top" title="<?= $page->parent->title;?>" alt="<?= $page->parent->title;?>">
                    <div class="card-body p-0">
                        <h6 class="card-title text-truncate"><?= $page->parent->title;?></h6>
                    </div>
                </a>
            </div>             
        </div> 
        <?php endif;?>        
        <?php foreach ($page->data as $row): ?>
        <div class="col">
            <div class="rbfy-card card p-3 border text-center">
                <a class="framed" href="<?= $row->link;?>">
                    <img src="<?= $row->thumbnail;?>" class="card-img-top" title="<?= $row->title;?>" alt="<?= $row->title;?>">
                    <div class="card-body p-0">
                        <h6 class="card-title text-truncate"><?= $row->title;?></h6>
                    </div>
                </a>
            </div>             
        </div> 
        <?php endforeach; ?>        
    </div>
</section>
<section class="mb-5">
    <?= $page->pagination->getPagesLinks(); ?>
</section>
