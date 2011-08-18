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
        public static function AttributesToAccount(Contact $contact, Account $account)
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
         * @see LeadsUtil::AttributesToAccount
         * @param $contact Contact model
         * @param $account Account model
         * @param $postData array of posted form data
         * @return Account, with mapped attributes from Contact
         */
        public static function AttributesToAccountWithNoPostData(Contact $contact, Account $account, array $postData)
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
    }
?>