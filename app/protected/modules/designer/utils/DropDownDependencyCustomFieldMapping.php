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
     * Class contains information about mapping for a particular 'level' in the drop down dependency.
     */
    class DropDownDependencyCustomFieldMapping
    {
        /**
         * Whether to allow a selection of an attribute name.
         * @var boolean
         */
        protected $allowAttributeSelection = true;

        /**
         * The position or 'level' of this object in relation to the other dependencies.
         * @var integer
         */
        protected $position;

        /**
         * The selected attribute name.
         * @var string
         */
        protected $attributeName;

        /**
         * Array of available model attribute names that can be selected for this level as an attribute name.
         * @var array
         */
        protected $availableCustomFieldAttributes;

        /**
         * CustomFieldData object that is used by the attribute name's customField.
         * @var CustomFieldData
         */
        protected $customFieldData;

        /**
         * Array of mapping data
         * @var array
         */
        protected $mappingData;

        /**
         * @param integer $position
         * @param string $attributeName
         * @param array $availableCustomFieldAttributes
         * @param CustomFieldData $customFieldData
         * @param array $mappingData
         */
        public function __construct($position,
                                    $attributeName,
                                    $availableCustomFieldAttributes,
                                    $customFieldData,
                                    $mappingData)
        {
            assert('is_int($position)');
            assert('is_string($attributeName) || $attributeName == null');
            assert('is_array($availableCustomFieldAttributes)');
            assert('$customFieldData instanceof CustomFieldData || $customFieldData == null');
            assert('is_array($mappingData) || $mappingData == null');
            $this->position                       = $position;
            $this->attributeName                  = $attributeName;
            $this->availableCustomFieldAttributes = $availableCustomFieldAttributes;
            $this->customFieldData                = $customFieldData;
            $this->mappingData                    = $mappingData;
        }

        /**
         * Sets $allowAttributeSelection to false. This method is called when a higher 'level' mapping is required first
         * before an attribute can be selected at this level.
         */
        public function doNotAllowAttributeSelection()
        {
            $this->allowAttributeSelection = false;
        }

        public function allowsAttributeSelection()
        {
            return $this->allowAttributeSelection;
        }

        public function getTitle()
        {
            return Yii::t('Default', 'Level: {number}', array('{number}' => ($this->position + 1)));
        }

        public function getPosition()
        {
            return $this->position;
        }

        public function getAttributeName()
        {
            return $this->attributeName;
        }

        public function getAvailableCustomFieldAttributes()
        {
            return $this->availableCustomFieldAttributes;
        }

        /**
         * In the event that this 'level' requires a higher level to be selected first, then a string with message
         * content will ber returned.
         */
        public function getSelectHigherLevelFirstMessage()
        {
            if ($this->allowsAttributeSelection())
            {
                throw new NotSupportedException();
            }
            return Yii::t('Default', 'First select level {number}', array('{number}' => ($this->position)));
        }

        public function getCustomFieldData()
        {
            return $this->customFieldData;
        }

        /**
         * Given a value, return the mapped parent value.
         * @param string $value
         */
        public function getMappingDataSelectedParentValueByValue($value)
        {
            assert('is_string($value)');
            if (isset($this->mappingData[$value]))
            {
                return $this->mappingData[$value];
            }
            return null;
        }
    }
?>