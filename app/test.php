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
    require_once('version.php');
    require_once('roots.php');

    define('IS_TEST', true);

    // Debug is used per instance.
    if (is_file(INSTANCE_ROOT . '/protected/config/debugTest.php'))
    {
        $debug          = INSTANCE_ROOT . '/protected/config/debugTest.php';
    }
    else
    {
        $debug          = INSTANCE_ROOT . '/protected/config/debugDIST.php';
    }
    // Runtime path is used per instance.
    $runtimePath    = INSTANCE_ROOT . '/protected/runtime';
    // The config is used per instance.
    $config         = INSTANCE_ROOT . '/protected/config/main.php';
    // Yii is used from the common source.
    $yii            = COMMON_ROOT   . '/../yii/framework/yii.php';
    // WebApplication is used from the common source.
    $webApplication = COMMON_ROOT   . '/protected/core/components/WebApplication.php';

    require_once($debug);
    require_once($yii);
    require_once($webApplication);

    $webApplication = Yii::createApplication('WebApplication', $config);
    $webApplication->setRuntimePath($runtimePath);
    $webApplication->run();
    exit;
?>
