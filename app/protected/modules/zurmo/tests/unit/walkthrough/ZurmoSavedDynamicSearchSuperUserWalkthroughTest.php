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

    /**
     * Currency model.
     * Walkthrough for the super user of all possible controller actions related to saved search.
     */
    class ZurmoSavedDynamicSearchSuperUserWalkthroughTest extends ZurmoWalkthroughBaseTest
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
            $this->assertEquals(0, count(SavedSearch::getAll()));

            //Test a saved search that validates and passes
            //Test form that validates
            $this->setGetArray(array(   'viewClassName'               => 'AccountsSearchView',
                                        'modelClassName'              => 'Account',
                                        'formModelClassName'          => 'AccountsSearchForm'));
            $this->setPostArray(array('ajax'                          => 'search-form',
                                      'AccountsSearchForm'            => array('savedSearchName' => 'a new name'),
                                      'save'                          => 'saveSearch'));
            $this->runControllerWithExitExceptionAndGetContent('zurmo/default/validateDynamicSearch');

            $savedSearches = SavedSearch::getAll();
            $this->assertEquals(1, count($savedSearches));
            $this->assertEquals('a new name', $savedSearches[0]->name);

            //Test loading saved search
            Yii::app()->user->setState('AccountsSearchView', null);
            $this->setGetArray(array('savedSearchId' => $savedSearches[0]->id));
            $content = $this->runControllerWithNoExceptionsAndGetContent('accounts/default/list');
            $id = $savedSearches[0]->id;
            $this->assertContains("<option value=\"{$id}\" selected=\"selected\">a new name</option>", $content);

            //Test deleting saved search
            $this->setGetArray(array('id' => $savedSearches[0]->id));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('zurmo/default/deleteSavedSearch', true);
            $this->assertEquals(0, count(SavedSearch::getAll()));
        }
    }
?>