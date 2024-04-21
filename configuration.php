<?php
/**
 +-------------------------------------------------------------------------+
 | Rubify  - An MiniDLNA Webapp                                      |
 | Version 1.0.0                                                           |
 |                                                                         |
 | This program is free software: you can redistribute it and/or modify    |
 | it under the terms of the GNU General Public License as published by    |
 | the Free Software Foundation.                                           |
 |                                                                         |
 | This file forms part of the Rubify software.                           |
 |                                                                         |
 | If you wish to use this file in another project or create a modified    |
 | version that will not be part of the Rubify Software, you              |
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

namespace Rubify\Framework;

defined('_RBFYEXEC') or die;

class RbfyConfig {
        public $sitename  = 'Rubify';
        public $live_site = 'https://<your-site>';     
        public $use_cache = true;
        public $use_symlink = false;        
        public $use_autolog = false;
        public $enable_upload = true;        
        public $key      = '<put-here-a-long-secured-key>';
	public $list_limit = 60;
        public $minidlna = [
                'dir'   => '/var/lib/minidlna',
                'http'  => 'http://192.168.1.1:8200'
                ];
	public $theme = 'default';
}

