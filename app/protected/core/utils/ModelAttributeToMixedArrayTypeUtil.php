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
     * Helper functionality for finding the element or
     * form type associated with a model's attribute.
     */
    class ModelAttributeToMixedArrayTypeUtil extends ModelAttributeToMixedTypeUtil
    {
        /**
         * Returns the a type that is derived by looking at several different components of an attribute.  This
         * includes the metadata element data, validator data, and relation information.  The type returned can
         * be utilized by different aspects of the application where an attribute type is needed.
         * @return string type
         */
        public static function getType($model, $attributeName)
        {
            assert('$model instanceof RedBeanModel || $model instanceof ModelForm');
            assert('is_string($attributeName) && $attributeName != ""');
            $metadata = $model->getMetadata();
            foreach ($metadata as $className => $perClassMetadata)
            {
                if (isset($perClassMetadata['elements'][$attributeName]))
                {
                    return $perClassMetadata['elements'][$attributeName];
                }
            }
            if ($model->isRelation($attributeName))
            {
                if ($model->getRelationModelClassName($attributeName) == 'User')
                {
                    return 'User';
                }
                elseif ($model->getRelationModelClassName($attributeName) == 'Currency')
                {
                    return 'Currency';
                }
                return 'DropDown';
            }
            else
            {
                $typeByValidator = static::getTypeByModelUsingValidator($model, $attributeName);
                if ($typeByValidator != null)
                {
                    return $typeByValidator;
                }
            }
            return 'Text';
        }
    }
?>
