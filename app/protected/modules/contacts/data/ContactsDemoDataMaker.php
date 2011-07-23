<?php
    /**
     * Class that builds demo users.
     */
    class ContactsDemoDataMaker extends PersonDemoDataMaker
    {
        protected $quantity = 20;

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
            for ($i = 0; $i < $this->quantity; $i++)
            {
                $contact = new Contact();
                $contact->account = RandomDataUtil::
                                        getRandomValueFromArray($demoDataByModelClassName["accounts"]);
                $state = RandomDataUtil::getRandomValueFromArray($demoDataByModelClassName['ContactState']);
                static::resolveModelAttributeValue($model, 'state', $state);

                $this->populateModel($contact);
                $saved = $contact->saved();
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

            static::resolveModelAttributeValue($model, 'website', static::makeUrlByDomainName($domainName));
            static::resolveModelAttributeValue($model->source, 'value', $source);
            static::resolveModelAttributeValue($model->industry, 'value', $industry);
        }

        public function setQuantity($quantity)
        {
            assert('is_int($quantity)');
            throw notImplementedException();

        }

        protected static function makeEmailAddressByPerson(& $model)
        {
            assert('$model instanceof Contact');

            $emailAddress = new EmailAddress();
            $emailAddress->emailAddress = $model->firstName . '.' . $model->lastName . static::resolveDomainName($model);
            return $emailAddress;
        }

        protected static function resolveDomainName(& $model)
        {
            assert('$model instanceof Contact');
            if($model->account != null)
            {
                $domainName = static::makeDomainByName(strval($model->account));
            }
            elseif($model->companyName != null)
            {
                $domainName = static::makeDomainByName($model->companyName);
            }
            else
            {
                $domainName = '@company.com';
            }
            return $domainName;
        }
    }
?>