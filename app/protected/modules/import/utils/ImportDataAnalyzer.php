<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
     *
     * Zurmo is free software; you can redistribute it and/or modify it under
     * the terms of the GNU Affero General Public License version 3 as published by the
     * Free Software Foundation with the addition of the following permission added
     * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
     * IN WHICH THE COPYRIGHT IS OWNED BY ZURMO, ZURMO DISCLAIMS THE WARRANTY
     * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
     *
     * Zurmo is distributed in the hope that it will be useful, but WITHOUT
     * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
     * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
     * details.
     *
     * You should have received a copy of the GNU Affero General Public License along with
     * this program; if not, see http://www.gnu.org/licenses or write to the Free
     * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
     * 02110-1301 USA.
     *
     * You can contact Zurmo, Inc. with a mailing address at 27 North Wacker Drive
     * Suite 370 Chicago, IL 60606. or at email address contact@zurmo.com.
     *
     * The interactive user interfaces in original and modified versions
     * of this program must display Appropriate Legal Notices, as required under
     * Section 5 of the GNU Affero General Public License version 3.
     *
     * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
     * these Appropriate Legal Notices must retain the display of the Zurmo
     * logo and Zurmo copyright notice. If the display of the logo is not reasonably
     * feasible for technical reasons, the Appropriate Legal Notices must display the words
     * "Copyright Zurmo Inc. 2013. All rights reserved".
     ********************************************************************************/

    /**
     * Class for handling the data analysis performed on mapped data in an import.  Each column mapping can
     * be analyzed and the resulting message and instructional data will be stored in an array which is accessible
     * once the analysis is complete.
     * NOTE - Analysis is only performed on mapped import columns and not extra columns with mapping rules.
     */
    class ImportDataAnalyzer
    {
        const STATUS_CLEAN = 1;

        const STATUS_WARN  = 2;

        const STATUS_SKIP  = 3;

        /**
         * ImportRules object to base the analysis on.
         * @var object
         */
        protected $importRules;

        /**
         * ImportDataProvider extended data provider for use in querying data to analyze.
         * @var object
         */
        protected $dataProvider;

        /**
         * Analyzing data can produce messages that need to be saved for later use.
         * @var array
         */
        protected $messagesData = array();

        protected $mappingData;

        protected $sanitizableColumnNames;

        private $customFieldsInstructionData;

        public static function getStatusLabelByType($type)
        {
            assert('is_int($type)');
            if ($type == self::STATUS_CLEAN)
            {
                $label = Zurmo::t('ImportModule', 'Ok');
            }
            elseif ($type == self::STATUS_WARN)
            {
                $label = Zurmo::t('ImportModule', 'Warning');
            }
            elseif ($type == self::STATUS_SKIP)
            {
                $label = Zurmo::t('ImportModule', 'Skip');
            }
            return $label;
        }

        public static function getStatusLabelAndVisualIdentifierContentByType($type)
        {
            assert('is_int($type)');
            $label = static::getStatusLabelByType($type);
            if ($type == self::STATUS_CLEAN)
            {
                $stageContent = ' stage-true';
            }
            elseif ($type == self::STATUS_WARN)
            {
                $stageContent = null;
            }
            elseif ($type == self::STATUS_SKIP)
            {
                $stageContent = ' stage-false';
            }
            $content = ZurmoHtml::tag('div', array('class' => "import-item-stage-status" . $stageContent),
                                      '<i>&#9679;</i>' . ZurmoHtml::tag('span', array(), $label));
            return ZurmoHtml::wrapAndRenderContinuumButtonContent($content);
        }

        protected static function resolveAttributeNameByRules(AttributeImportRules $attributeImportRules)
        {
            $attributeNames       = $attributeImportRules->getRealModelAttributeNames();
            if (count($attributeNames) > 1 || $attributeNames == null)
            {
                return null;
            }
            else
            {
                return $attributeNames[0];
            }
        }

        /**
         * @param string $importRules
         * @param object $dataProvider
         * @param array $mappingData
         * @param array $sanitizableColumnNames
         */
        public function __construct($importRules, ImportDataProvider $dataProvider, array $mappingData, array $sanitizableColumnNames)
        {
            assert('$importRules instanceof ImportRules');
            $this->importRules            = $importRules;
            $this->dataProvider           = $dataProvider;
            $this->mappingData            = $mappingData;
            $this->sanitizableColumnNames = $sanitizableColumnNames;
        }

        public function analyzePage()
        {
            $data = $this->dataProvider->getData(true);
            foreach ($data as $rowBean)
            {
                assert('$rowBean->id != null');
                $columnMessages = array();
                $shouldSkipRow  = false;
                foreach ($this->sanitizableColumnNames as $columnName)
                {
                    $attributeIndexOrDerivedType = $this->mappingData[$columnName]['attributeIndexOrDerivedType'];
                    $attributeImportRules = AttributeImportRulesFactory::
                                            makeByImportRulesTypeAndAttributeIndexOrDerivedType(
                                            $this->importRules->getType(),
                                            $attributeIndexOrDerivedType);
                    $modelClassName       = $attributeImportRules->getModelClassName();
                    $attributeName        = static::resolveAttributeNameByRules($attributeImportRules);
                    if (null != $attributeValueSanitizerUtilTypes = $attributeImportRules->getSanitizerUtilTypesInProcessingOrder())
                    {
                        assert('is_array($attributeValueSanitizerUtilTypes)');
                        foreach ($attributeValueSanitizerUtilTypes as $attributeValueSanitizerUtilType)
                        {
                            $sanitizer = ImportSanitizerUtilFactory::
                                         make($attributeValueSanitizerUtilType, $modelClassName, $attributeName,
                                         $columnName, $this->mappingData[$columnName]);
                            $sanitizer->analyzeByRow($rowBean);
                            if ($sanitizer->getShouldSkipRow())
                            {
                                $shouldSkipRow = true;
                            }
                            foreach ($sanitizer->getAnalysisMessages() as $message)
                            {
                                $columnMessages[$columnName][] = $message;
                            }
                            $classToEvaluate        = new ReflectionClass($sanitizer);
                            if ($classToEvaluate->implementsInterface('ImportSanitizerHasCustomFieldValuesInterface'))
                            {
                                $missingCustomFieldValues = $sanitizer->getMissingCustomFieldValues();
                                $this->getCustomFieldsInstructionData()->addMissingValuesByColumnName($missingCustomFieldValues, $columnName);
                            }
                        }
                    }
                }
                if (!empty($columnMessages))
                {
                    $rowBean->serializedAnalysisMessages = serialize($columnMessages);
                    if ($shouldSkipRow)
                    {
                        $rowBean->analysisStatus             = static::STATUS_SKIP;
                    }
                    else
                    {
                        $rowBean->analysisStatus             = static::STATUS_WARN;
                    }
                }
                else
                {
                    $rowBean->serializedAnalysisMessages = null;
                    $rowBean->analysisStatus             = static::STATUS_CLEAN;
                }
                R::store($rowBean);
            }
        }

        public function getCustomFieldsInstructionData()
        {
            if ($this->customFieldsInstructionData === null)
            {
                $this->customFieldsInstructionData = new CustomFieldsInstructionData();
            }
            return $this->customFieldsInstructionData;
        }
    }
?>