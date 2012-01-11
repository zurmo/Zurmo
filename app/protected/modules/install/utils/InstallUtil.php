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

    /**
     * The purpose of this class is to drill through Modules,
     * build the database for freezing, and provide the other
     * function required to complete an install.
     */
    class InstallUtil
    {
        public static function getSupportedDatabaseTypes()
        {
            return array('mysql');
        }

        ///////////////////////////////////////////////////////////////////////
        // Methods that only check things. They don't change anything.
        // The aim is that when everything that can be checked passes
        // its checks, the subsequent methods that modify things should
        // be expected to succeed.

        /**
         * @param $requiredVersions An array mapping server names to minimum
         *                          required versions. eg: array('apache' => '2.2.16')
         */
        public static function checkWebServer(array $minimumRequiredVersions, /* out */ &$actualVersion)
        {
            $matches = array();
            $serverName = $_SERVER['SERVER_SOFTWARE'];
            if (strrpos($serverName, 'Microsoft-IIS') !== false && strrpos($serverName, 'Microsoft-IIS') >= 0)
            {
                if (preg_match('/([^\/]+)\/(\d+\.\d)?/', $_SERVER['SERVER_SOFTWARE'], $matches)) // Not Coding Standard
                {
                    $serverName = strtolower($matches[1]);
                    $actualVersion = $matches[2];
                    if (array_key_exists($serverName, $minimumRequiredVersions))
                    {
                        return self::checkVersion($minimumRequiredVersions[$serverName], $actualVersion);
                    }
                }
            }
            elseif (strrpos($serverName, 'Apache') !== false && strrpos($serverName, 'Apache') >= 0)
            {
                if (preg_match('/([^\/]+)\/(\d+\.\d+(.\d+))?/', $_SERVER['SERVER_SOFTWARE'], $matches)) // Not Coding Standard
                {
                    $serverName = strtolower($matches[1]);
                    $actualVersion = $matches[2];
                    if (array_key_exists($serverName, $minimumRequiredVersions))
                    {
                        return self::checkVersion($minimumRequiredVersions[$serverName], $actualVersion);
                    }
                }
            }
            return false;
        }

        /**
         * @returns The Apache ModDeflate version, or false if not installed.
         */
        public static function checkApacheModDeflate()
        {
        }

        /**
         * @param $minimumRequiredVersion Minimum required php version in "5.3.3" format.
         */
        public static function checkPhp($minimumRequiredVersion, /* out */ &$actualVersion)
        {
            $actualVersion = PHP_VERSION;
            return self::checkVersion($minimumRequiredVersion, $actualVersion);
        }

        /**
         * @returns true/false for if the timezone has been set.
         */
        public static function checkPhpTimezoneSetting()
        {
            $timezone = ini_get('date.timezone');
            return !empty($timezone);
        }

        public static function isMbStringInstalled()
        {
            return function_exists('mb_strlen');
        }

        /**
         * @returns true, or the max memory setting is less than the minimum required.
         */
        public static function checkPhpMaxMemorySetting($minimumMemoryRequireBytes, /* out */ & $actualMemoryLimitBytes)
        {
            $memoryLimit            = ini_get('memory_limit');
            $actualMemoryLimitBytes = self::getBytes($memoryLimit);
            return $minimumMemoryRequireBytes <= $actualMemoryLimitBytes;
        }

        /**
         * @returns true if the max file size is sufficient.
         */
        public static function checkPhpUploadSizeSetting($minimumUploadRequireBytes, /* out */ & $actualUploadLimitBytes)
        {
            $maxFileSize            = ini_get('upload_max_filesize');
            $actualUploadLimitBytes = self::getBytes($maxFileSize);
            return $minimumUploadRequireBytes <= $actualUploadLimitBytes;
        }

        /**
         * @returns true if the max post size is sufficient.
         */
        public static function checkPhpPostSizeSetting($minimumPostRequireBytes, /* out */ & $actualPostLimitBytes)
        {
            $maxPostSize            = ini_get('post_max_size');
            $actualPostLimitBytes = self::getBytes($maxPostSize);
            return $minimumPostRequireBytes <= $actualPostLimitBytes;
        }

        /**
         * @returns true if file uploads is set to on.
         */
        public static function isFileUploadsOn()
        {
            $value = ini_get('file_uploads');
            if ($value)
            {
                return true;
            }
            return false;
        }

        protected static function getBytes($size)
        {
            if (preg_match('/\d+[G|M|K]/i', $size)) // Not Coding Standard
            {
                switch (strtoupper(substr(trim($size), -1)))
                {
                    case 'G':
                        return (int)$size * 1024 * 1024 * 1024;

                    case 'M':
                        return (int)$size * 1024 * 1024;

                    case 'K':
                        return (int)$size * 1024;

                    default:
                        return (int)$size;
                }
            }
            else
            {
                return 0;
            }
        }

        /**
         * @returns true, or the MySQL version if less than required, or false if not installed.
         */
        public static function checkDatabase($databaseType,
                                            $databaseHostname,
                                            $databaseUsername,
                                            $databasePassword,
                                            $minimumRequiredVersion,
                                            /* out */ &$actualVersion)
        {
            $actualVersion = DatabaseCompatibilityUtil::getDatabaseVersion($databaseType,
                                                                           $databaseHostname,
                                                                           $databaseUsername,
                                                                           $databasePassword);
            return self::checkVersion($minimumRequiredVersion, $actualVersion);
        }

        /**
         * @returns true, or the APC version if less than required, or false if not installed.
         */
        public static function checkAPC($minimumRequiredVersion, /* out */ &$actualVersion)
        {
            $actualVersion = phpversion('apc');
            if ($actualVersion !== false && $actualVersion !== null)
            {
                return self::checkVersion($minimumRequiredVersion, $actualVersion);
            }
            return false;
        }

        /**
         * @returns true, or the Soap version if less than required, or false if not installed.
         */
        public static function checkSoap()
        {
            return in_array('soap', get_loaded_extensions());
        }

        /**
         * @returns true, or the memcache version if less than required, or false if not installed.
         */
        public static function checkMemcache($minimumRequiredVersion, /* out */ &$actualVersion)
        {
            $actualVersion = phpversion('memcache');
            if ($actualVersion != false && extension_loaded('memcache'))
            {
                return self::checkVersion($minimumRequiredVersion, $actualVersion);
            }
            return false;
        }

        /**
         * @returns true, or the Tidy version if less than required, or false if not installed.
         */
        public static function checkTidy($minimumRequiredVersion, /* out */ &$actualVersion)
        {
            $actualVersion = phpversion('tidy');
            if ($actualVersion !== null)
            {
                return self::checkVersion($minimumRequiredVersion, $actualVersion);
            }
            return false;
        }

        /**
         * @returns true, or the Curl version if less than required, or false if not installed.
         */
        public static function checkCurl($minimumRequiredVersion, /* out */ &$actualVersion)
        {
            if (!in_array('curl', get_loaded_extensions()))
            {
                return false;
            }
            $versionInfo   = curl_version();
            $actualVersion = $versionInfo['version'];
            if ($actualVersion !== null)
            {
                return self::checkVersion($minimumRequiredVersion, $actualVersion);
            }
            return false;
        }

        public static function checkYii($minimumRequiredVersion, /* out */ &$actualVersion)
        {
            $actualVersion = Yii::getVersion();
            if ($actualVersion !== null)
            {
                return self::checkVersion($minimumRequiredVersion, $actualVersion);
            }
            return false;
        }

        public static function checkRedBean($minimumRequiredVersion, /* out */ &$actualVersion)
        {
            $actualVersion = R::getVersion();
            if ($actualVersion !== null)
            {
                return self::checkVersion($minimumRequiredVersion, $actualVersion);
            }
            return false;
        }

        public static function checkRedBeanPatched()
        {
            $contents = file_get_contents('../redbean/rb.php');
            return preg_match('/public function __call\(\$method, \$args\) {\s+return null;/', $contents) != 0; // Not Coding Standard
        }

        /**
         * @returns true, or an array of connection error number and string .
         */
        public static function checkMemcacheConnection($host, $port)
        {
            assert('is_string($host) && $host != ""');
            assert('is_int   ($port) && $port >= 1024');
            $errorNumber = 0;
            $errorString = '';
            $timeout     = 2;
            $connection = @fsockopen($host, $port, $errorNumber, $errorString, $timeout);
            if ($connection !== false)
            {
                fclose($connection);
                return true;
            }
            return array($errorNumber, $errorString);
        }

        public static function checkDatabaseMaxAllowedPacketsSize($databaseType,
                                                                $databaseHostname,
                                                                $databaseUsername,
                                                                $databasePassword,
                                                                $minimumRequireBytes,
                                                                /* out */ & $actualBytes)
        {
            assert('in_array($databaseType, self::getSupportedDatabaseTypes())');
            $actualBytes = DatabaseCompatibilityUtil::getDatabaseMaxAllowedPacketsSize($databaseType,
                                                                                       $databaseHostname,
                                                                                       $databaseUsername,
                                                                                       $databasePassword);
            return $minimumRequireBytes <= $actualBytes;
        }

        public static function checkDatabaseMaxSpRecursionDepth($databaseType,
                                                              $databaseHostname,
                                                              $databaseUsername,
                                                              $databasePassword,
                                                              $minimumRequiredMaxSpRecursionDepth,
                                                              /* out */ & $maxSpRecursionDepth)
        {
            assert('in_array($databaseType, self::getSupportedDatabaseTypes())');
            $maxSpRecursionDepth = DatabaseCompatibilityUtil::getDatabaseMaxSpRecursionDepth($databaseType,
                                                                                             $databaseHostname,
                                                                                             $databaseUsername,
                                                                                             $databasePassword);
            return $minimumRequiredMaxSpRecursionDepth <= $maxSpRecursionDepth;
        }

        public static function checkDatabaseDefaultCollation($databaseType,
                                                           $databaseHostname,
                                                           $databaseName,
                                                           $databaseUsername,
                                                           $databasePassword,
                                                           $notAllowedDatabaseCollations,
                                                           /* out */ & $databaseDefaultCollation)
        {
            assert('in_array($databaseType, self::getSupportedDatabaseTypes())');
            assert('is_array($notAllowedDatabaseCollations)');
            $databaseDefaultCollation = DatabaseCompatibilityUtil::getDatabaseDefaultCollation($databaseType,
                                                                                               $databaseHostname,
                                                                                               $databaseName,
                                                                                               $databaseUsername,
                                                                                               $databasePassword);
            return !in_array($databaseDefaultCollation, $notAllowedDatabaseCollations);
        }

        /**
         * Connects to the database.
         */
        public static function connectToDatabase($databaseType, $host, $databaseName, $username, $password)
        {
            assert('in_array($databaseType, self::getSupportedDatabaseTypes())');
            assert('is_string($host)         && $host         != ""');
            assert('is_string($databaseName) && $databaseName != ""');
            assert('is_string($username)     && $username     != ""');
            assert('is_string($password)');
            $connectionString = "$databaseType:host=$host;dbname=$databaseName"; // Not Coding Standard
            self::connectToDatabaseWithConnectionString($connectionString, $username, $password);
        }

        /**
         * Connects to the database with a connection string.
         */
        public static function connectToDatabaseWithConnectionString($connectionString, $username, $password)
        {
            assert('is_string($connectionString) && $connectionString != ""');
            assert('is_string($username)         && $username         != ""');
            assert('is_string($password)');
            RedBeanDatabase::setup($connectionString, $username, $password);
            assert('RedBeanDatabase::isSetup()');
        }

        /**
         * Creates the first user.
         */
        public static function createSuperUser($username, $password)
        {
            $user = new User();
            $user->username     = $username;
            $user->title->value = 'Mr.';
            $user->firstName    = 'Super';
            $user->lastName     = 'User';
            $user->setPassword($password);
            $saved = $user->save();
            assert('$saved'); // TODO - handle this properly.

            $group = Group::getByName('Super Administrators');
            $group->users->add($user);
            $saved = $group->save();
            assert('$saved'); // TODO - handle this properly.
            return $user;
        }

        /**
         * Drops all the tables in the databaes.
         */
        public static function dropAllTables()
        {
            $tableNames = R::getCol('show tables');
            foreach ($tableNames as $tableName)
            {
                R::exec("drop table $tableName");
            }
            assert('count(R::getCol("show tables")) == 0');
        }

        /**
         * Auto builds the database.
         */
        public static function autoBuildDatabase(& $messageLogger)
        {
            $rootModels = array();
            foreach (Module::getModuleObjects() as $module)
            {
                $moduleAndDependenciesRootModelNames = $module->getRootModelNamesIncludingDependencies();
                $rootModels = array_merge($rootModels, array_diff($moduleAndDependenciesRootModelNames, $rootModels));
            }
            RedBeanDatabaseBuilderUtil::autoBuildModels($rootModels, $messageLogger);
        }

        /**
         * Freezes the database.
         */
        public static function freezeDatabase()
        {
            RedBeanDatabase::freeze();
        }

        /**
         * Closes the database.
         */
        public static function close()
        {
            RedBeanDatabase::close();
        }

        /**
         * Writes configuration to debug.php and phpInstance.php.
         */
        public static function writeConfiguration($instanceRoot,
                                                  $databaseType, $databaseHost, $databaseName, $username, $password,
                                                  $memcacheHost = null, $memcachePort = null, $minifyScripts = true,
                                                  $language,
                                                  $perInstanceFilename = 'perInstance.php', $debugFilename = 'debug.php',
                                                  $hostInfo, $scriptUrl)
        {
            assert('is_dir($instanceRoot)');
            assert('in_array($databaseType, self::getSupportedDatabaseTypes())');
            assert('is_string($databaseHost) && $databaseHost != ""');
            assert('is_string($databaseName) && $databaseName != ""');
            assert('is_string($username)     && $username     != ""');
            assert('is_string($password)');
            assert('is_string($memcacheHost) || $memcacheHost == null');
            assert('(is_int   ($memcachePort) && $memcachePort >= 1024) || $memcachePort == null');
            assert('is_string($language)     && $language     != ""');
            //assert('is_string($hostInfo)     && $hostInfo     != ""');
            //assert('is_string($scriptUrl)     && $scriptUrl     != ""');

            $perInstanceConfigFileDist = "$instanceRoot/protected/config/perInstanceDIST.php";
            $debugConfigFileDist = "$instanceRoot/protected/config/debugDIST.php";

            $perInstanceConfigFile     = "$instanceRoot/protected/config/$perInstanceFilename";
            $debugConfigFile     = "$instanceRoot/protected/config/$debugFilename";

            copy($perInstanceConfigFileDist, $perInstanceConfigFile);
            copy($debugConfigFileDist, $debugConfigFile);
            chmod($perInstanceConfigFile, 0777);
            chmod($debugConfigFile, 0777);

            // NOTE: These keep the tidy formatting of the files they are modifying - the whitespace matters!
            $contents = file_get_contents($debugConfigFile);
            $contents = preg_replace('/\$debugOn\s*=\s*true;/',
                                     '$debugOn = false;',
                                     $contents);
            $contents = preg_replace('/\$forceNoFreeze\s*=\s*true;/',
                                     '$forceNoFreeze = false;',
                                     $contents);
            if ($minifyScripts)
            {
                $contents = preg_replace('/\$minifyScripts\s*=\s*false;/',
                                         '$minifyScripts = true;',
                                         $contents);
            }
            if ($memcacheHost == null && $memcachePort == null)
            {
                $contents = preg_replace('/\$memcacheLevelCaching\s*=\s*true;/',
                                                     '$memcacheLevelCaching = false;',
                                         $contents);
            }
            file_put_contents($debugConfigFile, $contents);

            $contents = file_get_contents($perInstanceConfigFile);
            $contents = preg_replace('/\$language\s*=\s*\'[a-z]+\';/', // Not Coding Standard
                                     "\$language         = '$language';",
                                     $contents);
            $contents = preg_replace('/\$connectionString\s*=\s*\'[a-z]+:host=[^;]+;dbname=[^;]+;/', // Not Coding Standard
                                   "\$connectionString = '$databaseType:host=$databaseHost;dbname=$databaseName';", // Not Coding Standard
                                     $contents);
            $contents = preg_replace('/\$username\s*=\s*\'[^\']+\';/', // Not Coding Standard
                                     "\$username         = '$username';",
                                     $contents);
            $contents = preg_replace('/\$password\s*=\s*\'[^\']+\';/', // Not Coding Standard
                                     "\$password         = '$password';",
                                     $contents);
            $contents = preg_replace('/\$memcacheServers\s*=\s*array\(.*?array\(\s+\'host\'\s*=>\s*\'[^\']+\',\s*\'port\'\s*=>\s*\d{4,},/s', // Not Coding Standard
                      "\$memcacheServers  = array( // An empty array means memcache is not used.
                            array(
                                'host'   => '$memcacheHost',
                                'port'   => $memcachePort, ",
                                     $contents);
            $contents = preg_replace('/\$instanceConfig\[\'components\'\]\[\'request\'\]\[\'hostInfo\'\]\s*=\s*\'.*?\';/', // Not Coding Standard
                                     "\$instanceConfig['components']['request']['hostInfo']         = '$hostInfo';",
                                     $contents);
            $contents = preg_replace('/\$instanceConfig\[\'components\'\]\[\'request\'\]\[\'scriptUrl\'\]\s*=\s*\'.*?\';/', // Not Coding Standard
                                     "\$instanceConfig['components']['request']['scriptUrl']         = '$scriptUrl';",
                                     $contents);
            $contents = preg_replace('/\s+\/\/ REMOVE THE REMAINDER OF THIS FILE FOR PRODUCTION.*?>/s', // Not Coding Standard
                                     "\n?>",
                                     $contents);
            file_put_contents($perInstanceConfigFile, $contents);
        }

        public static function isDebugConfigWritable($instanceRoot)
        {
            $debugConfigFileDist = "$instanceRoot/protected/config/debugDIST.php";

            if (defined('IS_TEST'))
            {
                $debugConfigFile     = "$instanceRoot/protected/config/debugTest.php";
            }
            else
            {
                $debugConfigFile     = "$instanceRoot/protected/config/debug.php";
            }
            copy($debugConfigFileDist, $debugConfigFile);
            $isWritable = is_writable($debugConfigFile);
            unlink($debugConfigFile);

            return $isWritable;
        }

        public static function isPerInstanceConfigWritable($instanceRoot)
        {
            $perInstanceConfigFileDist = "$instanceRoot/protected/config/perInstanceDIST.php";
            if (defined('IS_TEST'))
            {
                $perInstanceConfigFile     = "$instanceRoot/protected/config/perInstanceTest.php";
            }
            else
            {
                $perInstanceConfigFile     = "$instanceRoot/protected/config/perInstance.php";
            }

            copy($perInstanceConfigFileDist, $perInstanceConfigFile);
            $isWritable = is_writable($perInstanceConfigFile);
            unlink($perInstanceConfigFile);

            return $isWritable;
        }

        /**
         * Writes into perInstance.php that the installation is complete.
         */
        public static function writeInstallComplete($instanceRoot)
        {
            assert('is_dir($instanceRoot)');
            if (defined('IS_TEST'))
            {
                $perInstanceConfigFile     = "$instanceRoot/protected/config/perInstanceTest.php";
            }
            else
            {
                $perInstanceConfigFile     = "$instanceRoot/protected/config/perInstance.php";
            };
            // NOTE: These keep the tidy formatting of the files they are modifying - the whitespace matters!
            $contents = file_get_contents($perInstanceConfigFile);
            $contents = preg_replace('/\$installed\s*=\s*false;/',
                                     '$installed = true;',
                                     $contents);
            file_put_contents($perInstanceConfigFile, $contents);
        }

        public static function isVersion($version)
        {
            return preg_match('/^\d+\.\d+(.\d+)?/', $version) == 1; // Not Coding Standard
        }

        protected static function checkVersion($minimumRequiredVersion, $actualVersion)
        {
            assert('self::isVersion($minimumRequiredVersion)');
            assert('self::isVersion($actualVersion)');
            if (preg_match('/^\d+\.\d+$/', $actualVersion) == 1) // Not Coding Standard
            {
                $actualVersion .= '.0';
            }
            return version_compare($actualVersion, $minimumRequiredVersion) >= 0;
        }

        protected static function getVersionFromPhpInfo($regEx)
        {
            ob_start();
            phpinfo();
            $phpInfo = trim(ob_get_clean());
            $matches = array();
            if (preg_match("/$regEx/si", $phpInfo, $matches) == 1)
            {
                return $matches[1];
            }
            return false;
        }

        /**
         * Given an installSettingsForm, run the install including the schema creation and default data load. This is
         * used by the interactice install and the command line install.
         * @param object $form
         * @param object $messageStreamer
         */
        public static function runInstallation($form, & $messageStreamer)
        {
            assert('$form instanceof InstallSettingsForm');
            assert('$messageStreamer instanceof MessageStreamer');

            if (defined('IS_TEST'))
            {
                $perInstanceFilename     = "perInstanceTest.php";
                $debugFilename     = "debugTest.php";
            }
            else
            {
                $perInstanceFilename     = "perInstance.php";
                $debugFilename     = "debug.php";
            }

            $messageStreamer->add(Yii::t('Default', 'Connecting to Database.'));
            InstallUtil::connectToDatabase( $form->databaseType,
                                            $form->databaseHostname,
                                            $form->databaseName,
                                            $form->databaseUsername,
                                            $form->databasePassword);
            ForgetAllCacheUtil::forgetAllCaches();
            $messageStreamer->add(Yii::t('Default', 'Dropping existing tables.'));
            InstallUtil::dropAllTables();
            $messageStreamer->add(Yii::t('Default', 'Creating super user.'));
            InstallUtil::createSuperUser(   'super',
                                            $form->superUserPassword);
            $messageLogger = new MessageLogger($messageStreamer);
            Yii::app()->custom->runBeforeInstallationAutoBuildDatabase($messageLogger);
            $messageStreamer->add(Yii::t('Default', 'Starting database schema creation.'));
            InstallUtil::autoBuildDatabase($messageLogger);
            $messageStreamer->add(Yii::t('Default', 'Database schema creation complete.'));
            $messageStreamer->add(Yii::t('Default', 'Rebuilding Permissions.'));
            ReadPermissionsOptimizationUtil::rebuild();
            $messageStreamer->add(Yii::t('Default', 'Freezing database.'));
            InstallUtil::freezeDatabase();
            $messageStreamer->add(Yii::t('Default', 'Writing Configuration File.'));

            InstallUtil::writeConfiguration(INSTANCE_ROOT,
                                            $form->databaseType,
                                            $form->databaseHostname,
                                            $form->databaseName,
                                            $form->databaseUsername,
                                            $form->databasePassword,
                                            $form->memcacheHostname,
                                            (int)$form->memcachePortNumber,
                                            true,
                                            Yii::app()->language,
                                            $perInstanceFilename,
                                            $debugFilename,
                                            $form->hostInfo,
                                            $form->scriptUrl);
            $messageStreamer->add(Yii::t('Default', 'Setting up default data.'));
            DefaultDataUtil::load($messageLogger);
            Yii::app()->custom->runAfterInstallationDefaultDataLoad($messageLogger);
            $messageStreamer->add(Yii::t('Default', 'Installation Complete.'));
        }

        /**
         * Looks at the post_max_size, upload max size, and database max_allowed_packets
         * @return integer of max allowed file size for uploads.
         */
        public static function getMaxAllowedFileSize()
        {
            //todo: cache this information.
            $actualPostLimitBytes   = null;
            InstallUtil::checkPhpPostSizeSetting(1, $actualPostLimitBytes);
            $actualUploadLimitBytes = null;
            InstallUtil::checkPhpUploadSizeSetting(1, $actualUploadLimitBytes);
            $actualMaxAllowedBytes = DatabaseCompatibilityUtil::getDatabaseMaxAllowedPacketsSizeRb();
            return min($actualPostLimitBytes, $actualUploadLimitBytes, $actualMaxAllowedBytes);
        }

        /**
         * Method to run installation from command line. Use @InstallCommand.
         * @param array $args
         */
        public static function runFromInstallCommand($args)
        {
            assert('is_array($args)');
            $form            = new InstallSettingsForm();
            $template        = "{message}\n";
            $messageStreamer = new MessageStreamer($template);
            $messageStreamer->setExtraRenderBytes(0);
            $messageStreamer->add(Yii::t('Default', 'Connecting to Database.'));

            $form->databaseHostname  = $args[0];
            $form->databaseName      = $args[1];
            $form->databaseUsername  = $args[2];
            $form->databasePassword  = $args[3];
            $form->superUserPassword = $args[4];

            InstallUtil::runInstallation($form, $messageStreamer);
            if (isset($args[5]))
            {
                $messageStreamer->add(Yii::t('Default', 'Starting to load demo data.'));
                $messageLogger = new MessageLogger($messageStreamer);

                if (isset($args[6]))
                {
                    DemoDataUtil::load($messageLogger, intval($args[6]));
                }
                else
                {
                    DemoDataUtil::load($messageLogger, 3);
                }
                $messageStreamer->add(Yii::t('Default', 'Finished loading demo data.'));
            }
            // Send notification to super admin that need to setup hostInfo and scriptUrl params in perInstance.php
            $message                    = new NotificationMessage();
            $message->textContent       = Yii::t('Default', 'The system has detected that hostInfo or scriptUrl are not setup. Please open perInstance.php config file, and setup those two parametres.' );
            $rules                      = new HostInfoAndScriptUrlNotSetupNotificationRules();
            NotificationsUtil::submit($message, $rules);

            $messageStreamer->add(Yii::t('Default', 'Locking Installation.'));
            InstallUtil::writeInstallComplete(INSTANCE_ROOT);
            $messageStreamer->add(Yii::t('Default', 'Installation Complete.'));
        }

        /**
         * From the command line, run the autobuild method which will effectively update
         * the database schema.
         */
        public static function runAutoBuildFromUpdateSchemaCommand()
        {
            ForgetAllCacheUtil::forgetAllCaches();
            $unfreezeWhenDone     = false;
            if (RedBeanDatabase::isFrozen())
            {
                RedBeanDatabase::unfreeze();
                $unfreezeWhenDone = true;
            }
            $template        = "{message}\n";
            $messageStreamer = new MessageStreamer($template);
            $messageStreamer->setExtraRenderBytes(0);
            $messageStreamer->add(Yii::t('Default', 'Starting schema update process.'));
            $messageLogger = new MessageLogger($messageStreamer);
            self::autoBuildDatabase($messageLogger);
            $messageStreamer->add(Yii::t('Default', 'Schema update complete.'));
            if ($unfreezeWhenDone)
            {
                RedBeanDatabase::freeze();
            }
        }
    }
?>
