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

    class PortletsSecurityUtilTest extends ZurmoBaseTest
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
            $this->assertFalse(RightsUtil::canUserAccessModule('TasksModule', $betty));
            Yii::app()->user->userModel = $betty;
            $portlet1 = new Portlet();
            $portlet1->viewType = 'AccountsRelatedList';
            $portlet2 = new Portlet();
            $portlet2->viewType = 'ContactsRelatedList';
            $portlet3 = new Portlet();
            $portlet3->viewType = 'TasksMyList';
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
            $this->assertEquals(0, count($resolvedPortlets));
            Yii::app()->user->userModel = User::getByUsername('super');
            $resolvedPortlets = PortletsSecurityUtil::resolvePortletsForCurrentUser($portlets);
            $this->assertEquals($portlets, $resolvedPortlets);
        }
    }
?>