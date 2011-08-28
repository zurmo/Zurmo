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
     * Base class for importing into models that extend the activity model.
     */
    abstract class ActivitiesImportRules extends DerivedAttributeSupportedImportRules
    {
        /**
         * Override to block out additional attributes that are not importable
         * @return array
         */
        public static function getNonImportableAttributeNames()
        {
            return array_merge(parent::getNonImportableAttributeNames(), array('latestDateTime'));
        }

        /**
         * Override to handle special dynamically adding each activity item derived type that the user
         * has access too.
         * @return array
         */
        public static function getDerivedAttributeTypes()
        {
            $activityItemsDerivedAttributeTypes = static::getActivityItemsDerivedAttributeTypesAndResolveAccessByCurrentUser();
            return array_merge($activityItemsDerivedAttributeTypes, array('CreatedByUser',
                         'ModifiedByUser',
                         'CreatedDateTime',
                         'ModifiedDateTime'));
        }

        protected static function getActivityItemsDerivedAttributeTypesAndResolveAccessByCurrentUser()
        {
            $metadata                     = Activity::getMetadata();
            $derivedAttributeTypes        = array();
            $activityItemsModelClassNames = $metadata['Activity']['activityItemsModelClassNames'];
            foreach ($activityItemsModelClassNames as $modelClassName)
            {
                $moduleClassName = $modelClassName::getModuleClassName();
                if (RightsUtil::canUserAccessModule($moduleClassName, Yii::app()->user->userModel))
                {
                    $derivedAttributeTypes[] = $modelClassName . 'Derived';
                }
                //todo: add support for leads.
            }
            return $derivedAttributeTypes;

        }

        /**
         * The derived attributes for activities corresponds to the activityItems attribute.
         */
        public static function getActualModelAttributeNameForDerivedAttribute()
        {
            return 'activityItems';
        }
    }
?>