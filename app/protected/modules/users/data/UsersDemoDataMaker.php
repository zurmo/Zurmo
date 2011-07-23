<?php
    /**
     * Class that builds demo users.
     */
    class UsersDemoDataMaker extends PersonDemoDataMaker
    {
        protected $quantity;

        public static function getDependencies()
        {
            return array('groups', 'roles');
        }

        public function makeAll(& $demoDataByModelClassName)
        {
            assert('is_array($demoDataByModelClassName)');
            assert('isset($demoDataByModelClassName["Group"])');
            assert('isset($demoDataByModelClassName["Role"])');

            $user = new User();
            $user->username           = 'admin';
            $user->title->value       = 'Sir';
            $user->firstName          = 'Jason';
            $user->lastName           = 'Blue';
            $this->populateModel($user);
            $saved = $user->save();
            assert('$saved');
            $demoDataByModelClassName["Role"][0]->users->add($user);
            $saved = $demoDataByModelClassName["Role"][0]->save();
            assert('$saved');
            $demoDataByModelClassName['User'][] = $user;

            foreach (array('jim'   => 'Mr',
                           'john'  => 'Mr',
                           'sally' => 'Dr',
                           'mary'  => 'Mrs',
                           'katie' => 'Ms',
                           'jill'  => 'Ms',
                           'sam'   => 'Mr') as $username => $title)
            {
                $user = new User();
                $user->username           = $username;
                $user->title->value       = $title;
                $user->firstName          = ucfirst($username);
                $user->lastName           = 'Smith';
                $this->populateModel($user);
                $saved = $user->save();
                assert('$saved');
                $demoDataByModelClassName["Role"][1]->users->add($user);
                $saved = $demoDataByModelClassName["Role"][1]->save();
                assert('$saved');
                $demoDataByModelClassName['User'][] = $user;
            }
        }

        public function populateModel(& $model)
        {
            assert('$model instanceof User');
            parent::populateModel($model);
            static::resolveModelAttributeValue($model, 'language', Yii::app()->language);
            static::resolveModelAttributeValue($model, 'timeZone', Yii::app()->timeZoneHelper->getGlobalValue());
            $currencies = Currency::getAll();
            static::resolveModelAttributeValue($model, 'currency', $currencies[0]);
            static::resolveModelAttributeValue($model, 'username', strtolower($user->firstName . $user->lastName));
            assert('$username != null');
            $model->setPassword($model->username);
        }

        public function setQuantity($quantity)
        {
            throw notImplementedException();
        }
    }
?>