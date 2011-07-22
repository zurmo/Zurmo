<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2011 Zurmo Inc.
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
     * You can contact Zurmo, Inc. with a mailing address at 113 McHenry Road Suite 207,
     * Buffalo Grove, IL 60089, USA. or at email address contact@zurmo.com.
     ********************************************************************************/

    require_once('../bootstrap.php');
    require_once('protected/extensions/zurmoinc/framework/utils/MessageUtil.php');

    define('GOOD_YII_T', '/Yii::t\\(\'([^$]*?)\', \'([^$]*?)\'[),]/'); // Not Coding Standard
    define('ALL_YII_TS', '/Yii::t\\([\'"][a-zA-Z]+[\'"], [\'"].*/'); // Not Coding Standard

    if (php_sapi_name() === 'cli')
    {
        $basePath = realpath(dirname(__FILE__) . '/../../');

        require_once("$basePath/../roots.php");
        $yiit   = "$basePath/../../yii/framework/yiit.php";
        $config = "$basePath/config/main.php";

        define('MEMCACHE_ON', false);   //Manually define this as false otherwise a warning about this being undefined
                                        //appears

        //Setup database
        if (!RedBeanDatabase::isSetup())
        {
            RedBeanDatabase::setup(Yii::app()->db->connectionString,
                                   Yii::app()->db->username,
                                   Yii::app()->db->password);
        }

        $super = User::getByUsername('super');
        Yii::app()->user->userModel = $super;

        echo "Checking message file consistency...\n";

        $messagesDirectoryNamesToModuleNames = array("$basePath/extensions/zurmoinc/framework/messages" => 'framework');
        $modules = Module::getModuleObjects();
        foreach ($modules as $module)
        {
            $moduleName = $module->getName();
            $messageDirectoryName = "$basePath/modules/$moduleName/messages";
            if (is_dir($messageDirectoryName))
            {
                $messagesDirectoryNamesToModuleNames[$messageDirectoryName] = $moduleName;
            }
        }

        $problems = array();
        foreach ($messagesDirectoryNamesToModuleNames as $messagesDirectoryName => $moduleName)
        {
            $problems = array_merge($problems, checkLanguagesMessageFilesContainAllTheSameValidMessages  ($messagesDirectoryName, $moduleName));
            $problems = array_merge($problems, checkFirstLanguagesMessageFilesMessagesReallyExistInSource($messagesDirectoryName, $moduleName));
            $problems = array_merge($problems, checkFirstLanguagesMessageFilesContainAllExistingMessages ($messagesDirectoryName, $moduleName, $basePath));
            $problems = array_merge($problems, checkForYiiTCallsThatAreNotHowThisScriptExpectsThemToBe   ($messagesDirectoryName, $moduleName));
        }

        if (count($problems) == 0)

        {
            echo 'No';
        }
        else
        {
            foreach ($problems as $problem)
            {
                echo "  - $problem\n";
            }
            echo count($problems);
        }
            echo " problems found.\n";
    }
?>
