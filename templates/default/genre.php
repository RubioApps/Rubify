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
<?php require_once('search.php'); ?>

<section class="mb-5">
    <div class="rbfy-tracks-grid row row-cols-2 row-cols-sm-4 row-cols-md-6 row-cols-lg-8 g-2 g-lg-3 mt-1">          
        <?php foreach ($page->data as $row): ?>
        <div class="col">
            <div class="rbfy-card card p-3 border text-center">
                <a class="framed" href="<?= $row->link;?>">
                    <img src="<?= $row->thumbnail;?>" class="card-img-top" title="<?= $row->name;?>" alt="<?= $row->name;?>">
                    <div class="card-body p-0">
                        <h6 class="card-title text-truncate"><?= $row->name;?></h6>
                    </div>
                </a>
            </div>             
        </div> 
        <?php endforeach; ?>        
    </div>
</section>
