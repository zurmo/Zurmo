<?php
    /**
     * Class that builds demo opportunities.
     */
    class OpportunitiesDemoDataMaker extends DemoDataMaker
    {
        protected $ratioToLoad = 2;

        public static function getDependencies()
        {
            return array('contacts');
        }

        public function makeAll(& $demoDataHelper)
        {
            assert('$demoDataHelper instanceof DemoDataHelper');
            assert('$demoDataHelper->isSetRange("Currency")');
            assert('$demoDataHelper->isSetRange("User")');
            assert('$demoDataHelper->isSetRange("Account")');
            assert('$demoDataHelper->isSetRange("Contact")');

            $opportunities = array();
            for ($i = 0; $i < $this->resolveQuantityToLoad(); $i++)
            {
                $opportunity = new Opportunity();
                $opportunity->contacts->add($demoDataHelper->getRandomByModelName('Contact'));
                $opportunity->account = $opportunity->contacts[0]->account;
                $opportunity->owner   = $opportunity->contacts[0]->owner;
                $currencyValue = new CurrencyValue();
                $currencyValue->currency   = $demoDataHelper->getRandomByModelName('Currency');
                $opportunity->amount = $currencyValue;
                $this->populateModel($opportunity);
                $saved = $opportunity->save();
                assert('$saved');
                $opportunities[] = $opportunity->id;
            }
            $demoDataHelper->setRangeByModelName('Opportunity', $opportunities[0], $opportunities[count($opportunities)-1]);
        }

        public function populateModel(& $model)
        {
            assert('$model instanceof Opportunity');
            $opportunityRandomData = ZurmoRandomDataUtil::
                                     getRandomDataByModuleAndModelClassNames('OpportunitiesModule', 'Opportunity');

            parent::populateModel($model);
            $name        = RandomDataUtil::getRandomValueFromArray($opportunityRandomData['names']);
            $model->name = $name;
            $source    = RandomDataUtil::getRandomValueFromArray(static::getCustomFieldDataByName('SalesStages'));
            $stage     = RandomDataUtil::getRandomValueFromArray(static::getCustomFieldDataByName('LeadSources'));
            $model->stage->value  = $stage;
            $model->source->value = $source;
            $futureTimeStamp      = time() + (mt_rand(1, 200) * 60 * 60 * 24);
            $closeDate            = Yii::app()->dateFormatter->format(
                                    DatabaseCompatibilityUtil::getDateFormat(), $futureTimeStamp);
            $model->closeDate     = $closeDate;
            $model->amount->value = mt_rand(5, 350) * 1000;
        }
    }
?>