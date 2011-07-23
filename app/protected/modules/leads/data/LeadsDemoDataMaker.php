<?php
    /**
     * Class that builds demo leads.
     */
    class LeadsDemoDataMaker extends ContactsDemoDataMaker
    {
        protected $quantity = 40;

        public static function getDependencies()
        {
            return array('contacts');
        }

        public function makeAll(& $demoDataByModelClassName)
        {
            assert('is_array($demoDataByModelClassName)');
            assert('isset($demoDataByModelClassName["User"])');

            $demoDataByModelClassName['ContactState'] = ContactState::getAll();
            $statesBeginningWithStartingState = $this->getStatesBeforeOrStartingWithStartingState(
                                                    $demoDataByModelClassName['ContactState']);
            for ($i = 0; $i < $this->quantity; $i++)
            {
                $contact          = new Contact();
                $contact->owner   = RandomDataUtil::getRandomValueFromArray($demoDataByModelClassName['User']);
                $state = RandomDataUtil::getRandomValueFromArray($statesBeginningWithStartingState);
                static::resolveModelAttributeValue($model, 'state', $state);

                $this->populateModel($contact);
                $saved = $contact->saved();
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
            static::resolveModelAttributeValue($model, 'companyName', $name);
        }

        public function setQuantity($quantity)
        {
            assert('is_int($quantity)');
            throw notImplementedException();

        }

        protected function shouldIncludeState($stateOrder, $startingStateOrder)
        {
            return $stateOrder < $startingStateOrder;
        }
    }
?>