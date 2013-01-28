<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2012 Zurmo Inc.
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
    /**
     * Install Walkthrought Test
     * Walkthrough installation process and test all install controller actions.
     * To-Do: Check if program redirect user to index page, when application is already installed (Selenium?)
     */
    class InstallWalkthroughTest extends ZurmoWalkthroughBaseTest
    {
        protected $perInstanceConfigContents = "";
        protected $debugConfigContents = "";
        protected $perInstanceFile;
        protected $debugFile;
        protected $instanceRoot;

        protected $databaseHostname;
        protected $databaseUsername;
        protected $databasePassword;
        protected $databasePort;
        protected $databaseName;
        protected $superUserPassword;

        public function setUp()
        {
            parent::setUp();
            list(, $this->databaseHostname, $this->databasePort, $this->databaseName) = array_values(RedBeanDatabase::getDatabaseInfoFromDsnString(Yii::app()->db->connectionString));
            $this->databaseUsername          = Yii::app()->db->username;
            $this->databasePassword          = Yii::app()->db->password;
            $this->superUserPassword         = 'super';

            $this->instanceRoot              = INSTANCE_ROOT;
            $this->perInstanceFile           = "{$this->instanceRoot}/protected/config/perInstanceTest.php";
            $this->debugFile                 = "{$this->instanceRoot}/protected/config/debugTest.php";

            if (is_file($this->perInstanceFile))
            {
                $this->perInstanceConfigContents = file_get_contents($this->perInstanceFile);
                unlink($this->perInstanceFile);
            }
            if (is_file($this->debugFile))
            {
                $this->debugConfigContents = file_get_contents($this->debugFile);
                unlink($this->debugFile);
            }
            Yii::app()->gameHelper->muteScoringModelsOnSave();
        }

        public function teardown()
        {
            if (strlen($this->perInstanceConfigContents))
            {
                file_put_contents($this->perInstanceFile, $this->perInstanceConfigContents);
            }
            else
            {
                unlink($this->perInstanceFile);
            }

            if (strlen($this->debugConfigContents))
            {
                file_put_contents($this->debugFile, $this->debugConfigContents);
            }
            else
            {
                unlink($this->debugFile);
            }
            Yii::app()->gameHelper->unmuteScoringModelsOnSave();
            parent::teardown();
        }

        public function testAllActions()
        {
            //Ensure that installed = false
            Yii::app()->setApplicationInstalled(false);

            //Check index action.
            $this->runControllerWithNoExceptionsAndGetContent('install/default');
            $this->runControllerWithNoExceptionsAndGetContent('install/default/index');

            //Check welcome action.
            $this->runControllerWithNoExceptionsAndGetContent('install/default/welcome');

            //Check checkSystem action.
            if (isset($_SERVER['SERVER_SOFTWARE']))
            {
                $serverSoftware = $_SERVER['SERVER_SOFTWARE'];
            }
            $_SERVER['SERVER_SOFTWARE'] = 'Apache';
            $this->runControllerWithNoExceptionsAndGetContent('install/default/checkSystem');

            //Check settings action.
            $this->runControllerWithNoExceptionsAndGetContent('install/default/settings');

            //Check validateSettings action.
            //First validation will fail, and there should be at least validation errors.
            $this->setPostArray(array(
                'ajax'                => 'install-form',
                'InstallSettingsForm' => array(
                    'databaseHostname'      => '',
                    'databaseAdminUsername' => '',
                    'databaseAdminPassword' => '',
                    'databaseName'          => '',
                    'databaseUsername'      => '',
                    'databasePassword'      => '',
                    'databasePort'          => '',
                    'superUserPassword'     => '',
                    'memcacheHostname'      => '',
                    'memcachePortNumber'    => '',
                    'memcacheAvailable'     => '',
                    'databaseType'          => 'mysql',
                    'removeExistingData'    => '',
                    'installDemoData'       => '',
                )));
            $content = $this->runControllerWithExitExceptionAndGetContent('install/default/settings');
            $errors = CJSON::decode($content);
            $this->assertGreaterThanOrEqual(5, count($errors));

            $postData = array(
                'ajax'                => 'install-form',
                'InstallSettingsForm' => array(
                    'databaseHostname'      => $this->databaseHostname,
                    'databaseAdminUsername' => '',
                    'databaseAdminPassword' => '',
                    'databaseName'          => $this->databaseName,
                    'databaseUsername'      => $this->databaseUsername,
                    'databasePassword'      => $this->databasePassword,
                    'databasePort'          => $this->databasePort,
                    'superUserPassword'     => $this->superUserPassword,
                    'databaseType'          => 'mysql',
                    'removeExistingData'    => '1',
                    'installDemoData'       => '',
                )
            );
            if (MEMCACHE_ON)
            {
                $memcacheSettings = array(
                    'memcacheHostname'      => 'localhost',
                    'memcachePortNumber'    => '11211',
                    'memcacheAvailable'     => '1',
                );
            }
            else
            {
                $memcacheSettings = array(
                    'memcacheHostname'      => '',
                    'memcachePortNumber'    => '',
                    'memcacheAvailable'     => '0',
                );
            }
            $postData['InstallSettingsForm'] = array_merge(
                $postData['InstallSettingsForm'], $memcacheSettings
            );

            $this->setPostArray($postData);

            $content = $this->runControllerWithExitExceptionAndGetContent('install/default/settings');
            $errors = CJSON::decode($content);
            $this->assertEquals(1, count($errors));

            $postData['InstallSettingsForm']['hostInfo'] = 'http://www.example.com';
            $this->setPostArray($postData);

            $content = $this->runControllerWithExitExceptionAndGetContent('install/default/settings');
            $errors = CJSON::decode($content);
            $this->assertEquals(0, count($errors));

            //Run installation.
            $this->setPostArray(array(
                'InstallSettingsForm' => array(
                    'databaseHostname'      => $this->databaseHostname,
                    'databaseAdminUsername' => '',
                    'databaseAdminPassword' => '',
                    'databaseName'          => $this->databaseName,
                    'databaseUsername'      => $this->databaseUsername,
                    'databasePassword'      => $this->databasePassword,
                    'databasePort'          => $this->databasePort,
                    'superUserPassword'     => $this->superUserPassword,
                    'memcacheHostname'      => 'localhost',
                    'memcachePortNumber'    => '11211',
                    'memcacheAvailable'     => '1',
                    'databaseType'          => 'mysql',
                    'removeExistingData'    => '1',
                    'installDemoData'       => '',
                    'hostInfo'              => 'http://www.example.com'
                )));

            //Close db connection(new will be created during installation process).
            RedBeanDatabase::close();
            $this->runControllerWithExitExceptionAndGetContent('install/default/settings');
            $industryFieldData = CustomFieldData::getByName('Industries');
            $this->assertGreaterThan('0', count(unserialize($industryFieldData->serializedData)));

            //Check installDemoData action.
            RedBeanDatabase::close();
            DemoDataUtil::unsetLoadedModules();
            $this->runControllerWithNoExceptionsAndGetContent('install/default/installDemoData');
            $this->assertGreaterThan('0', Account::getAll());
            $this->assertGreaterThan('0', Contact::getAll());

            //Restore $_SERVER['SERVER_SOFTWARE']
            if (isset($serverSoftware))
            {
                $_SERVER['SERVER_SOFTWARE'] = $serverSoftware;
            }
            else
            {
                unset($_SERVER['SERVER_SOFTWARE']);
            }
        }
    }
?>