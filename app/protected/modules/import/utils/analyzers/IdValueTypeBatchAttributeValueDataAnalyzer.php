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

    class IdValueTypeBatchAttributeValueDataAnalyzer extends BatchAttributeValueDataAnalyzer
                                                              implements LinkedToMappingRuleDataAnalyzerInterface
    {
        const FOUND   = 'Found';

        const UNFOUND = 'Unfound';

        const EXTERNAL_SYSTEM_ID_TOO_LONG = 'External system id too long';

        protected $type;

        protected $attributeModelClassName;

        protected $externalSystemIdMaxLength = 40;

        public function __construct($modelClassName, $attributeNameOrNames)
        {
            parent:: __construct($modelClassName, $attributeNameOrNames);
            assert('count($this->attributeNameOrNames) == 1');
            $model                         = new $modelClassName(false);
            $this->attributeModelClassName = $this->resolveAttributeModelClassName($model,$this->attributeNameOrNames[0]);
            $this->messageCountData[static::FOUND]                        = 0;
            $this->messageCountData[static::UNFOUND]                      = 0;
            $this->messageCountData[static::EXTERNAL_SYSTEM_ID_TOO_LONG] = 0;
        }

        public function runAndMakeMessages(AnalyzerSupportedDataProvider $dataProvider, $columnName,
                                         $mappingRuleType, $mappingRuleData)
        {
            assert('is_string($columnName)');
            assert('is_string($mappingRuleType)');
            assert('is_array($mappingRuleData)');
            assert('is_int($mappingRuleData["type"])');
            assert('count($this->attributeNameOrNames) == 1');
            $this->ensureTypeValueIsValid($mappingRuleData["type"]);
            $this->type = $mappingRuleData["type"];
            if($this->type == IdValueTypeMappingRuleForm::EXTERNAL_SYSTEM_ID)
            {
                $modelClassName = $this->attributeModelClassName;
                RedBean_Plugin_Optimizer_ExternalSystemId::
                ensureColumnIsVarchar($modelClassName::getTableName($modelClassName),
                                      'externalSystemId',
                                      $this->externalSystemIdMaxLength);
            }
            $this->processAndMakeMessage($dataProvider, $columnName);
        }

        protected function ensureTypeValueIsValid($type)
        {
            assert('$type == IdValueTypeMappingRuleForm::ZURMO_MODEL_ID ||
                    $type == IdValueTypeMappingRuleForm::EXTERNAL_SYSTEM_ID');
        }

        protected function resolveAttributeModelClassName(RedBeanModel $model, $attributeName)
        {
            if ($attributeName == 'id')
            {
                return get_class($model);
            }
            return $model->getRelationModelClassName($attributeName);
        }

        protected function processAndMakeMessage(AnalyzerSupportedDataProvider $dataProvider, $columnName)
        {
            assert('is_string($columnName)');

            $page           = 0;
            $itemsProcessed = 0;
            $totalItemCount =  $dataProvider->getTotalItemCount(true);
            $dataProvider->getPagination()->setCurrentPage($page);
            while(null != $data = $dataProvider->getData(true))
            {
                foreach($data as $rowData)
                {
                    $this->analyzeByValue($rowData->$columnName);
                    $itemsProcessed ++;
                }

                if($itemsProcessed < $totalItemCount)
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

        protected function analyzeByValue($value)
        {
            $modelClassName = $this->attributeModelClassName;
            if($this->type == IdValueTypeMappingRuleForm::ZURMO_MODEL_ID)
            {
                $found = $this->resolveFoundIdByValue($value);
            }
            else
            {
                $found = $this->resolveFoundExternalSystemIdByValue($value);
            }
            if($found)
            {
                $this->messageCountData[static::FOUND] ++;
            }
            else
            {
                $this->messageCountData[static::UNFOUND] ++;
            }
            if($this->type == IdValueTypeMappingRuleForm::EXTERNAL_SYSTEM_ID)
            {
                if(strlen($value) > $this->externalSystemIdMaxLength)
                {
                    $this->messageCountData[static::EXTERNAL_SYSTEM_ID_TOO_LONG] ++;
                }
            }
        }

        protected function resolveFoundIdByValue($value)
        {
            assert('is_int($value) || is_string($value)');
            if(is_int($value))
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
            if(count($ids) == 0)
            {
                return false;
            }
            return true;
        }

        protected function resolveFoundExternalSystemIdByValue($value)
        {
            $modelClassName = $this->attributeModelClassName;
            $sql = 'select id from ' . $modelClassName::getTableName($modelClassName) .
            ' where externalSystemId = \'' . $value . '\' limit 1';
            $ids =  R::getCol($sql);
            assert('count($ids) <= 1');
            if(count($ids) == 0)
            {
                return false;
            }
            return true;
        }

        protected function getMessageByFailedCount($failed)
        {
            throw new NotSupportedException();
        }

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
            if($this->messageCountData[static::EXTERNAL_SYSTEM_ID_TOO_LONG] > 0)
            {
                $label   = '{invalid} value(s) were too large. ';
                $label  .= 'These rows will be skipped during the import.';
                $this->addMessage(Yii::t('Default', $label,
                              array('{invalid}' => $this->messageCountData[static::EXTERNAL_SYSTEM_ID_TOO_LONG])));
            }
        }
    }
?>