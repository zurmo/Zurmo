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

    class ActivityItemRelationToModelElementUtilTest extends BaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            SecurityTestHelper::createUsers();
        }

        public function testResolveModelElementClassNameByActionSecurity()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $bobby   = User::getByUsername('bobby');
            $this->assertEquals(Right::DENY, $bobby->getEffectiveRight('AccountsModule', AccountsModule::RIGHT_ACCESS_ACCOUNTS));
            $this->assertEquals(Right::DENY, $bobby->getEffectiveRight('ContactsModule', ContactsModule::RIGHT_ACCESS_CONTACTS));
            $this->assertEquals(Right::DENY, $bobby->getEffectiveRight('LeadsModule', LeadsModule::RIGHT_ACCESS_LEADS));

            //test Account model where user does not have access
            $elementName = ActivityItemRelationToModelElementUtil::resolveModelElementClassNameByActionSecurity('Account', $bobby);
            $this->assertNull($elementName);
            $bobby->setRight('AccountsModule', AccountsModule::RIGHT_ACCESS_ACCOUNTS);
            $this->assertTrue($bobby->save());

            //test Account model where user has access
            $elementName = ActivityItemRelationToModelElementUtil::resolveModelElementClassNameByActionSecurity('Account', $bobby);
            $this->assertEquals('AccountElement', $elementName);

            //test Contact model where has no access to either the leads or contacts module.
            $elementName = ActivityItemRelationToModelElementUtil::resolveModelElementClassNameByActionSecurity('Contact', $bobby);
            $this->assertNull($elementName);

            //test Contact model where user has access to only the leads module
            $bobby->setRight('LeadsModule', LeadsModule::RIGHT_ACCESS_LEADS);
            $this->assertTrue($bobby->save());
            $elementName = ActivityItemRelationToModelElementUtil::resolveModelElementClassNameByActionSecurity('Contact', $bobby);
            $this->assertEquals('LeadElement', $elementName);

            //test Contact model where user has access to only the contacts module
            $bobby->removeRight('LeadsModule', LeadsModule::RIGHT_ACCESS_LEADS);
            $bobby->setRight('ContactsModule', ContactsModule::RIGHT_ACCESS_CONTACTS);
            $this->assertTrue($bobby->save());
            $elementName = ActivityItemRelationToModelElementUtil::resolveModelElementClassNameByActionSecurity('Contact', $bobby);
            $this->assertEquals('ContactElement', $elementName);

            //test Contact model where user has access to both the contacts and leads module.
            $bobby->setRight('LeadsModule', LeadsModule::RIGHT_ACCESS_LEADS);
            $this->assertTrue($bobby->save());
            $elementName = ActivityItemRelationToModelElementUtil::resolveModelElementClassNameByActionSecurity('Contact', $bobby);
            $this->assertEquals('AllStatesContactElement', $elementName);
        }
    }
?>