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
     * A MultipleValuesCustomField owned by a SecurableItem in the sense that it is
     * included in a relation with RedBeanModel::OWNED - its lifetime
     * is controlled by the owning model. SecurableItems are secured
     * and auditable and so the related models that they own are secured
     * and auditable.
     */
    class OwnedMultipleValuesCustomField extends MultipleValuesCustomField
    {
        // On changing a member value the original value
        // is saved (ie: on change it again the original
        // value is not overwritten) so that on save the
        // changes can be written to the audit log.
        public $originalAttributeValues = array();

        /**
         * Array of original data.
         * @var array
         */
        private $originalCustomFieldValuesData;

        /**
         * Whether the @see $originalCustomFieldValuesData has been processed yet
         * @var boolean
         */
        private $originalCustomFieldValuesDataProcessed = false;

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
            if($attributeName == 'values')
            {
                if(!$this->originalCustomFieldValuesDataProcessed)
                {
                    $data = $value->getStringifiedData();
                    if(count($data) > 0)
                    {
                        $this->originalCustomFieldValuesData = $data;
                    }
                    $this->originalCustomFieldValuesDataProcessed = true;
                }
            }
            return $value;
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
            if($this->originalCustomFieldValuesDataProcessed)
            {
                $newData = $this->values->getStringifiedData();
                $oldData = $this->originalCustomFieldValuesData;
                if($oldData != $newData)
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
