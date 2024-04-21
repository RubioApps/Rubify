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

use Rubify\Framework\Helpers;
use Rubify\Framework\Language\Text;
?>
<?php if ($user->isLogged()): ?>
    <section class="container">
        <?php if($user->isAdmin()):?>
        <div class="d-flex justify-content-end pb-1 mb-3">                    
            <button type="button" id="button-user-list" class="btn btn-success bi bi-person me-1">
                <span class="d-none d-sm-inline"><?= Text::_('ADMIN_USERS');?></span>
            </button>                      
        </div>
        <?php endif;?>          
        <div class="accordion" id="profile-panes">          
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#profile-login">
                        <?= Text::_('USER_LOGIN'); ?>
                    </button>
                </h2>
                <div id="profile-login" class="accordion-collapse collapse">
                    <div class="accordion-body">
                        <form class="container">
                            <?= $factory::getToken(); ?>
                            <div class="mb-3">
                                <label for="uid" class="form-label">
                                    <?= Text::_('USER_NAME'); ?>
                                </label>
                                <input type="text" class="form-control w-50" name="uid" id="uid" aria-describedby="uid-help"
                                    disabled="disabled" value="<?= $factory->getParam('user'); ?>">
                                <div id="uid-help" class="form-text">
                                    <?= Text::_('USER_NAME_DESC'); ?>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="pwd" class="form-label">
                                    <?= Text::_('USER_PASSWORD'); ?>
                                </label>
                                <div class="d-flex">
                                    <input type="password" name="pwd" id="pwd" class="form-control w-50 me-1">
                                    <button type="button" id="password-eye" class="btn btn-secondary bi bi-eye-slash"></button>
                                </div>
                                <div id="pwd-help" class="form-text">
                                    <?= Text::_('USER_PASSWORD_DESC'); ?>
                                </div>
                            </div>
                            <div class="mb-3 text-end">
                                <button type="button" id="button-user-update" type="submit" class="btn btn-primary"><?= Text::_('SUBMIT');?></button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#profile-playlists">
                        <?= Text::_('USER_PLAYLISTS'); ?>
                    </button>
                </h2>
                <div id="profile-playlists" class="accordion-collapse collapse">
                    <div class="accordion-body">
                        <div class="text-end">
                            <button id="button-playlists" class="btn btn-primary"><span class="bi bi-music-note-liste p-1"></span><?= Text::_('PLAYLISTS');?></button>
                        </div>                        
                        <table class="table table-striped table-responsive">
                            <thead>
                                <tr>
                                    <th scope="col"><?=Text::_('PLAYLIST');?></th>
                                    <th scope="col"><?=Text::_('TRACKS');?></th>
                                    <th scope="col"><?=Text::_('UPDATED');?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($page->data['playlists'] as $row):?>
                                <tr>
                                    <th scope="row">
                                        <a class="framed" href="<?= $factory->Link('playlist.view','id='.$row->id,'format=raw');?>">
                                            <?= Text::_($row->name);?>
                                        </a>
                                    </th>
                                    <td><?= $row->count;?></td>
                                    <td><?= date_format(date_create($row->updated),"yy-m-d H:i:s");?></td>
                                </tr>
                                <?php endforeach;?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>   
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#profile-history">
                        <?= Text::_('USER_HISTORY'); ?>
                    </button>
                </h2>
                <div id="profile-history" class="accordion-collapse collapse">
                    <div class="accordion-body">
                        <div class="text-end">
                            <button id="button-history" class="btn btn-primary"><span class="bi bi-clock-history p-1"></span><?= Text::_('HISTORY');?></button>
                        </div>
                        <canvas id="history-chart"></canvas>
                    </div>
                </div>
            </div>
            <?php if($config->enable_upload):?>
            <div class="accordion-item">                
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#profile-uploads">
                        <?= Text::_('USER_UPLOADS'); ?>
                    </button>
                </h2>
                <div id="profile-uploads" class="accordion-collapse collapse">
                    <div class="accordion-body">
                        <div class="text-end mb-2">
                            <button id="button-upload-form" class="btn btn-info">
                                <span class="bi bi-upload p-1"></span><?= Text::_('UPLOAD_BUTTON');?>
                            </button>
                            <button id="button-upload-view" class="btn btn-success">
                                <span class="bi bi-eye p-1"></span><?= Text::_('USER_UPLOADS');?>
                            </button>                            
                        </div>                                              
                        <div class="mt-3 d-flex justify-content-center">
                            <ul class="list-group list-group-horizontal-sm mx-auto">
                                <li class="list-group-item flex-fill"><?= Text::_('UPLOAD_FILES');?>:
                                    <?= $page->data['uploads']->count;?>
                                </li>
                                <li class="list-group-item flex-fill"><?= Text::_('UPLOAD_SIZE');?>:
                                    <?= Helpers::formatFileSize($page->data['uploads']->size);?>
                                </li>                                
                                <li class="list-group-item flex-fill"><?= Text::_('UPLOAD_FIRST');?>:
                                    <?= $page->data['uploads']->first->format('Y-m-d H:i:s');?>
                                </li>
                                <li class="list-group-item flex-fill"><?= Text::_('UPLOAD_LAST');?>:
                                    <?= $page->data['uploads']->last->format('Y-m-d H:i:s');?>
                                </li>
                            </ul>
                        </div>                      
                    </div>
                </div>
            </div>   
            <?php endif;?>    
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#profile-downloads">
                        <?= Text::_('USER_DOWNLOADS'); ?>
                    </button>
                </h2>
                <div id="profile-downloads" class="accordion-collapse collapse">
                    <div class="accordion-body">
                        <table class="table table-striped table-responsive">
                            <thead>
                                <tr>
                                    <th scope="col"><?=Text::_('TRACKS');?></th>
                                    <th scope="col"><?=Text::_('CREATED');?></th>
                                    <th scope="col"><?=Text::_('EXPIRY');?></th>
                                    <th scope="col"><?=Text::_('REMAINING');?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($page->data['downloads'] as $row):?>
                                <tr>
                                    <th scope="row" title="<?= $row->token;?>"><?= $row->tracks;?></th>
                                    <td><?= date_format($row->created,"yy-m-d H:i:s");?></td>
                                    <td><?= date_format($row->expiry,"yy-m-d H:i:s");?></td>
                                    <td><?= $row->created->diff($row->expiry)->format('%a');?>&nbsp;<?= Text::_('DAYS');?></td>
                                </tr>
                                <?php endforeach;?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>                                                   
        </div>
    </section>
<?php endif; ?>
<!-- JS -->
<script type="text/javascript">
    jQuery(document).ready(function () {     
           
        let lbl = ["<?= join('","', array_keys($page->data['history']));?>"];
        let raw    = [<?= join(',', $page->data['history']);?>];
                                     
        $.rbfy.profile.init(lbl,raw);

        $('#button-user-list').on('click',function(e){
            e.preventDefault();
            $.rbfy.go($.rbfy.livesite+'/?task=user.list');
        });
        $('#button-user-update').on('click',function(e){
            e.preventDefault();
            $.rbfy.profile.update();
        });             
    });
</script>
