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

    $basePath = Yii::app()->getBasePath();
    require_once("$basePath/../../redbean/rb.php");

    /**
     * Relates models as RedBean associations, so that
     * the relationship is M:N via a join table.
     */
    class RedBeanManyToManyRelatedModels extends RedBeanMutableRelatedModels
    {
        protected $inside = false;

        /**
         * Constructs a new RedBeanModels which is a collection of classes extending model.
         * The models are created lazily.
         * Models are only constructed with beans by the model. Beans are
         * never used by the application directly.
         */
        public function __construct(RedBean_OODBBean $bean, $modelClassName)
        {
            assert('is_string($modelClassName)');
            assert('$modelClassName != ""');
            $this->modelClassName = $modelClassName;
            $tableName  = RedBeanModel::getTableName($modelClassName);
            $this->bean = $bean;
            $this->relatedBeansAndModels = array_values(R::related($this->bean, $tableName));
        }

        public function getErrors($attributeNameOrNames = null)
        {
            if (!$this->inside)
            {
                $this->inside = true;
                $errors = parent::getErrors($attributeNameOrNames);
                $this->inside = false;
            }
            else
            {
                $errors = array();
            }
            return $errors;
        }

        public function validate(array $attributeNames = null)
        {
            // Many many relations do not validation the related models.
            return true;
        }

        public function save($runValidation = true)
        {
            foreach ($this->deferredRelateBeans as $bean)
            {
                R::associate($this->bean, $bean);
                if (!RedBeanDatabase::isFrozen())
                {
                    $types = array($this->bean->getMeta("type"), $bean->getMeta("type"));
                    sort($types);
                    $tableName = implode("_", $types);
                    foreach ($types as $type)
                    {
                        $columnName = "{$type}_id";
                        RedBean_Plugin_Optimizer_Id::ensureIdColumnIsINT11($tableName, $columnName);
                    }
                }
            }
            $this->deferredRelateBeans = array();
            foreach ($this->deferredUnrelateBeans as $bean)
            {
                R::unassociate($this->bean, $bean);
            }
            $this->deferredUnrelateBeans = array();
            return true;
        }
    }
?>
