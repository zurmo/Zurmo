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

        public function makeAll(& $demoDataHelper)
        {
            assert('$demoDataHelper instanceof DemoDataHelper');
            assert('$demoDataHelper->isSetRange("User")');

            $contactStates = ContactState::getAll();
            $statesBeginningWithStartingState = $this->getStatesBeforeOrStartingWithStartingState($contactStates);
            $contacts = array();
            for ($i = 0; $i < $this->resolveQuantityToLoad(); $i++)
            {
                $contact          = new Contact();
                $contact->owner   = $demoDataHelper->getRandomByModelName('User');
                $contact->state   = RandomDataUtil::getRandomValueFromArray($statesBeginningWithStartingState);
                $this->populateModel($contact);
                $saved = $contact->save();
                assert('$saved');
                $contacts[] = $contact;
            }
            //We can use dummy model name here ContactsThatAreLeads, so we can distinct between contacts are leads
            $demoDataHelper->setRangeByModelName('ContactsThatAreLeads', $contacts[0]->id, $contacts[count($contacts)-1]->id);
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