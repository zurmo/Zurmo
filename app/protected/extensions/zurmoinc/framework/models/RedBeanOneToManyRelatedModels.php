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

    $basePath = Yii::app()->getBasePath();
    require_once("$basePath/../../redbean/rb.php");

    /**
     * Relates models as RedBean links, so that
     * the relationship is 1:M via a foreign key.
     */
    class RedBeanOneToManyRelatedModels extends RedBeanMutableRelatedModels
    {
        protected $relatedModelClassname;
        protected $owns;

        /**
         * Constructs a new RedBeanOneToManyRelatedModels. The models are retrieved lazily.
         * RedBeanOneToManyRelatedModels are only constructed with beans by the model.
         * Beans are never used by the application directly.
         */
        public function __construct(RedBean_OODBBean $bean, $modelClassName, $relatedModelClassName, $owns)
        {
            assert('is_string($modelClassName)');
            assert('is_string($relatedModelClassName)');
            assert('$modelClassName        != ""');
            assert('$relatedModelClassName != ""');
            assert('is_bool($owns)');
            parent::__construct($modelClassName, $bean);
            $this->relatedModelClassName = $relatedModelClassName;
            $this->owns = $owns;
        }

        public function save($runValidation = true)
        {
            if (!parent::save($runValidation))
            {
                return false;
            }
            foreach ($this->deferredRelateBeans as $bean)
            {
                R::$linkManager->link($bean, $this->bean);
                if (!RedBeanDatabase::isFrozen())
                {
                    $tableName  = RedBeanModel::getTableName($this->modelClassName);
                    $columnName = RedBeanModel::getTableName($this->relatedModelClassName) . '_id';
                    RedBean_Plugin_Optimizer_Id::ensureIdColumnIsINT11($tableName, $columnName);
                }
                R::store($bean);
            }
            $this->deferredRelateBeans = array();
            $tableName = RedBeanModel::getTableName($this->relatedModelClassName);
            foreach ($this->deferredUnrelateBeans as $bean)
            {
                if (!$this->owns)
                {
                    R::$linkManager->breakLink($bean, $tableName);
                    R::store($bean);
                }
                else
                {
                    R::trash($bean);
                }
            }
            $this->deferredUnrelateBeans = array();
            return true;
        }
    }
?>
