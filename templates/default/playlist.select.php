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
<form>
    <?= $factory->getToken(); ?>
    <div class="row p-1 text-start">
        <div class="col mt-2">
            <label for="playlist-select" class="form-label">
                <?= Text::_('PLAYLIST_ADD'); ?>
            </label>
            <select name="id" id="playlist-select" class="form-select form-select-lg mb-3">
                <?php foreach ($page->data as $p): ?>
                    <option value="<?= $p->id; ?>">
                        <?= Text::_(ucfirst($p->name)); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    <div class="row p-1 text-center">
        <div class="col mt-2">
            <?= Text::_('PLAYLIST_OR'); ?>
        </div>
    </div>
    <div class="row p-1 text-start">
        <div class="col mt-2">
            <label for="playlist-name" class="form-label">
                <?= Text::_('PLAYLIST_CREATE'); ?>
            </label>
            <input type="text" id="playlist-name" name="name" class="form-control" value="" autocomplete="false" />
        </div>
    </div>
</form>