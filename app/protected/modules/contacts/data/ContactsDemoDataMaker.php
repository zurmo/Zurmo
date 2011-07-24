<?php
    /**
     * Class that builds demo contacts.
     */
    Yii::import('application.modules.zurmo.data.PersonDemoDataMaker');
    class ContactsDemoDataMaker extends PersonDemoDataMaker
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
            assert('isset($demoDataByModelClassName["Account"])');

            $demoDataByModelClassName['ContactState'] = ContactState::getAll();
            $statesBeginningWithStartingState = $this->getStatesBeforeOrStartingWithStartingState(
                                                    $demoDataByModelClassName['ContactState']);
            for ($i = 0; $i < $this->resolveQuantityToLoad(); $i++)
            {
                $contact          = new Contact();
                $contact->owner   = RandomDataUtil::getRandomValueFromArray($demoDataByModelClassName['User']);
                $contact->account = RandomDataUtil::
                                        getRandomValueFromArray($demoDataByModelClassName["Account"]);
                $contact->state   = RandomDataUtil::getRandomValueFromArray($statesBeginningWithStartingState);
                $this->populateModel($contact);
                $saved = $contact->save();
                assert('$saved');
                $demoDataByModelClassName['Contact'][] = $contact;
            }
        }

        public function populateModel(& $model)
        {
            assert('$model instanceof Contact');
            parent::populateModel($model);
            $domainName = static::resolveDomainName($model);
            $source     = RandomDataUtil::getRandomValueFromArray(static::getCustomFieldDataByName('LeadSources'));
            $industry   = RandomDataUtil::getRandomValueFromArray(static::getCustomFieldDataByName('Industries'));

            $model->website          = static::makeUrlByDomainName($domainName);
            $model->source->value    = $source;
            $model->industry->value  = $industry;
        }

        protected static function makeEmailAddressByPerson(& $model)
        {
            assert('$model instanceof Contact');

            $email = new Email();
            $email->emailAddress = $model->firstName . '.' . $model->lastName . '@' . static::resolveDomainName($model);
            return $email;
        }

        protected static function resolveDomainName(& $model)
        {
            assert('$model instanceof Contact');
            if($model->account->id > 0)
            {
                $domainName = static::makeDomainByName(strval($model->account));
            }
            elseif($model->companyName != null)
            {
                $domainName = static::makeDomainByName($model->companyName);
            }
            else
            {
                $domainName = 'company.com';
            }
            return $domainName;
        }

        public static function getStatesBeforeOrStartingWithStartingState($states)
        {
            assert('is_array($states)');
            $startingStateOrder = ContactsUtil::getStartingStateOrder($states);
            $statesAfterStartingState = array();
            foreach ($states as $state)
            {
                if (static::shouldIncludeState($state->order, $startingStateOrder))
                {
                    $statesAfterStartingState[] = $state;
                }
            }
            return $statesAfterStartingState;
        }

        protected static function shouldIncludeState($stateOrder, $startingStateOrder)
        {
            return $stateOrder >= $startingStateOrder;
        }
    }
?>