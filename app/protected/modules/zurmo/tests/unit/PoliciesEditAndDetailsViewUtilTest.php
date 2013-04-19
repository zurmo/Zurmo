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

    class PoliciesEditAndDetailsViewUtilTest extends ZurmoBaseTest
    {
        public function testResolveMetadataFromPoliciesData()
        {
            $data = array(
                'UsersModule' => array(
                    'POLICY_ENFORCE_STRONG_PASSWORDS'   => array(
                        'displayName' => UsersModule::POLICY_ENFORCE_STRONG_PASSWORDS,
                        'explicit'    => null,
                        'inherited'   => Policy::YES,
                    ),
                    'POLICY_MINIMUM_PASSWORD_LENGTH'   => array(
                        'displayName' => UsersModule::POLICY_MINIMUM_PASSWORD_LENGTH,
                        'explicit'    => 5,
                        'inherited'   => null,
                    ),
                    'POLICY_MINIMUM_USERNAME_LENGTH'   => array(
                        'displayName' => UsersModule::POLICY_MINIMUM_USERNAME_LENGTH,
                        'explicit'    => null,
                        'inherited'   => null,
                    ),
                    'POLICY_PASSWORD_EXPIRES'   => array(
                        'displayName' => UsersModule::POLICY_PASSWORD_EXPIRES,
                        'explicit'    => null,
                        'inherited'   => Policy::YES,
                    ),
                    'POLICY_PASSWORD_EXPIRY_DAYS'   => array(
                        'displayName' => UsersModule::POLICY_PASSWORD_EXPIRY_DAYS,
                        'explicit'    => 10,
                        'inherited'   => 15,
                    ),
                ),
            );
            $metadata = PoliciesEditViewUtil::resolveMetadataFromData(
                $data,
                PoliciesEditAndDetailsView::getMetadata());
            $compareData = array(
                'global' => array(
                    'toolbar' => array(
                        'elements' => array(
                            array('type' => 'CancelLink', 'renderType' => 'Edit'),
                            array('type' => 'SaveButton', 'renderType' => 'Edit'),
                        ),
                    ),
                    'panels' => array(
                        array(
                            'rows' => array(
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array(
                                                    'attributeName' => 'UsersModule__POLICY_ENFORCE_STRONG_PASSWORDS',
                                                    'type' => 'PolicyInheritedYesNoText'),
                                            ),
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array(
                                                    'attributeName' => 'UsersModule__POLICY_MINIMUM_PASSWORD_LENGTH',
                                                    'type' => 'PolicyIntegerAndStaticDropDown'),
                                            ),
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array(
                                                    'attributeName' => 'UsersModule__POLICY_MINIMUM_USERNAME_LENGTH',
                                                    'type' => 'PolicyIntegerAndStaticDropDown'),
                                            ),
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array(
                                                    'attributeName' => 'null',
                                                    'type' => 'PolicyPasswordExpiry'),
                                            ),
                                        ),
                                    )
                                ),
                            ),
                        ),
                    ),
                ),
            );
            $this->assertSame($compareData, $metadata);
            $metadata = PoliciesEffectiveDetailsViewUtil::resolveMetadataFromData(
                $data,
                PoliciesEditAndDetailsView::getMetadata());
            $compareData = array(
                'global' => array(
                    'toolbar' => array(
                        'elements' => array(
                            array('type' => 'CancelLink', 'renderType' => 'Edit'),
                            array('type' => 'SaveButton', 'renderType' => 'Edit'),
                        ),
                    ),
                    'panels' => array(
                        array(
                            'rows' => array(
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array(
                                                    'attributeName' => 'UsersModule__POLICY_ENFORCE_STRONG_PASSWORDS__effective',
                                                    'type' => 'PolicyEffectiveYesNo'),
                                            ),
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array(
                                                    'attributeName' => 'UsersModule__POLICY_MINIMUM_PASSWORD_LENGTH__effective',
                                                    'type' => 'PolicyEffectiveInteger'),
                                            ),
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array(
                                                    'attributeName' => 'UsersModule__POLICY_MINIMUM_USERNAME_LENGTH__effective',
                                                    'type' => 'PolicyEffectiveInteger'),
                                            ),
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array(
                                                    'attributeName' => 'null',
                                                    'type' => 'PolicyEffectivePasswordExpiry'),
                                            ),
                                        ),
                                    )
                                ),
                            ),
                        ),
                    ),
                ),
            );
            $this->assertSame($compareData, $metadata);
        }
    }
?>
