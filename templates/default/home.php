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
    <div class="rbfy-home container g-2 mt-1">           
        <?php foreach ($page->data['menu'] as $row): ?>
        <a class="framed" href="<?= $row->link;?>">            
        <div class="row row-cols-2 p-3 m-1 border rounded">
            <div class="col-1">
                <img src="<?= $row->image;?>" class="img-fluid" title="<?= Text::_($row->label);?>" alt="<?= $row->name;?>">
            </div>
            <div class="col-11">                
                <span class="fs-4 text-truncate"><?= $row->stats . '&nbsp;';?><?= Text::_($row->label);?></span>
            </div>
        </div> 
        </a>
        <?php endforeach; ?>        
    </div>
</section>
<section>
    <div class="h3 mt-5"><?=Text::_('RECENTLY_ADDED');?></div>
    <div class="rbfy-tracks-grid row row-cols-2 row-cols-sm-4 row-cols-md-5 row-cols-lg-6 g-2 g-lg-3 mt-1">                
        <?php foreach ($page->data['recent_tracks'] as $row): ?>
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

