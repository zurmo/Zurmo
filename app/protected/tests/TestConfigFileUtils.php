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

    require_once('testRoots.php');

    class TestConfigFileUtils
    {
        public static function configureConfigFiles()
        {
            chdir(COMMON_ROOT);

            if (!is_file(INSTANCE_ROOT . '/protected/config/debugTest.php'))
            {
                copy(INSTANCE_ROOT . '/protected/config/debugDIST.php', INSTANCE_ROOT . '/protected/config/debugTest.php');
            }
            if (!is_file(INSTANCE_ROOT . '/protected/config/perInstanceTest.php'))
            {
                copy(INSTANCE_ROOT . '/protected/config/perInstanceDIST.php', INSTANCE_ROOT . '/protected/config/perInstanceTest.php');

                //Mark test application installed, because we need this variable to be set to true, for api tests
                $contents = file_get_contents(INSTANCE_ROOT . '/protected/config/perInstanceTest.php');
                $contents = preg_replace('/\$installed\s*=\s*false;/',
                                         '$installed = true;',
                                         $contents);

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
        }
    }
?>