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

    class ZurmoLanguageHelperTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            ZurmoDatabaseCompatibilityUtil::dropStoredFunctionsAndProcedures();
            SecurityTestHelper::createSuperAdmin();
            UserTestHelper::createBasicUser('billy');
            UserTestHelper::createBasicUser('sally');
        }

        public function testGetAndSetForCurrentUser()
        {
            Yii::app()->user->userModel =  User::getByUsername('super');
            Yii::app()->languageHelper->load();
            $this->assertEquals('en', Yii::app()->languageHelper->getForCurrentUser());
            Yii::app()->user->userModel->language = 'fr';
            $this->assertTrue(Yii::app()->user->userModel->save());
            Yii::app()->languageHelper->setActive('fr');
            $this->assertEquals('fr', Yii::app()->user->getState('language'));
            Yii::app()->user->clearStates();
            $this->assertEquals('fr', Yii::app()->languageHelper->getForCurrentUser());
            $this->assertEquals(null, Yii::app()->user->getState('language'));
            $this->assertEquals('en', Yii::app()->language);
        }

        /**
         * @depends testGetAndSetForCurrentUser
         */
        public function testGetAndSetByUser()
        {
            $metaData = ZurmoModule::getMetaData();
            Yii::app()->user->userModel =  User::getByUsername('super');
            $this->assertEquals(null, Yii::app()->user->getState('language'));
            $this->assertEquals('en', Yii::app()->language);
            $billy =  User::getByUsername('billy');
            $this->assertEquals(null, $billy->language);
            $billy->language = 'de';
            $this->assertTrue($billy->save());
            $this->assertEquals(null, Yii::app()->user->getState('language'));
            Yii::app()->user->clearStates();
            $this->assertEquals('de', $billy->language);
            $this->assertEquals(null, Yii::app()->user->getState('language'));
            $this->assertEquals('en', Yii::app()->language);
        }

        public function testActivateLanguage()
        {
            $status = Yii::app()->languageHelper->activateLanguage('de');
            $this->assertEquals(true, $status);

            // Trying with an unsupported language
            $exceptionRaised = false;
            try
            {
                Yii::app()->languageHelper->activateLanguage('aaaa');
            }
            catch (NotFoundException $e)
            {
                $exceptionRaised = true;
            }

            if (!$exceptionRaised)
            {
                $this->fail('NotFoundException has not been raised.');
            }
        }

        /**
         * @depends testActivateLanguage
         */
        public function testUpdateLanguage()
        {
            $status = Yii::app()->languageHelper->updateLanguage('de');
            $this->assertEquals(true, $status);

            // Trying with an unsupported language
            $exceptionRaised = false;
            try
            {
                Yii::app()->languageHelper->updateLanguage('aaaa');
            }
            catch (NotFoundException $e)
            {
                $exceptionRaised = true;
            }

            if (!$exceptionRaised)
            {
                $this->fail('NotFoundException has not been raised.');
            }
        }

        /**
         * @depends testUpdateLanguage
         */
        public function testDeactivateLanguage()
        {
            $status = Yii::app()->languageHelper->deactivateLanguage('de');
            $this->assertEquals(true, $status);

            // Trying with an unsupported language
            $exceptionRaised = false;
            try
            {
                Yii::app()->languageHelper->updateLanguage('aaaa');
            }
            catch (NotFoundException $e)
            {
                $exceptionRaised = true;
            }

            if (!$exceptionRaised)
            {
                $this->fail('NotFoundException has not been raised.');
            }
        }

        public function testGetSupportedLanguagesData()
        {
            $data = Yii::app()->languageHelper->getSupportedLanguagesData();
            $compareData = array(
                'code' => 'de',
                'name' => 'German',
                'nativeName' => 'Deutsch',
                'label' => 'German (Deutsch)'
            );
            $this->assertEquals($compareData, $data['de']);
        }

        public function testGetActiveLanguagesData()
        {
            Yii::app()->language = 'en'; //Set the base language back to english.
            Yii::app()->languageHelper->load();
            $data = Yii::app()->languageHelper->getActiveLanguagesDataForTesting();
            $compareData = array(
                'en' => array(
                    'canDeactivate' => false,
                    'label' => 'English (English)',
                    'nativeName' => 'English',
                    'name' => 'English'
                ),
            );
            $this->assertEquals($compareData, $data);

            //Now activate de.
            Yii::app()->languageHelper->activateLanguage('de');
            $data = Yii::app()->languageHelper->getActiveLanguagesDataForTesting();
            $compareData = array(
                'en' => array(
                    'canDeactivate' => false,
                    'label' => 'English (English)',
                    'nativeName' => 'English',
                    'name' => 'English'
                ),
                'de' => array(
                    'canDeactivate' => false,
                    'label' => 'German (Deutsch)',
                    'nativeName' => 'Deutsch',
                    'name' => 'German'
                ),
            );
            $this->assertEquals($compareData, $data);

            //Now activate es.
            Yii::app()->languageHelper->activateLanguage('it');
            $data = Yii::app()->languageHelper->getActiveLanguagesDataForTesting();
            $compareData = array(
                'en' => array(
                    'canDeactivate' => false,
                    'label' => 'English (English)',
                    'nativeName' => 'English',
                    'name' => 'English'
                ),
                'de' => array(
                    'canDeactivate' => false,
                    'label' => 'German (Deutsch)',
                    'nativeName' => 'Deutsch',
                    'name' => 'German'
                ),
                'it' => array(
                    'canDeactivate' => true,
                    'label' => 'Italian (Italiano)',
                    'nativeName' => 'Italiano',
                    'name' => 'Italian'
                ),
            );
            $this->assertEquals($compareData, $data);
        }

        /**
         * @depends testGetActiveLanguagesData
         */
        public function testCanDeactivateLanguage()
        {
            $this->assertEquals('en', Yii::app()->language);
            Yii::app()->user->userModel =  User::getByUsername('super');
            Yii::app()->languageHelper->load();
            //Cannot inactivate the base language.
            $this->assertFalse(Yii::app()->languageHelper->canDeactivateLanguage('en'));
            //De and Fr are in use by users.
            $this->assertFalse(Yii::app()->languageHelper->canDeactivateLanguage('de'));
            $this->assertFalse(Yii::app()->languageHelper->canDeactivateLanguage('fr'));
            $this->assertTrue(Yii::app()->languageHelper->canDeactivateLanguage('it'));

            $billy =  User::getByUsername('billy');
            $billy->language = 'en';
            $this->assertTrue($billy->save());

            //Now de should be able to be inactivated
            $this->assertTrue(Yii::app()->languageHelper->canDeactivateLanguage('de'));
        }

        /**
         * This test shows that accents are maybe not in the right encoding in the message file. This is just an example
         * of something that was not working in windows correctly. The result was the label would not display in the
         * input box in the browser in the module general in designer.
         */
        public function testAccentsAreEncodingProperly()
        {
            $this->assertEquals('Opportunité', ZurmoHtml::encode('Opportunité'));

            //First we need to activate French, to import the message
            Yii::app()->languageHelper->load();
            Yii::app()->languageHelper->activateLanguagesForTesting();
            Yii::app()->languageHelper->importMessagesForTesting();

            $label = OpportunitiesModule::getModuleLabelByTypeAndLanguage('SingularLowerCase', 'fr');
            $this->assertEquals('opportunité', $label);
            $this->assertEquals('opportunité', ZurmoHtml::encode($label));
        }
    }
?>