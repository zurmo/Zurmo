<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
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
     * You can contact Zurmo, Inc. with a mailing address at 27 North Wacker Drive
     * Suite 370 Chicago, IL 60606. or at email address contact@zurmo.com.
     *
     * The interactive user interfaces in original and modified versions
     * of this program must display Appropriate Legal Notices, as required under
     * Section 5 of the GNU Affero General Public License version 3.
     *
     * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
     * these Appropriate Legal Notices must retain the display of the Zurmo
     * logo and Zurmo copyright notice. If the display of the logo is not reasonably
     * feasible for technical reasons, the Appropriate Legal Notices must display the words
     * "Copyright Zurmo Inc. 2013. All rights reserved".
     ********************************************************************************/

    /**
     * Reports module walkthrough tests for regular users.
     */
    class ReportsRegularUserWalkthroughTest extends ZurmoRegularUserWalkthroughBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $nobody = User::getByUsername('nobody');
            Yii::app()->user->userModel = $nobody;

            $everyoneGroup = Group::getByName(Group::EVERYONE_GROUP_NAME);
            assert($everyoneGroup->save()); // Not Coding Standard

            $group1        = new Group();
            $group1->name  = 'Group1';
            $group1->users->add($nobody);
            assert($group1->save()); // Not Coding Standard
            ReadPermissionsOptimizationUtil::rebuild();
        }

        public function testRegularUserAllControllerActionsNoElevation()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            SavedReportTestHelper::makeSimpleContactRowsAndColumnsReport();
            $savedReports = SavedReport::getAll();
            $nobody = $this->logoutCurrentUserLoginNewUserAndGetByUsername('nobody');
            //should fail
            $this->setGetArray(array('id' => $savedReports[0]->id));
            $this->resetPostArray();
            $this->runControllerShouldResultInAccessFailureAndGetContent('reports/default/details');
            $this->setGetArray(array('id' => $savedReports[0]->id));
            $this->resetPostArray();
            $this->runControllerShouldResultInAccessFailureAndGetContent('reports/default/edit');
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $this->assertTrue($savedReports[0]->delete());
        }

        public static function makeRowsAndColumnsReportPostData()
        {
            $group1 = Group::getByName('Group1');

            return array(
                'validationScenario' => 'ValidateForDisplayAttributes',
                'RowsAndColumnsReportWizardForm' => array(
                    'moduleClassName' => 'AccountsModule',
                    'Filters' => array(
                        '0' => array(
                            'structurePosition' => 1,
                            'attributeIndexOrDerivedType' => 'name',
                            'operator' => 'isNotNull',
                            'value' => '',
                            'availableAtRunTime' => '0')),
                    'filtersStructure' => '1',
                    'displayAttributes' => '',
                    'DisplayAttributes' => array(
                        '0' => array(
                            'attributeIndexOrDerivedType' => 'name',
                            'label' => 'String')),

                    'name' => 'some rows and columns report',
                    'description' => 'some rows and columns report description',
                    'currencyConversionType' => '1',
                    'spotConversionCurrencyCode' => '',
                    'ownerId' => Yii::app()->user->userModel->id,
                    'ownerName' => 'Super User',
                    'explicitReadWriteModelPermissions' => array(
                        'type' => ExplicitReadWriteModelPermissionsUtil::MIXED_TYPE_NONEVERYONE_GROUP,
                        'nonEveryoneGroup' => $group1->id)),
                'FiltersRowCounter' => '1',
                'DisplayAttributesRowCounter' => '1',
                'OrderBysRowCounter' => '0',
            );
        }

        public function testRegularUserControllerActionsWithElevationToEdit()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $nobody = User::getByUsername('nobody');
            $group1 = Group::getByName('Group1');
            $nobody->setRight('AccountsModule', AccountsModule::RIGHT_ACCESS_ACCOUNTS);
            $nobody->setRight('AccountsModule', AccountsModule::RIGHT_CREATE_ACCOUNTS);
            $nobody->setRight('ReportsModule', ReportsModule::RIGHT_ACCESS_REPORTS);
            $nobody->setRight('ReportsModule', ReportsModule::RIGHT_CREATE_REPORTS);
            $nobody->setRight('ReportsModule', ReportsModule::RIGHT_DELETE_REPORTS);
            assert($nobody->save()); // Not Coding Standard

            $savedReports = SavedReport::getAll();
            $this->assertEquals(0, count($savedReports));
            $nobody = $this->logoutCurrentUserLoginNewUserAndGetByUsername('nobody');
            $content = $this->runControllerWithExitExceptionAndGetContent     ('reports/default/create');
            $this->assertFalse(strpos($content, 'Rows and Columns Report') === false);
            $this->assertFalse(strpos($content, 'Summation Report') === false);
            $this->assertFalse(strpos($content, 'Matrix Report') === false);

            $this->setGetArray(array('type' => 'RowsAndColumns'));
            $this->resetPostArray();
            $content = $this->runControllerWithNoExceptionsAndGetContent     ('reports/default/create');
            $this->assertFalse(strpos($content, 'Accounts') === false);

            $this->setGetArray(array('type' => 'RowsAndColumns'));
            $postData = static::makeRowsAndColumnsReportPostData();
            $postData['save'] = 'save';
            $postData['ajax'] = 'edit-form';
            $this->setPostArray($postData);
            $content = $this->runControllerWithExitExceptionAndGetContent('reports/default/save');
            $this->assertEquals('[]', $content);
            $postData = static::makeRowsAndColumnsReportPostData();
            $postData['save'] = 'save';
            $this->setPostArray($postData);
            $this->runControllerWithExitExceptionAndGetContent('reports/default/save');
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $savedReports = SavedReport::getAll();
            $this->assertEquals(1, count($savedReports));
            $nobody = $this->logoutCurrentUserLoginNewUserAndGetByUsername('nobody');
            $savedReports = SavedReport::getAll();
            $this->assertEquals(1, count($savedReports));
            $this->setGetArray(array('id' => $savedReports[0]->id));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('reports/default/details');
            $this->setGetArray(array('id' => $savedReports[0]->id));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('reports/default/edit');
            //Save an existing report
            $this->setGetArray(array('type' => 'RowsAndColumns', 'id' => $savedReports[0]->id));
            $postData = static::makeRowsAndColumnsReportPostData();
            $postData['save'] = 'save';
            $this->setPostArray($postData);
            $this->runControllerWithExitExceptionAndGetContent('reports/default/save');
            $this->assertEquals(1, count($savedReports));
            //Clone existing report
            $this->setGetArray(array('type' => 'RowsAndColumns', 'id' => $savedReports[0]->id, 'isBeingCopied' => '1'));
            $postData = static::makeRowsAndColumnsReportPostData();
            $postData['save'] = 'save';
            $this->setPostArray($postData);
            $this->runControllerWithExitExceptionAndGetContent('reports/default/save');
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $savedReports     = SavedReport::getAll();
            $this->assertEquals(2, count($savedReports));
        }
    }
?>