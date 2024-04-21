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
<section class="mb-1">
    <div class="row m-0 p-0 mb-5">
        <div class="col fs-3"><?= Text::_('PLAYLISTS');?></div>
        <div class="col text-end">
            <button id="playlist-create" class="btn btn-primary bi bi-plus">
                <span class="d-none d-sm-inline"><?=Text::_('PLAYLIST_CREATE');?></span>
            </button>
        </div>
    </div>
    <div class="row m-0 p-0 mt-5">
        <table class="table table-striped table-responsive">
            <thead>
                <th><?= Text::_('PLAYLIST_NAME');?></th>
                <th class="text-center"><?= Text::_('TRACKS');?></th>
                <th></th>
            </thead>
            <tbody>
                <?php foreach($page->data as $row):?>
                <tr class="fs-4" data-id="<?= $row->id;?>">       
                    <td class="text-start">
                        <a class="framed" href="<?= $factory->Link('playlist.view','id='.$row->id,'format=raw');?>">
                            <?= Text::_($row->name);?>
                        </a>
                        <span class="ms-2">(<?= (new DateTime($row->updated))->format('d M');?>)</span>
                    </td>                    
                    <td class="text-nowrap text-center"><?= $row->count;?></td>                    
                    <td class="text-end">
                        <?php if(!$row->isfavorites):?>
                        <button class="playlist-delete btn btn-secondary bi bi-x-circle"></button>
                        <?php endif;?>
                    </td>  
                </tr>
                <?php endforeach;?>
            </tbody>
        </table>
    </div>
</section>  
<!-- JS -->
<script type="text/javascript">
              
    jQuery(document).ready(function () {

        $($.tmpl.playlist.button.create).on('click',function(event){
            event.preventDefault();  
            $.rbfy.playlist.create();
            return false;
        });

        $($.tmpl.playlist.button.delete).on('click',function(event){
            event.preventDefault();  
            $.rbfy.playlist.delete();                    
            return false;
        });        
    });
</script>