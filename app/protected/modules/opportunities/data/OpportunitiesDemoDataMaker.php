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

        public function makeAll(& $demoDataByModelClassName)
        {
            assert('is_array($demoDataByModelClassName)');
            assert('isset($demoDataByModelClassName["Currency"])');
            assert('isset($demoDataByModelClassName["User"])');
            assert('isset($demoDataByModelClassName["Account"])');
            assert('isset($demoDataByModelClassName["Contact"])');

            for ($i = 0; $i < $this->resolveQuantityToLoad(); $i++)
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
                $saved = $opportunity->save();
                assert('$saved');
                $demoDataByModelClassName['Opportunity'][] = $opportunity;
            }
        }

        public function populateModel(& $model)
        {
            assert('$model instanceof Opportunity');
            $opportunityRandomData = ZurmoRandomDataUtil::
                                     getRandomDataByModuleAndModelClassNames('OpportunitiesModule', 'Opportunity');

            parent::populateModel($model);
            $name        = RandomDataUtil::getRandomValueFromArray($opportunityRandomData['names']);
            $model->name = $name;
            $stage       = RandomDataUtil::getRandomValueFromArray(static::getCustomFieldDataByName('SalesStages'));
            $source      = RandomDataUtil::getRandomValueFromArray(static::getCustomFieldDataByName('LeadSources'));
            $model->stage->value  = $stage;
            $model->source->value = $source;
            $futureTimeStamp      = time() + (mt_rand(1,200) * 60 * 60 * 24);
            $closeDate            = Yii::app()->dateFormatter->format(
                                    DatabaseCompatibilityUtil::getDateFormat(), $futureTimeStamp);
            $model->closeDate     = $closeDate;
            $model->amount->value = mt_rand(5, 350) * 1000;
        }
    }
?>