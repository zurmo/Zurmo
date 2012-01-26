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

            $user = new User();
            $this->populateModel($user);
            $user->username           = 'admin';
            $user->title->value       = 'Sir';
            $user->firstName          = 'Jason';
            $user->lastName           = 'Blue';
            $user->setPassword($user->username);
            $saved = $user->save();
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
                $saved = $user->save();
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