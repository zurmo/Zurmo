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
     * Extend this class when a searchForm should allow dynamic searches to be saved.
     */
    abstract class SavedDynamicSearchForm extends DynamicSearchForm
    {
        public $savedSearchId;

        public $savedSearchName;

        public $loadSavedSearchUrl;

        public static function getNonSearchableAttributes()
        {
            return array_merge(parent::getNonSearchableAttributes(), array('savedSearchId', 'savedSearchName', 'loadSavedSearchUrl'));
        }

        public function rules()
        {
            return array_merge(parent::rules(), array(
                               array('savedSearchId',      'safe'),
                               array('savedSearchName',    'safe'),
                               array('loadSavedSearchUrl', 'safe'),
                               array('savedSearchName',    'type',   'type' => 'string'),
                               array('savedSearchName',    'length', 'max'  => 64),
                               array('savedSearchName',    'validateSaveSearch', 'on' => 'validateSaveSearch'),
            ));
        }

        public function attributeLabels()
        {
            return array_merge(parent::attributeLabels(), array(
                               'savedSearchId'      => Zurmo::t('ZurmoModule',   'Id'),
                               'savedSearchName'    => Zurmo::t('ZurmoModule', 'Name'),
                               'loadSavedSearchUrl' => 'LoadSavedSearchUrl',
            ));
        }

        public function validateSaveSearch($attribute, $params)
        {
            if ($this->$attribute == null)
            {
                $this->addError('savedSearchName', Yii::t('yii', '{attribute} cannot be blank.',
                                                        array('{attribute}' => Zurmo::t('ZurmoModule', 'Name'))));
            }
        }
    }
?>