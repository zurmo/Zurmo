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

    class LatestActivitiesUtilTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            ZurmoDatabaseCompatibilityUtil::dropStoredFunctionsAndProcedures();
            SecurityTestHelper::createSuperAdmin();
            Yii::app()->user->userModel = User::getByUsername('super');
            $billy = UserTestHelper::createBasicUser('billy');
        }

        public function testGetMashableModelDataForCurrentUser()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $mashableModelData = LatestActivitiesUtil::getMashableModelDataForCurrentUser();
            $this->assertEquals(6, count($mashableModelData));
            Yii::app()->user->userModel = User::getByUsername('billy');
            $mashableModelData = LatestActivitiesUtil::getMashableModelDataForCurrentUser();
            $this->assertEquals(0, count($mashableModelData));
        }

        public function testGetSearchAttributesDataByModelClassNamesAndRelatedItemIds()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $modelClassNames = array('Meeting', 'Task', 'Note');
            $relationItemIds = array(5, 7, 9);
            $searchAttributesData = LatestActivitiesUtil::
                                        getSearchAttributesDataByModelClassNamesAndRelatedItemIds($modelClassNames,
                                                                                                  $relationItemIds,
                                                                                                  LatestActivitiesConfigurationForm::OWNED_BY_FILTER_ALL);
            $compareSearchAttributesData = array();
            $compareSearchAttributesData['Meeting']['clauses'] = array(
                1 => array('attributeName' => 'activityItems',
                           'relatedAttributeName' => 'id',
                           'operatorType' => 'oneOf',
                           'value' => array(5, 7, 9)),
                2 => array('attributeName' => 'startDateTime',
                           'operatorType' => 'lessThan',
                           'value' => DateTimeUtil::convertTimestampToDbFormatDateTime(time())),
            );
            $compareSearchAttributesData['Meeting']['structure'] = '1 and 2';
            $compareSearchAttributesData['Task']['clauses'] = array(
                1 => array('attributeName' => 'activityItems',
                           'relatedAttributeName' => 'id',
                           'operatorType' => 'oneOf',
                           'value' => array(5, 7, 9)),
                2 => array('attributeName' => 'completed',
                           'operatorType' => 'equals',
                           'value' => (bool)1,
                ),
            );
            $compareSearchAttributesData['Task']['structure'] = '1 and 2';
            $compareSearchAttributesData['Note']['clauses'] = array(
                1 => array('attributeName' => 'activityItems',
                           'relatedAttributeName' => 'id',
                           'operatorType' => 'oneOf',
                           'value' => array(5, 7, 9)),
            );

            $compareSearchAttributesData['Note']['structure'] = '1';
            $this->assertEquals($compareSearchAttributesData['Meeting'], $searchAttributesData[0]['Meeting']);
            $this->assertEquals($compareSearchAttributesData['Task'],    $searchAttributesData[1]['Task']);
            $this->assertEquals($compareSearchAttributesData['Note'],    $searchAttributesData[2]['Note']);

            $searchAttributesData = LatestActivitiesUtil::
                                        getSearchAttributesDataByModelClassNamesAndRelatedItemIds($modelClassNames,
                                                                                                  $relationItemIds,
                                                                                                  LatestActivitiesConfigurationForm::OWNED_BY_FILTER_USER);
            $compareSearchAttributesData['Meeting']['structure'] = '1 and 2 and 3';
            $compareSearchAttributesData['Meeting']['clauses'][3] = array(
                'attributeName' => 'owner',
                'operatorType' => 'equals',
                'value' => Yii::app()->user->userModel->id
            );
            $compareSearchAttributesData['Task']['structure']    = '1 and 2 and 3';
            $compareSearchAttributesData['Task']['clauses'][3] = array(
                'attributeName' => 'owner',
                'operatorType' => 'equals',
                'value' => Yii::app()->user->userModel->id
            );
            $compareSearchAttributesData['Note']['structure']    = '1 and 2';
            $compareSearchAttributesData['Note']['clauses'][2] = array(
                'attributeName' => 'owner',
                'operatorType' => 'equals',
                'value' => Yii::app()->user->userModel->id
            );
            $this->assertEquals($compareSearchAttributesData['Meeting'], $searchAttributesData[0]['Meeting']);
            $this->assertEquals($compareSearchAttributesData['Task'],    $searchAttributesData[1]['Task']);
            $this->assertEquals($compareSearchAttributesData['Note'],    $searchAttributesData[2]['Note']);
        }

        public function testGetSortAttributesByMashableModelClassNames()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $modelClassNames = array('Meeting', 'Task', 'Note');
            $sortAttributeData = LatestActivitiesUtil::getSortAttributesByMashableModelClassNames($modelClassNames);
            $compareSortAttributeData = array('Meeting' => 'latestDateTime',
                                              'Note'    => 'latestDateTime',
                                              'Task'    => 'latestDateTime');
            $this->assertEquals($compareSortAttributeData, $sortAttributeData);
        }

        public function testResolveMashableModelClassNamesByFilteredBy()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $mashableModelClassNames = array('Meeting', 'Task', 'Note');
            $filteredModelClassNames = LatestActivitiesUtil::resolveMashableModelClassNamesByFilteredBy(
                                                                $mashableModelClassNames,
                                                                LatestActivitiesConfigurationForm::FILTERED_BY_ALL);
            $this->assertEquals($mashableModelClassNames, $filteredModelClassNames);
            $filteredModelClassNames = LatestActivitiesUtil::resolveMashableModelClassNamesByFilteredBy(
                                                                $mashableModelClassNames, 'Task');
            $this->assertEquals(array('Task'), $filteredModelClassNames);
        }
    }
?>