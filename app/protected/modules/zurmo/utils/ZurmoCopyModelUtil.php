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
     * Utility for copying the attributes from one model to another. Utilized by 'duplicate' functionality accessed
     * from a detail view for a model.
     */
    class ZurmoCopyModelUtil
    {
        /**
         * Copy attributes from one model to another. If the attributes are relations, then only copy when it is a
         * HAS_ONE variant.  In the case that the relation is an OwnedModel, take special consideration for CurrencyValue
         * CustomField, and MultipleValuesCustomField models. If it is owned and not one of those 3, then it should just
         * copy the OwnedModel nonRelation attributes. An example of that would be Address or Email
         * @param RedBeanModel $model
         * @param RedBeanModel $copyToModel - model to copy attribute values from $model to
         */
        public static function copy(RedBeanModel $model, RedBeanModel $copyToModel)
        {
            $copyToModel->setIsCopied();
            foreach ($model->attributeNames() as $attributeName)
            {
                $isReadOnly = $model->isAttributeReadOnly($attributeName);
                if (!$model->isRelation($attributeName) && !$isReadOnly)
                {
                    static::copyNonRelation($model, $attributeName, $copyToModel);
                }
                elseif($model->isRelation($attributeName) && !$isReadOnly &&
                       $model->isRelationTypeAHasOneVariant($attributeName))
                {
                    static::copyRelation($model, $attributeName, $copyToModel);
                }
            }
            static::resolveExplicitPermissions($model, $copyToModel);
        }

        protected static function copyNonRelation(RedBeanModel $model, $attributeName, RedBeanModel $copyToModel)
        {
            $copyToModel->{$attributeName} = $model->{$attributeName};
        }

        protected static function copyRelation(RedBeanModel $model, $attributeName, RedBeanModel $copyToModel)
        {
            if($model->{$attributeName} instanceof CurrencyValue)
            {
                $currencyValue                 = new CurrencyValue();
                $currencyValue->value          = $model->{$attributeName}->value;
                $currencyValue->rateToBase     = $model->{$attributeName}->rateToBase;
                $currencyValue->currency       = $model->{$attributeName}->currency;
                $copyToModel->{$attributeName} = $currencyValue;
            }
            elseif($model->{$attributeName} instanceof OwnedModel)
            {
                static::copyOwnedModelRelation($model, $attributeName, $copyToModel);
            }
            elseif($model->{$attributeName} instanceof CustomField)
            {
                static::copyNonRelation($model->{$attributeName}, 'value', $copyToModel->{$attributeName});
            }
            elseif($model->{$attributeName} instanceof MultipleValuesCustomField)
            {
                static::copyMultipleValuesCustomFieldRelation($model, $attributeName, $copyToModel);
            }
            elseif(!$model->isOwnedRelation($attributeName))
            {
                static::copyNonRelation($model, $attributeName, $copyToModel);
            }
            else
            {
                //Not supported for copy
            }
        }

        protected static function copyOwnedModelRelation(RedBeanModel $model, $attributeName, RedBeanModel $copyToModel)
        {
            $relatedModelClassName         = get_class($model->{$attributeName});
            $relatedModel                  = new $relatedModelClassName();
            foreach($relatedModel->getAttributeNames() as $relatedAttributeName)
            {
                if(!$relatedModel->isRelation($relatedAttributeName) && !$relatedModel->isAttributeReadOnly($relatedAttributeName))
                {
                    static::copyNonRelation($model->{$attributeName}, $relatedAttributeName, $relatedModel);
                }
            }
            $copyToModel->{$attributeName} = $relatedModel;
        }

        protected static function copyMultipleValuesCustomFieldRelation(RedBeanModel $model, $attributeName, RedBeanModel $copyToModel)
        {
            foreach($model->{$attributeName}->values as $customFieldValue)
            {
                $newCustomFieldValue = new CustomFieldValue();
                $newCustomFieldValue->value = $customFieldValue->value;
                $copyToModel->{$attributeName}->values->add($newCustomFieldValue);
            }
        }

        protected static function resolveExplicitPermissions(RedBeanModel $model, RedBeanModel $copyToModel)
        {
            if($model instanceof SecurableItem)
            {
                $explicitReadWriteModelPermissions = ExplicitReadWriteModelPermissionsUtil::makeBySecurableItem($model);
                ExplicitReadWriteModelPermissionsUtil::
                resolveExplicitReadWriteModelPermissionsForDisplay($copyToModel, $explicitReadWriteModelPermissions);
            }
        }
    }
?>