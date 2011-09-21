<?php
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
        protected $databaseName;
        protected $superUserPassword;


        public function setUp()
        {
            $matches = array();
            assert(preg_match("/host=([^;]+);dbname=([^;]+)/", Yii::app()->db->connectionString, $matches) == 1); // Not Coding Standard
            $this->databaseHostname          = $matches[1];
            $this->databaseUsername          = Yii::app()->db->username;
            $this->databasePassword          = Yii::app()->db->password;
            $this->databaseName              = $matches[2];
            $this->superUserPassword         = 'super';

            $this->instanceRoot = INSTANCE_ROOT;
            $this->perInstanceFile      = "{$this->instanceRoot}/protected/config/perInstanceTest.php";
            $this->debugFile            = "{$this->instanceRoot}/protected/config/debugTest.php";

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
        }

        //To-Do: Check if program redirect user to index page, when application is already installed (Selenium?)

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
            $_SERVER['SERVER_SOFTWARE'] = 'Apache';
            $this->runControllerWithNoExceptionsAndGetContent('install/default/checkSystem');


            //Check settings action.
            $this->runControllerWithNoExceptionsAndGetContent('install/default/settings');

            //Check validateSettings action.
            //First validation will fail, and there should be at least validation errors.
            $this->setPostArray(array(
            	'ajax' => 'install-form',
                'InstallSettingsForm' => array(
                 	'databaseHostname' => '',
                    'databaseAdminUsername' => '',
                    'databaseAdminPassword' => '',
                    'databaseName' => '',
                    'databaseUsername' => '',
                    'databasePassword' => '',
                    'superUserPassword' => '',
                    'memcacheHostname' => '',
                    'memcachePortNumber' => '',
                    'memcacheAvailable' => '',
                    'databaseType' => 'mysql',
                    'removeExistingData' => '',
                    'installDemoData' => '',
                )));
            $content = $this->runControllerWithExitExceptionAndGetContent('install/default/settings');
            $errors = CJSON::decode($content);
            $this->assertGreaterThanOrEqual(5, count($errors));

            //This validation should pass.
            $this->setPostArray(array(
                'ajax' => 'install-form',
            	'InstallSettingsForm' => array(
            		'databaseHostname' => $this->databaseHostname,
            		'databaseAdminUsername' => '',
                    'databaseAdminPassword' => '',
                    'databaseName' => $this->databaseName,
                    'databaseUsername' => $this->databaseUsername,
                    'databasePassword' => $this->databasePassword,
                    'superUserPassword' => $this->superUserPassword,
                    'memcacheHostname' => 'localhost',
                    'memcachePortNumber' => '11211',
                    'memcacheAvailable' => 1,
                    'databaseType' => 'mysql',
                    'removeExistingData' => 1,
                    'installDemoData' => '',
                )));
            $content = $this->runControllerWithExitExceptionAndGetContent('install/default/settings');
            $errors = CJSON::decode($content);
            $this->assertEquals(0, count($errors));

            //Run installation.
            $this->setPostArray(array(
				'InstallSettingsForm' => array(
                	'databaseHostname' => $this->databaseHostname,
                    'databaseAdminUsername' => '',
                    'databaseAdminPassword' => '',
                    'databaseName' => $this->databaseName,
                    'databaseUsername' => $this->databaseUsername,
                    'databasePassword' => $this->databasePassword,
                    'superUserPassword' => $this->superUserPassword,
                    'memcacheHostname' => 'localhost',
                    'memcachePortNumber' => '11211',
                    'memcacheAvailable' => 1,
                    'databaseType' => 'mysql',
                    'removeExistingData' => 1,
                    'installDemoData' => '',
                )));
            //Close db connection(new will be created during installation process).
            RedBeanDatabase::close();
            $this->runControllerWithExitExceptionAndGetContent('install/default/settings');
            $industryFieldData = CustomFieldData::getByName('Industries');
            $this->assertGreaterThan('0', count(unserialize($industryFieldData->serializedData)));

            //Check installDemoData action.
            RedBeanDatabase::close();
            $this->runControllerWithNoExceptionsAndGetContent('install/default/installDemoData');
            $this->assertGreaterThan('0', Account::getAll());
            $this->assertGreaterThan('0', Contact::getAll());
        }
    }
?>