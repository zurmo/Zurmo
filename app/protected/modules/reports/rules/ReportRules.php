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
     * Base class of report rules that assist with reporting data.  Extend this class to make
     * a set of ReportRules that is for a specific module or a combiniation of modules and/or models.
     */
    abstract class ReportRules extends ModelToComponentRules
    {
        public static function getRulesName()
        {
            return 'ReportRules';
        }

        /**
         * Some relations such as a CustomField are shown as non-related nodes in the report wizard. For a custom field
         * this method would return true for example.  Whereas account -> opportunities would return false.
         * @param RedBeanModel $model
         * @param $relation
         * @return bool
         */
        public function relationIsReportedAsAttribute(RedBeanModel $model, $relation)
        {
            assert('is_string($relation)');
            $modelClassName = $model->getAttributeModelClassName($relation);
            $metadata       = static::getMetadata();
            if (isset($metadata[$modelClassName]) && isset($metadata[$modelClassName]['relationsReportedAsAttributes']) &&
            in_array($relation, $metadata[$modelClassName]['relationsReportedAsAttributes']))
            {
                return true;
            }

            if (in_array($model->getRelationModelClassName($relation),
                        array('OwnedCustomField',
                              'CustomField',
                              'OwnedMultipleValuesCustomField',
                              'MultipleValuesCustomField',
                              'CurrencyValue')))
            {
                return true;
            }
            return false;
        }

        /**
         * @param RedBeanModel $model
         * @param $attribute
         * @return bool
         */
        public function attributeIsReportable(RedBeanModel $model, $attribute)
        {
            assert('is_string($attribute)');
            $modelClassName = $model->getAttributeModelClassName($attribute);
            $metadata = static::getMetadata();
            if (isset($metadata[$modelClassName]) && isset($metadata[$modelClassName]['nonReportable']) &&
            in_array($attribute, $metadata[$modelClassName]['nonReportable']))
            {
                return false;
            }
            return true;
        }

        /**
         * @param RedBeanModel $model
         * @param string $attribute
         * @return null | string
         */
        public function getFilterValueElementType(RedBeanModel $model, $attribute)
        {
            assert('is_string($attribute)');
            $modelClassName = $model->getAttributeModelClassName($attribute);
            $metadata = static::getMetadata();
            if (isset($metadata[$modelClassName]) && isset($metadata[$modelClassName]['filterValueElementTypes']) &&
               isset($attribute, $metadata[$modelClassName]['filterValueElementTypes'][$attribute]))
            {
                return $metadata[$modelClassName]['filterValueElementTypes'][$attribute];
            }
            return null;
        }

        /**
         * @param RedBeanModel $model
         * @param string $relation
         * @return string
         * @throws NotSupportedException if the relation is not really reported as an attribute
         */
        public function getSortAttributeForRelationReportedAsAttribute(RedBeanModel $model, $relation)
        {
            assert('is_string($relation)');
            $modelClassName = $model->getAttributeModelClassName($relation);
            $metadata       = static::getMetadata();
            if (isset($metadata[$modelClassName]) && isset($metadata[$modelClassName]['relationsReportedAsAttributes']) &&
                in_array($relation, $metadata[$modelClassName]['relationsReportedAsAttributes']))
            {
                if (isset($metadata[$modelClassName]['relationsReportedAsAttributesSortAttributes'][$relation]))
                {
                    return $metadata[$modelClassName]['relationsReportedAsAttributesSortAttributes'][$relation];
                }
                else
                {
                    throw new NotSupportedException('Relations that report as attributes must also have a defined sort attribute');
                }
            }
            if (in_array($model->getRelationModelClassName($relation),
                array('OwnedCustomField',
                      'CustomField',
                      'OwnedMultipleValuesCustomField',
                      'MultipleValuesCustomField',
                      'CurrencyValue')))
            {
                return 'value';
            }
            throw new NotSupportedException();
        }

        /**
         * @param RedBeanModel $model
         * @param string $relation
         * @return null|string
         * @throws NotSupportedException if the relation is not really reported as an attribute
         */
        public function getGroupByRelatedAttributeForRelationReportedAsAttribute(RedBeanModel $model, $relation)
        {
            assert('is_string($relation)');
            $modelClassName = $model->getAttributeModelClassName($relation);
            $metadata       = static::getMetadata();
            if (isset($metadata[$modelClassName]) && isset($metadata[$modelClassName]['relationsReportedAsAttributes']) &&
                in_array($relation, $metadata[$modelClassName]['relationsReportedAsAttributes']))
            {
                if (isset($metadata[$modelClassName]['relationsReportedAsAttributesGroupByAttributes'][$relation]))
                {
                    return $metadata[$modelClassName]['relationsReportedAsAttributesGroupByAttributes'][$relation];
                }
                else
                {
                    return null;
                }
            }
            if (in_array($model->getRelationModelClassName($relation),
                array(  'OwnedCustomField',
                        'CustomField',
                        'OwnedMultipleValuesCustomField',
                        'MultipleValuesCustomField',
                        'CurrencyValue')))
            {
                return 'value';
            }
            throw new NotSupportedException();
        }

        /**
         * @param RedBeanModel $model
         * @param $relation
         * @return null | string
         */
        public function getRawValueRelatedAttributeForRelationReportedAsAttribute(RedBeanModel $model, $relation)
        {
            assert('is_string($relation)');
            $modelClassName = $model->getAttributeModelClassName($relation);
            $metadata       = static::getMetadata();
            if (isset($metadata[$modelClassName]) && isset($metadata[$modelClassName]['relationsReportedAsAttributes']) &&
                in_array($relation, $metadata[$modelClassName]['relationsReportedAsAttributes']))
            {
                if (isset($metadata[$modelClassName]['relationsReportedAsAttributesGroupByAttributes'][$relation]))
                {
                    return $metadata[$modelClassName]['relationsReportedAsAttributesGroupByAttributes'][$relation];
                }
            }
        }
    }
?>