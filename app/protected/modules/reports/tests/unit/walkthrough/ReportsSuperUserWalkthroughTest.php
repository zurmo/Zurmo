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
     * Reports module walkthrough tests for super users.
     */
    class ReportsSuperUserWalkthroughTest extends ZurmoWalkthroughBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            //Setup test data owned by the super user.
            $account = AccountTestHelper::createAccountByNameForOwner('superAccount', $super);
            AccountTestHelper::createAccountByNameForOwner('superAccount2', $super);
            ContactTestHelper::createContactWithAccountByNameForOwner('superContact', $super, $account);
        }

        public static function makeRowsAndColumnsReportPostData()
        {
            return array(
                'validationScenario' => 'ValidateForDisplayAttributes',
                'RowsAndColumnsReportWizardForm' => array(
                    'moduleClassName' => 'ReportsTestModule',
                    'Filters' => array(
                        '0' => array(
                            'structurePosition' => 1,
                            'attributeIndexOrDerivedType' => 'string',
                            'operator' => 'isNotNull',
                            'value' => '',
                            'availableAtRunTime' => '0')),
                    'filtersStructure' => '1',
                    'displayAttributes' => '',
                    'DisplayAttributes' => array(
                        '0' => array(
                            'attributeIndexOrDerivedType' => 'string',
                            'label' => 'String')),

                    'name' => 'some rows and columns report',
                    'description' => 'some rows and columns report description',
                    'currencyConversionType' => '1',
                    'spotConversionCurrencyCode' => '',
                    'ownerId' => Yii::app()->user->userModel->id,
                    'ownerName' => 'Super User',
                    'explicitReadWriteModelPermissions' => array(
                        'type' => '',
                        'nonEveryoneGroup' => '4')),
                'FiltersRowCounter' => '1',
                'DisplayAttributesRowCounter' => '1',
                'OrderBysRowCounter' => '0',
            );
        }

        public static function makeSummationReportPostData()
        {
            return array(
                'validationScenario' => 'ValidateForDisplayAttributes',
                'SummationReportWizardForm' => array(
                    'moduleClassName' => 'ReportsTestModule',
                    'Filters' => array(
                        '0' => array(
                            'structurePosition' => 1,
                            'attributeIndexOrDerivedType' => 'string',
                            'operator' => 'isNotNull',
                            'value' => '',
                            'availableAtRunTime' => '0')),
                    'filtersStructure' => '1',
                    'displayAttributes' => '',
                    'DisplayAttributes' => array(
                        '0' => array(
                            'attributeIndexOrDerivedType' => 'string',
                            'label' => 'Name')),

                    'name' => 'some summation report',
                    'description' => 'some summation report description',
                    'currencyConversionType' => '1',
                    'spotConversionCurrencyCode' => '',
                    'ownerId' => Yii::app()->user->userModel->id,
                    'ownerName' => 'Super User',
                    'explicitReadWriteModelPermissions' => array(
                        'type' => '',
                        'nonEveryoneGroup' => '4')),
                'FiltersRowCounter' => '1',
                'DisplayAttributesRowCounter' => '1',
                'OrderBysRowCounter' => '0',
            );
        }

        public function setUp()
        {
            parent::setUp();
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
        }

        public function testSuperUserAllDefaultControllerActions()
        {
            $this->runControllerWithNoExceptionsAndGetContent      ('reports/default/list');
            $this->runControllerWithExitExceptionAndGetContent     ('reports/default/create');
            $this->runControllerWithNoExceptionsAndGetContent      ('reports/default/selectType');
        }

        /**
         * @depends testSuperUserAllDefaultControllerActions
         */
        public function testCreateActionForRowsAndColumns()
        {
            $savedReports = SavedReport::getAll();
            $this->assertEquals(0, count($savedReports));
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
            $savedReports = SavedReport::getAll();
            $this->assertEquals(1, count($savedReports));
            $this->setGetArray(array('id' => $savedReports[0]->id));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('reports/default/details');
        }

        /**
         * @depends testCreateActionForRowsAndColumns
         */
        public function testExportActionForAsynchronous()
        {
            if (RedBeanDatabase::isFrozen())
            {
                return;
            }
            $savedReports = SavedReport::getAll();
            $this->assertEquals(1, count($savedReports));
            $this->setGetArray(array('id' => $savedReports[0]->id));
            //Test where there is no data to export
            $this->runControllerWithRedirectExceptionAndGetContent('reports/default/export');
            //todo: can do more export related tests for better coverage
        }

        /**
         * @depends testExportActionForAsynchronous
         */
        public function testActionRelationsAndAttributesTree()
        {
            $this->setGetArray(array('type' => 'RowsAndColumns', 'treeType' => ComponentForReportForm::TYPE_FILTERS));
            $postData = static::makeRowsAndColumnsReportPostData();
            $this->setPostArray($postData);
            $content = $this->runControllerWithNoExceptionsAndGetContent('reports/default/relationsAndAttributesTree');
            $this->assertTrue(strpos($content, '<div class="ReportRelationsAndAttributesTreeView') !== false);
            //With node id
            $this->setGetArray(array('type'     => 'RowsAndColumns', 'treeType' => ComponentForReportForm::TYPE_FILTERS,
                                     'nodeId'   => 'Filters_hasOne'));
            $postData = static::makeRowsAndColumnsReportPostData();
            $this->setPostArray($postData);
            $content = $this->runControllerWithExitExceptionAndGetContent('reports/default/relationsAndAttributesTree');
            $this->assertTrue(strpos($content, '{"id":"Filters_hasOne___createdByUser__User",') !== false); // Not Coding Standard
        }

        /**
         * @depends testActionRelationsAndAttributesTree
         */
        public function testActionAddAttributeFromTree()
        {
            $this->setGetArray(array('type'      => 'RowsAndColumns',
                                     'treeType'  => ComponentForReportForm::TYPE_FILTERS,
                                     'nodeId'    => 'Filters_phone',
                                     'rowNumber' => 4));
            $postData = static::makeRowsAndColumnsReportPostData();
            $this->setPostArray($postData);
            $content = $this->runControllerWithNoExceptionsAndGetContent('reports/default/addAttributeFromTree');
            $this->assertTrue(strpos($content, '<option value="equals">Equals</option>') !== false);
        }

        /**
         * @depends testActionAddAttributeFromTree
         */
        public function testGetAvailableSeriesAndRangesForChart()
        {
            $this->setGetArray(array('type'      => 'Summation'));
            $postData = static::makeSummationReportPostData();
            $this->setPostArray($postData);
            $content = $this->runControllerWithNoExceptionsAndGetContent('reports/default/getAvailableSeriesAndRangesForChart');
            $this->assertTrue(strpos($content, '{"firstSeriesDataAndLabels":{"":"(None)"},"firstRangeDataAndLabels":') !== false); // Not Coding Standard
        }

        /**
         * @depends testGetAvailableSeriesAndRangesForChart
         */
        public function testApplyAndResetRuntimeFilters()
        {
            $savedReports = SavedReport::getAll();
            $this->assertEquals(1, count($savedReports));
            $this->setGetArray(array('id' => $savedReports[0]->id));
            //validate filters, where it doesn't validate, the value is missing
            $this->setPostArray(array('RowsAndColumnsReportWizardForm' => array('Filters' => array(
                                       array('attributeIndexOrDerivedType' => 'string',
                                             'operator'                    => 'equals'))),
                                      'ajax' => 'edit-form'));
            $this->runControllerWithExitExceptionAndGetContent('reports/default/applyRuntimeFilters');
            //apply filters
            $this->setPostArray(array('RowsAndColumnsReportWizardForm' => array('Filters' => array(
                                        array('attributeIndexOrDerivedType' => 'string',
                                              'operator'                    => 'equals',
                                              'value'                       => 'text')))));
            $this->runControllerWithNoExceptionsAndGetContent('reports/default/applyRuntimeFilters', true);
            //Reset filters
            $this->setGetArray(array('id' => $savedReports[0]->id));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('reports/default/resetRuntimeFilters', true);
        }

        /**
         * @depends testApplyAndResetRuntimeFilters
         */
        public function testDrillDownDetails()
        {
            $savedReport = SavedReportTestHelper::makeSummationWithDrillDownReport();
            if (RedBeanDatabase::isFrozen())
            {
                return;
            }
            $this->setGetArray(array('id'                         => $savedReport->id,
                                     'rowId'                      => 2,
                                     'runReport'                  => true,
                                     'groupByRowValueowner__User' => Yii::app()->user->userModel->id));
            $postData = static::makeSummationReportPostData();
            $this->setPostArray($postData);
            $content = $this->runControllerWithNoExceptionsAndGetContent('reports/default/drillDownDetails');
            $this->assertTrue(strpos($content, '<th id="report-results-grid-view2_c2">Currency Value</th>') !== false);
        }

        /**
         * @depends testDrillDownDetails
         */
        public function testAutoComplete()
        {
            if (RedBeanDatabase::isFrozen())
            {
                return;
            }
            $this->setGetArray(array('term'            => 'a test',
                                     'moduleClassName' => 'ReportsModule',
                                     'type'            => Report::TYPE_SUMMATION));
            $content = $this->runControllerWithNoExceptionsAndGetContent('reports/default/autoComplete');
            $this->assertEquals('[]', $content);
        }

        /**
         * @depends testAutoComplete
         */
        public function testDelete()
        {
            $savedReports = SavedReport::getAll();
            $this->assertEquals(2, count($savedReports));
            $this->setGetArray(array('id' => $savedReports[0]->id));
            $this->runControllerWithRedirectExceptionAndGetContent('reports/default/delete');
            $savedReports = SavedReport::getAll();
            $this->assertEquals(1, count($savedReports));
        }

        //todo: test saving a report and changing owner so you don't have permissions anymore. it should do a flashbar and redirect you to the list view.
        //todo: test details view comes up ok when user cant delete or edit report, make sure options button doesn't blow up since it shouldn't display
    }
?>