<?php
    /**
     * Class that builds demo opportunities.
     */
    class OpportunitiesDemoDataMaker extends DemoDataMaker
    {
        protected $quantity = 20;

        public static function getDependencies()
        {
            return array('contacts');
        }

        public function makeAll(& $demoDataByModelClassName)
        {
            assert('is_array($demoDataByModelClassName)');
            assert('isset($demoDataByModelClassName["Currency"])');
            assert('isset($demoDataByModelClassName["User"])');
            assert('isset($demoDataByModelClassName["Account"])');
            assert('isset($demoDataByModelClassName["Contact"])');

            for ($i = 0; $i < $this->quantity; $i++)
            {
                $opportunity = new Opportunity();
                $opportunity->owner = RandomDataUtil::getRandomValueFromArray($demoDataByModelClassName['User']);
                $opportunity->contacts->add(RandomDataUtil::
                                            getRandomValueFromArray($demoDataByModelClassName["Contact"]));
                $opportunity->account = $opportunity->contacts[0]->account;
                $currencyValue = new CurrencyValue();
                $currencyValue->currency = RandomDataUtil::
                                            getRandomValueFromArray($demoDataByModelClassName["Currency"]);
                $opportunity->amount = $currencyValue;
                $this->populateModel($opportunity);
                $saved = $opportunity->saved();
                assert('$saved');
                $demoDataByModelClassName['Opportunity'][] = $opportunity;
            }
        }

        public function populateModel(& $model)
        {
            assert('$model instanceof Opportunity');
            assert('is_int($quantity)');
            $opportunityRandomData = ZurmoRandomDataUtil::
                                     getRandomDataByModuleAndModelClassNames('OpportunitiesModule', 'Account');

            parent::populateModel($model);
            $name = RandomDataUtil::getRandomValueFromArray($opportunityRandomData['names']);
            static::resolveModelAttributeValue($model, 'name', $name);
            $stage      = RandomDataUtil::getRandomValueFromArray(static::getCustomFieldDataByName('SalesStages'));
            $source     = RandomDataUtil::getRandomValueFromArray(static::getCustomFieldDataByName('LeadSources'));
            static::resolveModelAttributeValue($model->stage,  'value', $stage);
            static::resolveModelAttributeValue($model->source, 'value', $source);
            $futureTimeStamp = time() + (mt_rand(1,200) * 60 * 60 * 24);
            $closeDate = Yii::app()->dateFormatter->format(DatabaseCompatibilityUtil::getDateFormat(), $futureTimeStamp);
            static::resolveModelAttributeValue($model, 'closeDate',     $closeDate);
            static::resolveModelAttributeValue($model->amount, 'value', mt_rand(5, 350) * 1000);
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