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
                               'dynamicClauses' => Zurmo::t('Core', 'Advanced Search Rows'),
                               'dynamicStructure' => Zurmo::t('Core', 'Search Operator'),
            ));
        }

        public function validateDynamicStructure($attribute, $params)
        {
            if (count($this->dynamicClauses) > 0)
            {
                if (null != $errorMessage = SQLOperatorUtil::
                           resolveValidationForATemplateSqlStatementAndReturnErrorMessage($this->$attribute,
                          count($this->dynamicClauses)))
                {
                    $this->addError('dynamicStructure', $errorMessage);
                }
                else
                {
                    $formula = strtolower($this->dynamicStructure);
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
                            $errorContent = Zurmo::t('Core', 'Please use only integers lesser than {max}.', array('{max}' => count($this->dynamicClauses)));
                        }
                    }
                }
                if (isset($errorContent))
                {
                    $this->addError('dynamicStructure', Zurmo::t('Core', 'The structure is invalid. {error}', array('{error}' => $errorContent)));
                }
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
                        $this->addError('dynamicClauses', Zurmo::t('Core', 'You must select a field for row {rowNumber}',
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
                            $this->addError('dynamicClauses', Zurmo::t('Core', 'You must select a value for row {rowNumber}',
                            array('{rowNumber}' => $structurePosition)));
                        }
                    }
                }
            }
        }
    }
?>