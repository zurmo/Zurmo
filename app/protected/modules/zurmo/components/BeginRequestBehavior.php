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

    class BeginRequestBehavior extends CBehavior
    {
        public function attach($owner)
        {
            $owner->attachEventHandler('onBeginRequest', array($this, 'handleApplicationCache'));
            if (Yii::app()->apiRequest->isApiRequest())
            {
                $owner->detachEventHandler('onBeginRequest', array(Yii::app()->request, 'validateCsrfToken'));
            }

            $owner->attachEventHandler('onBeginRequest', array($this, 'handleImports'));
            if (Yii::app()->apiRequest->isApiRequest())
            {
                $owner->attachEventHandler('onBeginRequest', array($this, 'handleBeginApiRequest'));
                $owner->attachEventHandler('onBeginRequest', array($this, 'handleLibraryCompatibilityCheck'));
                $owner->attachEventHandler('onBeginRequest', array($this, 'handleStartPerformanceClock'));
            }
            else
            {
                $owner->attachEventHandler('onBeginRequest', array($this, 'handleLibraryCompatibilityCheck'));
                $owner->attachEventHandler('onBeginRequest', array($this, 'handleStartPerformanceClock'));
                $owner->attachEventHandler('onBeginRequest', array($this, 'handleBrowserCheck'));

                if (!Yii::app()->isApplicationInstalled())
                {
                    $owner->attachEventHandler('onBeginRequest', array($this, 'handleInstanceFolderCheck'));
                    $owner->attachEventHandler('onBeginRequest', array($this, 'handleInstallCheck'));
                    $owner->attachEventHandler('onBeginRequest', array($this, 'handleLoadLanguage'));
                    $owner->attachEventHandler('onBeginRequest', array($this, 'handleLoadTimeZone'));
                }
                else
                {
                    $owner->attachEventHandler('onBeginRequest', array($this, 'handleBeginRequest'));
                }
            }

            if (Yii::app()->isApplicationInstalled())
            {
                $owner->attachEventHandler('onBeginRequest', array($this, 'handleSetupDatabaseConnection'));
                $owner->attachEventHandler('onBeginRequest', array($this, 'handleClearCache'));
                $owner->attachEventHandler('onBeginRequest', array($this, 'handleLoadLanguage'));
                $owner->attachEventHandler('onBeginRequest', array($this, 'handleLoadTimeZone'));
                $owner->attachEventHandler('onBeginRequest', array($this, 'handleCheckAndUpdateCurrencyRates'));
                $owner->attachEventHandler('onBeginRequest', array($this, 'handleResolveCustomData'));
            }
        }

        /**
        * Load memcache extension if memcache extension is
        * loaded and if memcache server is avalable
        * @param $event
        */
        public function handleApplicationCache($event)
        {
            $memcacheServiceHelper = new MemcacheServiceHelper();
            if ($memcacheServiceHelper->runCheckAndGetIfSuccessful())
            {
                $cacheComponent = Yii::createComponent('CMemCache',
                    array('servers' => Yii::app()->params['memcacheServers']));
                Yii::app()->setComponent('cache',$cacheComponent);
            }
        }

        /**
        * Import all files that need to be included(for lazy loading)
        * @param $event
        */
        public function handleImports($event)
        {
            try
            {
                $filesToInclude = GeneralCache::getEntry('filesToInclude');
            }
            catch (NotFoundException $e)
            {
                $filesToInclude   = FileUtil::getFilesFromDir(Yii::app()->basePath . '/modules', Yii::app()->basePath . '/modules', 'application.modules');
                $filesToIncludeFromFramework = FileUtil::getFilesFromDir(Yii::app()->basePath . '/extensions/zurmoinc/framework', Yii::app()->basePath . '/extensions/zurmoinc/framework', 'application.extensions.zurmoinc.framework');
                $totalFilesToIncludeFromModules = count($filesToInclude);

                foreach ($filesToIncludeFromFramework as $key => $file)
                {
                    $filesToInclude[$totalFilesToIncludeFromModules + $key] = $file;
                }
                GeneralCache::cacheEntry('filesToInclude', $filesToInclude);
            }
            foreach ($filesToInclude as $file)
            {
                Yii::import($file);
            }
        }

        /**
        * This check is required during installation since if runtime, assets and data folders are missing
        * yii web application can not be started correctly.
        * @param $event
        */
        public function handleInstanceFolderCheck($event)
        {
            $instanceFoldersServiceHelper = new InstanceFoldersServiceHelper();
            if (!$instanceFoldersServiceHelper->runCheckAndGetIfSuccessful())
            {
                echo $instanceFoldersServiceHelper->getMessage();
                Yii::app()->end(0, false);
            }
        }

        public function handleInstallCheck($event)
        {
            $allowedInstallUrls = array (
                Yii::app()->createUrl('zurmo/default/unsupportedBrowser'),
                Yii::app()->createUrl('install/default'),
                Yii::app()->createUrl('install/default/welcome'),
                Yii::app()->createUrl('install/default/checkSystem'),
                Yii::app()->createUrl('install/default/settings'),
                Yii::app()->createUrl('install/default/runInstallation'),
                Yii::app()->createUrl('install/default/installDemoData'),
                Yii::app()->createUrl('min/serve')
            );
            $reqestedUrl = Yii::app()->getRequest()->getUrl();
            $redirect = true;
            foreach ($allowedInstallUrls as $allowedUrl)
            {
                if (strpos($reqestedUrl, $allowedUrl) === 0)
                {
                    $redirect = false;
                    break;
                }
            }
            if ($redirect)
            {
                $url = Yii::app()->createUrl('install/default');
                Yii::app()->request->redirect($url);
            }
        }

        public function handleBrowserCheck($event)
        {
            $browserName = Yii::app()->browser->getName();
            if (isset($_GET['ignoreBrowserCheck']))
            {
                $browserIsSupported = ($_GET['ignoreBrowserCheck'] == 1) ? 1 : 0;
            }
            else
            {
                $browserIsSupported = in_array($browserName, array('msie', 'mozilla', 'chrome', 'safari'));
            }
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
            if (Yii::app()->user->isGuest)
            {
                $allowedGuestUserUrls = array (
                    Yii::app()->createUrl('zurmo/default/unsupportedBrowser'),
                    Yii::app()->createUrl('zurmo/default/login'),
                    Yii::app()->createUrl('min/serve'),
                );
                $reqestedUrl = Yii::app()->getRequest()->getUrl();
                $isUrlAllowedToGuests = false;
                foreach ($allowedGuestUserUrls as $url)
                {
                    if (strpos($reqestedUrl, $url) === 0)
                    {
                        $isUrlAllowedToGuests = true;
                    }
                }
                if (!$isUrlAllowedToGuests)
                {
                    Yii::app()->user->loginRequired();
                }
            }
        }

        public function handleBeginApiRequest($event)
        {
            if (Yii::app()->user->isGuest)
            {
                $allowedGuestUserUrls = array (
                    Yii::app()->createUrl('zurmo/api/login'),
                    Yii::app()->createUrl('zurmo/api/logout'),
                );
                $isUrlAllowedToGuests = false;
                foreach ($allowedGuestUserUrls as $url)
                {
                    if (ZurmoUrlManager::getPositionOfPathInUrl($url) !== false)
                    {
                        $isUrlAllowedToGuests = true;
                        break;
                    }
                }

                if (!$isUrlAllowedToGuests)
                {
                    $message = Yii::t('Default', 'Login required.');
                    $result = new ApiResult(ApiResponse::STATUS_FAILURE, null, $message, null);
                    Yii::app()->apiHelper->sendResponse($result);
                    exit;
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
                ForgetAllCacheUtil::forgetAllCaches();
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
            if (Yii::app()->isApplicationInstalled())
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
            if (!Yii::app()->apiRequest->isApiRequest())
            {
                if (isset($_GET['lang']) && $_GET['lang'] != null)
                {
                    Yii::app()->languageHelper->setActive($_GET['lang']);
                }
            }
            else
            {
                if ($lang = Yii::app()->apiRequest->getLanguage())
                {
                    Yii::app()->languageHelper->setActive($lang);
                }
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

        public function handleResolveCustomData($event)
        {
            if (isset($_GET['resolveCustomData']) && $_GET['resolveCustomData'] == 1)
            {
                Yii::app()->custom->resolveIsCustomDataLoaded();
            }
        }
     }
?>
