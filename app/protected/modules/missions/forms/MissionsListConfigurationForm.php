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
     * Form to help manage the missions list display options which currently only includes type.
     * @see MissionsSearchDataProviderMetadataAdapter for available types
     */
    class MissionsListConfigurationForm extends CFormModel
    {
        /**
         * Filter by missions the current user created.
         * @var integer
         */
        const LIST_TYPE_CREATED = 1;

        /**
         * Filter by missions that are not taken
         * @var integer
         */
        const LIST_TYPE_AVAILABLE = 2;

        /**
         * Filter by missions that are taken by the current user but not accepted yet.
         * @var integer
         */
        const LIST_TYPE_MINE_TAKEN_BUT_NOT_ACCEPTED = 3;

        public $type;

        public function rules()
        {
            return array(
                array('type', 'type',    'type' => 'integer'),
            );
        }

        public static function getTypesAndLabels()
        {
            return array(self::LIST_TYPE_CREATED                     => self::getListTypeCreatedLabel(),
                         self::LIST_TYPE_AVAILABLE                   => self::getListTypeAvailableLabel(),
                         self::LIST_TYPE_MINE_TAKEN_BUT_NOT_ACCEPTED => self::getListTypeMineTakenButNotAcceptedLabel());
        }

        public static function getListTypeCreatedLabel()
        {
            return Yii::t('Default', 'Created');
        }

        public static function getListTypeAvailableLabel()
        {
            return Yii::t('Default', 'Available');
        }

        public static function getListTypeMineTakenButNotAcceptedLabel()
        {
            return Yii::t('Default', 'My Missions');
        }
    }
?>