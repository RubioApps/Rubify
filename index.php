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
define('_RBFYEXEC', 1);

/*
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
*/

// Start session
if(session_status() !== PHP_SESSION_ACTIVE) session_start();

// Start content
ob_start();

/*
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
*/

define('RBFY_BASE', dirname(__FILE__));
require_once RBFY_BASE . '/includes/defines.php';

// Load Factory
require_once RBFY_INCLUDES . '/factory.php';
$factory    = new Rubify\Framework\Factory();

// Get configuration and locale
$config     = $factory->getConfig();

// Get the language
$language   = $factory->getLanguage();

// Get the user
$user       = $factory->getUser();

// Bridge to the JS Framework
$factory->jsBridge();
    
// Get the current task
$task       = $factory->getTask();

// Save the current parameters
$factory->saveParams();   

// Get the router
$router     = $factory->getRouter();

// Get the page
$page = $factory->getPage();

// Dispatch
require_once $router->dispatch();

// Flush the content
ob_end_flush();

die();