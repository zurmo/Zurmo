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
     * Parent class for walkthrough documentation tests
     */
    class ZurmoWalkthroughBaseTest extends ZurmoBaseTest
    {
        private $testModelIds = array();

        public function setUp()
        {
            parent::setUp();
            Yii::app()->user->clearStates(); //reset session.
            Yii::app()->clientScript->reset();
            $_GET     = null;
            $_REQUEST = null;
            $_POST    = null;
            $_COOKIE  = null;
        }

        /**
         * Use this method to clear the current user and login a new user for a walkthrough.
         */
        protected function logoutCurrentUserLoginNewUserAndGetByUsername($username)
        {
            //clear states does not log the user out.
            //todo: actually log user out and then back in.
            Yii::app()->user->clearStates(); //reset session.
            Yii::app()->language = Yii::app()->getConfigLanguageValue();
            Yii::app()->timeZoneHelper->setTimeZone(Yii::app()->getConfigTimeZoneValue());
            $user = User::getByUsername($username);
             //todo: actually run login?
            Yii::app()->user->userModel = $user;
            //todo: can we somehow use behavior to do these type of loads like languageHelper->load()?
            //this way we can utilize the same process as the normal production run of the application.
            Yii::app()->languageHelper->load();
            Yii::app()->timeZoneHelper->load();
            return $user;
        }

        /**
         * Helper method to run a controller action that is
         * expected not to produce an exception.
         */
        protected function runControllerWithNoExceptionsAndGetContent($route, $empty = false)
        {
            $_SERVER['REQUEST_URI'] = '/index.php';
            $this->startOutputBuffer();
            try
            {
                Yii::app()->runController($route);
                $content = $this->endAndGetOutputBuffer();
                $this->doApplicationScriptPathsAllExist();
                if ($empty)
                {
                    $this->assertEmpty($content);
                }
                else
                {
                    $this->assertNotEmpty($content);
                }
                return $content;
            }
            catch (ExitException $e)
            {
                $this->endPrintOutputBufferAndFail();
            }
        }

        /**
         * Helper method to run a controller action that is
         * expected produce an exit exception
         */
        protected function runControllerWithExitExceptionAndGetContent($route)
        {
            $_SERVER['REQUEST_URI'] = '/index.php';
            $this->startOutputBuffer();
            try
            {
                Yii::app()->runController($route);
                $this->endPrintOutputBufferAndFail();
            }
            catch (ExitException $e)
            {
                $content = $this->endAndGetOutputBuffer();
                $this->doApplicationScriptPathsAllExist();
                return $content;
            }
        }

        /**
         * Helper method to run a controller action that is
         * expected produce a redirect exception.
         */
        protected function runControllerWithRedirectExceptionAndGetUrl($route)
        {
            $_SERVER['REQUEST_URI'] = '/index.php';
            $this->startOutputBuffer();
            try
            {
                Yii::app()->runController($route);
                $this->endPrintOutputBufferAndFail();
            }
            catch (RedirectException $e)
            {
                $content = $this->endAndGetOutputBuffer();
                $this->doApplicationScriptPathsAllExist();
                $this->assertEmpty($content);
                return $e->getUrl();
            }
        }

        /**
         * Helper method to run a controller action that is
         * expected produce a redirect exception.
         */
        protected function runControllerWithRedirectExceptionAndGetContent($route, $compareUrl = null,
                           $compareUrlContains = false)
        {
            $_SERVER['REQUEST_URI'] = '/index.php';
            $this->startOutputBuffer();
            try
            {
                Yii::app()->runController($route);
                $this->endPrintOutputBufferAndFail();
            }
            catch (RedirectException $e)
            {
                $content = $this->endAndGetOutputBuffer();
                $this->doApplicationScriptPathsAllExist();
                if ($compareUrl != null)
                {
                    if ($compareUrlContains)
                    {
                        $pos = strpos($e->getUrl(), $compareUrl);
                        if ($pos === false)
                        {
                            $this->fail($e->getUrl());
                        }
                    }
                    else
                    {
                        $this->assertEquals($compareUrl, $e->getUrl());
                    }
                }
                if (!empty($content))
                {
                    echo $content;
                }
                $this->assertEmpty($content);
                return $content;
            }
        }

        /**
         * Helper method to run a controller action that is
         * expected produce a redirect exception.
         */
        protected function runControllerWithNotSupportedExceptionAndGetContent($route)
        {
            $_SERVER['REQUEST_URI'] = '/index.php';
            $this->startOutputBuffer();
            try
            {
                Yii::app()->runController($route);
                $this->endPrintOutputBufferAndFail();
            }
            catch (NotSupportedException $e)
            {
                $content = $this->endAndGetOutputBuffer();
                $this->doApplicationScriptPathsAllExist();
                return $content;
            }
        }

        protected function runControllerShouldResultInAccessFailureAndGetContent($route)
        {
            $content = $this->runControllerWithExitExceptionAndGetContent($route);
            $this->assertFalse(strpos($content, 'You have tried to access a page you do not have access to.') === false);
            return $content;
        }

        protected function resetGetArray()
        {
            $_GET = array();
        }

        protected function setGetArray($data)
        {
            $this->resetGetArray();
            foreach ($data as $key => $value)
            {
                $_GET[$key] = $value;
            }
        }

        protected function resetPostArray()
        {
            $_POST = array();
        }

        protected function setPostArray($data)
        {
            $this->resetPostArray();
            foreach ($data as $key => $value)
            {
                $_POST[$key] = $value;
            }
        }

        protected static function getModelIdByModelNameAndName($modelName, $name)
        {
            $models = $modelName::getByName($name);
            return $models[0]->id;
        }

        protected function doApplicationScriptPathsAllExist()
        {
            foreach (Yii::app()->getClientScript()->getScriptFiles() as $scriptsPathsByPosition)
            {
                foreach ($scriptsPathsByPosition as $position => $scriptPath)
                {
                    $this->assertTrue(file_exists($scriptPath), $scriptPath . 'does not exist and it should.');
                }
            }
        }
    }
?>
