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

    class ReadPermissionsForReportUtilTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $sally = UserTestHelper::createBasicUser('sally');
            $sally->setRight('AccountsModule',      AccountsModule::RIGHT_ACCESS_ACCOUNTS);
            $sally->setRight('ContactsModule',      ContactsModule::RIGHT_ACCESS_CONTACTS);
            $sally->setRight('MeetingsModule',      MeetingsModule::RIGHT_ACCESS_MEETINGS);
            $sally->setRight('OpportunitiesModule', OpportunitiesModule::RIGHT_ACCESS_OPPORTUNITIES);
            $sally->setRight('ReportsTestModule',   ReportsTestModule::RIGHT_ACCESS_REPORTS_TESTS);
            if (!$sally->save())
            {
                throw new FailedToSaveModelException();
            }
        }

        public function testThatArrayMergeProperlyResolvesIndexes()
        {
            $attributeIndexes          = array('anExistingOne' => array('a', 'b'));
            $attributeIndexesToResolve = array();
            ReadPermissionsForReportUtil::resolveIndexesTogether($attributeIndexes, $attributeIndexesToResolve);
            $this->assertEquals(serialize(array('anExistingOne' => array('a', 'b'))), serialize($attributeIndexes));
            $attributeIndexes          = array('anExistingOne' => array('a', 'b'));
            $attributeIndexesToResolve = array('aNewOne' => array('a', 'b'));
            ReadPermissionsForReportUtil::resolveIndexesTogether($attributeIndexes, $attributeIndexesToResolve);
            $this->assertEquals(serialize(array('anExistingOne' => array('a', 'b'),
                                                'aNewOne' => array('a', 'b'))), serialize($attributeIndexes));
            $attributeIndexes          = array('anExistingOne' => array('a', 'b'));
            $attributeIndexesToResolve = array('aNewOne' => array('a', 'b'), 'anExistingOne' => array('a', 'b'));
            ReadPermissionsForReportUtil::resolveIndexesTogether($attributeIndexes, $attributeIndexesToResolve);
            $this->assertEquals(serialize(array('anExistingOne' => array('a', 'b'),
                                                'aNewOne' => array('a', 'b'))), serialize($attributeIndexes));
            $attributeIndexes          = array('anExistingOne' => array('a', 'b'));
            $attributeIndexesToResolve = array('aNewOne' => array('a', 'b'),
                                               'anExistingOne' => array('a', 'b'),
                                               'anotherNewOne' => array('a', 'b'));
            ReadPermissionsForReportUtil::resolveIndexesTogether($attributeIndexes, $attributeIndexesToResolve);
            $this->assertEquals(serialize(array('anExistingOne' => array('a', 'b'),
                                                'aNewOne' => array('a', 'b'),
                                                'anotherNewOne' => array('a', 'b'))), serialize($attributeIndexes));
        }

        public function testResolveReadPermissionAttributeIndexesForComponentWithNoNesting()
        {
            Yii::app()->user->userModel = User::getByUsername('sally');
            $filter                              = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                   Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType = 'name';
            $indexes = array();
            ReadPermissionsForReportUtil::resolveAttributeIndexesByComponents($indexes, array($filter));
            $this->assertEquals(array('' => array('owner__User', 'ReadOptimization')), $indexes);

            //Test with the super user.  There shouldn't be any read permission data
            Yii::app()->user->userModel = User::getByUsername('super');
            $filter                              = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                   Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType = 'name';
            $indexes = array();
            ReadPermissionsForReportUtil::resolveAttributeIndexesByComponents($indexes, array($filter));
            $this->assertEquals(array(), $indexes);
        }

        public function testResolveReadPermissionAttributeIndexesForComponentWithSingleNesting()
        {
            Yii::app()->user->userModel = User::getByUsername('sally');
            $filter                                = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType   = 'hasOne___name';
            $indexes = array();
            ReadPermissionsForReportUtil::resolveAttributeIndexesByComponents($indexes, array($filter));
            $this->assertEquals(array(''          => array('owner__User', 'ReadOptimization'),
                                      'hasOne___' => array('owner__User', 'ReadOptimization')), $indexes);

            //Test with the super user.  There shouldn't be any read permission data
            Yii::app()->user->userModel = User::getByUsername('super');
            $filter                                = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType   = 'hasOne___name';
            $indexes = array();
            ReadPermissionsForReportUtil::resolveAttributeIndexesByComponents($indexes, array($filter));
            $this->assertEquals(array(), $indexes);
        }

        public function testResolveReadPermissionAttributeIndexesForComponentWithDoubleNesting()
        {
            Yii::app()->user->userModel = User::getByUsername('sally');
            $filter                                = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType   = 'hasOne___hasMany3___name';
            $indexes = array();
            ReadPermissionsForReportUtil::resolveAttributeIndexesByComponents($indexes, array($filter));
            $compareIndexes = array(''                     => array('owner__User', 'ReadOptimization'),
                                    'hasOne___'            => array('owner__User', 'ReadOptimization'),
                                    'hasOne___hasMany3___' => array('owner__User', 'ReadOptimization'));
            $this->assertEquals($compareIndexes, $indexes);

            //Test with the super user.  There shouldn't be any read permission data
            Yii::app()->user->userModel = User::getByUsername('super');
            $filter                                = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType   = 'hasOne___hasMany3___name';
            $indexes = array();
            ReadPermissionsForReportUtil::resolveAttributeIndexesByComponents($indexes, array($filter));
            $this->assertEquals(array(), $indexes);
        }

        public function testDerivedRelationViaCastedUpModelAttributeThatCastsDownAndSkipsAModelOne()
        {
            Yii::app()->user->userModel = User::getByUsername('sally');
            $filter                                = new FilterForReportForm('AccountsModule', 'Account',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType   = 'meetings___name';
            $indexes = array();
            ReadPermissionsForReportUtil::resolveAttributeIndexesByComponents($indexes, array($filter));
            $compareIndexes = array(''            => array('owner__User', 'ReadOptimization'),
                                    'meetings___' => array('owner__User', 'ReadOptimization'));
            $this->assertEquals($compareIndexes, $indexes);

            //Test with the super user.  There shouldn't be any read permission data
            Yii::app()->user->userModel = User::getByUsername('super');
            $filter                                = new FilterForReportForm('AccountsModule', 'Account',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType   = 'meetings___name';
            $indexes = array();
            ReadPermissionsForReportUtil::resolveAttributeIndexesByComponents($indexes, array($filter));
            $this->assertEquals(array(), $indexes);
        }

        public function testDerivedRelationViaCastedUpModelAttributeWhenThroughARelation()
        {
            Yii::app()->user->userModel = User::getByUsername('sally');
            $filter                                = new FilterForReportForm('AccountsModule', 'Account',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType   = 'opportunities___meetings___name';
            $indexes = array();
            ReadPermissionsForReportUtil::resolveAttributeIndexesByComponents($indexes, array($filter));
            $compareIndexes = array(''                            => array('owner__User', 'ReadOptimization'),
                                    'opportunities___'            => array('owner__User', 'ReadOptimization'),
                                    'opportunities___meetings___' => array('owner__User', 'ReadOptimization'));
            $this->assertEquals($compareIndexes, $indexes);

            //Test with the super user.  There shouldn't be any read permission data
            Yii::app()->user->userModel = User::getByUsername('super');
            $filter                                = new FilterForReportForm('AccountsModule', 'Account',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType   = 'opportunities___meetings___name';
            $indexes = array();
            ReadPermissionsForReportUtil::resolveAttributeIndexesByComponents($indexes, array($filter));
            $this->assertEquals(array(), $indexes);
        }

        public function testInferredRelationModelAttributeWithCastingHintToNotCastDownSoFarWithItemAttribute()
        {
            Yii::app()->user->userModel = User::getByUsername('sally');
            $filter                                = new FilterForReportForm('MeetingsModule', 'Meeting',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType   = 'Account__activityItems__Inferred___name';
            $indexes = array();
            ReadPermissionsForReportUtil::resolveAttributeIndexesByComponents($indexes, array($filter));
            $compareIndexes = array(''                                    => array('owner__User', 'ReadOptimization'),
                                    'Account__activityItems__Inferred___' => array('owner__User', 'ReadOptimization'));
            $this->assertEquals($compareIndexes, $indexes);

            //Test with the super user.  There shouldn't be any read permission data
            Yii::app()->user->userModel = User::getByUsername('super');
            $filter                                = new FilterForReportForm('MeetingsModule', 'Meeting',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType   = 'Account__activityItems__Inferred___name';
            $indexes = array();
            ReadPermissionsForReportUtil::resolveAttributeIndexesByComponents($indexes, array($filter));
            $this->assertEquals(array(), $indexes);
        }

        public function testInferredRelationModelAttributeWithYetAnotherRelation()
        {
            Yii::app()->user->userModel = User::getByUsername('sally');
            $filter                                = new FilterForReportForm('MeetingsModule', 'Meeting',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType   = 'Account__activityItems__Inferred___opportunities___name';
            $indexes = array();
            ReadPermissionsForReportUtil::resolveAttributeIndexesByComponents($indexes, array($filter));
            $compareIndexes = array(''                                                    => array('owner__User', 'ReadOptimization'),
                                    'Account__activityItems__Inferred___'                 => array('owner__User', 'ReadOptimization'),
                                    'Account__activityItems__Inferred___opportunities___' => array('owner__User', 'ReadOptimization'));
            $this->assertEquals($compareIndexes, $indexes);

            //Test with the super user.  There shouldn't be any read permission data
            Yii::app()->user->userModel = User::getByUsername('super');
            $filter                                = new FilterForReportForm('MeetingsModule', 'Meeting',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType   = 'Account__activityItems__Inferred___opportunities___name';
            $indexes = array();
            ReadPermissionsForReportUtil::resolveAttributeIndexesByComponents($indexes, array($filter));
            $this->assertEquals(array(), $indexes);
        }
    }
?>