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
     * Contains all of the models of a particular type associated with a model in
     * a one to many relationship. Once added this controls the lifetime of the
     * models.
     */
    abstract class RedBeanMutableRelatedModels extends RedBeanModels
    {
        protected $modified = false;

        /**
         * Contains beans that need relating on save.
         * They are not related on add so that if save is
         * never called nothing is left in the database.
         */
        protected $deferredRelateBeans = array();

        /**
         * Contains beans that need unrelating on save.
         * They are not unrelated on remove so that if save is
         * never called the relation is still in the database.
         */
        protected $deferredUnrelateBeans = array();

        public function contains(RedBeanModel $model)
        {
            // Just adds self consistency checking.
            $contains = parent::contains($model);
            assert('$contains == '                                                                       .
                   '(array_search($model->getClassBean($this->modelClassName), $this->relatedBeansAndModels) !== false || ' .
                   ' array_search($model,                                      $this->relatedBeansAndModels) !== false)');
            return $contains;
        }

        /**
         * Adds a related model.
         */
        public function add(RedBeanModel $model)
        {
            assert("\$model instanceof {$this->modelClassName}");
            assert('!$this->contains($model)');
            assert('($oldCount = $this->count()) > -1'); // To save the value only when asserts are enabled.
            $bean = $model->getClassBean($this->modelClassName);
            $this->deferredRelateBeans[] = $bean;
            if (in_array($bean, $this->deferredUnrelateBeans))
            {
                self::array_remove_object($this->deferredUnrelateBeans, $bean);
            }
            $this->relatedBeansAndModels[] = $model;
            $this->modified = true;
            assert('$this->count() == $oldCount + 1');
            assert('$this->contains($model)');
        }

        /**
         * Unrelates a model.
         */
        public function remove(RedBeanModel $model)
        {
            assert("\$model instanceof {$this->modelClassName}");
            assert('$this->contains($model)');
            assert('($oldCount = $this->count()) > -1'); // To save the value only when asserts are enabled.
            for ($i = 0; $i < $this->count(); $i++)
            {
                if ($this->getByIndex($i)->isSame($model))
                {
                    $this->removeByIndex($i);
                }
            }
            assert('$this->count() == $oldCount - 1');
            assert('!$this->contains($model)');
        }

        /**
         * Unrelates a model by index.
         */
        public function removeByIndex($i)
        {
            assert('is_int($i)');
            assert('$i >= 0');
            assert('$i < $this->count()');
            assert('($oldCount = $this->count()) > -1'); // To save the value only when asserts are enabled.
            $model = $this[$i];
            $bean = $model->getClassBean($this->modelClassName);
            $this->deferredUnrelateBeans[] = $bean;
            if (in_array($bean, $this->deferredUnrelateBeans))
            {
                self::array_remove_object($this->deferredRelateBeans, $bean);
            }
            unset($this->relatedBeansAndModels[$i]);
            $this->relatedBeansAndModels = array_values($this->relatedBeansAndModels);
            $this->modified = true;
            assert('$this->count() == $oldCount - 1');
        }

        protected static function array_remove_object(&$array, $object)
        {
             // For anything other than an object this idiotic
             // method is not needed, unset will do the job.
            assert('gettype($object) == "object"');
            // This probably calls for a comment. All I want to do is remove the element
            // from the array that contains an $object. The comment I'd like to make is
            // that there must be a less silly way than this surely!??
            for ($i = 0; $i < count($array); $i++)
            {
                if ($array[$i] == $object)
                {
                    unset($array[$i]);
                    $array = array_values($array);
                    break;
                }
            }
        }

        /**
         * Unrelates all of the related models.
         */
        public function removeAll()
        {
            $oldCount = $this->count();
            while ($this->count() > 0)
            {
                $this->removeByIndex(0);
            }
            $this->modified = $oldCount > 0;
            assert('$this->count() == 0');
        }

        /**
         * Returns the errors for the related models.
         * @param $attributeNameOrNames See RedNeamModel::getErrors().
         */
        public function getErrors($attributeNameOrNames = null)
        {
            $allErrors = array();
            for ($i = 0; $i < $this->count(); $i++)
            {
                $errors = $this[$i]->getErrors($attributeNameOrNames);
                if (count($errors) > 0)
                {
                    $allErrors[$i] = $errors;
                }
            }
            return $allErrors;
        }

        public function save($runValidation = true)
        {
            $saved = parent::save($runValidation);
            if ($saved)
            {
                $this->modified = false;
            }
            return $saved;
        }

        /**
         * Returns true if any of the models have been added to or removed
         * from the collection or of any models in the collection have been
         * modified.
         */
        public function isModified()
        {
            return $this->modified || parent::isModified();
        }
    }
?>
