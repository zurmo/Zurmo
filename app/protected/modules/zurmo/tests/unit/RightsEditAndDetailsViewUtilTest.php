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

    class RightsEditAndDetailsViewUtilTest extends BaseTest
    {
        public function testResolveMetadataFromRightsData()
        {
            $rightsData = array(
                'UsersModule' => array(
                    'RIGHT_CHANGE_USER_PASSWORDS'   => array(
                        'displayName' => UsersModule::RIGHT_CHANGE_USER_PASSWORDS,
                        'explicit'    => null,
                        'inherited'   => Right::ALLOW,
                    ),
                    'RIGHT_LOGIN_VIA_WEB'   => array(
                        'displayName' => UsersModule::RIGHT_LOGIN_VIA_WEB,
                        'explicit'    => null,
                        'inherited'   => Right::DENY,
                    ),
                    'RIGHT_LOGIN_VIA_MOBILE'   => array(
                        'displayName' => UsersModule::RIGHT_LOGIN_VIA_MOBILE,
                        'explicit'    => Right::ALLOW,
                        'inherited'   => null,
                    ),
                    'RIGHT_LOGIN_VIA_WEB_API'   => array(
                        'displayName' => UsersModule::RIGHT_LOGIN_VIA_WEB_API,
                        'explicit'    => null,
                        'inherited'   => null,
                    ),
                ),
            );
            $metadata = RightsEditViewUtil::resolveMetadataFromData(
                $rightsData,
                RightsEditAndDetailsView::getMetadata());
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
                                                    'attributeName' => 'UsersModule__RIGHT_CHANGE_USER_PASSWORDS',
                                                    'type' => 'RightInheritedAllowStaticDropDown'),
                                            ),
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array(
                                                    'attributeName' => 'UsersModule__RIGHT_LOGIN_VIA_WEB',
                                                    'type' => 'RightInheritedDenyText'),
                                            ),
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array(
                                                    'attributeName' => 'UsersModule__RIGHT_LOGIN_VIA_MOBILE',
                                                    'type' => 'RightStaticDropDown'),
                                            ),
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array(
                                                    'attributeName' => 'UsersModule__RIGHT_LOGIN_VIA_WEB_API',
                                                    'type' => 'RightStaticDropDown'),
                                            ),
                                        ),
                                    )
                                ),
                            ),
                            'title' => 'Users',
                        ),
                    ),
                ),
            );
            $this->assertSame($compareData, $metadata);
            $metadata = RightsEffectiveDetailsViewUtil::resolveMetadataFromData(
                $rightsData,
                RightsEditAndDetailsView::getMetadata());
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
                                                    'attributeName' => 'UsersModule__RIGHT_CHANGE_USER_PASSWORDS__effective',
                                                    'type' => 'RightEffective'),
                                            ),
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array(
                                                    'attributeName' => 'UsersModule__RIGHT_LOGIN_VIA_WEB__effective',
                                                    'type' => 'RightEffective'),
                                            ),
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array(
                                                    'attributeName' => 'UsersModule__RIGHT_LOGIN_VIA_MOBILE__effective',
                                                    'type' => 'RightEffective'),
                                            ),
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array(
                                                    'attributeName' => 'UsersModule__RIGHT_LOGIN_VIA_WEB_API__effective',
                                                    'type' => 'RightEffective'),
                                            ),
                                        ),
                                    )
                                ),
                            ),
                            'title' => 'Users',
                        ),
                    ),
                ),
            );
            $this->assertSame($compareData, $metadata);
        }
    }
?>
