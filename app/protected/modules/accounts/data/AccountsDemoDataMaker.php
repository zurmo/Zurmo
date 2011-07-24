<?php
    /**
     * Class that builds demo accounts.
     */
    class AccountsDemoDataMaker extends DemoDataMaker
    {
        protected $ratioToLoad = 1;

        public static function getDependencies()
        {
            return array('users');
        }

        public function makeAll(& $demoDataByModelClassName)
        {
            assert('is_array($demoDataByModelClassName)');
            assert('isset($demoDataByModelClassName["User"])');
            for ($i = 0; $i < $this->resolveQuantityToLoad(); $i++)
            {
                $account = new Account();
                $account->owner = RandomDataUtil::getRandomValueFromArray($demoDataByModelClassName['User']);
                $this->populateModel($account);
                $saved = $account->save();
                assert('$saved');
                $demoDataByModelClassName['Account'][] = $account;
            }
        }

        public function populateModel(& $model)
        {
            assert('$model instanceof Account');
            parent::populateModel($model);
            $accountRandomData = ZurmoRandomDataUtil::getRandomDataByModuleAndModelClassNames('AccountsModule', 'Account');
            $name = RandomDataUtil::getRandomValueFromArray($accountRandomData['names']);
            $model->name = $name;

            $domainName = static::makeDomainByName(strval($model));
            $type       = RandomDataUtil::getRandomValueFromArray(static::getCustomFieldDataByName('AccountTypes'));
            $industry   = RandomDataUtil::getRandomValueFromArray(static::getCustomFieldDataByName('Industries'));

            $model->website         = static::makeUrlByDomainName($domainName);
            $model->type->value     =  $type;
            $model->industry->value = $industry;
            $model->officePhone     = RandomDataUtil::makeRandomPhoneNumber();
            $model->officeFax       = RandomDataUtil::makeRandomPhoneNumber();
            $model->primaryEmail    = static::makeEmailAddressByAccount($model);
            $model->billingAddress  = ZurmoRandomDataUtil::makeRandomAddress();
            $model->employees       = mt_rand(1, 95) * 10;
            $model->annualRevenue   = mt_rand(1, 780) * 1000000;
        }

        protected static function makeEmailAddressByAccount(& $model)
        {
            assert('$model instanceof Account');
            $email = new Email();
            $email->emailAddress = 'info@' . static::makeDomainByName(strval($model));
            return $email;
        }
    }
?>