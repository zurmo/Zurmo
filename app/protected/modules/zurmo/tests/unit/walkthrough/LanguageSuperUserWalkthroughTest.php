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
     * Language user interface actions.
     * Walkthrough for the super user of all possible controller actions.
     * Since this is a super user, he should have access to all controller actions
     * without any exceptions being thrown.
     */
    class LanguageSuperUserWalkthroughTest extends ZurmoWalkthroughBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
        }

        public function testSuperUserAllDefaultControllerActions()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Test all default controller actions that do not require any POST/GET variables to be passed.
            //This does not include portlet controller actions.
            $this->runControllerWithNoExceptionsAndGetContent     ('zurmo/language');
            $this->runControllerWithNoExceptionsAndGetContent     ('zurmo/language/configurationList');
        }

        public function testSuperUserModifyActiveLanguagesInCollection()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Confirm only english is the active language.
            $data = Yii::app()->languageHelper->getActiveLanguages();
            $compareData = array(
                'en',
            );
            $this->assertEquals($compareData, $data);

            //Make French and German language active.
            $this->resetGetArray();
            $this->setPostArray(array('LanguageCollection' => array(
                'fr' => array('active' => '1'), 'de' => array('active' => '1'))));
            $content = $this->runControllerWithNoExceptionsAndGetContent('zurmo/language/configurationList');
            $this->assertTrue(strpos($content, 'Changes to active languages saved successfully.') !== false);

            //Confirm the new languages are active
            $data = Yii::app()->languageHelper->getActiveLanguages();
            $compareData = array(
                'fr',
                'de',
                'en',
            );
            $this->assertEquals($compareData, $data);

            //Now inactivate the German language.
            $this->resetGetArray();
            $this->setPostArray(array('LanguageCollection' => array(
                'fr' => array('active' => '1'), 'de' => array('active' => ''))));
            $content = $this->runControllerWithNoExceptionsAndGetContent('zurmo/language/configurationList');
            $this->assertTrue(strpos($content, 'Changes to active languages changed successfully.') !== false);

            //Confirm the correct languages are active.
            $data = Yii::app()->languageHelper->getActiveLanguages();
            $compareData = array(
                'fr',
                'en',
            );
            $this->assertEquals($compareData, $data);
        }
    }
?>