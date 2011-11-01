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
     * Contains all of the models of a particular type, or a selection
     * of models of a particular type using a where clause, or all of the
     * models of a particular type associated with a bean in a one to many
     * relationship.
     */
    class RedBeanModels implements ArrayAccess, Iterator, Countable
    {
        protected $modelClassName;

        /**
         * The bean for the model to which these related models are related.
         */
        protected $bean;

        /**
         * Contains the related beans, until the related model has been
         * retrieved, at which point it contains the model. The bean for
         * that model can then be retrieved with getPrimaryBean().
         */
        protected $relatedBeansAndModels = array();

        private $position;

        /**
         * Constructs a new RedBeanModels which is a collection of classes extending model.
         * The models are created lazily.
         * Models are only constructed with beans by the model. Beans are
         * never used by the application directly.
         * The application can construct a new models object by constructing
         * them without specifying a bean. In other words, if Php had overloading
         * and friends the constructor without the $bean would be public, and the
         * constructor taking a bean would private and available only to RedBeanModel.
         */
        public function __construct($modelClassName, $sqlOrBean = '')
        {
            assert('is_string($sqlOrBean) || $sqlOrBean instanceof RedBean_OODBBean');
            $this->modelClassName = $modelClassName;
            $this->position = 0;
            $tableName = RedBeanModel::getTableName($modelClassName);
            if (is_string($sqlOrBean))
            {
                $this->relatedBeansAndModels = array_values(RedBean_Plugin_Finder::where($tableName, $sqlOrBean));
            }
            else
            {
                assert('$sqlOrBean instanceof RedBean_OODBBean');
                $this->bean = $sqlOrBean;
                // I had this...
                // $this->relatedBeansAndModels = array_values(R::related($this->bean, $tableName));
                // and the doco says to use related in place of findLinks which is deprecated,
                // but that is returning zero when I know there are linked beans.
                try
                {
                    // So I'm getting them with the link manager, but that can
                    // throw if the table or column doesn't exist yet because there
                    // are no linked beans.
                    if ($this->bean->id > 0)
                    {
                        $relatedIds                  = R::$linkManager->getKeys($this->bean, $tableName);
                        $this->relatedBeansAndModels = array_values(R::batch($tableName, $relatedIds));
                    }
                    else
                    {
                        $this->relatedBeansAndModels = array();
                    }
                }
                catch (RedBean_Exception_SQL $e)
                {
                    // SQLSTATE[42S02]: Base table or view not found...
                    // SQLSTATE[42S22]: Column not found...
                    if (!in_array($e->getSQLState(), array('42S02', '42S22')))
                    {
                        throw $e;
                    }
                    // If there is nothing yet linked
                    // just have no related models yet.
                    $this->relatedBeansAndModels = array();
                }
            }
        }

        /**
         * Returns the displayable string for the collection.
         * @return A string.
         */
        public function __toString()
        {
            return $this->count(). ' ' . Yii::t('Default', 'records') . '.';
        }

        /**
         * Implements ArrayAccess::offsetSet(). See the php documentation.
         */
        public function offsetSet($i, $value)
        {
            throw new NotSupportedException();
        }

        /**
         * Implements ArrayAccess::offsetExists(). See the php documentation.
         */
        public function offsetExists($i)
        {
            return $i < $this->count();
        }

        /**
         * Implements ArrayAccess::offsetUnset(). See the php documentation.
         */
        public function offsetUnset($i)
        {
            throw new NotSupportedException();
        }

        /**
         * Implements ArrayAccess::offsetGet(). See the php documentation.
         */
        public function offsetGet($i)
        {
            if ($i < $this->count())
            {
                return $this->getByIndex($i);
            }
            else
            {
                return null;
            }
        }

        /**
         * Implements Iterator::rewind(). See the php documentation.
         */
        public function rewind()
        {
            $this->position = 0;
        }

        /**
         * Implements Iterator::current(). See the php documentation.
         */
        function current()
        {
            return $this[$this->position];
        }

        /**
         * Implements Iterator::key(). See the php documentation.
         */
        function key()
        {
            return $this->position;
        }

        /**
         * Implements Iterator::next(). See the php documentation.
         */
        function next()
        {
            $this->position++;
        }

        /**
         * Implements Iterator::valid(). See the php documentation.
         */
        function valid()
        {
            return $this->position < $this->count();
        }

        /**
         * Returns the count of models in the collection.
         * Implements Countable::count().
         */
        public function count()
        {
            return count($this->relatedBeansAndModels);
        }

        /**
         * Returns whether the given model is already in the collection.
         */
         public function contains(RedBeanModel $model)
         {
            foreach ($this as $containedModel)
            {
                if ($containedModel->isSame($model))
                {
                    return true;
                }
            }
            return false;
         }

        /**
         * Returns a model by index. Used by Iterator.
         * @param $i An integer index >= 0 and < count().
         */
        protected function getByIndex($i)
        {
            assert('is_int($i)');
            assert('$i >= 0');
            assert('$i < $this->count()');
            $beanOrModel = $this->relatedBeansAndModels[$i];
            if ($beanOrModel instanceof RedBean_OODBBean)
            {
                $model = RedBeanModel::makeModel($beanOrModel, $this->modelClassName);
                $this->relatedBeansAndModels[$i] = $model;
            }
            return $this->relatedBeansAndModels[$i];
        }

        /**
         * Returns true if any of the models in the collection
         * have errors.
         */
        public function hasErrors($attributeNameOrNames)
        {
            assert('$attributeNameOrNames === null   || ' .
                   'is_string($attributeNameOrNames) || ' .
                   'is_array ($attributeNameOrNames) && AssertUtil::all($attributeNameOrNames, "is_string")');
            for ($i = 0; $i < $this->count(); $i++)
            {
                if ($this->relatedBeansAndModels[$i] instanceof RedBeanModel)
                {
                    $model = $this->relatedBeansAndModels[$i];
                    if ($model->hasErrors($attributeNameOrNames))
                    {
                        return true;
                    }
                }
            }
            return false;
        }

        /**
         * Validates all of the models in the collection that have been lazily
         * retrieved.
         */
        public function validate(array $attributeNames = null)
        {
            $hasErrors = false;
            for ($i = 0; $i < $this->count(); $i++)
            {
                if ($this->relatedBeansAndModels[$i] instanceof RedBeanModel)
                {
                    $model  = $this->relatedBeansAndModels[$i];
                    if (!$model->validate($attributeNames))
                    {
                        $hasErrors = true;
                    }
                }
            }
            return !$hasErrors;
        }

        /**
         * Saves all of the models in the collection that have been lazily
         * retrieved.
         */
        public function save($runValidation = true)
        {
            for ($i = 0; $i < $this->count(); $i++)
            {
                if ($this->relatedBeansAndModels[$i] instanceof RedBeanModel)
                {
                    if (!$this->relatedBeansAndModels[$i]->save($runValidation))
                    {
                        return false;
                    }
                }
            }
            return true;
        }

        /**
         * Returns true if any of the models in the collection have been
         * modified.
         */
        public function isModified()
        {
            for ($i = 0; $i < $this->count(); $i++)
            {
                if ($this->relatedBeansAndModels[$i] instanceof RedBeanModel)
                {
                    $model  = $this->relatedBeansAndModels[$i];
                    if ($model->isModified())
                    {
                        return true;
                    }
                }
            }
            return false;
        }
    }
?>
