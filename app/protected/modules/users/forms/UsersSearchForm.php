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

    class UsersSearchForm extends SearchForm
    {
        public $anyCity;
        public $anyStreet;
        public $anyState;
        public $anyPostalCode;
        public $anyCountry;
        public $anyEmail;
        public $anyInvalidEmail;
        public $anyOptOutEmail;

        public function rules()
        {
            return array(
                array('anyCity', 'safe'),
                array('anyStreet', 'safe'),
                array('anyState', 'safe'),
                array('anyPostalCode', 'safe'),
                array('anyCountry', 'safe'),
                array('anyEmail', 'safe'),
                array('anyInvalidEmail', 'boolean'),
                array('anyOptOutEmail', 'boolean'),
            );
        }

        public function attributeLabels()
        {
            return array(
                'anyCity'            => Yii::t('Default', 'Any City'),
                'anyStreet'          => Yii::t('Default', 'Any Street'),
                'anyState'           => Yii::t('Default', 'Any State'),
                'anyPostalCode'      => Yii::t('Default', 'Any Postal Code'),
                'anyCountry'         => Yii::t('Default', 'Any Country'),
                'anyEmail'           => Yii::t('Default', 'Any Email Address'),
                'anyInvalidEmail'    => Yii::t('Default', 'Any Invalid Email'),
                'anyOptOutEmail'     => Yii::t('Default', 'Any Opted Out Email'),
            );
        }

        public function resolveAttributesMappedToRealAttributesMetadata()
        {
            return null;
        }
    }
?>