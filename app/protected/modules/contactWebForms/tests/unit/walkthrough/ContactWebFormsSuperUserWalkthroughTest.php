<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2012 Zurmo Inc.
     *
     * Zurmo is free software; you can redistribute it and/or modify it under
     * the terms of the GNU Affero General Public License version 3 as published by the
     * Free Software Foundation with the addition of the following permission added
     * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
     * IN WHICH THE COPYRIGHT IS OWNED BY ZURMO, ZURMO DISCLAIMS THE WARRANTY
     * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
     *
     * Zurmo is distributed in the hope that it will be useful, but WITHOUT
     * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
     * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
     * details.
     *
     * You should have received a copy of the GNU Affero General Public License along with
     * this program; if not, see http://www.gnu.org/licenses or write to the Free
     * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
     * 02110-1301 USA.
     *
     * You can contact Zurmo, Inc. with a mailing address at 113 McHenry Road Suite 207,
     * Buffalo Grove, IL 60089, USA. or at email address contact@zurmo.com.
     ********************************************************************************/

    class ContactWebFormsSuperUserWalkthroughTest extends ZurmoWalkthroughBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            ContactWebFormTestHelper::createContactWebFormByName("Web Form 1");
            ContactWebFormTestHelper::createContactWebFormByName("Web Form 2");
            ContactWebFormTestHelper::createContactWebFormByName("Web Form 3");
            ContactWebFormTestHelper::createContactWebFormByName("Web Form 4");
            ContactWebFormTestHelper::createContactWebFormByName("Web Form 5");
            ContactWebFormTestHelper::createContactWebFormByName("Web Form 6");
            ContactWebFormTestHelper::createContactWebFormByName("Web Form 7");
            ContactWebFormTestHelper::createContactWebFormByName("Web Form 8");
            ContactWebFormTestHelper::createContactWebFormByName("Web Form 9");
            ContactWebFormTestHelper::createContactWebFormByName("Web Form 10");
            ContactWebFormTestHelper::createContactWebFormByName("Web Form 11");
            ContactWebFormTestHelper::createContactWebFormByName("Web Form 12");
        }

        public function testSuperUserAllDefaultControllerActions()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Test all default controller actions that do not require any POST/GET variables to be passed.
            //This does not include portlet controller actions.
            $this->runControllerWithNoExceptionsAndGetContent('contactWebForms/default');
            $this->runControllerWithNoExceptionsAndGetContent('contactWebForms/default/index');
            $this->runControllerWithNoExceptionsAndGetContent('contactWebForms/default/create');

            $content = $this->runControllerWithNoExceptionsAndGetContent('contactWebForms/default/list');
            $this->assertFalse(strpos($content, 'anyMixedAttributes') === false);
            //Test the search or paging of the listview.
            Yii::app()->clientScript->reset(); //to make sure old js doesn't make it to the UI
            $this->setGetArray(array('ajax' => 'list-view'));
            $content = $this->runControllerWithNoExceptionsAndGetContent('contactWebForms/default/list');
            $this->assertTrue(strpos($content, 'anyMixedAttributes') === false);
            $this->resetGetArray();

            //Default Controller actions requiring some sort of parameter via POST or GET
            //Load Model Edit Views
            $contactWebForms       = ContactWebForm::getAll();
            $this->assertEquals(12, count($contactWebForms));
            $contactWebFormId      = self::getModelIdByModelNameAndName('ContactWebForm', 'Web Form 1');
            $contactWebFormId2     = self::getModelIdByModelNameAndName('ContactWebForm', 'Web Form 2');
            $contactWebFormId3     = self::getModelIdByModelNameAndName('ContactWebForm', 'Web Form 3');
            $contactWebFormId4     = self::getModelIdByModelNameAndName('ContactWebForm', 'Web Form 4');
            $contactWebFormId5     = self::getModelIdByModelNameAndName('ContactWebForm', 'Web Form 5');
            $contactWebFormId6     = self::getModelIdByModelNameAndName('ContactWebForm', 'Web Form 6');
            $contactWebFormId7     = self::getModelIdByModelNameAndName('ContactWebForm', 'Web Form 7');
            $contactWebFormId8     = self::getModelIdByModelNameAndName('ContactWebForm', 'Web Form 8');
            $contactWebFormId9     = self::getModelIdByModelNameAndName('ContactWebForm', 'Web Form 9');
            $contactWebFormId10    = self::getModelIdByModelNameAndName('ContactWebForm', 'Web Form 10');
            $contactWebFormId10    = self::getModelIdByModelNameAndName('ContactWebForm', 'Web Form 11');
            $contactWebFormId10    = self::getModelIdByModelNameAndName('ContactWebForm', 'Web Form 12');

            $this->setGetArray(array('id' => $contactWebFormId));
            $this->runControllerWithNoExceptionsAndGetContent('contactWebForms/default/edit');
            //Save web form.
            $contactWebForm        = ContactWebForm::getById($contactWebFormId);
            $attributes            = ContactWebFormTestHelper::getContactWebFormAttributes();
            $this->setPostArray(array('ContactWebForm' => array('submitButtonLabel' => 'Test Save'),
                                      'attributeIndexOrDerivedType' => $attributes));
            $this->runControllerWithRedirectExceptionAndGetContent('contactWebForms/default/edit');
            $contactWebForm        = ContactWebForm::getById($contactWebFormId);
            $this->assertEquals('Test Save', $contactWebForm->submitButtonLabel);
            //Test having a failed validation on the contact during save.
            $this->setGetArray (array('id'       => $contactWebFormId));
            $this->setPostArray(array('ContactWebForm' => array('name' => ''), 'attributeIndexOrDerivedType' => $attributes));
            $content = $this->runControllerWithNoExceptionsAndGetContent('contactWebForms/default/edit');
            $this->assertFalse(strpos($content, 'Name cannot be blank') === false);

            //Load Model Detail Views
            $this->setGetArray(array('id' => $contactWebFormId));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('contactWebForms/default/details');
        }

        public function testSuperUserCreateAction()
        {
            $super                                      = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            Yii::app()->user->userModel                 = $super;
            $this->resetGetArray();
            $attributes                                 = ContactWebFormTestHelper::getContactWebFormAttributes();
            ContactsModule::loadStartingData();
            $contactStates                              = ContactState::getByName('New');
            $contactWebForm                             = array();
            $contactWebForm['name']                     = 'External Web Form (Drupal)';
            $contactWebForm['redirectUrl']              = 'http://www.zurmo.com/';
            $contactWebForm['submitButtonLabel']        = 'Save & Next';
            $contactWebForm['defaultState']             = $contactStates[0];
            $contactWebForm['defaultOwner']             = $super;
            $this->setPostArray(array('ContactWebForm'  => $contactWebForm, 'attributeIndexOrDerivedType' => $attributes));
            $redirectUrl                                = $this->runControllerWithRedirectExceptionAndGetUrl('contactWebForms/default/create');
            $contactWebForms                            = ContactWebForm::getByName('External Web Form (Drupal)');
            $this->assertEquals(1, count($contactWebForms));
            $this->assertTrue  ($contactWebForms[0]->id > 0);
            $this->assertEquals('Save & Next', $contactWebForms[0]->submitButtonLabel);
            $this->assertEquals($attributes, unserialize($contactWebForms[0]->serializedData));
            $compareRedirectUrl = Yii::app()->createUrl('contactWebForms/default/details', array('id' => $contactWebForms[0]->id));
            $this->assertEquals($compareRedirectUrl, $redirectUrl);
        }
    }
?>