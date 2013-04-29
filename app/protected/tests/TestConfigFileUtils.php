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

    require_once('testRoots.php');

    class TestConfigFileUtils
    {
        public static function configureConfigFiles()
        {
            $perInstanceTestConfigCreated = false;
            chdir(COMMON_ROOT);

            if (!is_file(INSTANCE_ROOT . '/protected/config/debugTest.php'))
            {
                copy(INSTANCE_ROOT . '/protected/config/debugDIST.php',
                    INSTANCE_ROOT . '/protected/config/debugTest.php');
            }
            if (!is_file(INSTANCE_ROOT . '/protected/config/perInstanceTest.php'))
            {
                $perInstanceTestConfigCreated = copy(INSTANCE_ROOT . '/protected/config/perInstanceDIST.php',
                    INSTANCE_ROOT . '/protected/config/perInstanceTest.php');

                // Mark test application installed, because we need this variable to be set to true, for api tests
                $contents = file_get_contents(INSTANCE_ROOT . '/protected/config/perInstanceTest.php');
                $contents = preg_replace('/\$installed\s*=\s*false;/',
                                         '$installed = true;',
                                         $contents);
                // Update database credentials to use a test db and user.
                $contents = preg_replace('/\$connectionString\s*=\s*\'mysql:host=localhost;port=3306;dbname=zurmo\';/', // Not Coding Standard
                    '$connectionString = \'mysql:host=localhost;port=3306;dbname=zurmo_test\';', // Not Coding Standard
                    $contents);
                $contents = preg_replace('/\$username\s*=\s*\'zurmo\';/',
                    '$username = \'zurmo_test\';',
                    $contents);
                $contents = preg_replace('/\$password\s*=\s*\'zurmo\';/',
                    '$password = \'zurmo_test\';',
                    $contents);
                // Add temp db Settings
                $tempDbSettings = <<<EOD
    \$instanceConfig['components']['tempDb'] = array(
        'class' => 'CDbConnection',
        'connectionString' => 'mysql:host=localhost;port=3306;dbname=zurmo_temp', // Not Coding Standard,
        'username'         => 'zurmo_temp',
        'password'         => 'zurmo_temp',
        'emulatePrepare' => true,
        'charset'        => 'utf8',
    );
EOD;
                $contents = preg_replace('=//@see CustomManagement=', // Not Coding Standard
                    "//@see CustomManagement\n" . $tempDbSettings,
                    $contents,
                    1);

                file_put_contents(INSTANCE_ROOT . '/protected/config/perInstanceTest.php', $contents);
            }

            $contents = file_get_contents(INSTANCE_ROOT . '/protected/config/perInstanceTest.php');

            if (!strpos($contents, '$testApiUrl'))
            {
                $testApiUrlSettings = <<<EOD
    \$testApiUrl = ''; // Set this url only for in perInstanceTest.php file. It should point to app directory, and it is used just for API tests.
                      // For example if zurmo index page is http://my-site.com/app/index.php, the value should be http://my-site.com/app
EOD;
                $contents = preg_replace('/\?\>/',
                        "\n" . $testApiUrlSettings . "\n" . "?>",
                        $contents);
                file_put_contents(INSTANCE_ROOT . '/protected/config/perInstanceTest.php', $contents);
            }

            if (!strpos($contents, '$authenticationTestSettings'))
            {
                $authenticationTestSettings = <<<EOD
    \$authenticationTestSettings = array(
        'ldapSettings' => array(
           'ldapServerType'           => '',
           'ldapHost'                 => '',
           'ldapPort'                 => '',
           'ldapBindRegisteredDomain' => '',
           'ldapBindPassword'         => '',
           'ldapBaseDomain'           => '',
           'ldapEnabled'              => '',
        ),
    );
EOD;
                $contents = preg_replace('/\?\>/',
                        "\n" . $authenticationTestSettings . "\n" . "?>",
                        $contents);

                file_put_contents(INSTANCE_ROOT . '/protected/config/perInstanceTest.php', $contents);
            }

            if (!strpos($contents, '$emailTestAccounts'))
            {
                $emailSettings = <<<EOD
    \$emailTestAccounts = array(
        'smtpSettings' => array(
            'outboundHost'     => '',
            'outboundPort'     => '',
            'outboundUsername' => '',
            'outboundPassword' => '',
            'outboundSecurity' => '',
        ),
        'dropboxImapSettings' => array(
            'imapHost'         => '',
            'imapUsername'     => '',
            'imapPassword'     => '',
            'imapPort'         => '',
            'imapSSL'          => '',
            'imapFolder'       => '',
        ),
        'userSmtpSettings' => array(
            'outboundHost'     => '',
            'outboundPort'     => '',
            'outboundUsername' => '',
            'outboundPassword' => '',
            'outboundSecurity' => '',
        ),
        'userImapSettings' => array(
            'imapHost'         => '',
            'imapUsername'     => '',
            'imapPassword'     => '',
            'imapPort'         => '',
            'imapSSL'          => '',
            'imapFolder'       => '',
        ),
        'testEmailAddress'     => '',
    );
EOD;
                $contents = preg_replace('/\?\>/',
                        "\n" . $emailSettings . "\n" . "?>",
                        $contents);

                file_put_contents(INSTANCE_ROOT . '/protected/config/perInstanceTest.php', $contents);
            }

            if ($perInstanceTestConfigCreated)
            {
                echo "\nPlease update the newly created ".INSTANCE_ROOT .
                    "/protected/config/perInstanceTest.php with latest test and tempDb credentials.\n";
                exit;
            }
        }
    }
?>