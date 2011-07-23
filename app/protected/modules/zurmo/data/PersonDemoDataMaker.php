<?php
    /**
     * Modules that have models that extend or mixin the Person model should extend this class for creating demo data.
     */
    abstract class PersonDemoDataMaker extends DemoDataMaker
    {
        public function populateModel(& $model)
        {
            //todo: assert instanceof Person or mixes in Person.
            parent::populateModel($model);
            $personRandomData = ZurmoRandomDataUtil::getRandomDataByModuleAndModelClassNames('ZurmoModule', 'Person');

            $title = new Title();
            if(RandomDataUtil::getRandomBooleanValue())
            {
                static::resolveModelAttributeValue($model, 'firstName',
                            RandomDataUtil::getRandomValueFromArray($personRandomData['femaleFirstNames']));
                $title->value = RandomDataUtil::getRandomValueFromArray($personRandomData['femaleTitles']);
            }
            else
            {
                static::resolveModelAttributeValue($model, 'firstName',
                            RandomDataUtil::getRandomValueFromArray($personRandomData['maleFirstNames']));
                $title->value = RandomDataUtil::getRandomValueFromArray($personRandomData['maleTitles']);
            }
            static::resolveModelAttributeValue($model, 'title', $title);
            static::resolveModelAttributeValue($model, 'lastName',
                        RandomDataUtil::getRandomValueFromArray($personRandomData['lastNames']));
            $jobTitlesAndDepartments = RandomDataUtil::getRandomValueFromArray($personRandomData['jobTitlesAndDepartments']);
            static::resolveModelAttributeValue($model, 'jobTitles',       $jobTitlesAndDepartments[0]);
            static::resolveModelAttributeValue($model, 'departments',     $jobTitlesAndDepartments[1]);
            static::resolveModelAttributeValue($model, 'officePhone',     RandomDataUtil::makeRandomPhoneNumber());
            static::resolveModelAttributeValue($model, 'officeFax',       RandomDataUtil::makeRandomPhoneNumber());
            static::resolveModelAttributeValue($model, 'mobilePhone',     RandomDataUtil::makeRandomPhoneNumber());
            static::resolveModelAttributeValue($model, 'primaryEmail',    static::makeEmailAddressByPerson($model));
            static::resolveModelAttributeValue($model, 'primaryAddress',  ZurmoRandomDataUtil::makeRandomAddress());
        }

        protected static function makeEmailAddressByPerson(& $model)
        {
            $emailAddress = new EmailAddress();
            $emailAddress->emailAddress = $model->firstName . '.' . $model->lastName . '@company.com';
            return $emailAddress;
        }
    }
?>