<?php
    /**
     * Class that builds demo accounts.
     */
    class AccountsDemoDataMaker extends DemoDataMaker
    {
        protected $quantity = 20;

        public static function getDependencies()
        {
            return array('users');
        }

        public function makeAll(& $demoDataByModelClassName)
        {
            assert('is_array($demoDataByModelClassName)');
            assert('isset($demoDataByModelClassName["User"])');
            assert('$this->quantity < 123'); //our random seeder only supports 123 at the moment
            for ($i = 0; $i < $this->quantity; $i++)
            {
                $account = new Account();
                $account->owner = RandomDataUtil::getRandomValueFromArray($demoDataByModelClassName['User']);
                $this->populateModel($account);
                $saved = $account->saved();
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
            static::resolveModelAttributeValue($model, 'name', $name);

            $domainName = static::makeDomainByName(strval($model));
            $type     = RandomDataUtil::getRandomValueFromArray(static::getCustomFieldDataByName('AccountTypes'));
            $industry   = RandomDataUtil::getRandomValueFromArray(static::getCustomFieldDataByName('Industries'));

            static::resolveModelAttributeValue($model,                   'website',
                                                                         static::makeUrlByDomainName($domainName));
            static::resolveModelAttributeValue($model->type,             'value', $type);
            static::resolveModelAttributeValue($model->industry,         'value', $industry);
            static::resolveModelAttributeValue($model, 'officePhone',    RandomDataUtil::makeRandomPhoneNumber());
            static::resolveModelAttributeValue($model, 'officeFax',      RandomDataUtil::makeRandomPhoneNumber());
            static::resolveModelAttributeValue($model, 'primaryEmail',   static::makeEmailAddressByAccount($model));
            static::resolveModelAttributeValue($model, 'billingAddress', ZurmoRandomDataUtil::makeRandomAddress());
            static::resolveModelAttributeValue($model, 'employees',      mt_rand(1, 95) * 10);
            static::resolveModelAttributeValue($model, 'annualRevenue',  mt_rand(1, 780) * 1000000);
        }

        public function setQuantity($quantity)
        {
            assert('is_int($quantity)');
            throw notImplementedException();

        }

        protected static function makeEmailAddressByAccount(& $model)
        {
            assert('$model instanceof Account');
            $emailAddress = new EmailAddress();
            $emailAddress->emailAddress = 'info@' . static::resolveDomainName(strval($model));
            return $emailAddress;
        }
    }
?>