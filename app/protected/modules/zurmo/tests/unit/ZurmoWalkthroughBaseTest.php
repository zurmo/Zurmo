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
            //Mimic page request to page request behavior where the php cache would be reset.
            RedBeanModelsCache::forgetAll(true);
            //todo: maybe call GeneralCache forgetAllPHPCache and also expand PermissionsCache
            //to have flags for php cache forgetting only.
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

        protected function createCheckBoxCustomFieldByModule($moduleClassName, $name)
        {
            $extraPostData = array( 'defaultValue' => '1', 'isAudited' => '1');
            $this->createCustomAttributeWalkthroughSequence($moduleClassName, $name, 'CheckBox', $extraPostData);
        }

        protected function createCurrencyValueCustomFieldByModule($moduleClassName, $name)
        {
            $extraPostData = array( 'isAudited' => '1', 'isRequired' => '1');
            $this->createCustomAttributeWalkthroughSequence($moduleClassName, $name, 'CurrencyValue', $extraPostData);
        }

        protected function createDateCustomFieldByModule($moduleClassName, $name)
        {
            $extraPostData = array( 'defaultValueCalculationType' => '', 'isAudited' => '1', 'isRequired' => '1');
            $this->createCustomAttributeWalkthroughSequence($moduleClassName, $name, 'Date', $extraPostData);
        }

        protected function createDateTimeCustomFieldByModule($moduleClassName, $name)
        {
            $extraPostData = array( 'defaultValueCalculationType' => '', 'isAudited' => '1', 'isRequired' => '1');
            $this->createCustomAttributeWalkthroughSequence($moduleClassName, $name, 'DateTime', $extraPostData);
        }

        protected function createDecimalCustomFieldByModule($moduleClassName, $name)
        {
            $extraPostData = array( 'defaultValue' => '123', 'isAudited' => '1', 'isRequired' => '1',
                                    'maxLength' => '18', 'precisionLength' => '2');
            $this->createCustomAttributeWalkthroughSequence($moduleClassName, $name, 'Decimal', $extraPostData);
        }

        protected function createIntegerCustomFieldByModule($moduleClassName, $name)
        {
            $extraPostData = array( 'defaultValue' => '123', 'isAudited' => '1', 'isRequired' => '1',
                                    'maxLength' => '11', 'minValue' => '2', 'maxValue' => '400');
            $this->createCustomAttributeWalkthroughSequence($moduleClassName, $name, 'Integer', $extraPostData);
        }

        protected function createPhoneCustomFieldByModule($moduleClassName, $name)
        {
            $extraPostData = array( 'defaultValue' => '5423', 'isAudited' => '1', 'isRequired' => '1',
                                    'maxLength' => '20');
            $this->createCustomAttributeWalkthroughSequence($moduleClassName, $name, 'Phone', $extraPostData);
        }

        protected function createTextCustomFieldByModule($moduleClassName, $name)
        {
            $extraPostData = array( 'defaultValue' => 'aText', 'isAudited' => '1', 'isRequired' => '1',
                                    'maxLength' => '255');
            $this->createCustomAttributeWalkthroughSequence($moduleClassName, $name, 'Text', $extraPostData);
        }

        protected function createTextAreaCustomFieldByModule($moduleClassName, $name)
        {
            $extraPostData = array( 'defaultValue' => 'aTextDesc', 'isAudited' => '1', 'isRequired' => '1');
            $this->createCustomAttributeWalkthroughSequence($moduleClassName, $name, 'TextArea', $extraPostData);
        }

        protected function createUrlCustomFieldByModule($moduleClassName, $name)
        {
            $extraPostData = array( 'defaultValue' => 'http://www.zurmo.com', 'isAudited' => '1', 'isRequired' => '1',
                                    'maxLength' => '200');
            $this->createCustomAttributeWalkthroughSequence($moduleClassName, $name, 'Url', $extraPostData);
        }

        protected function createDropDownCustomFieldByModule($moduleClassName, $name)
        {
            $extraPostData = array( 'defaultValueOrder'   => '2',
                                    'isAudited'           => '1',
                                    'isRequired'          => '1',
                                    'customFieldDataData' => array(
                                                'a', 'b', 'c'
                                    ),
                                    'customFieldDataLabels' => array(
                                        'fr' => array('aFr', 'bFr', 'cFr'),
                                        'de' => array('aDe', 'bDe', 'cDe'),
                                    )
                                    );
            $this->createCustomAttributeWalkthroughSequence($moduleClassName, $name, 'DropDown', $extraPostData);
        }

        protected function createRadioDropDownCustomFieldByModule($moduleClassName, $name)
        {
            $extraPostData = array( 'defaultValueOrder'   => '2',
                                    'isAudited'           => '1',
                                    'isRequired'          => '1',
                                    'customFieldDataData' => array(
                                                'd', 'e', 'f'
                                    ));
            $this->createCustomAttributeWalkthroughSequence($moduleClassName, $name, 'RadioDropDown', $extraPostData);
        }

        protected function createMultiSelectDropDownCustomFieldByModule($moduleClassName, $name)
        {
            $extraPostData = array( 'defaultValueOrder'   => '1',
                                    'isAudited'           => '1',
                                    'isRequired'          => '1',
                                    'customFieldDataData' => array(
                                                'gg', 'hh', 'rr'
                                    ));
            $this->createCustomAttributeWalkthroughSequence($moduleClassName, $name, 'DropDown', $extraPostData);
        }

        protected function createModuleEditBadValidationPostData()
        {
            return array('singularModuleLabels' =>
                            array('de' => '', 'it' => 'forget everything but this', 'es' => '', 'en' => '', 'fr' => ''),
                         'pluralModuleLabels' =>
                            array('de' => '', 'it' => '', 'es' => '', 'en' => '', 'fr' => '')
                        );
        }

        protected function createModuleEditGoodValidationPostData($singularName)
        {
            assert('strtolower($singularName) == $singularName'); // Not Coding Standard
            $pluralName = $singularName .'s';
            return array('singularModuleLabels' =>
                            array('de' => $singularName, 'it' => $singularName, 'es' => $singularName,
                                    'en' => $singularName, 'fr' => $singularName),
                         'pluralModuleLabels' =>
                            array(  'de' => $pluralName, 'it' => $pluralName, 'es' => $pluralName,
                                    'en' => $pluralName, 'fr' => $pluralName)
                        );
        }

        protected function createAttributeLabelBadValidationPostData()
        {
            return array('de' => '', 'it' => 'forget everything but this', 'es' => '', 'en' => '', 'fr' => ''
                        );
        }

        protected function createAttributeLabelGoodValidationPostData($name)
        {
            assert('strtolower($name) == $name'); // Not Coding Standard
            return array('de' => $name . ' de', 'it' => $name . ' it', 'es' => $name . ' es',
                                    'en' => $name . ' en', 'fr' => $name . ' fr'
                        );
        }

        protected function createCustomAttributeWalkthroughSequence($moduleClassName,
                                                                    $name,
                                                                    $attributeTypeName,
                                                                    $extraPostData,
                                                                    $attributeName = null)
        {
            assert('$name[0] == strtolower($name[0])'); // Not Coding Standard
            assert('is_array($extraPostData)'); // Not Coding Standard
            $formName = $attributeTypeName . 'AttributeForm';
            $this->setGetArray(array(   'moduleClassName'       => $moduleClassName,
                                        'attributeTypeName'     => $attributeTypeName,
                                        'attributeName'         => $attributeName));
            $this->resetPostArray();
            //Now test going to the user interface edit view.
            $content = $this->runControllerWithNoExceptionsAndGetContent('designer/default/attributeEdit');

            //Now validate save with failed validation.
            $this->setPostArray(array(   'ajax'                 => 'edit-form',
                                        $formName => array_merge(array(
                                            'attributeLabels' => $this->createAttributeLabelBadValidationPostData($name),
                                            'attributeName'     => $name,
                                        ), $extraPostData)));
            $content = $this->runControllerWithExitExceptionAndGetContent('designer/default/attributeEdit');
            $this->assertTrue(strlen($content) > 50); //approximate, but should definetely be larger than 50.
            //Now validate save with successful validation.
            $this->setPostArray(array(   'ajax'                 => 'edit-form',
                                        $formName => array_merge(array(
                                            'attributeLabels' => $this->createAttributeLabelGoodValidationPostData($name),
                                            'attributeName'     => $name,
                                        ), $extraPostData)));
            $content = $this->runControllerWithExitExceptionAndGetContent('designer/default/attributeEdit');
            $this->assertEquals('[]', $content);

            //Now save successfully.
            $this->setPostArray(array(   'save'                 => 'Save',
                                        $formName => array_merge(array(
                                            'attributeLabels' => $this->createAttributeLabelGoodValidationPostData($name),
                                            'attributeName'     => $name,
                                        ), $extraPostData)));
            $this->runControllerWithRedirectExceptionAndGetContent('designer/default/attributeEdit');
            //Now confirm everything did in fact save correctly.
            $modelClassName = $moduleClassName::getPrimaryModelName();
            $newModel       = new $modelClassName(false);
            $compareData = array(
                'de' => $name . ' de',
                'it' => $name . ' it',
                'es' => $name . ' es',
                'en' => $name . ' en',
                'fr' => $name . ' fr',
            );
            $this->assertEquals(
                $compareData, $newModel->getAttributeLabelsForAllSupportedLanguagesByAttributeName($name));

            //Now go to the detail viwe of the attribute.
            $this->setGetArray(array(   'moduleClassName'       => $moduleClassName,
                                        'attributeTypeName'     => $attributeTypeName,
                                        'attributeName'         => $name));
            $this->resetPostArray();
            $content = $this->runControllerWithNoExceptionsAndGetContent('designer/default/attributeDetails');

            //Now test going to the user interface edit view for the existing attribute.
            $content = $this->runControllerWithNoExceptionsAndGetContent('designer/default/attributeEdit');
        }
    }
?>
