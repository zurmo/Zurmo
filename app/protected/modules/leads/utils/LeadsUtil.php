<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
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
     * You can contact Zurmo, Inc. with a mailing address at 27 North Wacker Drive
     * Suite 370 Chicago, IL 60606. or at email address contact@zurmo.com.
     *
     * The interactive user interfaces in original and modified versions
     * of this program must display Appropriate Legal Notices, as required under
     * Section 5 of the GNU General Public License version 3.
     *
     * In accordance with Section 7(b) of the GNU General Public License version 3,
     * these Appropriate Legal Notices must retain the display of the Zurmo
     * logo and Zurmo copyright notice. If the display of the logo is not reasonably
     * feasible for technical reasons, the Appropriate Legal Notices must display the words
     * "Copyright Zurmo Inc. 2013. All rights reserved".
     ********************************************************************************/

    /**
     * Helper class with functions
     * to assist in working with Leads module
     * information
     */
    class LeadsUtil
    {
        /**
         * Given a contact and an account, use the mapping in the
         * Leads Module to copy attributes from contact to Account
         * order number is.
         * @param $contact Contact model
         * @param $account Account model
         * @return Account, with mapped attributes from Contact
         */
        public static function attributesToAccount(Contact $contact, Account $account)
        {
            assert('!empty($contact->id)');
            $metadata = LeadsModule::getMetadata();
            $map = $metadata['global']['convertToAccountAttributesMapping'];
            foreach ($map as $contactAttributeName => $accountAttributeName)
            {
                $account->$accountAttributeName = $contact->$contactAttributeName;
            }
            return $account;
        }

        /**
         * Given a post data array, map the lead to account attributes
         * but only if the post data does not contain a set attribute.
         * This method is used when a posted form has an empty value on
         * an input field.  We do not want to set the mapped field since
         * the use of setAttributes will pick up the correct information
         * from the posted data.  This will allow form validation to work
         * properly in the case where a mapped field is cleared to blank
         * in the input field and submitted. Such an event should trigger
         * a form validation error.
         * @see LeadsUtil::attributesToAccount
         * @param $contact Contact model
         * @param $account Account model
         * @param $postData array of posted form data
         * @return Account, with mapped attributes from Contact
         */
        public static function attributesToAccountWithNoPostData(Contact $contact, Account $account, array $postData)
        {
            assert('is_array($postData)');
            assert('!empty($contact->id)');
            $metadata = LeadsModule::getMetadata();
            $map = $metadata['global']['convertToAccountAttributesMapping'];
            foreach ($map as $contactAttributeName => $accountAttributeName)
            {
                if (!isset($postData[$accountAttributeName]))
                {
                    $account->$accountAttributeName = $contact->$contactAttributeName;
                }
            }
            return $account;
        }

        /**
         * If no states exist, throws MissingContactsStartingStateException
         * @return ContactState object
         */
        public static function getStartingState()
        {
            $states = ContactState::getAll('order');
            if (count($states) == 0)
            {
                throw new MissingContactsStartingStateException();
            }
            return $states[0];
        }

        /**
         * Get an array of only the states from the starting state onwards, order/name pairings of the
         * existing lead states ordered by order.
         * @return array
         */
        public static function getLeadStateDataFromStartingStateOnAndKeyedById()
        {
            $leadStatesData = array();
            $states            = ContactState::getAll('order');
            $startingState     = ContactsUtil::getStartingStateId();
            foreach ($states as $state)
            {
                if ($startingState == $state->id)
                {
                    break;
                }
                $leadStatesData[$state->id] = $state->name;
            }
            return $leadStatesData;
        }

        /**
         * Get an array of only the states from the starting state onwards, order/translated label pairings of the
         * existing lead states ordered by order.
         * @return array
         */
        public static function getLeadStateDataFromStartingStateKeyedByIdAndLabelByLanguage($language)
        {
            assert('is_string($language)');
            $leadStatesData = array();
            $states            = ContactState::getAll('order');
            $startingState     = ContactsUtil::getStartingStateId();
            foreach ($states as $state)
            {
                if ($startingState == $state->id)
                {
                    break;
                }
                $leadStatesData[$state->id] = ContactsUtil::resolveStateLabelByLanguage($state, $language);
            }
            return $leadStatesData;
        }

        /**
         * Get an array of states from the starting state onwards, id/translated label pairings of the
         * existing contact states ordered by order.
         * @return array
         */
        public static function getLeadStateDataFromStartingStateLabelByLanguage($language)
        {
            assert('is_string($language)');
            $leadStatesData = array();
            $states            = ContactState::getAll('order');
            $startingState     = ContactsUtil::getStartingStateId();
            foreach ($states as $state)
            {
                if ($startingState == $state->id)
                {
                    break;
                }
                $state->name = ContactsUtil::resolveStateLabelByLanguage($state, $language);
                $leadStatesData[] = $state;
            }
            return $leadStatesData;
        }

        public static function isStateALead(ContactState $state)
        {
            assert('$state->id > 0');
            $leadStatesData = self::getLeadStateDataFromStartingStateOnAndKeyedById();
            if (isset($leadStatesData[$state->id]))
            {
                return true;
            }
            else
            {
                return false;
            }
        }

        public static function isStateALeadByStateName($stateName)
        {
            assert('is_string($stateName)');
            $leadStatesData = self::getLeadStateDataFromStartingStateOnAndKeyedById();
            foreach ($leadStatesData as $leadStateName)
            {
                if ($stateName == $leadStateName)
                {
                    return true;
                }
            }
            return false;
        }
    }
?>