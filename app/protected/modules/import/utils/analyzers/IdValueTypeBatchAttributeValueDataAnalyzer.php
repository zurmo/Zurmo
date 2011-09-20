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
     * Data analyzer for columns mapped to attributes that are either ids or relation ids.  For importing ids, there
     * are several approved value types including a zurmo model id as well as an external system id that can be used to
     * maintain key integerity during the entirety of a data import.
     */
    class IdValueTypeBatchAttributeValueDataAnalyzer extends BatchAttributeValueDataAnalyzer
                                                              implements LinkedToMappingRuleDataAnalyzerInterface
    {
        /**
         * Index used for values found matched to an existing model in the database.
         * @var string
         */
        const FOUND   = 'Found';

        /**
         * Index used for when a value is not found matched to an existing model in the database.
         * @var unknown_type
         */
        const UNFOUND = 'Unfound';

        /**
         * Identifies when the value provided is too large.
         * @var string
         */
        const EXTERNAL_SYSTEM_ID_TOO_LONG = 'External system id too long';

        /**
         * Identifies the type of value provided. IdValueTypeMappingRuleForm::ZURMO_MODEL_ID or
         * IdValueTypeMappingRuleForm::EXTERNAL_SYSTEM_ID
         * @var integer
         */
        protected $type;

        /**
         * The attribute is expected to be a relation. This is the model class name for that relation.
         * @var string
         */
        protected $attributeModelClassName;

        /**
         * Max allowed length of a value when the type of value is IdValueTypeMappingRuleForm::EXTERNAL_SYSTEM_ID
         * @var integer
         */
        protected $externalSystemIdMaxLength = 40;

        /**
         * Override to ensure the attribute is only a single attribute and also setup the message count data.
         * @param string $modelClassName
         * @param string $attributeName
         */
        public function __construct($modelClassName, $attributeName)
        {
            parent:: __construct($modelClassName, $attributeName);
            assert('is_string($attributeName)');
            $model                         = new $modelClassName(false);
            $this->attributeModelClassName = $this->resolveAttributeModelClassName($model, $this->attributeName);
            $this->messageCountData[static::FOUND]                        = 0;
            $this->messageCountData[static::UNFOUND]                      = 0;
            $this->messageCountData[static::EXTERNAL_SYSTEM_ID_TOO_LONG] = 0;
        }

        /**
         * @see LinkedToMappingRuleDataAnalyzerInterface::runAndMakeMessages()
         */
        public function runAndMakeMessages(AnalyzerSupportedDataProvider $dataProvider, $columnName,
                                         $mappingRuleType, $mappingRuleData)
        {
            assert('is_string($columnName)');
            assert('is_string($mappingRuleType)');
            assert('is_array($mappingRuleData)');
            assert('is_int($mappingRuleData["type"])');
            $this->ensureTypeValueIsValid($mappingRuleData["type"]);
            $this->type = $mappingRuleData["type"];
            if ($this->type == IdValueTypeMappingRuleForm::EXTERNAL_SYSTEM_ID)
            {
                $modelClassName  = $this->attributeModelClassName;
                $tableColumnName = ExternalSystemIdUtil::EXTERNAL_SYSTEM_ID_COLUMN_NAME;
                RedBean_Plugin_Optimizer_ExternalSystemId::
                ensureColumnIsVarchar($modelClassName::getTableName($modelClassName),
                                      $tableColumnName,
                                      $this->externalSystemIdMaxLength);
            }
            $this->processAndMakeMessage($dataProvider, $columnName);
        }

        /**
         * Ensure the type is an accepted type.
         * @param unknown_type integer
         */
        protected function ensureTypeValueIsValid($type)
        {
            assert('$type == IdValueTypeMappingRuleForm::ZURMO_MODEL_ID ||
                    $type == IdValueTypeMappingRuleForm::EXTERNAL_SYSTEM_ID');
        }

        /**
         * Given a model and an attribute, return the model class name for the attribute.
         * @param object $model
         * @param string $attributeName
         */
        protected function resolveAttributeModelClassName(RedBeanModel $model, $attributeName)
        {
            assert('is_string($attributeName)');
            if ($attributeName == 'id')
            {
                return get_class($model);
            }
            return $model->getRelationModelClassName($attributeName);
        }

        /**
         * @see BatchAttributeValueDataAnalyzer::processAndMakeMessage()
         */
        protected function processAndMakeMessage(AnalyzerSupportedDataProvider $dataProvider, $columnName)
        {
            assert('is_string($columnName)');
            $page           = 0;
            $itemsProcessed = 0;
            $totalItemCount =  $dataProvider->getTotalItemCount(true);
            $dataProvider->getPagination()->setCurrentPage($page);
            while (null != $data = $dataProvider->getData(true))
            {
                foreach ($data as $rowData)
                {
                    $this->analyzeByValue($rowData->$columnName);
                    $itemsProcessed ++;
                }

                if ($itemsProcessed < $totalItemCount)
                {
                    $page ++;
                    $dataProvider->getPagination()->setCurrentPage($page);
                }
                else
                {
                    break;
                }
            }
            return $this->makeMessages();
        }

        /**
         * @see BatchAttributeValueDataAnalyzer::analyzeByValue()
         */
        protected function analyzeByValue($value)
        {
            $modelClassName = $this->attributeModelClassName;
            if ($this->type == IdValueTypeMappingRuleForm::ZURMO_MODEL_ID)
            {
                $found = $this->resolveFoundIdByValue($value);
            }
            else
            {
                $found = $this->resolveFoundExternalSystemIdByValue($value);
            }
            if ($found)
            {
                $this->messageCountData[static::FOUND] ++;
            }
            else
            {
                $this->messageCountData[static::UNFOUND] ++;
            }
            if ($this->type == IdValueTypeMappingRuleForm::EXTERNAL_SYSTEM_ID)
            {
                if (strlen($value) > $this->externalSystemIdMaxLength)
                {
                    $this->messageCountData[static::EXTERNAL_SYSTEM_ID_TOO_LONG] ++;
                }
            }
        }

        /**
         * Tries to find the value in the system. If found, returns true, otherwise false.
         * @param string $value
         */
        protected function resolveFoundIdByValue($value)
        {
            assert('is_int($value) || is_string($value) || $value == null');
            if ($value == null)
            {
                return false;
            }
            elseif (is_int($value))
            {
                $sqlReadyString = $value;
            }
            else
            {
                $sqlReadyString = '\'' . $value . '\'';
            }
            $modelClassName = $this->attributeModelClassName;
            $sql = 'select id from ' . $modelClassName::getTableName($modelClassName) .
            ' where id = ' . $sqlReadyString . ' limit 1';
            $ids =  R::getCol($sql);
            assert('count($ids) <= 1');
            if (count($ids) == 0)
            {
                return false;
            }
            return true;
        }

        /**
         * Tries to find the value in the system. If found, returns true, otherwise false.
         * @param string $value
         */
        protected function resolveFoundExternalSystemIdByValue($value)
        {
            assert('is_int($value) || is_string($value) || $value == null');
            if ($value == null)
            {
                return false;
            }
            $modelClassName = $this->attributeModelClassName;
            $columnName     = ExternalSystemIdUtil::EXTERNAL_SYSTEM_ID_COLUMN_NAME;
            $sql = 'select id from ' . $modelClassName::getTableName($modelClassName) .
            ' where ' . $columnName . ' = \'' . $value . '\' limit 1';
            $ids =  R::getCol($sql);
            assert('count($ids) <= 1');
            if (count($ids) == 0)
            {
                return false;
            }
            return true;
        }

        protected function getMessageByFailedCount($failed)
        {
            throw new NotSupportedException();
        }

        /**
         * @see BatchAttributeValueDataAnalyzer::makeMessages()
         */
        protected function makeMessages()
        {
            $label   = '{found} record(s) will be updated ';
            $label  .= 'and {unfound} record(s) will be skipped during import.';
            $this->addMessage(Yii::t('Default', $label,
                              array('{found}' => $this->messageCountData[static::FOUND],
                                    '{unfound}' => $this->messageCountData[static::UNFOUND])));
            $this->resolveMakeExternalSystemIdTooLargeMessage();
        }

        protected function resolveMakeExternalSystemIdTooLargeMessage()
        {
            if ($this->messageCountData[static::EXTERNAL_SYSTEM_ID_TOO_LONG] > 0)
            {
                $label   = '{invalid} value(s) were too large. ';
                $label  .= 'These rows will be skipped during the import.';
                $this->addMessage(Yii::t('Default', $label,
                              array('{invalid}' => $this->messageCountData[static::EXTERNAL_SYSTEM_ID_TOO_LONG])));
            }
        }
    }
?>