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
     * MappingRules data is not validated using this form, however the mapping rules data is collected and stored
     * in the mappingData array.
     * @see MappingRuleFormAndElementTypeUtil::validateMappingRuleForms
     */
    class ImportWizardForm extends ConfigurableMetadataModel
    {
        /**
         * Set externally as the import model id when available;
         * @var integer
         */
        public $id;

        /**
         * @var string
         */
        public $importRulesType;

        /**
         * Array of file upload specific information including name, type, and size.
         * @var array
         */
        public $fileUploadData;

        /**
         * True/false whether the import file's first row is a header row or not.
         * @var boolean
         */
        public $firstRowIsHeaderRow;

        /**
         * Object containing information on how to setup permissions for the new models that are created during the
         * import process.
         * @var object ExplicitReadWriteModelPermissions
         */
        protected $explicitReadWriteModelPermissions;

        /**
         * Mapping data array indexed by column name containing the mapping rules, attribute index or derived type, and
         * type information.
         * @var array
         */
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

        /**
         * Validation used in the saveMappingData scenario to make sure the mapping data is correct based on
         * user input. Runs several different validations on the data.  This does not validate the validity of the
         * mapping rules data itself. That is done seperately.
         * @see MappingRuleFormAndElementTypeUtil::validateMappingRuleForms
         * @param string $attribute
         * @param array $params
         */
        public function validateMappingData($attribute, $params)
        {
            assert('$this->importRulesType != null');
            assert('$this->mappingData != null');
            $atLeastOneAttributeMappedOrHasRules   = false;
            $attributeMappedOrHasRulesMoreThanOnce = false;
            $mappedAttributes                      = array();
            $importRulesClassName                  = ImportRulesUtil::
                                                     getImportRulesClassNameByType($this->importRulesType);
            foreach($this->mappingData as $columnName => $data)
            {
                if($data['attributeIndexOrDerivedType'] != null)
                {
                    $atLeastOneAttributeMappedOrHasRules = true;
                    if(in_array($data['attributeIndexOrDerivedType'], $mappedAttributes))
                    {
                        $attributeMappedOrHasRulesMoreThanOnce = true;
                    }
                    else
                    {
                        $mappedAttributes[] = $data['attributeIndexOrDerivedType'];
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
            $mappedAttributeIndicesOrDerivedAttributeTypes = ImportMappingUtil::
                                                             getMappedAttributeIndicesOrDerivedAttributeTypesByMappingData(
                                                             $this->mappingData);
            $requiredAttributeCollection                   = $importRulesClassName::
                                                             getRequiredAttributesCollectionNotIncludingReadOnly();
            $mappedAttributeImportRulesCollection          = AttributeImportRulesFactory::makeCollection(
                                                             $this->importRulesType,
                                                             $mappedAttributeIndicesOrDerivedAttributeTypes);
            if(!ImportRulesUtil::areAllRequiredAttributesMappedOrHaveRules($requiredAttributeCollection,
                                                                           $mappedAttributeImportRulesCollection))
            {
                $this->addError('mappingData', Yii::t('Default', 'All required attributes must be mapped or added.'));
            }
            try
            {
                ImportRulesUtil::checkIfAnyAttributesAreDoubleMapped($mappedAttributeImportRulesCollection);
            }
            catch(ImportAttributeMappedMoreThanOnceException $e)
            {
                $this->addError('mappingData', Yii::t('Default',
                'The following attribute is mapped more than once. {message}', array('{message}' => $e->getMessage())));
            }
        }
    }
?>