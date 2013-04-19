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

    class VariableStatesForReportUtilTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            ContactsModule::loadStartingData();
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

        public function testResolveVariableStateAttributeIndexesForComponentWithNoNesting()
        {
            Yii::app()->user->userModel = User::getByUsername('sally');
            $stateAdapter                        = new ContactsStateMetadataAdapter(array('clauses' => array(), 'structure' => ''));
            $this->assertTrue(count($stateAdapter->getStateIds()) > 0);
            $filter                              = new FilterForReportForm('ContactsModule', 'Contact',
                                                   Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType = 'officePhone';
            $indexes = array();
            VariableStatesForReportUtil::resolveAttributeIndexesByComponents($indexes, array($filter));
            $this->assertEquals(array('' => array('state', $stateAdapter->getStateIds())), $indexes);

            //Test with the super user.  There shouldn't be any variable state data
            Yii::app()->user->userModel = User::getByUsername('super');
            $filter                              = new FilterForReportForm('ContactsModule', 'Contact',
                                                   Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType = 'officePhone';
            $indexes = array();
            VariableStatesForReportUtil::resolveAttributeIndexesByComponents($indexes, array($filter));
            $this->assertEquals(array(), $indexes);
        }

        public function testResolveVariableStateAttributeIndexesForComponentWithSingleNesting()
        {
            Yii::app()->user->userModel = User::getByUsername('sally');
            $stateAdapter                          = new ContactsStateMetadataAdapter(array('clauses' => array(), 'structure' => ''));
            $this->assertTrue(count($stateAdapter->getStateIds()) > 0);
            $filter                                = new FilterForReportForm('AccountsModule', 'Account',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType   = 'contacts___officePhone';
            $indexes = array();
            VariableStatesForReportUtil::resolveAttributeIndexesByComponents($indexes, array($filter));
            $this->assertEquals(array('contacts___' => array('state', $stateAdapter->getStateIds())), $indexes);

            //Test with the super user.  There shouldn't be any variable state data
            Yii::app()->user->userModel = User::getByUsername('super');
            $filter                                = new FilterForReportForm('AccountsModule', 'Account',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType   = 'contacts___officePhone';
            $indexes = array();
            VariableStatesForReportUtil::resolveAttributeIndexesByComponents($indexes, array($filter));
            $this->assertEquals(array(), $indexes);
        }

        public function testResolveVariableStateAttributeIndexesForComponentWithDoubleNesting()
        {
            Yii::app()->user->userModel = User::getByUsername('sally');
            $stateAdapter                          = new ContactsStateMetadataAdapter(array('clauses' => array(), 'structure' => ''));
            $this->assertTrue(count($stateAdapter->getStateIds()) > 0);
            $filter                                = new FilterForReportForm('AccountsModule', 'Account',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType   = 'opportunities___contacts___officePhone';
            $indexes = array();
            VariableStatesForReportUtil::resolveAttributeIndexesByComponents($indexes, array($filter));
            $this->assertEquals(array('opportunities___contacts___' => array('state', $stateAdapter->getStateIds())), $indexes);

            //Test with the super user.  There shouldn't be any variable state data
            Yii::app()->user->userModel = User::getByUsername('super');
            $filter                                = new FilterForReportForm('AccountsModule', 'Account',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType   = 'opportunities___contacts___officePhone';
            $indexes = array();
            VariableStatesForReportUtil::resolveAttributeIndexesByComponents($indexes, array($filter));
            $this->assertEquals(array(), $indexes);
        }

        public function testResolveVariableStateAttributeIndexesForComponentWithTwoDoubleNestingCausingTwoVariableStates()
        {
            Yii::app()->user->userModel = User::getByUsername('sally');
            $stateAdapter                          = new ContactsStateMetadataAdapter(array('clauses' => array(), 'structure' => ''));
            $this->assertTrue(count($stateAdapter->getStateIds()) > 0);
            $filter                                = new FilterForReportForm('ContactsModule', 'Contact',
                Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType   = 'opportunities___contacts___officePhone';
            $indexes = array();
            VariableStatesForReportUtil::resolveAttributeIndexesByComponents($indexes, array($filter));
            $this->assertEquals(array('' =>                            array('state', $stateAdapter->getStateIds()),
                                      'opportunities___contacts___' => array('state', $stateAdapter->getStateIds())), $indexes);

            //Test with the super user.  There shouldn't be any variable state data
            Yii::app()->user->userModel = User::getByUsername('super');
            $filter                                = new FilterForReportForm('ContactsModule', 'Contact',
                Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType   = 'opportunities___contacts___officePhone';
            $indexes = array();
            VariableStatesForReportUtil::resolveAttributeIndexesByComponents($indexes, array($filter));
            $this->assertEquals(array(), $indexes);
        }

        public function testDerivedRelationViaCastedUpModelAttributeWhenThroughARelation()
        {
            Yii::app()->user->userModel = User::getByUsername('sally');
            $stateAdapter                          = new ContactsStateMetadataAdapter(array('clauses' => array(), 'structure' => ''));
            $this->assertTrue(count($stateAdapter->getStateIds()) > 0);
            $filter                                = new FilterForReportForm('AccountsModule', 'Account',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType   = 'contacts___meetings___name';
            $indexes = array();
            VariableStatesForReportUtil::resolveAttributeIndexesByComponents($indexes, array($filter));
            $this->assertEquals(array('contacts___' => array('state', $stateAdapter->getStateIds())), $indexes);

            //Test with the super user.  There shouldn't be any variable state data
            Yii::app()->user->userModel = User::getByUsername('super');
            $filter                                = new FilterForReportForm('AccountsModule', 'Account',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType   = 'contacts___meetings___name';
            $indexes = array();
            VariableStatesForReportUtil::resolveAttributeIndexesByComponents($indexes, array($filter));
            $this->assertEquals(array(), $indexes);
        }

        public function testInferredRelationModelAttributeWithCastingHintToNotCastDownSoFarWithItemAttribute()
        {
            Yii::app()->user->userModel = User::getByUsername('sally');
            $stateAdapter                          = new ContactsStateMetadataAdapter(array('clauses' => array(), 'structure' => ''));
            $this->assertTrue(count($stateAdapter->getStateIds()) > 0);
            $filter                                = new FilterForReportForm('MeetingsModule', 'Meeting',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType   = 'Contact__activityItems__Inferred___officePhone';
            $indexes = array();
            VariableStatesForReportUtil::resolveAttributeIndexesByComponents($indexes, array($filter));
            $this->assertEquals(array('Contact__activityItems__Inferred___' =>
                                array('state', $stateAdapter->getStateIds())), $indexes);

            //Test with the super user.  There shouldn't be any variable state data
            Yii::app()->user->userModel = User::getByUsername('super');
            $filter                                = new FilterForReportForm('MeetingsModule', 'Meeting',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType   = 'Contact__activityItems__Inferred___officePhone';
            $indexes = array();
            VariableStatesForReportUtil::resolveAttributeIndexesByComponents($indexes, array($filter));
            $this->assertEquals(array(), $indexes);
        }

        public function testInferredRelationModelAttributeWithYetAnotherRelation()
        {
            Yii::app()->user->userModel = User::getByUsername('sally');
            $stateAdapter                          = new ContactsStateMetadataAdapter(array('clauses' => array(), 'structure' => ''));
            $this->assertTrue(count($stateAdapter->getStateIds()) > 0);
            $filter                                = new FilterForReportForm('MeetingsModule', 'Meeting',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType   = 'Account__activityItems__Inferred___contacts___officePhone';
            $indexes = array();
            VariableStatesForReportUtil::resolveAttributeIndexesByComponents($indexes, array($filter));
            $this->assertEquals(array('Account__activityItems__Inferred___contacts___' =>
                                array('state', $stateAdapter->getStateIds())), $indexes);

            //Test with the super user.  There shouldn't be any variable state data
            Yii::app()->user->userModel = User::getByUsername('super');
            $filter                                = new FilterForReportForm('MeetingsModule', 'Meeting',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType   = 'Account__activityItems__Inferred___contacts___name';
            $indexes = array();
            VariableStatesForReportUtil::resolveAttributeIndexesByComponents($indexes, array($filter));
            $this->assertEquals(array(), $indexes);
        }
    }
?>