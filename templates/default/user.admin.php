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
<?php if ($user->isLogged() && $user->isAdmin()): ?>
<section class="container">
    <form>
        <?= $factory::getToken(); ?> 
        <div class="user-add p-2 mb-2 bg-light-subtle">
            <div class="d-flex flex-row w-100 fs-3 border-bottom mb-3">
                <?= Text::_('ADMIN_USER_ADD');?>
            </div>
            <div class="d-flex justify-content-end">
                <button type="button" class="button-submit bi bi-check btn btn-success m-1"></button>
                <button type="button" class="button-edit d-none bi bi-pen btn btn-info m-1"></button>
            </div>             
            <div class="d-flex flex-row">
                <div class="d-flex flex-column w-100">
                    <label for="uid" class="form-label"><?= Text::_('USER_NAME'); ?></label>
                    <div class="user-id">                    
                        <input id="uid" type="text" class="form-control" name="user" aria-describedby="uid-help" value="" aria-describedby="uid-help" />                    
                    </div>
                    <div id="uid-help" class="form-text"><?= Text::_('USER_NAME_DESC'); ?></div>
                    <label for="pwd" class="form-label mt-3"><?= Text::_('PASSWORD'); ?></label>                
                    <div class="user-pwd d-flex">                                           
                        <input id="pwd" type="password" name="password" class="form-control d-inline me-1" value="" aria-describedby="pwd-help" />
                        <button id="password-eye" type="button" class="my-auto btn btn-secondary bi bi-eye-slash"></button>
                    </div>
                    <div id="pwd-help" class="form-text"><?= Text::_('USER_PASSWORD_DESC'); ?></div>                
                    <label for="pwd" class="form-label mt-3"><?= Text::_('USER_LEVEL'); ?></label>
                    <div class="user-lvl">                    
                        <select id="lvl" class="form-select" name="level" aria-describedby="pwd-help">
                            <option value="user" selected="selected"><?= Text::_('ADMIN_USER_COMMON');?></option>
                            <option value="admin"><?= Text::_('ADMIN');?></option>                        
                        </select>
                    </div>
                    <div id="lvl-help" class="form-text"><?= Text::_('USER_LEVEL_DESC'); ?></div>                
                </div>
            </div>            
        </div>       
        <div class="d-flex flex-row w-100 fs-3 border-bottom mt-3 mb-3">
                <?= Text::_('ADMIN_USER_UPDATE');?>
        </div>                
        <?php foreach($page->data as $row):?>
        <div class="user-edit d-flex flex-row p-2 mb-2 border-bottom">
            <div class="d-flex flex-column w-100">
                <div class="user-id p-1" data-default="<?= $row->uid;?>"><?= Text::_($row->uid);?></div>
                <div class="user-pwd p-1 d-flex" data-default=""></div>
                <div class="user-lvl p-1" data-default="<?= $row->level;?>"><?= Text::_($row->level);?></div>
            </div>
            <div class="d-flex flex-column text-nowrap">
                <div class="user-cmd p-0 text-end">
                    <button type="button" class="button-submit d-none bi bi-check btn btn-success m-1" data-src="<?= $row->uid; ?>"></button>
                    <button type="button" class="button-edit bi bi-pen btn btn-info m-1" data-src="<?= $row->uid; ?>"></button>
                    <button type="button" class="button-remove bi bi-x-circle btn btn-danger m-1" data-src="<?= $row->uid; ?>"></button>                        
                </div>
            </div>                                
        </div>
        <?php endforeach;?>                
    </form>
</section>
<?php endif; ?>
<!-- JS -->
<script type="text/javascript">
    jQuery(document).ready(function () {  
        
        //Initalize the password eye
        $.rbfy.profile.eye();

        //Bind the edit buttons
        $('.button-edit').on('click',function(){

            const row = $(this).parents('.user-edit,.user-add');            
            const button = {
                submit: row.find('.button-submit'),
                edit:   row.find('.button-edit'),
                remove: row.find('.button-remove')
            };           
            let eye = $('#password-eye');

            //Retore the initial state
            $('input#uid').appendTo($('.user-add').first('.user-id')).removeAttr('disabled');;
            $('input#pwd').appendTo($('.user-add').first('.user-pwd'));
            $('select#lvl').appendTo($('.user-add').first('.user-lvl'));            
            $('.user-edit').each(function(){
                $(this).removeClass('bg-light-subtle');
                $(this).find('[data-default]').each(function(){
                    $(this).html($(this).attr('data-default'));
                });
                $(this).find('.button-submit').addClass('d-none');
                $(this).find('.button-edit').removeClass('d-none');
                eye.removeClass('show bi-eye').addClass('bi-eye-slash');
            }); 
            $('.user-add').removeClass('bg-light-subtle');
            $('.user-add').find('.button-submit').addClass('d-none');
            $('.user-add').find('.button-edit').removeClass('d-none');

            //Active the selected row
            row.addClass('bg-light-subtle');           
            button.submit.removeClass('d-none');
            button.edit.addClass('d-none'); 

            if(row.hasClass('user-edit'))
                $('input#uid').attr('disabled','disabled');

            const uid = row.find('.user-id').empty();
            const pwd = row.find('.user-pwd').empty();
            const lvl = row.find('.user-lvl').empty();              

            $('input#uid').val(uid.attr('data-default'));
            $('input#pwd').val(pwd.attr('data-default')).attr('type','password');
            $('select#lvl').val(lvl.attr('data-default'));

            uid.append($('input#uid'));
            pwd.append($('input#pwd')).append(eye);
            lvl.empty().append($('select#lvl')); 
            eye.unbind();     
            $.rbfy.profile.eye();      
        });

        //Bind the remove buttons
        $('.button-remove').on('click',function(){
            const row = $(this).parents('.user-edit');    
            const uid = $(this).attr('data-src');
            $('input#uid').val(uid);
            $.rbfy.profile.remove();
        });

        //Bind the add button
        $('.button-submit').on('click',function(){
            const row = $(this).parents('.user-edit,.user-add'); 
            if(row.hasClass('user-edit')){
                $.rbfy.profile.update();
            } else {
                $.rbfy.profile.add();
            }
        });        
    });
</script>
