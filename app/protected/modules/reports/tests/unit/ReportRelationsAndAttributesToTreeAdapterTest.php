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

    class ReportRelationsAndAttributesToTreeAdapterTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
        }

        public function setup()
        {
            parent::setUp();
            Yii::app()->user->userModel = User::getByUsername('super');
        }

        public function testGetDataWhereNodeIdIsSource()
        {
            //getData($nodeId)
        }

        public function testGetDataForEachTreeType()
        {
            //getData($nodeId)
            //test completely getAttributesData using different treeTypes
            //make sure calculated derived attribute doesnt show up as a filter since you can't filter on it
        }

        public function testGetDataForVariousReportTypes()
        {
            //getData($nodeId)
            //Main objective is to test completely makeModelRelationsAndAttributesToReportAdapter($moduleClassName, $modelClassName)
        }

        public function testGetDataWhereNodeIdIsNotSourceButAChildRelation()
        {
            //getData($nodeId)
        }

        public function testGetDataWhereNodeIdIsTwoRelationsDeep()
        {
            //getData($nodeId)
        }

        public function testRemoveTreeTypeFromNodeId()
        {
            //removeTreeTypeFromNodeId($nodeId, $treeType)
        }

        public function testResolveInputPrefixData()
        {
        //resolveInputPrefixData($nodeIdWithoutTreeType, $formModelClassName, $treeType, $rowNumber)

            //test both when there is one part and more than one part
        }

        public function testResolveAttributeByNodeId()
        {
            //resolveAttributeByNodeId($nodeIdWithoutTreeType)

            //test both when there is one part and more than one part
        }

        public function testNextedGroupBys()
        {
            $report                              = new Report();
            $report->setType(Report::TYPE_SUMMATION);
            $report->setModuleClassName('ReportsTestModule');
            $adapter = new ReportRelationsAndAttributesToTreeAdapter($report, ComponentForReportForm::TYPE_GROUP_BYS);
            $data    = $adapter->getData(ComponentForReportForm::TYPE_GROUP_BYS . '_hasOne');
            $this->assertEquals('GroupBys_hasOne___createdByUser__User', $data[0]['id']);
            $this->assertEquals('GroupBys_hasOne___id', $data[6]['id']);
            $this->assertEquals('GroupBys_hasOne___name', $data[13]['id']);
        }

        /**
         * @depends testNextedGroupBys
         */
        public function testWhereNestedGroupBysAndGettingDataForOrderBy()
        {
            $report                              = new Report();
            $report->setType(Report::TYPE_SUMMATION);
            $groupBy = new GroupByForReportForm('ReportsTestModule', 'ReportModelTestItem', $report->getType());
            $groupBy->attributeIndexOrDerivedType = 'hasOne___name';
            $groupBy->axis                        = 'x';
            $report->addGroupBy($groupBy);
            $report->setModuleClassName('ReportsTestModule');
            $adapter = new ReportRelationsAndAttributesToTreeAdapter($report, ComponentForReportForm::TYPE_ORDER_BYS);
            $data    = $adapter->getData(ComponentForReportForm::TYPE_ORDER_BYS . '_hasOne');
            $this->assertEquals('OrderBys_hasOne___name', $data[0]['id']);
            $this->assertEquals('OrderBys_hasOne___createdByUser', $data[1]['id']);
            $this->assertEquals('OrderBys_hasOne___hasMany3', $data[2]['id']);
            $this->assertEquals('OrderBys_hasOne___modifiedByUser', $data[3]['id']);
            $this->assertEquals('OrderBys_hasOne___owner', $data[4]['id']);
        }
    }
?>