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
     * Model for saving a report definition.  This does not save report results, but only the definition of how a report
     * should act when run.  Most information is defined as serializedData.  @see Report model which is used to interact
     * with most report components views.
     */
    class SavedReport extends OwnedSecurableItem
    {
        public function __toString()
        {
            if (trim($this->name) == '')
            {
                return Zurmo::t('ReportsModule', '(Unnamed)');
            }
            return $this->name;
        }

        public static function getByName($name)
        {
            assert('is_string($name) && $name != ""');
            return self::getSubset(null, null, null, "name = '$name'");
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
                    'moduleClassName',
                    'name',
                    'serializedData',
                    'type'
                ),
                'rules' => array(
                    array('description',         'type',   'type' => 'string'),
                    array('moduleClassName',     'required'),
                    array('moduleClassName',     'type',   'type' => 'string'),
                    array('moduleClassName',     'length', 'max'  => 64),
                    array('name',                'required'),
                    array('name',                'type',   'type' => 'string'),
                    array('name',                'length', 'max'  => 64),
                    array('serializedData',      'required'),
                    array('serializedData',      'type', 'type' => 'string'),
                    array('type',                'required'),
                    array('type',                'type',   'type' => 'string'),
                    array('type',                'length', 'max'  => 15),
                ),
                'elements' => array(
                    'type'            => 'ReportTypeStaticDropDown',
                    'moduleClassName' => 'ModuleForReportStaticDropDown',
                )
            );
            return $metadata;
        }

        public static function isTypeDeletable()
        {
            return true;
        }

        public static function getModuleClassName()
        {
            return 'ReportsModule';
        }

        public static function hasReadPermissionsOptimization()
        {
            return true;
        }

        protected static function translatedAttributeLabels($language)
        {
            return array_merge(parent::translatedAttributeLabels($language),
                array(
                    'description'       => Zurmo::t('ZurmoModule', 'Description',  array(), null, $language),
                    'name'              => Zurmo::t('ZurmoModule', 'Name',  array(), null, $language),
                    'type'              => Zurmo::t('Core',        'Type',  array(), null, $language),
                )
            );
        }

        public static function getContactIdsByReportId($id)
        {
            $contactIds         = array();
            $savedReport        = SavedReport::getById($id);
            $report             = SavedReportToReportAdapter::makeReportBySavedReport($savedReport);
            $attributeName      = null;
            foreach ($report->getDisplayAttributes() as $key => $displayAttribute)
            {
                if ($displayAttribute->getAttributeIndexOrDerivedType() == 'id')
                {
                    $attributeName      = ReportResultsRowData::resolveAttributeNameByKey($key);
                    break;
                }
            }
            if (!$attributeName)
            {
                $moduleClassName                                = $report->getModuleClassName();
                $modelClassName                                 = $moduleClassName::getPrimaryModelName();
                $displayAttribute                               = new DisplayAttributeForReportForm($moduleClassName,
                                                                                        $modelClassName,
                                                                                        $report->getType());
                $displayAttribute->attributeIndexOrDerivedType  = 'id';
                $report->addDisplayAttribute($displayAttribute);
                $attributeName                                  = ReportResultsRowData::resolveAttributeNameByKey(($key + 1));
            }
            $reportDataProvider             = new RowsAndColumnsReportDataProvider($report);
            $reportResultsRowDataItems      = $reportDataProvider->getData();
            foreach ($reportResultsRowDataItems as $reportResultsRowDataItem)
            {
                $contact        = $reportResultsRowDataItem->getModel($attributeName);
                $contactIds[]   = $contact->id;
            }
            return $contactIds;
        }

        public static function getGamificationRulesType()
        {
            return 'ReportGamification';
        }
    }
?>