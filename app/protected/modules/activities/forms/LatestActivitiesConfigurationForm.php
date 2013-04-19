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
     * Form to help manage latest activity display options including filtering by model type, view type being a list
     * or a summary, and also if the data should roll up or not from related models.
     */
    class LatestActivitiesConfigurationForm extends CFormModel
    {
        /**
         * Value to be used to signal that the filtering is for all models and not a specific one.
         * @see LatestActivitiesMashableFilterRadioElement
         * @var string
         */
        const  FILTERED_BY_ALL = 'all';

        /**
         * Value to be used to signal that the ownership filter is for all activities.
         * @see LatestActivitiesMashableFilterRadioElement
         * @var string
         */
        const  OWNED_BY_FILTER_ALL = 'all';

        /**
         * Value to be used to signal that the ownership filter is for only activities owned by the user.
         * @see LatestActivitiesMashableFilterRadioElement
         * @var string
         */
        const OWNED_BY_FILTER_USER = 'user';

        /**
         * Should the latest activity feed rollup data beyond just the related model.
         * @var boolean
         */
        public $rollup;

        /**
         * Whether to filter latest activity feed by ownership for the current user.  Can also be a user id that is an integer.
         * Defaults to not filtering on anything, thus showing all available models that implement the
         * MashableActivityInterface.
         * @var string
         */
        public $ownedByFilter = self::OWNED_BY_FILTER_ALL;

        /**
         * What model to filter by if any for the latest activity feed.  Defaults to not filtering on anything, thus
         * showing all available models that implement the MashableActivityInterface.
         * @var string
         */
        public $filteredByModelName = self::FILTERED_BY_ALL;

        /**
         * Filtered by models that implement the MashableActivityInterface and by what models the current user has
         * rights to see, this array contains the model class names as the indexes and the translated model labels
         * as the values.
         * @var array
         */
        public $mashableModelClassNamesAndDisplayLabels;

        public function rules()
        {
            return array(
                array('filteredByModelName', 'type',    'type' => 'string'),
                array('rollup',              'boolean'),
                array('ownedByFilter',       'type',    'type' => 'string')
            );
        }
    }
?>