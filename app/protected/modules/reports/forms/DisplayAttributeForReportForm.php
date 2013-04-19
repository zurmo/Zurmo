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
     * Component form for display attribute definitions
     */
    class DisplayAttributeForReportForm extends ComponentForReportForm
    {
        const COLUMN_ALIAS_PREFIX = 'col';

        const HEADER_SORTABLE_TYPE_ASORT = 'asort';

        /**
         * @var string
         */
        public $label;

        /**
         * Each display attribute has a unique column alias name that helps identify it during the rendering of the grid
         * @var string
         */
        public $columnAliasName;

        /**
         * Some display attributes can be used just for querying and not for displaying in the grid.
         * @var bool
         */
        public $queryOnly                      = false;

        /**
         * In the case of summation with drill down, a display attribute value can be used to build the filter
         * for the drill down results.
         * @var bool
         */
        public $valueUsedAsDrillDownFilter     = false;

        /**
         * Some display attributes such as account name, are rendered using the model.  This is because in a query
         * select id from account, the account model is created and then $account->name is used.  But other display
         * attributes such as integer (SUM) would be use the value directly from the select statement like
         * select SUM(integer) from account.
         * @var bool
         */
        public $madeViaSelectInsteadOfViaModel = false;

        /**
         * @var integer the counter for generating automatic column alias names
         */
        protected static $count = 0;

        /**
         * Indicates the model alias for working with the resultsRowData. Sometimes there can be the same related model
         * more than once via different relations.  This makes sure the resultsRowData knows which model to use. Only applies
         * when the display attribute is made via the model and not via the select
         * @var string
         */
        protected $modelAliasUsingTableAliasName;

        /**
         * @return string component type
         */
        public static function getType()
        {
            return static::TYPE_DISPLAY_ATTRIBUTES;
        }

        /**
         * Sets the columnAliasName as unique across all instances of DisplayAttributeReportForms
         * @param string $moduleClassName
         * @param string $modelClassName
         * @param string $reportType
         * @param int $rowKey
         */
        public function __construct($moduleClassName, $modelClassName, $reportType, $rowKey = 0)
        {
            parent::__construct($moduleClassName, $modelClassName, $reportType, $rowKey);
            $this->columnAliasName = self::COLUMN_ALIAS_PREFIX . static::$count++;
        }

        /**
         * Makes sure the attributeIndexOrDerivedType always populates first before label otherwise any
         * custom label gets wiped out.
         * (non-PHPdoc)
         * @see ComponentForReportForm::attributeNames()
         */
        public function attributeNames()
        {
            $attributeNames = parent::attributeNames();
            if (count($attributeNames) != 6)
            {
                throw new NotSupportedException();
            }
            array_unshift( $attributeNames, array_pop( $attributeNames ) );
            return $attributeNames;
        }

        /**
         * @return array
         */
        public function rules()
        {
            return array_merge(parent::rules(), array(
                array('label', 'required'),
                array('label', 'type', 'type' => 'string'),
            ));
        }

        /**
         * @param string $name
         * @param mixed $value
         */
        public function __set($name, $value)
        {
            parent::__set($name, $value);
            if ($name == 'attributeIndexOrDerivedType')
            {
                $this->label = $this->getDisplayLabel();
            }
        }

        /**
         * Used primarily by testing to reset the count used to define the unique column alias names.
         */
        public static function resetCount()
        {
            static::$count = 0;
        }

        /**
         * @param $modelAliasUsingTableAliasName
         */
        public function setModelAliasUsingTableAliasName($modelAliasUsingTableAliasName)
        {
            assert('is_string($modelAliasUsingTableAliasName)');
            $this->modelAliasUsingTableAliasName = $modelAliasUsingTableAliasName;
        }

        /**
         * @return string
         */
        public function getModelAliasUsingTableAliasName()
        {
            return $this->modelAliasUsingTableAliasName;
        }

        /**
         * @param $key
         * @return string
         */
        public function resolveAttributeNameForGridViewColumn($key)
        {
            assert('is_int($key)');
            $modelToReportAdapter = $this->makeResolvedAttributeModelRelationsAndAttributesToReportAdapter();
            if ($modelToReportAdapter->isDisplayAttributeMadeViaSelect($this->getResolvedAttribute()))
            {
                return $this->columnAliasName;
            }
            return ReportResultsRowData::resolveAttributeNameByKey($key);
        }

        /**
         * An example of a linkable attribute is if you run a report on contacts, and show a column of account names.
         * The account name can be linkable to the account record.
         * @return bool
         */
        public function isALinkableAttribute()
        {
            $resolvedAttribute = $this->getResolvedAttribute();
            if ($resolvedAttribute == 'name' || $resolvedAttribute == 'FullName')
            {
                return true;
            }
            return false;
        }

        /**
         * @return mixed
         */
        public function getRawValueRelatedAttribute()
        {
            $modelToReportAdapter = $this->makeResolvedAttributeModelRelationsAndAttributesToReportAdapter();
            return $modelToReportAdapter->getRawValueRelatedAttribute($this->getResolvedAttribute());
        }

        /**
         * Raw values such as those used by the header x-axis or y-axis rows/columns need to be translated. An example
         * is a dropdown where the value is the raw database value and needs to be properly translated for display.
         * Another example is dynamic __User, where the value is the user id, and needs to be stringified to the User
         * model.
         * @param $value
         * @return string
         */
        public function resolveValueAsLabelForHeaderCell($value)
        {
            $tContent             = null;
            $translatedValue      = $value;
            $resolvedAttribute    = $this->getResolvedAttribute();
            $displayElementType   = $this->getDisplayElementType();
            $modelToReportAdapter = $this->makeResolvedAttributeModelRelationsAndAttributesToReportAdapter();
            if ($modelToReportAdapter->getModel()->isAttribute($resolvedAttribute) &&
               $modelToReportAdapter->getModel()->isRelation($resolvedAttribute) &&
               !$modelToReportAdapter->getModel()->isOwnedRelation($resolvedAttribute))
            {
                $relationModelClassName = $modelToReportAdapter->getModel()->getRelationModelClassName($resolvedAttribute);
                $relatedModel = $relationModelClassName::getById((int)$value);
                if ($relatedModel->isAttribute('serializedLabels'))
                {
                    $translatedValue     = $relatedModel->resolveTranslatedNameByLanguage(Yii::app()->language);
                }
            }
            elseif ($displayElementType == 'User')
            {
                $user            = User::getById((int)$value);
                $translatedValue = strval($user);
            }
            elseif ($displayElementType == 'DropDown')
            {
                $customFieldData = CustomFieldDataModelUtil::getDataByModelClassNameAndAttributeName(
                                   $this->getResolvedAttributeModelClassName(), $this->getResolvedAttribute());
                $dataAndLabels   = CustomFieldDataUtil::getDataIndexedByDataAndTranslatedLabelsByLanguage(
                                   $customFieldData, Yii::app()->language);
                if (isset($dataAndLabels[$value]))
                {
                    $translatedValue = $dataAndLabels[$value];
                }
            }
            elseif ($displayElementType == 'CheckBox')
            {
                if ($value)
                {
                    $translatedValue = Zurmo::t('ReportsModule', 'Yes');
                }
                elseif ($value == false && $value != '')
                {
                    $translatedValue = Zurmo::t('ReportsModule', 'No');
                }
            }
            elseif ($displayElementType == 'GroupByModifierMonth')
            {
                $translatedValue = DateTimeUtil::getMonthName($value);
            }
            if ($translatedValue === null)
            {
                $translatedValue = '';
            }
            return $translatedValue;
        }

        /**
         * For matrix reports, months for example need to be sorted using asort so the columns or rows are sorted
         * correctly. Eventually expand to support sorting by users and custom fields.
         * @return string | null
         * @throws NotSupportedException
         */
        public function getHeaderSortableType()
        {
            if ($this->attributeIndexOrDerivedType == null)
            {
                throw new NotSupportedException();
            }
            $modelToReportAdapter = $this->makeResolvedAttributeModelRelationsAndAttributesToReportAdapter();
            if ($modelToReportAdapter->isAttributeACalculatedGroupByModifier($this->getResolvedAttribute()))
            {
                return self::HEADER_SORTABLE_TYPE_ASORT;
            }
            else
            {
                return null;
            }
        }
    }
?>