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

    class RequiredAttributesValidViewUtilTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
        }

        public function setUp()
        {
            parent::setUp();
            Yii::app()->user->userModel = User::getByUsername('super');
        }

        public function testSetAsMissingRequiredAttributes()
        {
            $value = ZurmoConfigurationUtil::getByModuleName('ContactsModule', 'SampleDummyView_layoutMissingRequiredAttributes');
            $this->assertNull($value);
            RequiredAttributesValidViewUtil::setAsMissingRequiredAttributes('ContactsModule', 'SampleDummyView');
            $value = ZurmoConfigurationUtil::getByModuleName('ContactsModule', 'SampleDummyView_layoutMissingRequiredAttributes');
            $this->assertEquals($value, 1);
        }

        /**
         * @expectedException NotSupportedException
         */
        public function testSetAsMissingRequiredAttributesWithInvalidView()
        {
            ZurmoConfigurationUtil::setByModuleName('ContactsModule', 'SampleDummyView_layoutMissingRequiredAttributes', -99);
            RequiredAttributesValidViewUtil::setAsMissingRequiredAttributes('ContactsModule', 'SampleDummyView');
        }

        /**
         * @expectedException NotSupportedException
         */
        public function testRemoveAttributeAsMissingRequiredAttributeWithInvalidView()
        {
            RequiredAttributesValidViewUtil::setAsMissingRequiredAttributes('AccountsModule', 'InvalidView');
            ZurmoConfigurationUtil::setByModuleName('AccountsModule', 'InvalidView_layoutMissingRequiredAttributes', 99);
            RequiredAttributesValidViewUtil::removeAttributeAsMissingRequiredAttribute('AccountsModule', 'InvalidView');
        }

        public function testRemoveAttributeAsMissingRequiredAttribute()
        {
            $value = ZurmoConfigurationUtil::getByModuleName('AccountsModule', 'SampleDummyView_layoutMissingRequiredAttributes');
            $this->assertNull($value);

            RequiredAttributesValidViewUtil::setAsMissingRequiredAttributes('AccountsModule', 'SampleDummyView');
            $value = ZurmoConfigurationUtil::getByModuleName('AccountsModule', 'SampleDummyView_layoutMissingRequiredAttributes');
            $this->assertEquals($value, 1);

            RequiredAttributesValidViewUtil::removeAttributeAsMissingRequiredAttribute('AccountsModule', 'SampleDummyView');
            $value = ZurmoConfigurationUtil::getByModuleName('AccountsModule', 'SampleDummyView_layoutMissingRequiredAttributes');
            $this->assertNull($value);
        }

        public function testSetAsContainingRequiredAttributes()
        {
            $value = ZurmoConfigurationUtil::getByModuleName('ContactsModule', 'DummyView1_layoutMissingRequiredAttributes');
            $this->assertNull($value);
            RequiredAttributesValidViewUtil::setAsContainingRequiredAttributes('ContactsModule', 'DummyView1');
            $value = ZurmoConfigurationUtil::getByModuleName('ContactsModule', 'DummyView1_layoutMissingRequiredAttributes');
            $this->assertNull($value);
        }

        public function testIsViewMissingRequiredAttributes()
        {
            $value = RequiredAttributesValidViewUtil::isViewMissingRequiredAttributes('ContactsModule', 'MissingViewShouldAppearInAnyModule');
            $this->assertFalse($value);
            RequiredAttributesValidViewUtil::setAsMissingRequiredAttributes('ContactsModule', 'ContactsPageView');
            $value = RequiredAttributesValidViewUtil::isViewMissingRequiredAttributes('ContactsModule', 'ContactsPageView');
            $this->assertTrue($value);
        }

        public function testResolveValidView()
        {
            $content = RequiredAttributesValidViewUtil::resolveValidView('ContactsModule', 'MissingViewShouldAppearInAnyModule');
            $this->assertNull($content);

            RequiredAttributesValidViewUtil::setAsMissingRequiredAttributes('ContactsModule', 'ContactsListView');
            $value = ZurmoConfigurationUtil::getByModuleName('ContactsModule', 'ContactsListView_layoutMissingRequiredAttributes');
            $this->assertEquals($value, 1);

            $content = RequiredAttributesValidViewUtil::resolveValidView('ContactsModule', 'ContactsListView');
            $this->assertEquals($content, 'There are required fields missing from the following' .
                    ' layout: Contacts List View.  Please contact your administrator.');
        }

        public function testResolveToSetAsMissingRequiredAttributesByModelClassName()
        {
            RequiredAttributesValidViewUtil::resolveToSetAsMissingRequiredAttributesByModelClassName('Contact', 'lastName');
            $booleanTest = RequiredAttributesValidViewUtil::isViewMissingRequiredAttributes('ContactsModule', 'ContactEditAndDetailsView');
            $this->assertFalse($booleanTest);

            RequiredAttributesValidViewUtil::resolveToSetAsMissingRequiredAttributesByModelClassName('Contact', 'dummyEmailShouldNotAppearInAnyView');
            $booleanTest = RequiredAttributesValidViewUtil::isViewMissingRequiredAttributes('ContactsModule', 'ContactEditAndDetailsView');
            $this->assertTrue($booleanTest);

            RequiredAttributesValidViewUtil::resolveToRemoveAttributeAsMissingRequiredAttribute('Contact', 'dummyEmailShouldNotAppearInAnyView');
            $booleanTest = RequiredAttributesValidViewUtil::isViewMissingRequiredAttributes('ContactsModule', 'ContactEditAndDetailsView');
            $this->assertFalse($booleanTest);
        }
    }
?>