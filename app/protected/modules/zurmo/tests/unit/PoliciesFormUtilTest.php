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

    class PoliciesFormUtilTest extends BaseTest
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

       public function testPoliciesUtilGetAllPoliciesData()
        {
            $group = new Group();
            $group->name = 'viewGroup';
            $saved = $group->save();
            $this->assertTrue($saved);
            $this->assertEquals(null, $group->getEffectivePolicy('UsersModule', UsersModule::POLICY_ENFORCE_STRONG_PASSWORDS));
            $group->setPolicy('UsersModule', UsersModule::POLICY_ENFORCE_STRONG_PASSWORDS, Policy::YES);
            $this->assertTrue($group->save());
            $this->assertEquals(Policy::YES, $group->getEffectivePolicy('UsersModule', UsersModule::POLICY_ENFORCE_STRONG_PASSWORDS));
            $this->assertEquals(Policy::YES, $group->getExplicitActualPolicy('UsersModule', UsersModule::POLICY_ENFORCE_STRONG_PASSWORDS));
            $data = PoliciesUtil::getAllModulePoliciesDataByPermitable($group);
            $compareData = array(
                'UsersModule' => array(
                    'POLICY_ENFORCE_STRONG_PASSWORDS'   => array(
                        'displayName' => UsersModule::POLICY_ENFORCE_STRONG_PASSWORDS,
                        'explicit'    => Policy::YES,
                        'inherited'   => null,
                        'effective'   => Policy::YES,
                    ),
                    'POLICY_MINIMUM_PASSWORD_LENGTH'   => array(
                        'displayName' => UsersModule::POLICY_MINIMUM_PASSWORD_LENGTH,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => 5,
                    ),
                    'POLICY_MINIMUM_USERNAME_LENGTH'   => array(
                        'displayName' => UsersModule::POLICY_MINIMUM_USERNAME_LENGTH,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => 3,
                    ),
                    'POLICY_PASSWORD_EXPIRES'   => array(
                        'displayName' => UsersModule::POLICY_PASSWORD_EXPIRES,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => null,
                    ),
                    'POLICY_PASSWORD_EXPIRY_DAYS'   => array(
                        'displayName' => UsersModule::POLICY_PASSWORD_EXPIRY_DAYS,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => null,
                    ),
                ),
            );
            $this->assertEquals($compareData, $data);
            $group->forget();
        }

        /**
         * @depends testPoliciesUtilGetAllPoliciesData
         */
        public function testPoliciesFormUtil()
        {
            $group = Group::getByName('viewGroup');
            $group1 = Group::getByName(Group::EVERYONE_GROUP_NAME);
            $group1->setPolicy('UsersModule', UsersModule::POLICY_PASSWORD_EXPIRES, Policy::YES);
            $group1->setPolicy('UsersModule', UsersModule::POLICY_PASSWORD_EXPIRY_DAYS, 15);
            $group1->save();
            $group1->forget();
            $group1 = Group::getByName(Group::EVERYONE_GROUP_NAME);
            $this->assertEquals(Policy::YES, $group1->getEffectivePolicy      ('UsersModule', UsersModule::POLICY_PASSWORD_EXPIRES));
            $this->assertEquals(Policy::YES, $group1->getExplicitActualPolicy ('UsersModule', UsersModule::POLICY_PASSWORD_EXPIRES));
            $this->assertEquals(null,        $group1->getInheritedActualPolicy('UsersModule', UsersModule::POLICY_PASSWORD_EXPIRES));
            $this->assertEquals(15,          $group1->getEffectivePolicy      ('UsersModule', UsersModule::POLICY_PASSWORD_EXPIRY_DAYS));
            $this->assertEquals(15,          $group1->getExplicitActualPolicy ('UsersModule', UsersModule::POLICY_PASSWORD_EXPIRY_DAYS));
            $this->assertEquals(null,        $group1->getInheritedActualPolicy('UsersModule', UsersModule::POLICY_PASSWORD_EXPIRY_DAYS));
            $data = PoliciesUtil::getAllModulePoliciesDataByPermitable($group);
            $form = PoliciesFormUtil::makeFormFromPoliciesData($data);
            $compareData = array(
                'UsersModule' => array(
                    'POLICY_ENFORCE_STRONG_PASSWORDS'   => array(
                        'displayName' => UsersModule::POLICY_ENFORCE_STRONG_PASSWORDS,
                        'explicit'    => Policy::YES,
                        'inherited'   => null,
                        'effective'   => Policy::YES,
                    ),
                    'POLICY_MINIMUM_PASSWORD_LENGTH'   => array(
                        'displayName' => UsersModule::POLICY_MINIMUM_PASSWORD_LENGTH,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => 5,
                    ),
                    'POLICY_MINIMUM_USERNAME_LENGTH'   => array(
                        'displayName' => UsersModule::POLICY_MINIMUM_USERNAME_LENGTH,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => 3,
                    ),
                    'POLICY_PASSWORD_EXPIRES'   => array(
                        'displayName' => UsersModule::POLICY_PASSWORD_EXPIRES,
                        'explicit'    => null,
                        'inherited'   => Policy::YES,
                        'effective'   => Policy::YES,
                    ),
                    'POLICY_PASSWORD_EXPIRY_DAYS'   => array(
                        'displayName' => UsersModule::POLICY_PASSWORD_EXPIRY_DAYS,
                        'explicit'    => null,
                        'inherited'   => 15,
                        'effective'   => 15,
                    ),
                ),
            );
            $this->assertEquals($compareData, $form->data);
            $group->forget();
            $group1->forget();
        }

        /**
         * @depends testPoliciesFormUtil
         */
        public function testPoliciesFormUtilSetPoliciesFromPost()
        {
            $group = Group::getByName('viewGroup');
            $data = PoliciesUtil::getAllModulePoliciesDataByPermitable($group);
            $form = PoliciesFormUtil::makeFormFromPoliciesData($data);
            $compareData = array(
                'UsersModule' => array(
                    'POLICY_ENFORCE_STRONG_PASSWORDS'   => array(
                        'displayName' => UsersModule::POLICY_ENFORCE_STRONG_PASSWORDS,
                        'explicit'    => Policy::YES,
                        'inherited'   => null,
                        'effective'   => Policy::YES,
                    ),
                    'POLICY_MINIMUM_PASSWORD_LENGTH'   => array(
                        'displayName' => UsersModule::POLICY_MINIMUM_PASSWORD_LENGTH,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => 5,
                    ),
                    'POLICY_MINIMUM_USERNAME_LENGTH'   => array(
                        'displayName' => UsersModule::POLICY_MINIMUM_USERNAME_LENGTH,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => 3,
                    ),
                    'POLICY_PASSWORD_EXPIRES'   => array(
                        'displayName' => UsersModule::POLICY_PASSWORD_EXPIRES,
                        'explicit'    => null,
                        'inherited'   => Policy::YES,
                        'effective'   => Policy::YES,
                    ),
                    'POLICY_PASSWORD_EXPIRY_DAYS'   => array(
                        'displayName' => UsersModule::POLICY_PASSWORD_EXPIRY_DAYS,
                        'explicit'    => null,
                        'inherited'   => 15,
                        'effective'   => 15,
                    ),
                ),
            );
            $this->assertEquals($compareData, $form->data);
            $fakePost = array(
                'UsersModule__POLICY_MINIMUM_PASSWORD_LENGTH'   => strval(5),
                'UsersModule__POLICY_ENFORCE_STRONG_PASSWORDS'  => '',
                'UsersModule__POLICY_PASSWORD_EXPIRY_DAYS'      => strval(10),

            );
            $validatedPost = PoliciesFormUtil::typeCastPostData($fakePost);
            $saved = PoliciesFormUtil::setPoliciesFromCastedPost($validatedPost, $group);
            $this->assertTrue($saved);
            $group->forget();
            $group = Group::getByName('viewGroup');
            $data = PoliciesUtil::getAllModulePoliciesDataByPermitable($group);
            $form = PoliciesFormUtil::makeFormFromPoliciesData($data);
            $compareData = array(
                'UsersModule' => array(
                    'POLICY_ENFORCE_STRONG_PASSWORDS'   => array(
                        'displayName' => UsersModule::POLICY_ENFORCE_STRONG_PASSWORDS,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => null,
                    ),
                    'POLICY_MINIMUM_PASSWORD_LENGTH'   => array(
                        'displayName' => UsersModule::POLICY_MINIMUM_PASSWORD_LENGTH,
                        'explicit'    => 5,
                        'inherited'   => null,
                        'effective'   => 5,
                    ),
                    'POLICY_MINIMUM_USERNAME_LENGTH'   => array(
                        'displayName' => UsersModule::POLICY_MINIMUM_USERNAME_LENGTH,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => 3,
                    ),
                    'POLICY_PASSWORD_EXPIRES'   => array(
                        'displayName' => UsersModule::POLICY_PASSWORD_EXPIRES,
                        'explicit'    => null,
                        'inherited'   => Policy::YES,
                        'effective'   => Policy::YES,
                    ),
                    'POLICY_PASSWORD_EXPIRY_DAYS'   => array(
                        'displayName' => UsersModule::POLICY_PASSWORD_EXPIRY_DAYS,
                        'explicit'    => 10,
                        'inherited'   => 15,
                        'effective'   => 10,
                    ),
                ),
            );
            $this->assertEquals($compareData, $form->data);
            $group->forget();

            $group = Group::getByName('viewGroup');
            $fakePost = array(
                'UsersModule__POLICY_MINIMUM_PASSWORD_LENGTH'   => '',
            );
            $validatedPost = PoliciesFormUtil::typeCastPostData($fakePost);
            $saved = PoliciesFormUtil::setPoliciesFromCastedPost($validatedPost, $group);
            $this->assertTrue($saved);
            $group->forget();
            $group = Group::getByName('viewGroup');
            $data = PoliciesUtil::getAllModulePoliciesDataByPermitable($group);
            $form = PoliciesFormUtil::makeFormFromPoliciesData($data);
            $compareData = array(
                'UsersModule' => array(
                    'POLICY_ENFORCE_STRONG_PASSWORDS'   => array(
                        'displayName' => UsersModule::POLICY_ENFORCE_STRONG_PASSWORDS,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => null,
                    ),
                    'POLICY_MINIMUM_PASSWORD_LENGTH'   => array(
                        'displayName' => UsersModule::POLICY_MINIMUM_PASSWORD_LENGTH,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => 5,
                    ),
                    'POLICY_MINIMUM_USERNAME_LENGTH'   => array(
                        'displayName' => UsersModule::POLICY_MINIMUM_USERNAME_LENGTH,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => 3,
                    ),
                    'POLICY_PASSWORD_EXPIRES'   => array(
                        'displayName' => UsersModule::POLICY_PASSWORD_EXPIRES,
                        'explicit'    => null,
                        'inherited'   => Policy::YES,
                        'effective'   => Policy::YES,
                    ),
                    'POLICY_PASSWORD_EXPIRY_DAYS'   => array(
                        'displayName' => UsersModule::POLICY_PASSWORD_EXPIRY_DAYS,
                        'explicit'    => 10,
                        'inherited'   => 15,
                        'effective'   => 10,
                    ),
                ),
            );
            $this->assertEquals($compareData, $form->data);
            $group->forget();
        }

        public function testGetDerivedAttributeNameFromTwoStrings()
        {
            $attributeName = FormModelUtil::getDerivedAttributeNameFromTwoStrings('x', 'y');
            $this->assertEquals('x__y', $attributeName);
        }

        /**
         * @depends testPoliciesFormUtilSetPoliciesFromPost
         */
        public function testPoliciesFormValidate()
        {
            $fakePostData = array(
                'UsersModule__POLICY_MINIMUM_PASSWORD_LENGTH__helper'   => '',
                'UsersModule__POLICY_MINIMUM_PASSWORD_LENGTH'          => strval(10),
                'UsersModule__POLICY_MINIMUM_USERNAME_LENGTH__helper'   => strval(PolicyIntegerAndStaticDropDownElement::HELPER_DROPDOWN_VALUE_YES),
                'UsersModule__POLICY_MINIMUM_USERNAME_LENGTH'          => strval(5),
                'UsersModule__POLICY_PASSWORD_EXPIRES'                 => strval(Policy::NO),
            );
            $group = Group::getByName('viewGroup');
            $data = PoliciesUtil::getAllModulePoliciesDataByPermitable($group);
            $policiesForm = PoliciesFormUtil::makeFormFromPoliciesData($data);
            $validatedPost = PoliciesFormUtil::typeCastPostData($fakePostData);
            $policiesForm = PoliciesFormUtil::loadFormFromCastedPost($policiesForm, $validatedPost);
            $validated = $policiesForm->validate();
            $this->assertTrue($validated);
            $fakePostData = array(
                'UsersModule__POLICY_MINIMUM_PASSWORD_LENGTH__helper'   => strval(PolicyIntegerAndStaticDropDownElement::HELPER_DROPDOWN_VALUE_YES),
                'UsersModule__POLICY_MINIMUM_PASSWORD_LENGTH'          => '',
                'UsersModule__POLICY_MINIMUM_USERNAME_LENGTH__helper'   => strval(PolicyIntegerAndStaticDropDownElement::HELPER_DROPDOWN_VALUE_YES),
                'UsersModule__POLICY_MINIMUM_USERNAME_LENGTH'          => '',
                'UsersModule__POLICY_PASSWORD_EXPIRES'                 => strval(Policy::YES),
                'UsersModule__POLICY_PASSWORD_EXPIRY_DAYS'             => '',
            );
            $validatedPost = PoliciesFormUtil::typeCastPostData($fakePostData);
            $policiesForm = PoliciesFormUtil::loadFormFromCastedPost($policiesForm, $validatedPost);
            $validated = $policiesForm->validate();
            $this->assertFalse($validated);
            $compareData = array(
                'UsersModule__POLICY_MINIMUM_PASSWORD_LENGTH' => array(
                    'You must specify a value.',
                ),
                'UsersModule__POLICY_MINIMUM_USERNAME_LENGTH' => array(
                    'You must specify a value.',
                ),
                'UsersModule__POLICY_PASSWORD_EXPIRY_DAYS' => array(
                    'You must specify a value.',
                ),
            );
            $this->assertEquals($compareData, $policiesForm->getErrors());
        }
    }
?>
