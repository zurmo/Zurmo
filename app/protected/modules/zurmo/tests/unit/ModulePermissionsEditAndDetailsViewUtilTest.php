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

    class ModulePermissionsEditAndDetailsViewUtilTest extends ZurmoBaseTest
    {
        public function testResolveMetadataFromPermissionsData()
        {
            $data = array(
                'AccountsModule' => array(
                    Permission::CHANGE_OWNER => array(
                        'explicit'    => null,
                        'inherited'   => null,
                    ),
                    Permission::CHANGE_PERMISSIONS => array(
                        'explicit'    => null,
                        'inherited'   => null,
                    ),
                    Permission::DELETE => array(
                        'explicit'    => null,
                        'inherited'   => null,
                    ),
                    Permission::READ => array(
                        'explicit'    => null,
                        'inherited'   => Permission::ALLOW,
                    ),
                    Permission::WRITE => array(
                        'explicit'    => null,
                        'inherited'   => Permission::DENY,
                    ),
                ),
                'ContactsModule' => array(
                    Permission::CHANGE_OWNER => array(
                        'explicit'    => null,
                        'inherited'   => null,
                    ),
                    Permission::CHANGE_PERMISSIONS => array(
                        'explicit'    => null,
                        'inherited'   => null,
                    ),
                    Permission::DELETE => array(
                        'explicit'    => null,
                        'inherited'   => null,
                    ),
                    Permission::READ => array(
                        'explicit'    => null,
                        'inherited'   => Permission::ALLOW,
                    ),
                    Permission::WRITE => array(
                        'explicit'    => null,
                        'inherited'   => Permission::DENY,
                    ),
                ),
            );
            $this->assertTrue(in_array('AccountsModule', GroupModulePermissionsDataToEditViewAdapater::getAdministrableModuleClassNames()));
            $newData = GroupModulePermissionsDataToEditViewAdapater::resolveData($data);
            $this->assertNotEmpty($newData);
            $this->assertEquals($data, $newData);
            $metadata = ModulePermissionsEditViewUtil::resolveMetadataFromData(
                $newData,
                ModulePermissionsEditAndDetailsView::getMetadata());
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
                                array('title' => 'Accounts', 'cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array(
                                                    'attributeName' => 'AccountsModule__' . Permission::READ,
                                                    'type' => 'PermissionInheritedAllowStaticDropDown'),
                                            ),
                                        ),
                                        array(
                                            'elements' => array(
                                                array(
                                                    'attributeName' => 'AccountsModule__' . Permission::WRITE,
                                                    'type' => 'PermissionDenyText'),
                                            ),
                                        ),
                                        array(
                                            'elements' => array(
                                                array(
                                                    'attributeName' => 'AccountsModule__' . Permission::DELETE,
                                                    'type' => 'PermissionStaticDropDown'),
                                            ),
                                        ),
                                    )
                                ),
                                array('title' => 'Contacts&#160;&#38;&#160;Leads', 'cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array(
                                                    'attributeName' => 'ContactsModule__' . Permission::READ,
                                                    'type' => 'PermissionInheritedAllowStaticDropDown'),
                                            ),
                                        ),
                                        array(
                                            'elements' => array(
                                                array(
                                                    'attributeName' => 'ContactsModule__' . Permission::WRITE,
                                                    'type' => 'PermissionDenyText'),
                                            ),
                                        ),
                                        array(
                                            'elements' => array(
                                                array(
                                                    'attributeName' => 'ContactsModule__' . Permission::DELETE,
                                                    'type' => 'PermissionStaticDropDown'),
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
            $metadata = ModulePermissionsActualDetailsViewUtil::resolveMetadataFromData(
                $newData,
                ModulePermissionsEditAndDetailsView::getMetadata());
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
                                array('title' => 'Accounts', 'cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array(
                                                    'attributeName' => 'AccountsModule__' . Permission::READ . '__actual',
                                                    'type' => 'PermissionActual'),
                                            ),
                                        ),
                                        array(
                                            'elements' => array(
                                                array(
                                                    'attributeName' => 'AccountsModule__' . Permission::WRITE . '__actual',
                                                    'type' => 'PermissionActual'),
                                            ),
                                        ),
                                        array(
                                            'elements' => array(
                                                array(
                                                    'attributeName' => 'AccountsModule__' . Permission::DELETE . '__actual',
                                                    'type' => 'PermissionActual'),
                                            ),
                                        ),
                                    )
                                ),
                                array('title' => 'Contacts&#160;&#38;&#160;Leads', 'cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array(
                                                    'attributeName' => 'ContactsModule__' . Permission::READ . '__actual',
                                                    'type' => 'PermissionActual'),
                                            ),
                                        ),
                                        array(
                                            'elements' => array(
                                                array(
                                                    'attributeName' => 'ContactsModule__' . Permission::WRITE . '__actual',
                                                    'type' => 'PermissionActual'),
                                            ),
                                        ),
                                        array(
                                            'elements' => array(
                                                array(
                                                    'attributeName' => 'ContactsModule__' . Permission::DELETE . '__actual',
                                                    'type' => 'PermissionActual'),
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

        public function testResolveWritePermissionsFromArray()
        {
            $fakePost = array(
                'LeadsModule__' . Permission::READ             => Permission::ALLOW,
                'LeadsModule__' . Permission::WRITE            => Permission::ALLOW,
                'AccountsModule__' . Permission::READ          => '',
                'OpportunitiesModule__' . Permission::DELETE   => Permission::DENY,
            );
            $readyToSetPostData = ModulePermissionsEditViewUtil::resolveWritePermissionsFromArray($fakePost);
            $compareData = array(
                'LeadsModule__' . Permission::READ                => Permission::ALLOW,
                'LeadsModule__' . Permission::WRITE               => Permission::ALLOW,
                'LeadsModule__' . Permission::CHANGE_PERMISSIONS  => Permission::ALLOW,
                'LeadsModule__' . Permission::CHANGE_OWNER        => Permission::ALLOW,
                'AccountsModule__' . Permission::READ             => '',
                'OpportunitiesModule__' . Permission::DELETE      => Permission::DENY,
            );
            $this->assertEquals($compareData, $readyToSetPostData);
        }

        public function testMatchingGetPermissionsForView()
        {
            $permissionsNames = ModulePermissionsEditViewUtil::getPermissionNamesForView();
            $permissions = ModulePermissionsEditViewUtil::getPermissionsForView();
            $this->assertEquals(count($permissionsNames), count($permissions));
            $this->assertEquals(3, count($permissionsNames));
        }
    }
?>