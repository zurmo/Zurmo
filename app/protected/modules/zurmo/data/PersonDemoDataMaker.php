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
            $personRandomData        = ZurmoRandomDataUtil::
                                       getRandomDataByModuleAndModelClassNames('ZurmoModule', 'Person');
            $jobTitlesAndDepartments = RandomDataUtil::
                                       getRandomValueFromArray($personRandomData['jobTitlesAndDepartments']);
            $lastName                = RandomDataUtil::getRandomValueFromArray($personRandomData['lastNames']);
            if(RandomDataUtil::getRandomBooleanValue())
            {
                $model->firstName = RandomDataUtil::getRandomValueFromArray($personRandomData['femaleFirstNames']);
                $title = RandomDataUtil::getRandomValueFromArray($personRandomData['femaleTitles']);
            }
            else
            {
                $model->firstName = RandomDataUtil::getRandomValueFromArray($personRandomData['maleFirstNames']);
                $title = RandomDataUtil::getRandomValueFromArray($personRandomData['maleTitles']);
            }
            $model->title->value   = $title;
            $model->lastName       = $lastName;
            $model->jobTitle       = $jobTitlesAndDepartments[0];
            $model->department     = $jobTitlesAndDepartments[1];
            $model->officePhone    = RandomDataUtil::makeRandomPhoneNumber();
            $model->officeFax      = RandomDataUtil::makeRandomPhoneNumber();
            $model->mobilePhone    = RandomDataUtil::makeRandomPhoneNumber();
            $model->primaryEmail   = static::makeEmailAddressByPerson($model);
            $model->primaryAddress = ZurmoRandomDataUtil::makeRandomAddress();
        }

        protected static function makeEmailAddressByPerson(& $model)
        {
            $email = new Email();
            $email->emailAddress = $model->firstName . '.' . $model->lastName . '@company.com';
            return $email;
        }
    }
?>