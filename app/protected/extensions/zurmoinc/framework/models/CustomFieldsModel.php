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

    class CustomFieldsModel extends RedBeanModel
    {
        protected function constructDerived($bean, $setDefaults)
        {
            assert('$bean === null || $bean instanceof RedBean_OODBBean');
            assert('is_bool($setDefaults)');
            parent::constructDerived($bean, $setDefaults);
            $metadata = $this->getMetadata();
            foreach ($metadata as $unused => $classMetadata)
            {
                if (isset($classMetadata['customFields']))
                {
                    foreach ($classMetadata['customFields'] as $customFieldName => $customFieldDataName)
                    {
                        $customField     = $this->unrestrictedGet($customFieldName);
                        $customFieldData = CustomFieldData::getByName($customFieldDataName);
                        if ($bean === null)
                        {
                            if ($customField instanceof CustomField &&
                                ($customField->value === null || $customField->value === '') && $setDefaults)
                            {
                                $customField->value = $customFieldData->defaultValue;
                            }
                            elseif ($customField instanceof MultipleValuesCustomField &&
                                 $customField->values->count() == 0 && $setDefaults)
                            {
                                $customFieldValue = new CustomFieldValue();
                                $customFieldValue->value = $customFieldData->defaultValue;
                                $customField->values->add($customFieldValue);
                            }
                        }
                        $customField->data = $customFieldData;
                    }
                }
            }
        }

        /**
         * Utilized for existing models, that were not originally saved with a new custom field.  This will be utilized
         * to ensure cached models properly generate information needed by the model.
         * (non-PHPdoc)
         * @see RedBeanModel::constructIncomplete()
         */
        protected function constructIncomplete($bean)
        {
            assert('$bean === null || $bean instanceof RedBean_OODBBean');
            $metadata = $this->getMetadata();
            foreach ($metadata as $unused => $classMetadata)
            {
                if (isset($classMetadata['customFields']))
                {
                    foreach ($classMetadata['customFields'] as $customFieldName => $customFieldDataName)
                    {
                        $customField       = $this->unrestrictedGet($customFieldName);
                        $customFieldData   = CustomFieldData::getByName($customFieldDataName);
                        $customField->data = $customFieldData;
                    }
                }
            }
        }
    }
?>
