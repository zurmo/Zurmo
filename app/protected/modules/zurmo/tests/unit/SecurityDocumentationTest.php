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

    // The actual tests are in PermissionsTest, GroupTest, etc, etc, etc.
    // This is executable documentation.
    class SecurityDocumentationTest extends ZurmoBaseTest
    {
        public function testABitOfEverythingAsAnExample()
        {
            $superAdminDude = new User();
            $superAdminDude->title->value       = 'Miss';
            $superAdminDude->username           = 'laura';
            $superAdminDude->firstName          = 'Laura';
            $superAdminDude->lastName           = 'Laurason';
            $superAdminDude->setPassword('laura');
            $this->assertTrue($superAdminDude->save());

            $adminDude = new User();
            $adminDude->title->value       = 'Mr.';
            $adminDude->username           = 'jason';
            $adminDude->firstName          = 'Jason';
            $adminDude->lastName           = 'Jasonson';
            $adminDude->setPassword('jason');
            $this->assertTrue($adminDude->save());

            $accountOwner = new User();
            $accountOwner->title->value       = 'Mr.'; // :P
            $accountOwner->username           = 'lisay';
            $accountOwner->firstName          = 'lisa';
            $accountOwner->lastName           = 'Lisason';
            $accountOwner->setPassword('lisay');
            $this->assertTrue($accountOwner->save());

            $salesDude1 = new User();
            $salesDude1->title->value       = 'Mr.';
            $salesDude1->username           = 'ray45';
            $salesDude1->firstName          = 'Ray';
            $salesDude1->lastName           = 'Rayson';
            $salesDude1->setPassword('ray45');
            $this->assertTrue($salesDude1->save());

            $salesDude2 = new User();
            $salesDude2->title->value       = 'Mr.';
            $salesDude2->username           = 'stafford';
            $salesDude2->firstName          = 'Stafford';
            $salesDude2->lastName           = 'Staffordson';
            $salesDude2->setPassword('stafford');
            $this->assertTrue($salesDude2->save());

            $managementDudette = new User();
            $managementDudette->title->value       = 'Ms.';
            $managementDudette->username           = 'donna';
            $managementDudette->firstName          = 'Donna';
            $managementDudette->lastName           = 'Donnason';
            $managementDudette->setPassword('donna');
            $this->assertTrue($managementDudette->save());

            $supportDude = new User();
            $supportDude->title->value        = 'Mr.';
            $supportDude->username            = 'rossy';
            $supportDude->firstName           = 'Ross';
            $supportDude->lastName            = 'Rosson';
            $supportDude->setPassword('rossy');
            $this->assertTrue($supportDude->save());

            $superAdminDudes = new Group();
            $superAdminDudes->name = 'Super Admin Dudes';
            $superAdminDudes->users->add($superAdminDude);
            $this->assertTrue($superAdminDudes->save());

            $adminDudes = new Group();
            $adminDudes->name = 'Admin Dudes';
            $adminDudes->users ->add($adminDude);
            $adminDudes->groups->add($superAdminDudes);
            $this->assertTrue($adminDudes->save());

            $superAdminDudes->setPolicy('UsersModule', UsersModule::POLICY_PASSWORD_EXPIRES, 0);
            $this->assertTrue($superAdminDudes->save());

            $adminDudes->setRight ('UsersModule', UsersModule::RIGHT_CHANGE_USER_PASSWORDS);
            $adminDudes->setRight ('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB);
            $adminDudes->setRight ('UsersModule', UsersModule::RIGHT_LOGIN_VIA_MOBILE);
            $adminDudes->setRight ('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB_API);
            $adminDudes->setPolicy('UsersModule', UsersModule::POLICY_PASSWORD_EXPIRY_DAYS, 90);
            $this->assertTrue($adminDudes->save());

            $salesDudes = new Group();
            $salesDudes->name = 'Sales Dudes';
            $salesDudes->users->add($salesDude1);
            $salesDudes->users->add($salesDude2);
            $this->assertTrue($salesDudes->save());

            $managementDudes = new Group();
            $managementDudes->name = 'Management Dudes';
            $managementDudes->users->add($managementDudette);
            $this->assertTrue($managementDudes->save());

            $everyone = Group::getByName(Group::EVERYONE_GROUP_NAME);
            $everyone->setRight ('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB);
            $everyone->setPolicy('UsersModule', UsersModule::POLICY_PASSWORD_EXPIRES,     1);
            $everyone->setPolicy('UsersModule', UsersModule::POLICY_PASSWORD_EXPIRY_DAYS, 30);
            $this->assertTrue($everyone->save());

            Yii::app()->user->userModel = $accountOwner;

            $account = new Account();
            $account->name = 'Doozy Co.';
            $this->assertTrue($account->save());
            // The account has no explicit permissions set at this point.

            // The account owner has full permissions implicitly.
            $this->assertEquals(Permission::ALL,  $account->getEffectivePermissions($accountOwner));

            // Nobody else has permissions.
            $this->assertEquals(Permission::NONE, $account->getEffectivePermissions($adminDude));
            $this->assertEquals(Permission::NONE, $account->getEffectivePermissions($adminDudes));
            $this->assertEquals(Permission::NONE, $account->getEffectivePermissions($salesDude1));
            $this->assertEquals(Permission::NONE, $account->getEffectivePermissions($salesDude2));
            $this->assertEquals(Permission::NONE, $account->getEffectivePermissions($managementDudette));
            $this->assertEquals(Permission::NONE, $account->getEffectivePermissions($salesDudes));
            $this->assertEquals(Permission::NONE, $account->getEffectivePermissions($managementDudes));
            $this->assertEquals(Permission::NONE, $account->getEffectivePermissions($supportDude));

            // Everyone is given read permissions to the account.
            $everyone = Group::getByName(Group::EVERYONE_GROUP_NAME);
            $account->addPermissions($everyone, Permission::READ);
            $account->save();

            // In one step everyone has read permissions, except the owner who still has full.
            $this->assertEquals(Permission::READ,                            $account->getEffectivePermissions($adminDude));
            $this->assertEquals(Permission::READ,                            $account->getEffectivePermissions($adminDudes));
            $this->assertEquals(Permission::ALL,                             $account->getEffectivePermissions($accountOwner));
            $this->assertEquals(Permission::READ,                            $account->getEffectivePermissions($salesDude1));
            $this->assertEquals(Permission::READ,                            $account->getEffectivePermissions($salesDude2));
            $this->assertEquals(Permission::READ,                            $account->getEffectivePermissions($managementDudette));
            $this->assertEquals(Permission::READ,                            $account->getEffectivePermissions($salesDudes));
            $this->assertEquals(Permission::READ,                            $account->getEffectivePermissions($managementDudes));
            $this->assertEquals(Permission::READ,                            $account->getEffectivePermissions($supportDude));
            $this->assertEquals(Permission::READ,                            $account->getEffectivePermissions($everyone));

            // Sales Dudes is given write permissions to the account.
            $account->addPermissions($salesDudes, Permission::WRITE);
            $account->save();

            // The Sales Dudes group and everyone in it has write.
            $this->assertEquals(Permission::READ,                            $account->getEffectivePermissions($adminDude));
            $this->assertEquals(Permission::READ,                            $account->getEffectivePermissions($adminDudes));
            $this->assertEquals(Permission::ALL,                             $account->getEffectivePermissions($accountOwner));
            $this->assertEquals(Permission::READ_WRITE,                      $account->getEffectivePermissions($salesDude1));
            $this->assertEquals(Permission::READ_WRITE,                      $account->getEffectivePermissions($salesDude2));
            $this->assertEquals(Permission::READ,                            $account->getEffectivePermissions($managementDudette));
            $this->assertEquals(Permission::READ_WRITE,                      $account->getEffectivePermissions($salesDudes));
            $this->assertEquals(Permission::READ,                            $account->getEffectivePermissions($managementDudes));
            $this->assertEquals(Permission::READ,                            $account->getEffectivePermissions($supportDude));
            $this->assertEquals(Permission::READ,                            $account->getEffectivePermissions($everyone));

            // Management Dudes is given change owner permissions to the account.
            $account->addPermissions($managementDudes, Permission::CHANGE_OWNER);
            $account->save();

            // The Managment Dudes group and everyone in it has change owner.
            $this->assertEquals(Permission::READ,                            $account->getEffectivePermissions($adminDude));
            $this->assertEquals(Permission::READ,                            $account->getEffectivePermissions($adminDudes));
            $this->assertEquals(Permission::ALL,                             $account->getEffectivePermissions($accountOwner));
            $this->assertEquals(Permission::READ_WRITE,                      $account->getEffectivePermissions($salesDude1));
            $this->assertEquals(Permission::READ_WRITE,                      $account->getEffectivePermissions($salesDude2));
            $this->assertEquals(Permission::READ | Permission::CHANGE_OWNER, $account->getEffectivePermissions($managementDudette));
            $this->assertEquals(Permission::READ_WRITE,                      $account->getEffectivePermissions($salesDudes));
            $this->assertEquals(Permission::READ | Permission::CHANGE_OWNER, $account->getEffectivePermissions($managementDudes));
            $this->assertEquals(Permission::READ,                            $account->getEffectivePermissions($supportDude));
            $this->assertEquals(Permission::READ,                            $account->getEffectivePermissions($everyone));

            // We want to remove Support Dude's read on the account.

            // The first way... having thought about our security and groups well...

            // Everyone's read permission is removed, and instead Sales Dudes
            // and Managment Dudes are given read permissions. Order is irrelevant.
            $account->removePermissions($everyone,        Permission::READ);
            $account->addPermissions   ($salesDudes,      Permission::READ);
            $account->addPermissions   ($managementDudes, Permission::READ);
            $account->save();

            // The effect is that Support Dude and Admin Dudes lose read permissions because
            // now nobody has that permission via Everyone.
            $this->assertEquals(Permission::NONE,                            $account->getEffectivePermissions($adminDude));
            $this->assertEquals(Permission::NONE,                            $account->getEffectivePermissions($adminDudes));
            $this->assertEquals(Permission::ALL,                             $account->getEffectivePermissions($accountOwner));
            $this->assertEquals(Permission::READ_WRITE,                      $account->getEffectivePermissions($salesDude1));
            $this->assertEquals(Permission::READ_WRITE,                      $account->getEffectivePermissions($salesDude2));
            $this->assertEquals(Permission::READ | Permission::CHANGE_OWNER, $account->getEffectivePermissions($managementDudette));
            $this->assertEquals(Permission::READ_WRITE,                      $account->getEffectivePermissions($salesDudes));
            $this->assertEquals(Permission::READ | Permission::CHANGE_OWNER, $account->getEffectivePermissions($managementDudes));
            $this->assertEquals(Permission::NONE, /* <<< */                  $account->getEffectivePermissions($supportDude));
            $this->assertEquals(Permission::NONE, /* <<< */                  $account->getEffectivePermissions($everyone));

            // Permissions are set back.
            $account->addPermissions   ($everyone,        Permission::READ);
            $account->removePermissions($salesDudes,      Permission::READ);
            $account->removePermissions($managementDudes, Permission::READ);
            $account->save();

            // Support Dude and Admin Dudes get their read back.
            $this->assertEquals(Permission::READ,                            $account->getEffectivePermissions($adminDude));
            $this->assertEquals(Permission::READ,                            $account->getEffectivePermissions($adminDudes));
            $this->assertEquals(Permission::ALL,                             $account->getEffectivePermissions($accountOwner));
            $this->assertEquals(Permission::READ_WRITE,                      $account->getEffectivePermissions($salesDude1));
            $this->assertEquals(Permission::READ_WRITE,                      $account->getEffectivePermissions($salesDude2));
            $this->assertEquals(Permission::READ | Permission::CHANGE_OWNER, $account->getEffectivePermissions($managementDudette));
            $this->assertEquals(Permission::READ_WRITE,                      $account->getEffectivePermissions($salesDudes));
            $this->assertEquals(Permission::READ | Permission::CHANGE_OWNER, $account->getEffectivePermissions($managementDudes));
            $this->assertEquals(Permission::READ,                            $account->getEffectivePermissions($supportDude));
            $this->assertEquals(Permission::READ,                            $account->getEffectivePermissions($everyone));

            // The second way... more ad-hoc...

            // We explicitly deny. Deny's have precedence over allows.
            $account->addPermissions($supportDude, Permission::READ, Permission::DENY);
            $account->save();

            // The effect is that Support Dude loses read permissions but
            // Everyone else still has read.
            $this->assertEquals(Permission::READ,                            $account->getEffectivePermissions($adminDude));
            $this->assertEquals(Permission::READ,                            $account->getEffectivePermissions($adminDudes));
            $this->assertEquals(Permission::ALL,                             $account->getEffectivePermissions($accountOwner));
            $this->assertEquals(Permission::READ_WRITE,                      $account->getEffectivePermissions($salesDude1));
            $this->assertEquals(Permission::READ_WRITE,                      $account->getEffectivePermissions($salesDude2));
            $this->assertEquals(Permission::READ | Permission::CHANGE_OWNER, $account->getEffectivePermissions($managementDudette));
            $this->assertEquals(Permission::READ_WRITE,                      $account->getEffectivePermissions($salesDudes));
            $this->assertEquals(Permission::READ | Permission::CHANGE_OWNER, $account->getEffectivePermissions($managementDudes));
            $this->assertEquals(Permission::NONE, /* <<< */                  $account->getEffectivePermissions($supportDude));
            $this->assertEquals(Permission::READ,                            $account->getEffectivePermissions($everyone));

            // Managment Dudes has all permissions is denied.
            // This takes precedence over the read permission the group was given.
            $account->addPermissions($managementDudes, Permission::ALL, Permission::DENY);
            $account->save();

            // The effect is that Management Dudes lose all permissions
            // regardless of what they have been granted.
            $this->assertEquals(Permission::READ,                            $account->getEffectivePermissions($adminDude));
            $this->assertEquals(Permission::READ,                            $account->getEffectivePermissions($adminDudes));
            $this->assertEquals(Permission::ALL,                             $account->getEffectivePermissions($accountOwner));
            $this->assertEquals(Permission::READ_WRITE,                      $account->getEffectivePermissions($salesDude1));
            $this->assertEquals(Permission::READ_WRITE,                      $account->getEffectivePermissions($salesDude2));
            $this->assertEquals(Permission::NONE, /* <<< */                  $account->getEffectivePermissions($managementDudette));
            $this->assertEquals(Permission::READ_WRITE,                      $account->getEffectivePermissions($salesDudes));
            $this->assertEquals(Permission::NONE, /* <<< */                  $account->getEffectivePermissions($managementDudes));
            $this->assertEquals(Permission::NONE,                            $account->getEffectivePermissions($supportDude));
            $this->assertEquals(Permission::READ,                            $account->getEffectivePermissions($everyone));

            // We'll give Management Dudes back their permissions.
            $account->removePermissions($managementDudes, Permission::ALL, Permission::DENY);
            // And give management dudette change permissions.
            $account->addPermissions($managementDudette, Permission::CHANGE_PERMISSIONS);
            $account->save();

            $this->assertEquals(Permission::READ,                            $account->getEffectivePermissions($adminDude));
            $this->assertEquals(Permission::READ,                            $account->getEffectivePermissions($adminDudes));
            $this->assertEquals(Permission::ALL,                             $account->getEffectivePermissions($accountOwner));
            $this->assertEquals(Permission::READ_WRITE,                      $account->getEffectivePermissions($salesDude1));
            $this->assertEquals(Permission::READ_WRITE,                      $account->getEffectivePermissions($salesDude2));
            $this->assertEquals(Permission::READ | Permission::CHANGE_PERMISSIONS | Permission::CHANGE_OWNER,
                                                                             $account->getEffectivePermissions($managementDudette));
            $this->assertEquals(Permission::READ_WRITE,                      $account->getEffectivePermissions($salesDudes));
            $this->assertEquals(Permission::READ | Permission::CHANGE_OWNER, $account->getEffectivePermissions($managementDudes));
            $this->assertEquals(Permission::NONE,                            $account->getEffectivePermissions($supportDude));
            $this->assertEquals(Permission::READ,                            $account->getEffectivePermissions($everyone));

            // Then we'll just nuke eveyone's permissions. If you use this it is for
            // the kind of scenario where an admin wants to re-setup permissions from scratch
            // so you'd put a Do You Really Want To Do This???? kind of message.
            Permission::removeAll();

            // Removing all permissions is done directly on the database,
            // so we need to forget our account and get it back again.
            $accountId = $account->id;
            $account->forget();
            unset($account);
            $account = Account::getById($accountId);

            // Nobody else has permissions again.
            $this->assertEquals(Permission::NONE, $account->getEffectivePermissions($adminDude));
            $this->assertEquals(Permission::NONE, $account->getEffectivePermissions($adminDudes));
            $this->assertEquals(Permission::NONE, $account->getEffectivePermissions($salesDude1));
            $this->assertEquals(Permission::NONE, $account->getEffectivePermissions($salesDude2));
            $this->assertEquals(Permission::NONE, $account->getEffectivePermissions($managementDudette));
            $this->assertEquals(Permission::NONE, $account->getEffectivePermissions($salesDudes));
            $this->assertEquals(Permission::NONE, $account->getEffectivePermissions($managementDudes));
            $this->assertEquals(Permission::NONE, $account->getEffectivePermissions($supportDude));

            // TODO
            // - Permissions on modules.
            // - Permissions on types.
            // - Permissions on fields.

            // All users have the right to login via the web, because the Everyone group was granted that right.
            $this->assertEquals(Right::ALLOW, $adminDude        ->getEffectiveRight('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB));
            $this->assertEquals(Right::ALLOW, $adminDudes       ->getEffectiveRight('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB));
            $this->assertEquals(Right::ALLOW, $salesDude1       ->getEffectiveRight('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB));
            $this->assertEquals(Right::ALLOW, $salesDude2       ->getEffectiveRight('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB));
            $this->assertEquals(Right::ALLOW, $managementDudette->getEffectiveRight('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB));
            $this->assertEquals(Right::ALLOW, $salesDudes       ->getEffectiveRight('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB));
            $this->assertEquals(Right::ALLOW, $managementDudes  ->getEffectiveRight('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB));
            $this->assertEquals(Right::ALLOW, $supportDude      ->getEffectiveRight('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB));
            $this->assertEquals(Right::ALLOW, $everyone         ->getEffectiveRight('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB));

            $this->assertEquals(Right::ALLOW, $adminDude        ->getEffectiveRight('UsersModule', UsersModule::RIGHT_CHANGE_USER_PASSWORDS));
            $this->assertEquals(Right::ALLOW, $adminDudes       ->getEffectiveRight('UsersModule', UsersModule::RIGHT_CHANGE_USER_PASSWORDS));
            $this->assertEquals(Right::DENY,  $salesDude1       ->getEffectiveRight('UsersModule', UsersModule::RIGHT_CHANGE_USER_PASSWORDS));
            $this->assertEquals(Right::DENY,  $salesDude2       ->getEffectiveRight('UsersModule', UsersModule::RIGHT_CHANGE_USER_PASSWORDS));
            $this->assertEquals(Right::DENY,  $managementDudette->getEffectiveRight('UsersModule', UsersModule::RIGHT_CHANGE_USER_PASSWORDS));
            $this->assertEquals(Right::DENY,  $salesDudes       ->getEffectiveRight('UsersModule', UsersModule::RIGHT_CHANGE_USER_PASSWORDS));
            $this->assertEquals(Right::DENY,  $managementDudes  ->getEffectiveRight('UsersModule', UsersModule::RIGHT_CHANGE_USER_PASSWORDS));
            $this->assertEquals(Right::DENY,  $supportDude      ->getEffectiveRight('UsersModule', UsersModule::RIGHT_CHANGE_USER_PASSWORDS));
            $this->assertEquals(Right::DENY,  $everyone         ->getEffectiveRight('UsersModule', UsersModule::RIGHT_CHANGE_USER_PASSWORDS));

            // All users have a password expiry days of 30 because it was set on Everyone, but that was overridden
            // for Admin Dudes with a more generous password expiry policy set for them.
            $this->assertEquals(90,           $adminDude        ->getEffectivePolicy('UsersModule', UsersModule::POLICY_PASSWORD_EXPIRY_DAYS));
            $this->assertEquals(90,           $adminDudes       ->getEffectivePolicy('UsersModule', UsersModule::POLICY_PASSWORD_EXPIRY_DAYS));
            $this->assertEquals(90,           $adminDude        ->getEffectivePolicy('UsersModule', UsersModule::POLICY_PASSWORD_EXPIRY_DAYS));
            $this->assertEquals(90,           $adminDudes       ->getEffectivePolicy('UsersModule', UsersModule::POLICY_PASSWORD_EXPIRY_DAYS));
            $this->assertEquals(30,           $salesDude1       ->getEffectivePolicy('UsersModule', UsersModule::POLICY_PASSWORD_EXPIRY_DAYS));
            $this->assertEquals(30,           $salesDude2       ->getEffectivePolicy('UsersModule', UsersModule::POLICY_PASSWORD_EXPIRY_DAYS));
            $this->assertEquals(30,           $managementDudette->getEffectivePolicy('UsersModule', UsersModule::POLICY_PASSWORD_EXPIRY_DAYS));
            $this->assertEquals(30,           $salesDudes       ->getEffectivePolicy('UsersModule', UsersModule::POLICY_PASSWORD_EXPIRY_DAYS));
            $this->assertEquals(30,           $managementDudes  ->getEffectivePolicy('UsersModule', UsersModule::POLICY_PASSWORD_EXPIRY_DAYS));
            $this->assertEquals(30,           $supportDude      ->getEffectivePolicy('UsersModule', UsersModule::POLICY_PASSWORD_EXPIRY_DAYS));
            $this->assertEquals(30,           $everyone         ->getEffectivePolicy('UsersModule', UsersModule::POLICY_PASSWORD_EXPIRY_DAYS));

            // But all users' passwords, except Super Admin Dudes, expire because of the policy set on Everyone,
            // which is set more specifically for Super Admin Dudes.
            $this->assertEquals(0,            $superAdminDude   ->getEffectivePolicy('UsersModule', UsersModule::POLICY_PASSWORD_EXPIRES));
            $this->assertEquals(0,            $superAdminDudes  ->getEffectivePolicy('UsersModule', UsersModule::POLICY_PASSWORD_EXPIRES));
            $this->assertEquals(1,            $adminDude        ->getEffectivePolicy('UsersModule', UsersModule::POLICY_PASSWORD_EXPIRES));
            $this->assertEquals(1,            $adminDudes       ->getEffectivePolicy('UsersModule', UsersModule::POLICY_PASSWORD_EXPIRES));
            $this->assertEquals(1,            $salesDude1       ->getEffectivePolicy('UsersModule', UsersModule::POLICY_PASSWORD_EXPIRES));
            $this->assertEquals(1,            $salesDude2       ->getEffectivePolicy('UsersModule', UsersModule::POLICY_PASSWORD_EXPIRES));
            $this->assertEquals(1,            $managementDudette->getEffectivePolicy('UsersModule', UsersModule::POLICY_PASSWORD_EXPIRES));
            $this->assertEquals(1,            $salesDudes       ->getEffectivePolicy('UsersModule', UsersModule::POLICY_PASSWORD_EXPIRES));
            $this->assertEquals(1,            $managementDudes  ->getEffectivePolicy('UsersModule', UsersModule::POLICY_PASSWORD_EXPIRES));
            $this->assertEquals(1,            $supportDude      ->getEffectivePolicy('UsersModule', UsersModule::POLICY_PASSWORD_EXPIRES));
            $this->assertEquals(1,            $everyone         ->getEffectivePolicy('UsersModule', UsersModule::POLICY_PASSWORD_EXPIRES));

            // The policy set on Super Admin Dudes that their passwords don't expire is more explicit than the Everyone
            // setting and so takes precedence. While ALLOW for permissions and rights is just required from any one
            // source (explicit or inherited from a group) and DENY on any source overrides it, the effective policy
            // is the most explicit. A policy set specifically on a user overrides a policy set on a group they are
            // directly in, which overrides one that that group is in, and so on, which overrides anything set on the
            // Everyone group. If nothing is set the policy value is null.

            // TODO
            // - Roles.
        }
    }
?>
