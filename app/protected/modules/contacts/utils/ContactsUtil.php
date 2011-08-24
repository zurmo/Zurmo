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
     * to assist in working with Contacts module
     * information
     */
    class ContactsUtil
    {
        /**
         * Given an array of states, determine what the startingState
         * order number is.
         * @return int order
         */
        public static function getStartingStateOrder(array $states)
        {
            $metadata = ContactsModule::getMetadata();
            $startingState = $metadata['global']['startingStateId'];
            $startingStateOrder = 0;
            foreach ($states as $state)
            {
                if ($state->id == $startingState)
                {
                    $startingStateOrder = $state->order;
                    break;
                }
            }
            return $startingStateOrder;
        }

        /**
         * @return ContactState object
         */
        public static function getStartingState()
        {
            $metadata = ContactsModule::getMetadata();
            return ContactState::getById($metadata['global']['startingStateId']);
        }

        /**
         * @return integer Id
         */
        public static function getStartingStateId()
        {
            $metadata = ContactsModule::getMetadata();
            return $metadata['global']['startingStateId'];
        }

        /**
         * Get an array of order/name pairings of the existing contact states ordered by order.
         * @return array
         */
        public static function getContactStateDataKeyedByOrder()
        {
            $contactStatesData = array();
            $states = ContactState::getAll('order');
            foreach ($states as $state)
            {
                $contactStatesData[$state->order] = $state->name;
            }
            return $contactStatesData;
        }

        /**
         * Get an array of order/name pairings of the existing contact states ordered by order.
         * @return array
         */
        public static function getContactStateDataKeyedById()
        {
            $contactStatesData = array();
            $states = ContactState::getAll('order');
            foreach ($states as $state)
            {
                $contactStatesData[$state->id] = $state->name;
            }
            return $contactStatesData;
        }

        /**
         * Get an array of only the states from the starting state onwards, id/name pairings of the
         * existing contact states ordered by order.
         * @return array
         */
        public static function getContactStateDataFromStartingStateOnAndKeyedById()
        {
            $contactStatesData = array();
            $states            = ContactState::getAll('order');
            $startingState     = self::getStartingStateId();
            $includeState      = false;
            foreach ($states as $state)
            {
                if ($startingState == $state->id || $includeState)
                {
                    if ($startingState == $state->id)
                    {
                        $includeState = true;
                    }
                    $contactStatesData[$state->id] = $state->name;
                }
            }
            return $contactStatesData;
        }

        public static function setStartingStateById($startingStateId)
        {
            assert('is_int($startingStateId)');
            $metadata = ContactsModule::getMetadata();
            $metadata['global']['startingStateId'] = $startingStateId;
            ContactsModule::setMetadata($metadata);
        }

        public static function setStartingStateByOrder($startingStateOrder)
        {
            $states = ContactState::getAll('order');
            foreach ($states as $order => $state)
            {
                if ($startingStateOrder == $state->order)
                {
                    self::setStartingStateById($state->id);
                    return;
                }
            }
            throw new NotSupportedException();
        }

        /**
         * Given two module class names and a user, resolve based on the user's access what if any adapter should
         * be utilized.  If the user has access to both modules, then return null. If the user has access to none
         * of the modules, then return false. Otherwise return a string with the name of the appropriate adapter
         * to use.
         * @param string $moduleClassNameFirstStates
         * @param string $moduleClassNameLaterStates
         * @param object $user User model
         */
        public static function resolveContactStateAdapterByModulesUserHasAccessTo(  $moduleClassNameFirstStates,
                                                                                    $moduleClassNameLaterStates,
                                                                                    $user)
        {
            assert('is_string($moduleClassNameFirstStates)');
            assert('is_string($moduleClassNameLaterStates)');
            assert('$user instanceof User && $user->id > 0');
            $canAccessFirstStatesModule  = RightsUtil::canUserAccessModule($moduleClassNameFirstStates, $user);
            $canAccessLaterStatesModule = RightsUtil::canUserAccessModule($moduleClassNameLaterStates, $user);
            if ($canAccessFirstStatesModule && $canAccessLaterStatesModule)
            {
                return null;
            }
            elseif (!$canAccessFirstStatesModule && $canAccessLaterStatesModule)
            {
                $prefix = substr($moduleClassNameLaterStates, 0, strlen($moduleClassNameLaterStates) - strlen('Module'));
                return $prefix . 'StateMetadataAdapter';
            }
            elseif ($canAccessFirstStatesModule && !$canAccessLaterStatesModule)
            {
                $prefix = substr($moduleClassNameFirstStates, 0, strlen($moduleClassNameFirstStates) - strlen('Module'));
                return $prefix . 'StateMetadataAdapter';
            }
            else
            {
                return false;
            }
        }
    }
?>