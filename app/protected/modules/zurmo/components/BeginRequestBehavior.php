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

    class BeginRequestBehavior extends CBehavior
    {
        public function attach($owner)
        {
            $owner->attachEventHandler('onBeginRequest', array($this, 'handleLibraryCompatibilityCheck'));
            $owner->attachEventHandler('onBeginRequest', array($this, 'handleStartPerformanceClock'));
            $owner->attachEventHandler('onBeginRequest', array($this, 'handleBrowserCheck'));
            if(!Yii::app()->isApplicationInstalled())
            {
                $owner->attachEventHandler('onBeginRequest', array($this, 'handleInstallCheck'));
                $owner->attachEventHandler('onBeginRequest', array($this, 'handleLoadLanguage'));
                $owner->attachEventHandler('onBeginRequest', array($this, 'handleLoadTimeZone'));
            }
            else
            {
                $owner->attachEventHandler('onBeginRequest', array($this, 'handleBeginRequest'));
                $owner->attachEventHandler('onBeginRequest', array($this, 'handleSetupDatabaseConnection'));
                $owner->attachEventHandler('onBeginRequest', array($this, 'handleClearCache'));
                $owner->attachEventHandler('onBeginRequest', array($this, 'handleLoadLanguage'));
                $owner->attachEventHandler('onBeginRequest', array($this, 'handleLoadTimeZone'));
                $owner->attachEventHandler('onBeginRequest', array($this, 'handleCheckAndUpdateCurrencyRates'));
            }
        }

        public function handleInstallCheck($event)
        {
            if (!array_key_exists('r', $_GET) ||
            !in_array($_GET['r'], array('zurmo/default/unsupportedBrowser',
                                        'install/default',
                                        'install/default/welcome',
                                        'install/default/checkSystem',
                                        'install/default/settings',
                                        'install/default/runInstallation',
                                        'install/default/installDemoData')))
            {
                $url = Yii::app()->createUrl('install/default');
                Yii::app()->request->redirect($url);
            }
        }

        public function handleBrowserCheck($event)
        {
            $browserName = Yii::app()->browser->getName();
            $browserIsSupported = in_array($browserName, array('msie', 'mozilla', 'chrome', 'safari'));
            if (array_key_exists('r', $_GET)                                   &&
                in_array($_GET['r'], array('zurmo/default/unsupportedBrowser')) &&
                $browserIsSupported)
            {
                $url = Yii::app()->createUrl('/zurmo/default');
                Yii::app()->request->redirect($url);
            }
            if ((!array_key_exists('r', $_GET) ||
                 !in_array($_GET['r'], array('zurmo/default/unsupportedBrowser'))) &&
                !$browserIsSupported)
            {
                $url = Yii::app()->createUrl('zurmo/default/unsupportedBrowser', array('name' => $browserName));
                Yii::app()->request->redirect($url);
            }
        }

        public function handleBeginRequest($event)
        {
            if (!array_key_exists('r', $_GET) ||
                !in_array($_GET['r'], array('zurmo/default/unsupportedBrowser',
                                            'zurmo/default/login')))
            {
                if (Yii::app()->user->isGuest)
                {
                    Yii::app()->user->loginRequired();
                }
            }
        }

        public function handleLibraryCompatibilityCheck($event)
        {
            $basePath       = Yii::app()->getBasePath();
            require_once("$basePath/../../redbean/rb.php");
            $redBeanVersion =  R::getVersion();
            $yiiVersion     =  YiiBase::getVersion();
            if ( $redBeanVersion != Yii::app()->params['redBeanVersion'])
            {
                echo Yii::t('Default', 'Your RedBean version is currentVersion and it should be acceptableVersion.',
                                array(  'currentVersion' => $redBeanVersion,
                                        'acceptableVersion' => Yii::app()->params['redBeanVersion']));
                Yii::app()->end(0, false);
            }
            if ( $yiiVersion != Yii::app()->params['yiiVersion'])
            {
                echo Yii::t('Default', 'Your Yii version is currentVersion and it should be acceptableVersion.',
                                array(  'currentVersion' => $yiiVersion,
                                        'acceptableVersion' => Yii::app()->params['yiiVersion']));
                Yii::app()->end(0, false);
            }
        }

        /**
         * In the case where you have reloaded the database, some cached items might still exist.  This is a way
         * to clear that cache. Helpful during development and testing.
         */
        public function handleClearCache($event)
        {
            if (isset($_GET['clearCache']) && $_GET['clearCache'] == 1)
            {
                RedBeanModelsCache::forgetAll();
                PermissionsCache::forgetAll();
                ZurmoGeneralCache::forgetAll();
            }
        }

        public function handleStartPerformanceClock($event)
        {
            Yii::app()->performance->startClock();
        }

        public function handleSetupDatabaseConnection($event)
        {
            RedBeanDatabase::setup(Yii::app()->db->connectionString,
                                   Yii::app()->db->username,
                                   Yii::app()->db->password);

            if(Yii::app()->isApplicationInstalled())
            {
                if (!FORCE_NO_FREEZE)
                {
                    RedBeanDatabase::freeze();
                }
            }
            else
            {
                throw new NotSupportedException();
            }
        }

        public function handleLoadLanguage($event)
        {
            if (isset($_POST['lang']) && $_POST['lang'] != null)
            {
                Yii::app()->languageHelper->setActive($_POST['lang']);
            }
            Yii::app()->languageHelper->load();
        }

        public function handleLoadTimeZone($event)
        {
            Yii::app()->timeZoneHelper->load();
        }

        public function handleCheckAndUpdateCurrencyRates($event)
        {
            Yii::app()->currencyHelper->checkAndUpdateCurrencyRates();
        }
    }
?>
