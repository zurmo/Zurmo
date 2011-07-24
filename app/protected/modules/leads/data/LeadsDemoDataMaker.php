<?php
    /**
     * Class that builds demo leads.
     */
    class LeadsDemoDataMaker extends ContactsDemoDataMaker
    {
        protected $ratioToLoad = 2;

        public static function getDependencies()
        {
            return array('accounts');
        }

        public function makeAll(& $demoDataByModelClassName)
        {
            assert('is_array($demoDataByModelClassName)');
            assert('isset($demoDataByModelClassName["User"])');

            $demoDataByModelClassName['ContactState'] = ContactState::getAll();
            $statesBeginningWithStartingState = $this->getStatesBeforeOrStartingWithStartingState(
                                                    $demoDataByModelClassName['ContactState']);
            for ($i = 0; $i < $this->resolveQuantityToLoad(); $i++)
            {
                $contact          = new Contact();
                $contact->owner   = RandomDataUtil::getRandomValueFromArray($demoDataByModelClassName['User']);
                $contact->state   = RandomDataUtil::getRandomValueFromArray($statesBeginningWithStartingState);
                $this->populateModel($contact);
                $saved = $contact->save();
                assert('$saved');
                $demoDataByModelClassName['ContactsThatAreLeads'][] = $contact;
            }
        }

        public function populateModel(& $model)
        {
            assert('$model instanceof Contact');
            parent::populateModel($model);
            $accountRandomData = ZurmoRandomDataUtil::getRandomDataByModuleAndModelClassNames('AccountsModule', 'Account');
            $name = RandomDataUtil::getRandomValueFromArray($accountRandomData['names']);
            $model->companyName = $name;
        }

        protected static function shouldIncludeState($stateOrder, $startingStateOrder)
        {
            return $stateOrder < $startingStateOrder;
        }
    }
?>