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
     * This form works with the import wizard views to collect data from the user interface and validate it.
     */
    class ImportWizardForm extends ConfigurableMetadataModel
    {

        const MAPPING_COLUMN_ATTRIBUTE = 1;

        const MAPPING_COLUMN_TYPE      = 2;

        const MAPPING_COLUMN_IMPORT    = 3;

        const MAPPING_COLUMN_RULES     = 4;

        /**
         * Set externally as the import model id when available;
         * @var integer
         */
        public $id;

        public $importRulesType;

        public $fileUploadData;

        public $firstRowIsHeaderRow;

        public $modelPermissions;

        public $mappingData;

        public function rules()
        {
            return array(
                array('importRulesType',     'required'),
                array('fileUploadData', 	 'type', 'type' => 'string'),
                array('firstRowIsHeaderRow', 'boolean'),
                array('modelPermissions',    'type', 'type' => 'string'),
                array('mappingData', 		 'type', 'type' => 'string'),
            );
        }

        public function attributeLabels()
        {
            return array(
                'importRulesType'      => Yii::t('Default', 'Module To Import To'),
                'fileUploadData'       => Yii::t('Default', 'File Upload Data'),
                'firstRowIsHeaderRow'  => Yii::t('Default', 'First Row is Header Row'),
                'modelPermissions'     => Yii::t('Default', 'Model Permissions'),
                'mappingData'          => Yii::t('Default', 'Mapping Data'),
            );
        }
    }
?>