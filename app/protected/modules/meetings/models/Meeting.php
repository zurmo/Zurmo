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

    class Meeting extends MashableActivity
    {
        public function __toString()
        {
            try
            {
                if (trim($this->name) == '')
                {
                    return Zurmo::t('MeetingsModule', '(Unnamed)');
                }
                return $this->name;
            }
            catch (AccessDeniedSecurityException $e)
            {
                return '';
            }
        }

        /**
         * @return value of what is considered the 'call' type. It could be in the future named something else
         * or changed by the user.  This api will be expanded to handle that.  By default it will return 'Call'
         */
        public static function getCategoryCallValue()
        {
            return 'Call';
        }

        public static function getModuleClassName()
        {
            return 'MeetingsModule';
        }

        public static function canSaveMetadata()
        {
            return true;
        }

        public static function getDefaultMetadata()
        {
            $metadata = parent::getDefaultMetadata();
            $metadata[__CLASS__] = array(
                'members' => array(
                    'description',
                    'endDateTime',
                    'location',
                    'name',
                    'startDateTime',
                ),
                'rules' => array(
                    array('description',      'type', 'type' => 'string'),
                    array('endDateTime',      'type', 'type' => 'datetime'),
                    array('endDateTime',      'RedBeanModelCompareDateTimeValidator', 'type' => 'after',
                                              'compareAttribute' => 'startDateTime'),
                    array('location',         'type',    'type' => 'string'),
                    array('location',         'length',  'min'  => 3, 'max' => 64),
                    array('name',             'required'),
                    array('name',             'type',    'type' => 'string'),
                    array('name',             'length',  'min'  => 3, 'max' => 64),
                    array('startDateTime',    'required'),
                    array('startDateTime',    'type', 'type' => 'datetime'),
                    array('startDateTime',    'RedBeanModelCompareDateTimeValidator', 'type' => 'before',
                                              'compareAttribute' => 'endDateTime'),
                ),
                'relations' => array(
                    'category'             => array(RedBeanModel::HAS_ONE, 'OwnedCustomField', RedBeanModel::OWNED,
                                                    RedBeanModel::LINK_TYPE_SPECIFIC, 'category'),
                ),
                'elements' => array(
                    'endDateTime'   => 'DateTime',
                    'startDateTime' => 'DateTime',
                ),
                'customFields' => array(
                    'category'     => 'MeetingCategories',
                ),
                'defaultSortAttribute' => 'name',
                'noAudit' => array(
                    'description'
                ),
            );
            return $metadata;
        }

        protected static function translatedAttributeLabels($language)
        {
            return array_merge(parent::translatedAttributeLabels($language),
                array(
                    'category'      => Zurmo::t('MeetingsModule', 'Category',    array(), null, $language),
                    'description'   => Zurmo::t('ZurmoModule',    'Description', array(), null, $language),
                    'endDateTime'   => Zurmo::t('MeetingsModule', 'End Time',    array(), null, $language),
                    'location'      => Zurmo::t('MeetingsModule', 'Location',    array(), null, $language),
                    'name'          => Zurmo::t('ZurmoModule',    'Name',        array(), null, $language),
                    'startDateTime' => Zurmo::t('MeetingsModule', 'Start Time',  array(), null, $language),
                )
            );
        }

        public static function isTypeDeletable()
        {
            return true;
        }

        public static function getMashableActivityRulesType()
        {
            return 'Meeting';
        }

        protected function beforeSave()
        {
            if (parent::beforeSave())
            {
                if (array_key_exists('startDateTime', $this->originalAttributeValues) &&
                    $this->startDateTime != null)
                {
                    $this->unrestrictedSet('latestDateTime', $this->startDateTime);
                }
                return true;
            }
            else
            {
                return false;
            }
        }

        public static function hasReadPermissionsOptimization()
        {
            return true;
        }

        public static function getGamificationRulesType()
        {
            return 'MeetingGamification';
        }
    }
?>
