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

    class ZurmoLanguageHelperTest extends BaseTest
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
            $languageHelper = new ZurmoLanguageHelper();
            $languageHelper->load();
            $this->assertEquals('en', $languageHelper->getForCurrentUser());
            Yii::app()->user->userModel->language = 'fr';
            $this->assertTrue(Yii::app()->user->userModel->save());
            $languageHelper->setActive('fr');
            $this->assertEquals('fr', Yii::app()->user->getState('language'));
            Yii::app()->user->clearStates();
            $this->assertEquals('fr', $languageHelper->getForCurrentUser());
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
            $languageHelper = new ZurmoLanguageHelper();
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

        public function testGetSupportedLanguagesData()
        {
            $languageHelper = new ZurmoLanguageHelper();
            $data = $languageHelper->getSupportedLanguagesData();
            $compareData = array(
                'en' => 'English',
                'es' => 'Spanish',
                'it' => 'Italian',
                'fr' => 'French',
                'de' => 'German',
            );
            $this->assertEquals($compareData, $data);
        }

        /**
         * @depends testGetSupportedLanguagesData
         */
        public function testGetActiveLanguagesData()
        {
            Yii::app()->language = 'en'; //Set the base language back to english.
            $languageHelper = new ZurmoLanguageHelper();
            $languageHelper->load();
            $data = $languageHelper->getActiveLanguagesData();
            $compareData = array(
                'en' => 'English',
            );
            $this->assertEquals($compareData, $data);

            //Now activate de.
            $languageHelper->setActiveLanguages(array('en', 'de'));
            $data = $languageHelper->getActiveLanguagesData();
            $compareData = array(
                'en' => 'English',
                'de' => 'German',
            );
            $this->assertEquals($compareData, $data);
        }

        /**
         * @depends testGetActiveLanguagesData
         */
        public function testCanInactivateLanguage()
        {
            $this->assertEquals('en', Yii::app()->language);
            Yii::app()->user->userModel =  User::getByUsername('super');
            $languageHelper = new ZurmoLanguageHelper();
            $languageHelper->load();
            //Cannot inactivate the base language.
            $this->assertFalse($languageHelper->canInactivateLanguage('en'));
            //De and Fr are in use by users.
            $this->assertFalse($languageHelper->canInactivateLanguage('de'));
            $this->assertFalse($languageHelper->canInactivateLanguage('fr'));
            $this->assertTrue($languageHelper->canInactivateLanguage('it'));

            $billy =  User::getByUsername('billy');
            $billy->language = 'en';
            $this->assertTrue($billy->save());

            //Now de should be able to be inactivated
            $this->assertTrue($languageHelper->canInactivateLanguage('de'));
        }

        /**
         * @depends testCanInactivateLanguage
         */
        public function testGetAndSetActiveLanguages()
        {
            Yii::app()->language = 'en'; //Set the base language back to english.
            $languageHelper = new ZurmoLanguageHelper();
            $languageHelper->load();
            $data = $languageHelper->getActiveLanguages();
            $compareData = array(
                'en',
                'de',
            );
            $this->assertEquals($compareData, $data);
            $languageHelper->setActiveLanguages(array('en', 'de', 'fr'));
            $data = $languageHelper->getActiveLanguages();
            $compareData = array(
                'en',
                'de',
                'fr',
            );
            $this->assertEquals($compareData, $data);
        }

        /**
         * @depends testGetAndSetActiveLanguages
         */
        public function testLanguagesToLanguageCollectionViewUtil()
        {
            $data = LanguagesToLanguageCollectionViewUtil::getLanguagesData();
            $compareData = array('de' => array(
                                    'label' => 'German',
                                    'active' => true,
                                    'canInactivate' => true,
                                 ),
                                 'en' => array(
                                    'label' => 'English',
                                    'active' => true,
                                    'canInactivate' => false,
                                 ),
                                 'es' => array(
                                    'label' => 'Spanish',
                                    'active' => false,
                                    'canInactivate' => true,
                                 ),
                                 'fr' => array(
                                    'label' => 'French',
                                    'active' => true,
                                    'canInactivate' => false,
                                 ),
                                 'it' => array(
                                    'label' => 'Italian',
                                    'active' => false,
                                    'canInactivate' => true,
                                 ));
            $this->assertEquals($compareData, $data);
        }

        /**
         * This test shows that accents are maybe not in the right encoding in the message file. This is just an example
         * of something that was not working in windows correctly. The result was the label would not display in the
         * input box in the browser in the module general edit in designer.
         */
        public function testAccentsAreEncodingProperly()
        {
            $this->assertEquals('Opportunité', CHtml::encode('Opportunité'));

            $label = OpportunitiesModule::getModuleLabelByTypeAndLanguage('SingularLowerCase', 'fr');
            $this->assertEquals('opportunité', $label);
            $this->assertEquals('opportunité', CHtml::encode($label));
        }
    }
?>