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
     * Extend this class when a searchForm should allow dynamic searches in advanced search. This means the user
     * can add any field as a search parameter.
     */
    abstract class DynamicSearchForm extends SearchForm
    {
        const DYNAMIC_NAME             = 'dynamicClauses';

        const DYNAMIC_STRUCTURE_NAME   = 'dynamicStructure';

        public $dynamicStructure;

        /**
         * Make sure to populate with sanitized clauses
         * @see SearchUtil::sanitizeDynamicSearchAttributesByDesignerTypeForSavingModel
         * @var array
         */
        public $dynamicClauses = array();

        public static function getNonSearchableAttributes()
        {
            return array_merge(parent::getNonSearchableAttributes(), array('dynamicStructure', 'dynamicClauses'));
        }

        public function rules()
        {
            return array_merge(parent::rules(), array(
                               array('dynamicStructure', 'safe'),
                               array('dynamicStructure',   'validateDynamicStructure', 'on' => 'validateDynamic, validateSaveSearch'),
                               array('dynamicClauses',   'safe'),
                               array('dynamicClauses',   'validateDynamicClauses', 'on' => 'validateDynamic, validateSaveSearch'),
            ));
        }

        public function attributeLabels()
        {
            return array_merge(parent::attributeLabels(), array(
                               'dynamicClauses' => Yii::t('Default', 'Advanced Search Rows'),
                               'dynamicStructure' => Yii::t('Default', 'Search Operator'),
            ));
        }

        public function validateDynamicStructure($attribute, $params)
        {
            if (count($this->dynamicClauses) > 0)
            {
                $formula = strtolower($this->$attribute);
                if (!$this->validateParenthesis($formula))
                {
                    $errorContent = Yii::t('Default', 'Please fix your parenthesis.');
                }
                else
                {
                    $formula = str_replace("(", "", $formula);
                    $formula = str_replace(")", "", $formula);
                    $arguments = preg_split("/or|and/", $formula);
                    foreach ($arguments as $argument)
                    {
                        $argument = trim($argument);
                        if (!is_numeric($argument) ||
                            !(intval($argument) <= count($this->dynamicClauses)) ||
                            !(intval($argument) > 0) ||
                            !(preg_match("/\./", $argument) === 0) )
                        {
                            $errorContent = Yii::t('Default', 'Please use only integers lesser than {max}.', array('{max}' => count($this->dynamicClauses)));
                        }
                    }
                }
                if (isset($errorContent))
                {
                    $this->addError('dynamicStructure', Yii::t('Default', 'The structure is invalid. {error}', array('{error}' => $errorContent)));
                }
            }
        }

        /*
         * Function for validation of parenthesis in a formula
         */
        protected function  validateParenthesis($formula)
        {
            $val = 0;
            for ($i = 0; $i <= strlen($formula); $i++)
            {
                $char = substr($formula, $i, 1);
                if ($char === "(")
                {
                    $val += 1;
                }
                elseif ($char === ")")
                {
                    $val -= 1;
                }
                if ($val < 0)
                {
                    return false;
                }
            }
            if ($val !== 0)
            {
                return false;
            }
            else
            {
                return true;
            }
        }

        public function validateDynamicClauses($attribute, $params)
        {
            if ($this->$attribute != null)
            {
                foreach ($this->$attribute as $key => $rowData)
                {
                    $structurePosition = $rowData['structurePosition'];
                    if ($rowData['attributeIndexOrDerivedType'] == null)
                    {
                        $this->addError('dynamicClauses', Yii::t('Default', 'You must select a field for row {rowNumber}',
                        array('{rowNumber}' => $structurePosition)));
                    }
                    else
                    {
                        unset($rowData['attributeIndexOrDerivedType']);
                        unset($rowData['structurePosition']);
                        $dynamicStructure = '';
                        $metadataAdapter  = new DynamicSearchDataProviderMetadataAdapter(
                            array('clauses' => array(), 'structure' => ''),
                            $this,
                            Yii::app()->user->userModel->id,
                            array($rowData),
                            $dynamicStructure
                        );
                        $metadata = $metadataAdapter->getAdaptedDataProviderMetadata();
                        if (count($metadata['clauses']) == 0)
                        {
                            $this->addError('dynamicClauses', Yii::t('Default', 'You must select a value for row {rowNumber}',
                            array('{rowNumber}' => $structurePosition)));
                        }
                    }
                }
            }
        }
    }
?>