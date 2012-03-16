<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2012 Zurmo Inc.
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
     * Special class to isolate the autoBuildDatabase method and test that the rows are the correct
     * count before and after running this method.  AutoBuildDatabase is used both on installation
     * but also during an upgrade or manually  to update the database schema based on any detected
     * changes.
     */
    class AutoBuildDatabaseTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
        }

        public function testAutoBuildDatabase()
        {
            $unfreezeWhenDone     = false;
            if (RedBeanDatabase::isFrozen())
            {
                RedBeanDatabase::unfreeze();
                $unfreezeWhenDone = true;
            }
            $super                      = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $messageLogger              = new MessageLogger();
            $beforeRowCount             = DatabaseCompatibilityUtil::getTableRowsCountTotal();
            InstallUtil::autoBuildDatabase($messageLogger);
            $afterRowCount              = DatabaseCompatibilityUtil::getTableRowsCountTotal();
            //There are only 6 extra rows that are not being removed during the autobuild process.
            //These need to eventually be fixed so they are properly removed, except currency which is ok.
            //activity_items (3), contact_Opportunity, (1) _group__user (1), currency (1)
            $this->assertEquals($beforeRowCount, ($afterRowCount - 6));
            if ($unfreezeWhenDone)
            {
                RedBeanDatabase::freeze();
            }
        }
    }
?>
