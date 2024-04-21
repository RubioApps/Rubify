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

list($oid , $alias) = Helpers::getId();
?>
<section class="container">
    <div class="row p-1 pt-3 pb-3 pt-xxl-0 d-flex align-items-center">      
        <div class="col w-100">
            <div id="searchbox" class="d-flex form-inputs">
                <input id="query" name="q" type="search" class="form-control" placeholder="<?= Text::_('SEARCH');?>">
                <i class="bx bx-search"></i>
            </div>                
        </div>      
    </div>               
</section> 
<!-- Autocomplete -->
<script type="text/javascript">   
jQuery(document).ready(function(){   
    $.rbfy.search({ task: '<?= $task;?>', oid: '<?= $oid;?>', alias: '<?= $alias;?>'});
});                 
</script> 
