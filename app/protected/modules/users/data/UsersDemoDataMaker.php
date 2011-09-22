<?php
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

            foreach (array('jim'   => 'Mr',
                           'john'  => 'Mr',
                           'sally' => 'Dr',
                           'mary'  => 'Mrs',
                           'katie' => 'Ms',
                           'jill'  => 'Ms',
                           'sam'   => 'Mr') as $username => $title)
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
                $role = Role::getById($roleIdRange['startId']+1);
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