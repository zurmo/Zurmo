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

    /**
     * Class that builds demo users.
     */
    Yii::import('application.modules.zurmo.data.PersonDemoDataMaker');
    class UsersDemoDataMaker extends PersonDemoDataMaker
    {
        public static function getDependencies()
        {
            return array('groups', 'roles');
        }

        public function makeAll(& $demoDataHelper)
        {
            assert('$demoDataHelper instanceof DemoDataHelper');
            assert('$demoDataHelper->isSetRange("Group")');
            assert('$demoDataHelper->isSetRange("Role")');

            $super               = User::getByUsername('super');
            $email               = new Email();
            $email->emailAddress = 'Super.User@test.zurmo.com';
            $super->primaryEmail = $email;
            $saved               = $super->save();
            assert('$saved');
            UserConfigurationFormAdapter::setValue($super, true, 'turnOffEmailNotifications');

            $userAvatarForm             = new UserAvatarForm($super);
            $userAvatarForm->avatarType = User::AVATAR_TYPE_PRIMARY_EMAIL;
            $saved                      = $userAvatarForm->save();
            assert('$saved');

            $user = new User();
            $this->populateModel($user);
            $user->username           = 'admin';
            $user->title->value       = 'Sir';
            $user->firstName          = 'Jason';
            $user->lastName           = 'Blue';
            $email                    = new Email();
            $email->emailAddress      = 'Jason.Blue@test.zurmo.com';
            $user->primaryEmail       = $email;
            $user->setPassword($user->username);
            $saved                    = $user->save();
            assert('$saved');
            UserConfigurationFormAdapter::setValue($user, true, 'turnOffEmailNotifications');

            $userAvatarForm             = new UserAvatarForm($user);
            $userAvatarForm->avatarType = User::AVATAR_TYPE_PRIMARY_EMAIL;
            $saved                      = $userAvatarForm->save();
            assert('$saved');

            $userStartId = $user->id;
            $roleIdRange = $demoDataHelper->getRangeByModelName('Role');
            $role = Role::getById($roleIdRange['startId']);
            assert('$role instanceof Role');
            $role->users->add($user);
            $saved = $role->save();
            assert('$saved');

            foreach (array('jim'   => 'Mr.',
                           'john'  => 'Mr.',
                           'sally' => 'Dr.',
                           'mary'  => 'Mrs.',
                           'katie' => 'Ms.',
                           'jill'  => 'Ms.',
                           'sam'   => 'Mr.') as $username => $title)
            {
                $user = new User();
                $this->populateModel($user);
                $user->username           = $username;
                $user->setPassword($user->username);
                $user->title->value       = $title;
                $user->firstName          = ucfirst($username);
                $user->lastName           = 'Smith';
                $email                    = new Email();
                $email->emailAddress      = $user->firstName . '@test.zurmo.com';
                $user->primaryEmail       = $email;
                $saved                    = $user->save();
                assert('$saved');
                UserConfigurationFormAdapter::setValue($user, true, 'turnOffEmailNotifications');

                $userAvatarForm             = new UserAvatarForm($user);
                $userAvatarForm->avatarType = User::AVATAR_TYPE_PRIMARY_EMAIL;
                $saved                      = $userAvatarForm->save();
                assert('$saved');

                $roleIdRange = $demoDataHelper->getRangeByModelName('Role');
                $role = Role::getById($roleIdRange['startId'] + 1);
                assert('$role instanceof Role');
                $role->users->add($user);
                $saved = $role->save();
                assert('$saved');
            }
            $demoDataHelper->setRangeByModelName('User', $userStartId, $user->id);
        }

        public function populateModel(& $model)
        {
            assert('$model instanceof User');
            parent::populateModel($model);
            $model->language = Yii::app()->language;
            $model->timeZone = Yii::app()->timeZoneHelper->getGlobalValue();
            $currencies      = Currency::getAll();
            $model->currency = $currencies[0];
        }
    }
?>