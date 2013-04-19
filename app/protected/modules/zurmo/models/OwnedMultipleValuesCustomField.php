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
     * A MultipleValuesCustomField owned by a SecurableItem in the sense that it is
     * included in a relation with RedBeanModel::OWNED - its lifetime
     * is controlled by the owning model. SecurableItems are secured
     * and auditable and so the related models that they own are secured
     * and auditable.
     */
    class OwnedMultipleValuesCustomField extends MultipleValuesCustomField
    {
        /**
         * OwnedMultipleValuesCustomField does not need to have a bean because it stores no attributes and has no relations
         * @see RedBeanModel::canHaveBean();
         * @var boolean
         */
        private static $canHaveBean = false;

        // On changing a member value the original value
        // is saved (ie: on change it again the original
        // value is not overwritten) so that on save the
        // changes can be written to the audit log.
        public $originalAttributeValues = array();

        /**
         * Array of original data.
         * @var array
         */
        private $originalCustomFieldValuesData = array();

        /**
         * Whether the @see $originalCustomFieldValuesData has been processed yet
         * @var boolean
         */
        private $originalCustomFieldValuesDataProcessed = false;

       /**
         * @see RedBeanModel::getHasBean()
         */
        public static function getCanHaveBean()
        {
            if (get_called_class() == 'OwnedMultipleValuesCustomField')
            {
                return self::$canHaveBean;
            }
            return parent::getCanHaveBean();
        }

        public function __set($attributeName, $value)
        {
            AuditUtil::saveOriginalAttributeValue($this, $attributeName, $value);
            parent::__set($attributeName, $value);
        }

        /**
         * Method extended to provide some additional logic.  Because the relation 'values' data is going to be processed
         * for audit in this class, the original data for 'values' needs to be stored when a __get is performed. Normally
         * during __set this is performed, but we cannot do this because we can't override the __set in the 'values' class.
         * The 'values' class is a RedBeanOneToManyRelationModels.  This data then will be further processed in the
         * afterSave method.
         * (non-PHPdoc)
         * @see RedBeanModel::__get()
         * @see $this->afterSave
         */
        public function __get($attributeName)
        {
            $value = parent::__get($attributeName);
            if ($attributeName == 'values')
            {
                if (!$this->originalCustomFieldValuesDataProcessed)
                {
                    $data = $value->getStringifiedData();
                    if (count($data) > 0)
                    {
                        $this->originalCustomFieldValuesData = $data;
                    }
                    $this->originalCustomFieldValuesDataProcessed = true;
                }
            }
            return $value;
        }

        /**
         * Utilized by workflow engine.  Resolves and returns data only if a change has been made to 'values'.  Otherwise
         * returns null.  Make sure to use === when evaluating this for null.
         * Be careful when calling this as this will not always contain the correct information
         * if you didn't already call ->values previously.
         * @return array
         */
        public function resolveOriginalCustomFieldValuesDataForNewData()
        {
            if ($this->values->count() != count($this->originalCustomFieldValuesData))
            {
                return $this->originalCustomFieldValuesData;
            }
            foreach ($this->values as $customFieldValue)
            {
                if (!in_array($customFieldValue->value, $this->originalCustomFieldValuesData))
                {
                    return $this->originalCustomFieldValuesData;
                }
            }
            return null;
        }

        public function save($runValidation = true, array $attributeNames = null)
        {
            AuditUtil::throwNotSupportedExceptionIfNotCalledFromAnItem();
            return parent::save($runValidation, $attributeNames);
        }

        /**
         * Extended method to properly process 'values' audit information.  The original information is stored during
         * a __get call.
         * (non-PHPdoc)
         * @see RedBeanModel::afterSave()
         * @see $this->__get
         */
        protected function afterSave()
        {
            if ($this->originalCustomFieldValuesDataProcessed)
            {
                $newData = $this->values->getStringifiedData();
                $oldData = $this->originalCustomFieldValuesData;
                if ($oldData != $newData)
                {
                    $this->originalAttributeValues['values'] = $oldData;
                }
            }
            parent::afterSave();
        }

        public function forgetOriginalAttributeValues()
        {
            $this->unrestrictedSet('originalAttributeValues', array());
            $this->originalCustomFieldValuesData           = array();
            $this->originalCustomFieldValuesDataProcessed  = false;
        }
    }
?>