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
<?php if ($user->isLogged() && $user->isAdmin()): ?>
<section class="container">
    <form>
        <?= $factory::getToken(); ?>

        <?php if($page->data->uid):?>            
        <div id="profile-login">           
            <div class="mb-3">
                <label for="uid" class="form-label"><?= Text::_('USER_NAME'); ?></label>
                <input type="text" class="form-control w-50" name="uid" id="uid" aria-describedby="uid-help"  disabled="disabled" value="<?= $page->data->uid; ?>">
                <div id="uid-help" class="form-text"><?= Text::_('USER_NAME_DESC'); ?></div>
            </div>
            <div class="mb-3">
                <label for="pwd" class="form-label"><?= Text::_('USER_PASSWORD'); ?></label>
                <div class="d-flex">
                    <input type="password" name="pwd" id="pwd" class="form-control w-50 me-1">
                    <button type="button" class="password-eye btn btn-secondary bi bi-eye"></button>
                </div>
                <div id="pwd-help" class="form-text"><?= Text::_('USER_PASSWORD_DESC'); ?></div>
            </div>
            <div class="mb-3 justify-content-left d-flex">
                <button type="button" id="button-user-remove" type="submit" class="btn m-1 btn-danger btn bi bi-person-fill-dash">
                    <span class="d-none d-sm-inline"><?= Text::_('ADMIN_USER_REMOVE');?></span>
                </button>
                <button type="button" id="button-user-update" type="submit" class="btn m-1 btn-success btn bi bi-person-check-fill">
                    <span class="d-none d-sm-inline"><?= Text::_('ADMIN_USER_UPDATE');?></span>
                </button>
            </div>             
        </div>       
        <?php else:?>
        <div id="profile-login">        
            <div class="mb-3">
                <label for="uid" class="form-label"><?= Text::_('USER_NAME'); ?></label>
                <input type="text" class="form-control w-50" name="uid" id="uid" aria-describedby="uid-help" value="">
                <div id="uid-help" class="form-text"><?= Text::_('USER_NAME_DESC'); ?></div>
            </div>
            <div class="mb-3">
                <label for="pwd" class="form-label"><?= Text::_('USER_PASSWORD'); ?></label>
                <div class="d-flex">
                    <input type="password" name="pwd" id="pwd" class="form-control w-50 me-1">
                    <button type="button" class="password-eye btn btn-secondary bi bi-eye"></button>
                </div>
                <div id="pwd-help" class="form-text"><?= Text::_('USER_PASSWORD_DESC'); ?></div>
            </div>
            <div class="mb-3">
                <label for="lvl" class="form-label"><?= Text::_('USER_LEVEL'); ?></label>
                <div class="d-flex">
                    <select name="level" id="lvl">
                        <option value="user" selected><?= Text::_('USER');?></option>
                        <option value="admin"><?= Text::_('ADMIN');?></option>                        
                    </select>
                </div>
            </div>            
            <div class="mb-3 justify-content-left d-flex">
                <button type="button" id="button-user-add" type="submit" class="btn m-1 btn-primary btn bi bi-person-plus">
                    <span class="d-none d-sm-inline"><?= Text::_('ADMIN_USER_ADD');?></span>
                </button>
            </div>               
        </div>        
        <?php endif;?>

    </form>
</section>
<?php endif; ?>
<!-- JS -->
<script type="text/javascript">
    jQuery(document).ready(function () { 

        $.rbfy.profile.eye();

        $('#button-user-update').on('click',function(e){
            e.preventDefault();
            $.rbfy.profile.update();
        });                       

        $('#button-user-remove').on('click',function(e){
            e.preventDefault();
            $.rbfy.profile.remove();
        });     
        
        $('#button-user-add').on('click',function(e){
            e.preventDefault();
            $.rbfy.profile.add();
        });             

    });
</script>
