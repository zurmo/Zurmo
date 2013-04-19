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
     * Form to help manage marketing list member display options supporting filtering by subscription type.
     */
    class MarketingListMembersConfigurationForm extends CFormModel
    {
        /**
         * Value to be used to signal that the filtering is for all subscriptions and not a specific one.
         * @var string
         */
        const  FILTERED_USER_ALL = 'all';

        /**
         * Value to be used to signal that the filtering is for just subscribers.
         * @var string
         */
        const  FILTER_USER_SUBSCRIBERS = 'subscribers';

        /**
         * Value to be used to signal that the filtering is for just unsubscribers
         * @var string
         */
        const FILTER_USER_UNSUBSCRIBERS = 'unsubscribers';

        /**
         * Whether to filter marketing list member feed by subscription type.
         * Defaults to not filtering on anything
         * @var string
         */
        public $filteredBySubscriptionType = self::FILTERED_USER_ALL;

        /**
         * Search criteria, could be name or email to filter the marketing list member feed.
         */
        public $filteredBySearchTerm = null;

        public function rules()
        {
            return array(
                array('filteredBySubscriptionType', 'type',    'type' => 'string'),
                array('filteredBySearchTerm',       'type',    'type' => 'string'),
            );
        }
    }
?>