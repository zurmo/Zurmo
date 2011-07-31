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
        /**
         * Set externally as the import model id when available;
         * @var integer
         */
        public $id;

        public $importRulesType;

        public $fileUploadData;

        public $firstRowIsHeaderRow;

        protected $explicitReadWriteModelPermissions;

        public $mappingData;

        public function rules()
        {
            return array(
                array('importRulesType',     'required'),
                array('fileUploadData', 	 'type', 'type' => 'string'),
                array('firstRowIsHeaderRow', 'boolean'),
                array('mappingData', 		 'type', 'type' => 'string'),
                array('newPassword',        'validateMappingData', 'on'   => 'saveMappingData'),
            );
        }

        public function attributeLabels()
        {
            return array(
                'importRulesType'                   => Yii::t('Default', 'Module To Import To'),
                'fileUploadData'                    => Yii::t('Default', 'File Upload Data'),
                'firstRowIsHeaderRow'               => Yii::t('Default', 'First Row is Header Row'),
                'explicitReadWriteModelPermissions' => Yii::t('Default', 'Model Permissions'),
                'mappingData'                       => Yii::t('Default', 'Mapping Data'),
            );
        }

        public function getExplicitReadWriteModelPermissions()
        {
            return $this->explicitReadWriteModelPermissions;
        }

        public function setExplicitReadWriteModelPermissions($explicitReadWriteModelPermissions)
        {
            assert($explicitReadWriteModelPermissions instanceof ExplicitReadWriteModelPermissions);
            $this->explicitReadWriteModelPermissions = $explicitReadWriteModelPermissions;
        }

        public function validateMappingData($attribute, $params)
        {
            assert('$this->importRulesType != null');
            $atLeastOneAttributeMappedOrHasRules   = false;
            $attributeMappedOrHasRulesMoreThanOnce = false;
            $mappedAttributes                      = array();
            $importRulesClassName                  = $this->importRulesType . 'ImportRules';
            foreach($this->mappingData as $columnName => $data)
            {
                if($data['attributeNameOrDerivedType'] != null)
                {
                    $atLeastOneAttributeMappedOrHasRules = true;
                    if(in_array($data['attributeNameOrDerivedType'], $mappedAttributes))
                    {
                        $attributeMappedOrHasRulesMoreThanOnce = true;
                    }
                    else
                    {
                        $mappedAttributes[] = $data['attributeNameOrDerivedType'];
                    }
                }
            }
            if($attributeMappedOrHasRulesMoreThanOnce)
            {
                $this->addError('mappingData', Yii::t('Default', 'You can only map each attribute once.'));
            }
            if(!$atLeastOneAttributeMappedOrHasRules)
            {
                $this->addError('mappingData', Yii::t('Default', 'You must map at least one of your import columns.'));
            }
            $mappedAttributesOrDerivedAttributeTypes = ImportMappingUtil::
                                                       getMappedAttributesOrDerivedAttributeTypesByMappingData(
                                                       $this->mappingData);
            $requiredAttributeCollection             = $importRulesClassName::getRequiredAttributesCollectionNotIncludingReadOnly();
            $mappedAttributeRulesCollection          = AttributeImportRulesFactory::makeCollection(
                                                       $this->importRulesType,
                                                       $mappedAttributeOrDerivedAttributeTypes);
            if(!ImportRulesUtil::areAllRequiredAttributesMappedOrHaveRules($requiredAttributeCollection,
                                                                           $mappedAttributeRulesCollection))
            {
                $this->addError('mappingData', Yii::t('Default', 'All required attributes must be mapped or added.'));
            }
            try
            {
                ImportRulesUtil::checkIfAnyAttributesAreDoubleMapped($mappedAttributeRulesCollection);
            }
            catch(ImportAttributeMappedMoreThanOnceException $e)
            {
                $this->addError('mappingData', Yii::t('Default',
                'The following attribute is mapped more than once. {message}', array('{message}' => $e->getMessage())));
            }
        }
    }
?>