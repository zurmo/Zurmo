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
     * Class helps interaction between the user interface, forms, and controllers that are involved in setting
     * the user status to active or inactive. An inactive user is a user who cannot login to the application via
     * the the normal login, mobile, or web-api. If a user is inactive, they will recieve explict permissions
     * to DENY those login rights. Upon changing the user to active, the explict permissions will be removed and the
     * rights will be controlled via the normal role/group hierarchy.
     * @see DerivedUserStatusElement
     * @see UserStatusUtil
     */
    class UserStatus
    {
        /**
         * True if the user has an active status.
         * @var boolean
         */
        private $active = true;

        /**
         * Return true if the status is active
         */
        public function isActive()
        {
            return $this->active;
        }

        public function setActive()
        {
            $this->active = true;
        }

        public function setInactive()
        {
            $this->active = false;
        }
    }
?>