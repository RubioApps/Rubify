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
