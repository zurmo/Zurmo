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
     * Class to help the notifications module understand the logic for specific notifications
     * it processes and creates.
     */
    abstract class NotificationRules
    {
        /**
         * Sets to true during @see NotificationRules::getUsers();
         * @var boolean
         */
        protected $usersLoaded = false;

        /**
         * Users to send the notification too
         * @var array
         */
        protected $users       = array();

        /**
         * Defines whether a job is considered critical.  Critical jobs that fail will create
         * email alerts immediately to certain users, usually admins.
         * @var boolean
         */
        protected $critical    = false;

        /**
        * Defines whether multiple notifications by type for a single owner can be created.
        * @var boolean
        */
        protected $allowDuplicates    = false;

        /**
         * @returns Translated label that describes this rule type.
         */
        public static function getDisplayName()
        {
            throw new NotImplementedException();
        }

        /**
         * @return true/false whether to allow multiple notifications by type for a single owner to be
         * created.
         */
        public function allowDuplicates()
        {
            return $this->allowDuplicates;
        }

        /**
        * @param boolean $allowDuplicates
        */
        public function setAllowDuplicates($allowDuplicates)
        {
            assert('is_bool($allowDuplicates)');
            $this->allowDuplicates = $allowDuplicates;
        }

        /**
         * @return true/false whether the notification is considered critical, in which case an Email
         * will be sent out in addition to the notification.
         */
        public function isCritical()
        {
            return $this->critical;
        }

        /**
         * Set the notification as being critical or not. This will override the default
         * setting for this particular NotificationRules
         * @param boolean $critical
         */
        public function setCritical($critical)
        {
            assert('is_bool($critical)');
            $this->critical = $critical;
        }

        /**
         * @return The type of the NotificationRules
         */
        public static function getType()
        {
            throw new NotImplementedException();
        }

        /**
         * @return array of users to receive a notification.
         */
        public function getUsers()
        {
            if (!$this->usersLoaded)
            {
                $this->loadUsers();
                $this->usersLoaded = true;
            }
            return $this->users;
        }

        /**
         * Add a user to receive a notification.
         * @param User $user
         */
        public function addUser(User $user)
        {
            assert('$user->id > 0');
            if (!isset($this->users[$user->id]))
            {
                $this->users[$user->id] = $user;
            }
        }

        /**
         * Loads users to notify. Override in child class if needed.
         */
        protected function loadUsers()
        {
        }
    }
?>