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
<?php if ($user->isLogged() && !$config->use_autolog): ?>
    <form method="post" id="upload-form" enctype="multipart/form-data">    
    <div class="form-group">
        <div class="row p-1 mb-1">
            <input type="file" name="file" class="form-control form-control-sm" required />
        </div>
        <div class="row p-1 mb-1">
            <input type="text" name="title" class="form-control form-control-sm" placeholder="<?= Text::_('TRACK');?>" autocomplete="false" />            
        </div>     
        <div class="row p-1 mb-1">
            <input type="text" name="artist" class="form-control form-control-sm" placeholder="<?= Text::_('ARTIST');?>" autocomplete="false"  />
        </div>
        <div class="row p-1 mb-1">
            <input type="text" name="album" class="form-control form-control-sm" placeholder="<?= Text::_('ALBUM');?>" autocomplete="false"  />
        </div>           
        <div class="row p-1 mb-1">
            <input type="text" name="genre" id="upload-genre" class="form-control form-control-sm" placeholder="<?= Text::_('GENRE');?>" style="z-index:1023" />
        </div>        
    </div>
    <div class="progress mt-4 mb-3">
        <div class="progress-bar bg-success" id="upload-bar" role="progressbar" style="width:0%;" >0%</div>
    </div>
    <?= $factory->getToken();?>
    </form>
<?php endif; ?>
<!-- JS -->
<script type="text/javascript">
    jQuery(document).ready(function () {
        $.rbfy.upload.form();
    });
</script>
