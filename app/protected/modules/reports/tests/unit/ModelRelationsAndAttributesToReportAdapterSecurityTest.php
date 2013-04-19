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

    class ModelRelationsAndAttributesToReportAdapterSecurityTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $sally = UserTestHelper::createBasicUser('sally');
            $sally->setRight('AccountsModule',      AccountsModule::RIGHT_ACCESS_ACCOUNTS);
            $sally->setRight('OpportunitiesModule', OpportunitiesModule::RIGHT_ACCESS_OPPORTUNITIES);
            $sally->setRight('MeetingsModule',      MeetingsModule::RIGHT_ACCESS_MEETINGS);
            if (!$sally->save())
            {
                throw new FailedToSaveModelException();
            }
        }

        public function testGetAllReportableRelationsAsASuperUser()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $model              = new Account();
            $rules              = new AccountsReportRules();
            $report             = new Report();
            $report->setType(Report::TYPE_ROWS_AND_COLUMNS);
            $report->setModuleClassName('AccountsModule');
            $adapter            = new ModelRelationsAndAttributesToReportAdapter($model, $rules, $report->getType());
            $relations = $adapter->getSelectableRelationsData();
            $relations = $adapter->getSelectableRelationsDataResolvedForUserAccess(Yii::app()->user->userModel, $relations);
            $this->assertEquals(12, count($relations));
            $compareData        = array('label' => 'Billing Address');
            $this->assertEquals($compareData, $relations['billingAddress']);
            $compareData        = array('label' => 'Contacts');
            $this->assertEquals($compareData, $relations['contacts']);
            $compareData        = array('label' => 'Created By User');
            $this->assertEquals($compareData, $relations['createdByUser']);
            $compareData        = array('label' => 'Meetings');
            $this->assertEquals($compareData, $relations['meetings']);
            $compareData        = array('label' => 'Modified By User');
            $this->assertEquals($compareData, $relations['modifiedByUser']);
            $compareData        = array('label' => 'Notes');
            $this->assertEquals($compareData, $relations['notes']);
            $compareData        = array('label' => 'Opportunities');
            $this->assertEquals($compareData, $relations['opportunities']);
            $compareData        = array('label' => 'Owner');
            $this->assertEquals($compareData, $relations['owner']);
            $compareData        = array('label' => 'Primary Email');
            $this->assertEquals($compareData, $relations['primaryEmail']);
            $compareData        = array('label' => 'Shipping Address');
            $this->assertEquals($compareData, $relations['shippingAddress']);
            $compareData        = array('label' => 'Secondary Email');
            $this->assertEquals($compareData, $relations['secondaryEmail']);
            $compareData        = array('label' => 'Tasks');
            $this->assertEquals($compareData, $relations['tasks']);
        }

        /**
         * Sally cannot access notes, tasks, or contacts. User is always accessible regardless of right to access
         */
        public function testGetAllReportableRelationsAsANonElevatedUser()
        {
            Yii::app()->user->userModel = User::getByUsername('sally');
            $model              = new Account();
            $rules              = new AccountsReportRules();
            $report             = new Report();
            $report->setType(Report::TYPE_ROWS_AND_COLUMNS);
            $report->setModuleClassName('AccountsModule');
            $adapter            = new ModelRelationsAndAttributesToReportAdapter($model, $rules, $report->getType());
            $relations = $adapter->getSelectableRelationsData();
            $relations = $adapter->getSelectableRelationsDataResolvedForUserAccess(Yii::app()->user->userModel, $relations);
            $this->assertEquals(9, count($relations));
            $compareData        = array('label' => 'Billing Address');
            $this->assertEquals($compareData, $relations['billingAddress']);
            $compareData        = array('label' => 'Created By User');
            $this->assertEquals($compareData, $relations['createdByUser']);
            $compareData        = array('label' => 'Meetings');
            $this->assertEquals($compareData, $relations['meetings']);
            $compareData        = array('label' => 'Modified By User');
            $this->assertEquals($compareData, $relations['modifiedByUser']);
            $compareData        = array('label' => 'Opportunities');
            $this->assertEquals($compareData, $relations['opportunities']);
            $compareData        = array('label' => 'Owner');
            $this->assertEquals($compareData, $relations['owner']);
            $compareData        = array('label' => 'Primary Email');
            $this->assertEquals($compareData, $relations['primaryEmail']);
            $compareData        = array('label' => 'Shipping Address');
            $this->assertEquals($compareData, $relations['shippingAddress']);
            $compareData        = array('label' => 'Secondary Email');
            $this->assertEquals($compareData, $relations['secondaryEmail']);
        }
    }
?>
