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
     * Sanitizers that support the use of external system ids as possible values should extend this class.
     */
    abstract class ExternalSystemIdSuppportedSanitizerUtil extends SanitizerUtil
    {
        /**
         * Max allowed length of a value when the type of value is IdValueTypeMappingRuleForm::EXTERNAL_SYSTEM_ID
         * @var integer
         */
        protected $externalSystemIdMaxLength = 40;

        /**
         * When the attribute is expected to be a relation. This is the model class name for that relation.
         * @var string
         */
        protected $attributeModelClassName;

        /**
         * Given an external system id and model class name, try to find the associated model if it exists. If it is
         * not found, a NotFoundException will be thrown.  Otherwise the model will be made and returned.
         * @param string $id
         * @param string $modelClassName
         * @return RedBeanModel $model
         * @throws NotFoundException
         */
        public static function getModelByExternalSystemIdAndModelClassName($id, $modelClassName)
        {
            assert('$id != null && is_string($id)');
            assert('is_string($modelClassName)');
            $tableName = $modelClassName::getTableName($modelClassName);
            $beans = R::find($tableName, ExternalSystemIdUtil::EXTERNAL_SYSTEM_ID_COLUMN_NAME . " = '$id'");
            assert('count($beans) <= 1');
            if (count($beans) == 0)
            {
                throw new NotFoundException();
            }
            return RedBeanModel::makeModel(end($beans), $modelClassName);
        }

        /**
         * Tries to find the value in the system. If found, returns true, otherwise false.
         * @param string $value
         * @return boolean
         */
        protected function resolveFoundIdByValue($value)
        {
            assert('is_int($value) || is_string($value) || $value == null');
            if ($value == null)
            {
                return false;
            }
            elseif (is_int($value))
            {
                $sqlReadyString = $value;
            }
            else
            {
                $sqlReadyString = '\'' . $value . '\'';
            }
            $modelClassName = $this->attributeModelClassName;
            $sql = 'select id from ' . $modelClassName::getTableName($modelClassName) .
                   ' where id = ' . $sqlReadyString . ' limit 1';
            $ids =  R::getCol($sql);
            assert('count($ids) <= 1');
            if (count($ids) == 0)
            {
                return false;
            }
            return true;
        }

        /**
         * Given a model and an attribute, return the model class name for the attribute.
         * @param RedBeanModel $model
         * @param string $attributeName
         * @return string $attributeModelClassName
         */
        protected function resolveAttributeModelClassName(RedBeanModel $model, $attributeName)
        {
            assert('is_string($attributeName)');
            if ($attributeName == 'id')
            {
                return get_class($model);
            }
            return $model->getRelationModelClassName($attributeName);
        }

        protected function resolveForFoundModel()
        {
            $label = Zurmo::t('ImportModule', 'Is an existing record and will be updated.');
            $this->analysisMessages[] = $label;
        }

        /**
         * Tries to find the value in the system. If found, returns true, otherwise false.
         * @param string $value
         * @return boolean
         */
        protected function resolveFoundExternalSystemIdByValue($value)
        {
            assert('is_int($value) || is_string($value) || $value == null');
            if ($value == null)
            {
                return false;
            }
            $modelClassName = $this->attributeModelClassName;
            $columnName     = ExternalSystemIdUtil::EXTERNAL_SYSTEM_ID_COLUMN_NAME;
            $sql = 'select id from ' . $modelClassName::getTableName($modelClassName) .
                ' where ' . $columnName . ' = \'' . $value . '\' limit 1';
            $ids =  R::getCol($sql);
            assert('count($ids) <= 1');
            if (count($ids) == 0)
            {
                return false;
            }
            return true;
        }
    }
?>