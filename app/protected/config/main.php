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

    // Used in a symlinked configuration where
    // multiple instances use the same source code.
    if (defined('IS_TEST'))
    {
        $perInstanceFilename = 'perInstanceTest.php';
    }
    else
    {
        $perInstanceFilename = 'perInstance.php';
    }

    if (is_file(INSTANCE_ROOT . '/protected/config/' . $perInstanceFilename))
    {
        $perInstanceConfigWithSymlinkedMainConfig          = INSTANCE_ROOT . '/protected/config/' . $perInstanceFilename;
    }
    else
    {
        $perInstanceConfigWithSymlinkedMainConfig          = INSTANCE_ROOT . '/protected/config/perInstanceDIST.php';
    }

    // Used in a non-symlinked configuration such
    // as in running unit tests directly in the
    // checked out source code.
    if (is_file(COMMON_ROOT   . '/protected/config/' . $perInstanceFilename))
    {
        $perInstanceConfigInSameDirAsMainConfig          = COMMON_ROOT   . '/protected/config/' . $perInstanceFilename;
    }
    else
    {
        $perInstanceConfigInSameDirAsMainConfig          = COMMON_ROOT   . '/protected/config/perInstanceDIST.php';
    }

    // The per instance version is used in preference.
    if (file_exists($perInstanceConfigWithSymlinkedMainConfig))
    {
        require_once($perInstanceConfigWithSymlinkedMainConfig);
    }
    else
    {
        require_once($perInstanceConfigInSameDirAsMainConfig);
    }

    if (!isset($maintenanceMode))
    {
        $maintenanceMode = false;
    }

    $config = CMap::mergeArray(
        require(COMMON_ROOT . '/protected/config/common.php'),
        array(
            'language'        => $language,
            'theme'           => $theme,
            'installed'       => $installed,
            'maintenanceMode' => $maintenanceMode,
            'components'  => array(
                'db' => array(
                    'connectionString' => $connectionString,
                    'username'         => $username,
                    'password'         => $password,
                ),
                'currencyHelper' => array(
                    'baseCode' => $currencyBaseCode,
                ),
                'urlManager' => $urlManager,
            ),
        )
    );
    if (isset($instanceConfig))
    {
        return CMap::mergeArray($config, $instanceConfig);
    }
    else
    {
        return $config;
    }
?>
