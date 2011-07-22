<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2011 Zurmo Inc.
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

    class PortletsSecurityUtilTest extends BaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            ZurmoDatabaseCompatibilityUtil::dropStoredFunctionsAndProcedures();
            SecurityTestHelper::createSuperAdmin();
            Yii::app()->user->userModel = User::getByUsername('super');
            SecurityTestHelper::createUsers();
            SecurityTestHelper::createGroups();
            SecurityTestHelper::createAccounts();
        }

        public function testResolvePortletsForCurrentUser()
        {
            $betty                      = User::getByUsername('betty');
            $this->assertFalse(RightsUtil::canUserAccessModule('AccountsModule', $betty));
            $this->assertFalse(RightsUtil::canUserAccessModule('ContactsModule', $betty));
            $this->assertTrue(RightsUtil::canUserAccessModule('WorldClockModule', $betty));
            Yii::app()->user->userModel = $betty;
            $portlet1 = new Portlet();
            $portlet1->viewType = 'AccountsRelatedList';
            $portlet2 = new Portlet();
            $portlet2->viewType = 'ContactsRelatedList';
            $portlet3 = new Portlet();
            $portlet3->viewType = 'WorldClock';
            $portlets = array();
            $portlets[0][0] = $portlet1;
            $portlets[0][1] = $portlet2;
            $portlets[0][2] = $portlet3;
            $portlets[1][0] = $portlet3;
            $portlets[1][1] = $portlet1;
            $portlets[1][2] = $portlet3;
            $this->assertEquals(2, count($portlets));
            $resolvedPortlets = PortletsSecurityUtil::resolvePortletsForCurrentUser($portlets);
            $comparePortlets = array();
            $comparePortlets[0][0] = $portlet3;
            $comparePortlets[1][0] = $portlet3;
            $comparePortlets[1][1] = $portlet3;
            $this->assertEquals(2, count($resolvedPortlets));
            $this->assertEquals(1, count($resolvedPortlets[0]));
            $this->assertEquals(2, count($resolvedPortlets[1]));
            $this->assertEquals($comparePortlets, $resolvedPortlets);
            Yii::app()->user->userModel = User::getByUsername('super');
            $resolvedPortlets = PortletsSecurityUtil::resolvePortletsForCurrentUser($portlets);
            $this->assertEquals($portlets, $resolvedPortlets);
        }
    }
?>