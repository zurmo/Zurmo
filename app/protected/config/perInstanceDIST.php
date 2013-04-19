<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
     *
     * Zurmo is free software; you can redistribute it and/or modify it under
     * the terms of the GNU General Public License version 3 as published by the
     * Free Software Foundation with the addition of the following permission added
     * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
     * IN WHICH THE COPYRIGHT IS OWNED BY ZURMO, ZURMO DISCLAIMS THE WARRANTY
     * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
     *
     * Zurmo is distributed in the hope that it will be useful, but WITHOUT
     * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
     * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
     * details.
     *
     * You should have received a copy of the GNU General Public License along with
     * this program; if not, see http://www.gnu.org/licenses or write to the Free
     * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
     * 02110-1301 USA.
     *
     * You can contact Zurmo, Inc. with a mailing address at 27 North Wacker Drive
     * Suite 370 Chicago, IL 60606. or at email address contact@zurmo.com.
     *
     * The interactive user interfaces in original and modified versions
     * of this program must display Appropriate Legal Notices, as required under
     * Section 5 of the GNU General Public License version 3.
     *
     * In accordance with Section 7(b) of the GNU General Public License version 3,
     * these Appropriate Legal Notices must retain the display of the Zurmo
     * logo and Zurmo copyright notice. If the display of the logo is not reasonably
     * feasible for technical reasons, the Appropriate Legal Notices must display the words
     * "Copyright Zurmo Inc. 2013. All rights reserved".
     ********************************************************************************/

    // Configure for production.
    $language         = 'en'; // As per language codes under the messages directory.
    $currencyBaseCode = 'USD';
    $theme            = 'default';
    $connectionString = 'mysql:host=localhost;port=3306;dbname=zurmo'; // Not Coding Standard
    $username         = 'zurmo';
    $password         = 'zurmo';
    $memcacheServers  = array( // An empty array means memcache is not used.
                            array(
                                'host'   => '127.0.0.1',
                                'port'   => 11211, // This is the default memcached port.
                                'weight' => 100,
                            ),
                        );
    $adminEmail       = 'info@zurmo.com';
    $installed        = false; // Set to true by the installation process.
    $maintenanceMode  = false; // Set to true during upgrade process or other maintenance tasks.
    $instanceConfig   = array(); //Set any parameters you want to have merged into configuration array.
                                 //@see CustomManagement
    //$instanceConfig['components']['request']['hostInfo'] = '';
    //$instanceConfig['components']['request']['scriptUrl'] = '';

    $urlManager = array (); // Set any parameters you want to customize url manager.

    if (is_file(INSTANCE_ROOT . '/protected/config/perInstanceConfig.php'))
    {
        require_once INSTANCE_ROOT . '/protected/config/perInstanceConfig.php';
    }
    define('ZURMO_TOKEN', 'defaultValue');
?>
