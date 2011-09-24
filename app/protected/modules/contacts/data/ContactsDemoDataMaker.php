<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2011 Zurmo Inc.
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

        public function makeAll(& $demoDataHelper)
        {
            assert('$demoDataHelper instanceof DemoDataHelper');
            assert('$demoDataHelper->isSetRange("User")');
            assert('$demoDataHelper->isSetRange("Account")');

            $contactStates = ContactState::getAll();
            $statesBeginningWithStartingState = $this->getStatesBeforeOrStartingWithStartingState($contactStates);
            $contacts = array();
            for ($i = 0; $i < $this->resolveQuantityToLoad(); $i++)
            {
                $contact          = new Contact();
                $contact->account = $demoDataHelper->getRandomByModelName('Account');
                $contact->state   = RandomDataUtil::getRandomValueFromArray($statesBeginningWithStartingState);
                $contact->owner   = $contact->account->owner;
                $this->populateModel($contact);
                $saved = $contact->save();
                assert('$saved');
                $contacts[] = $contact->id;
            }
            $demoDataHelper->setRangeByModelName('Contact', $contacts[0], $contacts[count($contacts)-1]);
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
            if ($model->account->id > 0)
            {
                $domainName = static::makeDomainByName(strval($model->account));
            }
            elseif ($model->companyName != null)
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