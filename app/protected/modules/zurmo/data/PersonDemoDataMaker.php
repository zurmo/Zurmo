<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2012 Zurmo Inc.
     *
     * Zurmo is free software; you can redistribute it and/or modify it under
     * the terms of the GNU General Public License version 3 as published by the
     * Free Software Foundation with the addition of the following permission added
     * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
     * IN WHICH THE COPYRIGHT IS OWNED BY ZURMO, ZURMO DISCLAIMS THE WARRANTY
     * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
     *
     * Zurmo is distributed in the hope that it will be useful, but WITHOUT
     * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
     * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
     * details.
     *
     * You should have received a copy of the GNU General Public License along with
     * this program; if not, see http://www.gnu.org/licenses or write to the Free
     * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
     * 02110-1301 USA.
     *
     * You can contact Zurmo, Inc. with a mailing address at 113 McHenry Road Suite 207,
     * Buffalo Grove, IL 60089, USA. or at email address contact@zurmo.com.
     ********************************************************************************/

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
            if ($model->firstName == null && RandomDataUtil::getRandomBooleanValue())
            {
                $model->firstName = RandomDataUtil::getRandomValueFromArray($personRandomData['femaleFirstNames']);
                $title = RandomDataUtil::getRandomValueFromArray($personRandomData['femaleTitles']);
            }
            elseif ($model->firstName == null)
            {
                $model->firstName = RandomDataUtil::getRandomValueFromArray($personRandomData['maleFirstNames']);
                $title = RandomDataUtil::getRandomValueFromArray($personRandomData['maleTitles']);
            }
            if ($model->lastName == null)
            {
                $model->lastName   = $lastName;
            }
            $model->title->value   = $title;
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