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

?>
<?php foreach ($page->data as $row): ?>
<div class="queue-item row row-cols-3 p-2 border-bottom" data-src="<?= $row->oid; ?>" data-order="<?= $row->order; ?>">
    <div class="col-2 p-0">
        <img class="queue-art img-fluid" src="<?= $row->thumbnail; ?>" />
    </div>
    <div class="queue-link col-9 p-0 ps-2">
        <div class="queue-title fs-4 text-truncate">
            <?= $row->title; ?>
        </div>
        <div class="queue-artist fs-6 text-truncate">
            <?= $row->artist; ?>
        </div>
    </div>  
    <div class="col-1 p-0 text-end">
        <button type="button" class="bi bi-x-circle btn btn-remove" data-src="<?= $row->oid; ?>"></button>
    </div>
</div>
<?php endforeach; ?>
 