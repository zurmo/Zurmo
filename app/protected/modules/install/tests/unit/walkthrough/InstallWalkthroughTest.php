<?php
    class InstallWalkthroughTest extends ZurmoWalkthroughBaseTest
    {

        protected $perInstanceConfig = "";
        protected $debugConfig = "";
        protected $perInstanceFile;
        protected $debugFile;
        protected $instanceRoot;

        public function setUp()
        {
            $this->instanceRoot = INSTANCE_ROOT;
            $this->perInstanceFile      = "{$this->instanceRoot}/protected/config/perInstanceTest.php";
            $this->debugFile            = "{$this->instanceRoot}/protected/config/debugTest.php";

            if (is_file($this->perInstanceFile))
            {
                $this->perInstanceConfig = file_get_contents($this->perInstanceFile);
                unlink($this->perInstanceFile);
            }
            if (is_file($this->debugFile))
            {
                $this->debugConfig = file_get_contents($this->debugFile);
                unlink($this->debugFile);
            }
        }

        public function teardown()
        {
            if (strlen($this->perInstanceConfig))
            {
                file_put_contents($this->perInstanceFile, $this->perInstanceConfig);
            }
            else
            {
                unlink($this->perInstanceFile);
            }

            if (strlen($this->debugConfig))
            {
                file_put_contents($this->debugFile, $this->debugConfig);
            }
            else
            {
                unlink($this->debugFile);
            }
        }

        public function testZurmoAlreadyInstalledActions()
        {
            //Set $installed = true in perInstanceTest.php file.
            //As this change will not affect Yii params, set it directly.
            //We need to test all controller actions, and all should fail.

            Yii::app()->setApplicationInstalled(true);
            //All actions below should fail and redirect user to index page.
            //To-do: fix code below
            /*
            $this->runControllerWithRedirectExceptionAndGetContent('install/default');
            $this->runControllerWithRedirectExceptionAndGetContent('install/default/index');
            $this->runControllerWithRedirectExceptionAndGetContent('install/default/welcome');
            $this->runControllerWithRedirectExceptionAndGetContent('install/default/checkSystem');
            $this->runControllerWithRedirectExceptionAndGetContent('install/default/settings');
            $this->runControllerWithRedirectExceptionAndGetContent('install/default/validateSettings');
            $this->runControllerWithRedirectExceptionAndGetContent('install/default/runInstallation');
            $this->runControllerWithRedirectExceptionAndGetContent('install/default/installDemoData');
			*/
        }

        public function testAllActions()
        {
            Yii::app()->setApplicationInstalled(false);

            //Check index action.
            $this->runControllerWithRedirectExceptionAndGetContent('install/default');
            $this->runControllerWithRedirectExceptionAndGetContent('install/default/index');

            //Check welcome action.
            $this->runControllerWithNoExceptionsAndGetContent('install/default/welcome');

            //Check checkSystem action.
            $this->runControllerWithNoExceptionsAndGetContent('install/default/checkSystem');

            //Check settings action.
            $this->runControllerWithNoExceptionsAndGetContent('install/default/settings');

            //Check validateSettings action.
            $this->setPostArray(array(
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
                        'memcacheAvailable' => true,
                        'databaseType' => 'mysql',
                        'removeExistingData' => true,
                        'installDemoData' => false,
                    )));
            $this->runControllerWithNoExceptionsAndGetContent('install/default/validateSettings');
            //To-Do: should we go with more combinations of post array above?

            //Check runInstallation action, separate. We need to empty database.
            $this->setPostArray(array(
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
                                    'memcacheAvailable' => true,
                                    'databaseType' => 'mysql',
                                    'removeExistingData' => true,
                                    'installDemoData' => false,

                                )));
            $this->runControllerWithNoExceptionsAndGetContent('install/default/runInstallation');

            //Check installDemoData action
            $this->runControllerWithNoExceptionsAndGetContent('install/default/installDemoData');
            //Check if demo data are installed.
        }
    }
?>