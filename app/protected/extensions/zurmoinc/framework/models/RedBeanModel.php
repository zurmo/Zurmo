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
     * Abstraction over the top of an application database accessed via
     * <a href="http://www.redbeanphp.com/">RedBean</a>. The base class for
     * an MVC model. Replaces the M part of MVC in Yii. Yii maps from the
     * database scheme to the objects, (good for database guys, not so good
     * for OO guys), this maps from objects to the database schema.
     *
     * A domain model is created by extending RedBeanModel and supplying
     * a getDefaultMetadata() method.
     *
     * Static getXxxx() methods can be supplied to query for the given domain
     * models, and instance methods should supply additional behaviour.
     *
     * getDefaultMetadata() returns an array of the class name mapped to
     * an array containing 'members' mapped to an array of member names,
     * (to be accessed as $model->memberName).
     *
     * It can then optionally have, 'relations' mapped
     * to an array of relation names, (to be accessed as $model->relationName),
     * mapped to its type, (the extending model class to which it relates).
     *
     * And it can then optionally have as well, 'rules' mapped to an array of
     * attribute names, (attributes are members and relations), a validator name,
     * and the parameters to the validator, if any, as per the Yii::CModel::rules()
     * method.See http://www.yiiframework.com/wiki/56/reference-model-rules-validation.
     *
     * These are used to automatically and dynamically create the database
     * schema on the fly as opposed to Yii's getting attributes from an
     * already existing schema.
     */
    abstract class RedBeanModel extends CComponent implements Serializable
    {
        // Models that have not been saved yet have no id as far
        // as the database is concerned. Until they are saved they are
        // assigned a negative id, so that they have identity.
        private static $nextPseudoId = -1;

        // The id of an unsaved model.
        private $pseudoId;

        // A model maps to one or more beans. If Person extends RedBeanModel
        // there is one bean, but if User then extends Person a User model
        // has two beans, the one holding the person data and the one holding
        // the extended User data. In this way in inheritance hierarchy from
        // model is normalized over several tables, one for each extending
        // class.
        private $modelClassNameToBean                            = array();
        private $attributeNameToBeanAndClassName                 = array();
        private $attributeNamesNotBelongsToOrManyMany            = array();
        private $relationNameToRelationTypeModelClassNameAndOwns = array();
        private $relationNameToRelatedModel                      = array();
        private $unlinkedRelationNames                           = array();
        private $validators                                      = array();
        private $attributeNameToErrors                           = array();
        private $scenarioName                                    = '';
        // An object is automatcally savable if it is new or contains
        // modified members or related objects.
        // If it is newly created and has never had any data put into it
        // it can be saved explicitly but it wont be saved automatically
        // when it is a related model and will be redispensed next
        // time it is referenced.
        protected $modified       = false;
        protected $deleted        = false;
        protected $isInIsModified = false;
        protected $isInHasErrors  = false;
        protected $isInGetErrors  = false;
        protected $isValidating   = false;
        protected $isSaving       = false;

        // Mapping of Yii validators to validators doing things that
        // are either required for RedBean, or that simply implement
        // The semantics that we want.
        private static $yiiValidatorsToRedBeanValidators = array(
            'CDefaultValueValidator' => 'RedBeanModelDefaultValueValidator',
            'CNumberValidator'       => 'RedBeanModelNumberValidator',
            'CTypeValidator'         => 'RedBeanModelTypeValidator',
            'CRequiredValidator'     => 'RedBeanModelRequiredValidator',
            'CUniqueValidator'       => 'RedBeanModelUniqueValidator',
            'defaultCalculatedDate'  => 'RedBeanModelDefaultCalculatedDateValidator',
            'readOnly'               => 'RedBeanModelReadOnlyValidator',
            'dateTimeDefault'        => 'RedBeanModelDateTimeDefaultValueValidator',
        );

        /**
         * Used in an extending class's getDefaultMetadata() method to specify
         * that a relation is 1:1 and that the class on the 1 side of the
         * relation.
         */
        const HAS_ONE_BELONGS_TO = 0;

        /**
         * Used in an extending class's getDefaultMetadata() method to specify
         * that a relation is 1:M and that the class on the M side of the
         * relation.
         * Note: Currently if you have a relation that is set to HAS_MANY_BELONGS_TO, then that relation name
         * must be the strtolower() same as the related model class name.  This is the current support for this
         * relation type.  If something different is set, an exception will be thrown.
         */
        const HAS_MANY_BELONGS_TO = 1;

        /**
         * Used in an extending class's getDefaultMetadata() method to specify
         * that a relation is 1:1.
         */
        const HAS_ONE    = 2;

        /**
         * Used in an extending class's getDefaultMetadata() method to specify
         * that a relation is 1:M and that the class is on the 1 side of the
         * relation.
         */
        const HAS_MANY   = 3;

        /**
         * Used in an extending class's getDefaultMetadata() method to specify
         * that a relation is M:N and that the class on the either side of the
         * relation.
         */
        const MANY_MANY  = 4;

        /**
         * Used in an extending class's getDefaultMetadata() method to specify
         * that a 1:1 or 1:M relation is one in which the left side of the relation
         * owns the model or models on the right side, meaning that if the model
         * is deleted it owns the related models and they are deleted along with it.
         * If not specified the related model is independent and is not deleted.
         */
        const OWNED = true;

        /**
         * Gets all the models from the database of the named model type.
         * @param $orderBy TODO
         * @param $modelClassName Pass only when getting it at runtime
         *                        gets the wrong name.
         * @return An array of models of the type of the extending model.
         */
        public static function getAll($orderBy = null, $sortDescending = false, $modelClassName = null)
        {
            assert('$orderBy        === null || is_string($orderBy)        && $orderBy        != ""');
            assert('is_bool($sortDescending)');
            assert('$modelClassName === null || is_string($modelClassName) && $modelClassName != ""');
            $quote = DatabaseCompatibilityUtil::getQuote();
            $orderBySql = null;
            if ($orderBy !== null)
            {
                $orderBySql = "$quote$orderBy$quote";
                if ($sortDescending)
                {
                    $orderBySql .= ' desc';
                }
            }
            return static::getSubset(null, null, null, null, $orderBySql, $modelClassName);
        }

        /**
         * Gets a range of models from the database of the named model type.
         * @param $modelClassName
         * @param $joinTablesAdapter null or instance of joinTablesAdapter.
         * @param $offset The zero based index of the first model to be returned.
         * @param $count The number of models to be returned.
         * @param $where TODO
         * @param $orderBy - sql string. Example 'a desc' or 'a.b desc'.  Currently only supports non-related attributes
         * @param $modelClassName Pass only when getting it at runtime gets the wrong name.
         * @return An array of models of the type of the extending model.
         */
        public static function getSubset(RedBeanModelJoinTablesQueryAdapter $joinTablesAdapter = null,
                                         $offset = null, $count = null,
                                         $where = null, $orderBy = null,
                                         $modelClassName = null,
                                         $selectDistinct = false)
        {
            assert('$offset  === null || is_integer($offset)  && $offset  >= 0');
            assert('$count   === null || is_integer($count)   && $count   >= 1');
            assert('$where   === null || is_string ($where)   && $where   != ""');
            assert('$orderBy === null || is_string ($orderBy) && $orderBy != ""');
            assert('$modelClassName === null || is_string($modelClassName) && $modelClassName != ""');
            if ($modelClassName === null)
            {
                $modelClassName = get_called_class();
            }
            if ($joinTablesAdapter == null)
            {
                $joinTablesAdapter = new RedBeanModelJoinTablesQueryAdapter($modelClassName);
            }
            $tableName = self::getTableName($modelClassName);
            $sql = static::makeSubsetOrCountSqlQuery($tableName, $joinTablesAdapter, $offset, $count, $where,
                                                     $orderBy, false, $selectDistinct);
            $ids   = R::getCol($sql);
            $tableName = self::getTableName($modelClassName);
            $beans = R::batch ($tableName, $ids);
            return self::makeModels($beans, $modelClassName);
        }

        /**
         * @param boolean $selectCount If true then make this a count query. If false, select ids from rows.
         * @param array $quotedExtraSelectColumnNameAndAliases - extra columns to select.
         * @return string - sql statement.
         */
        public static function makeSubsetOrCountSqlQuery($tableName,
                                                         RedBeanModelJoinTablesQueryAdapter $joinTablesAdapter,
                                                         $offset = null, $count = null,
                                                         $where = null, $orderBy = null,
                                                         $selectCount = false,
                                                         $selectDistinct = false,
                                                         array $quotedExtraSelectColumnNameAndAliases = array())
        {
            assert('is_string($tableName) && $tableName != ""');
            assert('$offset  === null || is_integer($offset)  && $offset  >= 0');
            assert('$count   === null || is_integer($count)   && $count   >= 1');
            assert('$where   === null || is_string ($where)   && $where   != ""');
            assert('$orderBy === null || is_string ($orderBy) && $orderBy != ""');
            assert('is_bool($selectCount)');
            assert('is_bool($selectDistinct)');
            $quote = DatabaseCompatibilityUtil::getQuote();
            if ($selectDistinct)
            {
                $distinctPart = 'distinct ';
            }
            else
            {
                $distinctPart = null;
            }
            if ($selectCount)
            {
                $sql = "select count({$distinctPart}{$quote}$tableName{$quote}.{$quote}id{$quote}) ";
            }
            else
            {
                $sql = "select {$distinctPart}{$quote}$tableName{$quote}.{$quote}id{$quote} id ";
            }
            foreach ($quotedExtraSelectColumnNameAndAliases as $columnName => $columnAlias)
            {
                $sql .= ", $columnName $columnAlias ";
            }
            $sql .= "from ";
            //Added ( ) around from tables to ensure precedence over joins.
            $joinFromPart   = $joinTablesAdapter->getJoinFromQueryPart();
            if ($joinFromPart !== null)
            {
                $sql .= "(";
                $sql .= "{$quote}$tableName{$quote}";
                $sql .= ", $joinFromPart) ";
            }
            else
            {
                $sql .= "{$quote}$tableName{$quote}";
                $sql .= ' ';
            }
            $sql           .= $joinTablesAdapter->getJoinQueryPart();
            $joinWherePart  = $joinTablesAdapter->getJoinWhereQueryPart();
            if ($where !== null)
            {
                $sql .= "where $where";
                if ($joinWherePart != null)
                {
                    $sql .= " and $joinWherePart";
                }
            }
            elseif ($joinWherePart != null)
            {
                $sql .= " where $joinWherePart";
            }
            if ($orderBy !== null)
            {
                $sql .= " order by $orderBy";
            }
            if ($count !== null)
            {
                $sql .= " limit $count";
            }
            if ($offset !== null)
            {
                $sql .= " offset $offset";
            }
            return $sql;
        }

        /**
         * @param $modelClassName
         * @param $joinTablesAdapter null or instance of joinTablesAdapter.
         * @param $modelClassName Pass only when getting it at runtime gets the wrong name.
         */
        public static function getCount(RedBeanModelJoinTablesQueryAdapter $joinTablesAdapter = null,
                                        $where = null, $modelClassName = null, $selectDistinct = false)
        {
            assert('$where          === null || is_string($where)');
            assert('$modelClassName === null || is_string($modelClassName) && $modelClassName != ""');
            if ($modelClassName === null)
            {
                $modelClassName = get_called_class();
            }
            if ($joinTablesAdapter == null)
            {
                $joinTablesAdapter = new RedBeanModelJoinTablesQueryAdapter($modelClassName);
            }
            $tableName      = self::getTableName($modelClassName);
            $sql = static::makeSubsetOrCountSqlQuery($tableName, $joinTablesAdapter, null, null, $where, null, true,
                                                     $selectDistinct);
            $count = R::getCell($sql);
            if ($count === null)
            {
                $count = 0;
            }
            return $count;
        }

        /**
         * Gets a model from the database by Id.
         * @param $id Integer Id.
         * @param $modelClassName Pass only when getting it at runtime
         *                        gets the wrong name.
         * @return A model of the type of the extending model.
         */
        public static function getById($id, $modelClassName = null)
        {
            assert('is_integer($id) && $id > 0');
            assert('$modelClassName === null || is_string($modelClassName) && $modelClassName != ""');
            // I would have thought it was correct to user R::load() and get
            // a null, or error or something if the bean doesn't exist, but
            // it still returns a bean. So until I've investigated further
            // I'm using Finder.
            if ($modelClassName === null)
            {
                $modelClassName = get_called_class();
            }
            $tableName = self::getTableName($modelClassName);
            $beans = RedBean_Plugin_Finder::where($tableName, "id = '$id'");
            assert('count($beans) <= 1');
            if (count($beans) == 0)
            {
                throw new NotFoundException();
            }
            return RedBeanModel::makeModel(end($beans), $modelClassName);
        }

        /**
         * Constructs a new model.
         * Important:
         * Models are only constructed with beans by the RedBeanModel. Beans are
         * never used by the application directly.
         * The application can construct a new model object by constructing a
         * model without specifying a bean. In other words, if Php had
         * overloading a constructor with $setDefaults would be public, and
         * a constructor taking a $bean and $forceTreatAsCreation would be private.
         * @param $setDefaults. If false the default validators will not be run
         *                      on construction. The Yii way is that defaults are
         *                      filled in after the fact, which is counter the usual
         *                      for objects.
         * @param $bean A bean. Never specified by an application.
         * @param $forceTreatAsCreation. Never specified by an application.
         * @see getById()
         * @see makeModel()
         * @see makeModels()
         */
        public function __construct($setDefaults = true, RedBean_OODBBean $bean = null, $forceTreatAsCreation = false)
        {
            $this->pseudoId = self::$nextPseudoId--;
            if ($bean === null)
            {
                foreach (array_reverse(RuntimeUtil::getClassHierarchy(get_class($this), 'RedBeanModel')) as $modelClassName)
                {
                    $tableName = self::getTableName($modelClassName);
                    $newBean = R::dispense($tableName);
                    $this->modelClassNameToBean[$modelClassName] = $newBean;
                    $this->mapAndCacheMetadataAndSetHints($modelClassName, $newBean);
                }
                // The yii way of doing defaults is the the default validator
                // fills in the defaults on attributes that don't have values
                // when you validator, or save. This weird, since when you get
                // a model the things with defaults have not been defaulted!
                // We want that semantic.
                if ($setDefaults)
                {
                    $this->runDefaultValidators();
                }
                $forceTreatAsCreation = true;
            }
            else
            {
                assert('$bean->id > 0');
                $first = true;
                foreach (RuntimeUtil::getClassHierarchy(get_class($this), 'RedBeanModel') as $modelClassName)
                {
                    if ($first)
                    {
                        $lastBean = $bean;
                        $first = false;
                    }
                    else
                    {
                        $tableName = self::getTableName($modelClassName);
                        $lastBean = R::getBean($lastBean, $tableName);
                        assert('$lastBean !== null');
                        assert('$lastBean->id > 0');
                    }
                    $this->modelClassNameToBean[$modelClassName] = $lastBean;
                    $this->mapAndCacheMetadataAndSetHints($modelClassName, $lastBean);
                }
                $this->modelClassNameToBean = array_reverse($this->modelClassNameToBean);
            }
            $this->constructDerived($bean, $setDefaults);
            if ($forceTreatAsCreation)
            {
                $this->onCreated();
            }
            else
            {
                $this->onLoaded();
                RedBeanModelsCache::cacheModel($this);
            }
            $this->modified = false;
        }

        // Derived classes can insert additional steps into the construction.
        protected function constructDerived($bean, $setDefaults)
        {
            assert('$bean === null || $bean instanceof RedBean_OODBBean');
            assert('is_bool($setDefaults)');
        }

        public function serialize()
        {
            return serialize(array(
                $this->pseudoId,
                $this->modelClassNameToBean,
                $this->attributeNameToBeanAndClassName,
                $this->attributeNamesNotBelongsToOrManyMany,
                $this->relationNameToRelationTypeModelClassNameAndOwns,
                $this->validators,
            ));
        }

        public function unserialize($data)
        {
            try
            {
                $data = unserialize($data);
                assert('is_array($data)');
                if (count($data) != 6)
                {
                    return null;
                }

                $this->pseudoId                                        = $data[0];
                $this->modelClassNameToBean                            = $data[1];
                $this->attributeNameToBeanAndClassName                 = $data[2];
                $this->attributeNamesNotBelongsToOrManyMany            = $data[3];
                $this->relationNameToRelationTypeModelClassNameAndOwns = $data[4];
                $this->validators                                      = $data[5];

                $this->relationNameToRelatedModel = array();
                $this->unlinkedRelationNames      = array();
                $this->attributeNameToErrors      = array();
                $this->scenarioName               = '';
                $this->modified                   = false;
                $this->deleted                    = false;
                $this->isInIsModified             = false;
                $this->isInHasErrors              = false;
                $this->isInGetErrors              = false;
                $this->isValidating               = false;
                $this->isSaving                   = false;
            }
            catch (Exception $e)
            {
                return null;
            }
        }

        /**
         * Overriding constructors must call this function to ensure that
         * they leave the newly constructed instance not modified since
         * anything modifying the class during constructionm will set it
         * modified automatically.
         */
        protected function setNotModified()
        {
            $this->modified = false;        // This sets this class to the right state.
            assert('!$this->isModified()'); // This tests that related classes are in the right state.
        }

        /**
         * By default the table name is the lowercased class name. If this
         * conflicts with a database keyword override to return true.
         * RedBean does not quote table names in most cases.
         */
        // Public for unit testing.
        public static function mangleTableName()
        {
            return false;
        }

        /**
         * Returns the table name for a class.
         * For use by RedBeanModelDataProvider. It will not
         * be of any use to an application. Applications
         * should not be doing anything table related.
         * Derived classes can refer directly to the
         * table name.
         */
        public static function getTableName($modelClassName)
        {
            assert('is_string($modelClassName) && $modelClassName != ""');
            $tableName = strtolower($modelClassName);
            if ($modelClassName::mangleTableName())
            {
                $tableName = '_' . $tableName;
            }
            return $tableName;
        }

        /**
         * Returns the table names for an array of classes.
         * For use by RedBeanModelDataProvider. It will not
         * be of any use to an application.
         */
        public static function getTableNames($classNames)
        {
            $tableNames = array();
            foreach ($classNames as $className)
            {
                $tableNames[] = self::getTableName($className);
            }
            return $tableNames;
        }

        /**
         * Used by classes such as containers which use sql to
         * optimize getting models from the database.
         */
        public static function getForeignKeyName($modelClassName, $relationName)
        {
            assert('is_string($modelClassName)');
            assert('$modelClassName != ""');
            $metadata = $modelClassName::getMetadata();
            foreach ($metadata as $modelClassName => $modelClassMetadata)
            {
                if (isset($metadata[$modelClassName]["relations"]) &&
                    array_key_exists($relationName, $metadata[$modelClassName]["relations"]))
                {
                    $relatedModelClassName = $metadata[$modelClassName]['relations'][$relationName][1];
                    $relatedModelTableName = self::getTableName($relatedModelClassName);
                    $columnName = '';
                    if (strtolower($relationName) != strtolower($relatedModelClassName))
                    {
                        $columnName = strtolower($relationName) . '_';
                    }
                    $columnName .= $relatedModelTableName . '_id';
                    return $columnName;
                }
            }
            throw new NotSupportedException;
        }

        /**
         * Called on construction when a new model is created.
         */
        protected function onCreated()
        {
        }

        /**
         * Called on construction when a model is loaded.
         */
        protected function onLoaded()
        {
        }

        /**
         * Called when a model is modified.
         */
        protected function onModified()
        {
        }

        /**
         * Used for mixins.
         */
        protected function mapAndCacheMetadataAndSetHints($modelClassName, RedBean_OODBBean $bean)
        {
            assert('is_string($modelClassName)');
            assert('$modelClassName != ""');
            $metadata = $this->getMetadata();
            if (isset($metadata[$modelClassName]))
            {
                $hints = array();
                if (isset($metadata[$modelClassName]['members']))
                {
                    foreach ($metadata[$modelClassName]['members'] as $memberName)
                    {
                        $this->attributeNameToBeanAndClassName[$memberName] = array($bean, $modelClassName);
                        $this->attributeNamesNotBelongsToOrManyMany[] = $memberName;
                        if (substr($memberName, -2) == 'Id')
                        {
                            $columnName = strtolower($memberName);
                            $hints[$columnName] = 'id';
                        }
                    }
                }
                if (isset($metadata[$modelClassName]['relations']))
                {
                    foreach ($metadata[$modelClassName]['relations'] as $relationName => $relationTypeModelClassNameAndOwns)
                    {
                        assert('in_array(count($relationTypeModelClassNameAndOwns), array(2, 3))');

                        $relationType           = $relationTypeModelClassNameAndOwns[0];
                        $relationModelClassName = $relationTypeModelClassNameAndOwns[1];
                        if ($relationType == self::HAS_MANY_BELONGS_TO &&
                           strtolower($relationName) != strtolower($relationModelClassName))
                        {
                            $label = 'Relations of type HAS_MANY_BELONGS_TO must have the relation name ' .
                                     'the same as the related model class name. Relation: {relationName} ' .
                                     'Relation model class name: {relationModelClassName}';
                            throw new NotSupportedException(Yii::t('Default', $label,
                                      array('{relationName}' => $relationName,
                                            '{relationModelClassName}' => $relationModelClassName)));
                        }
                        if (count($relationTypeModelClassNameAndOwns) == 3)
                        {
                            $relationTypeModelClassNameAndOwns[2] == self::OWNED;
                            $owns = true;
                        }
                        else
                        {
                            $owns = false;
                        }
                        assert('in_array($relationType, array(self::HAS_ONE_BELONGS_TO, self::HAS_MANY_BELONGS_TO, ' .
                                                             'self::HAS_ONE, self::HAS_MANY, self::MANY_MANY))');
                        $this->attributeNameToBeanAndClassName[$relationName] = array($bean, $modelClassName);
                        $this->relationNameToRelationTypeModelClassNameAndOwns[$relationName] = array($relationType, $relationModelClassName, $owns);
                        if (!in_array($relationType, array(self::HAS_ONE_BELONGS_TO, self::HAS_MANY_BELONGS_TO, self::MANY_MANY)))
                        {
                            $this->attributeNamesNotBelongsToOrManyMany[] = $relationName;
                        }
                    }
                }
                if (isset($metadata[$modelClassName]['rules']))
                {
                    foreach ($metadata[$modelClassName]['rules'] as $validatorMetadata)
                    {
                        assert('isset($validatorMetadata[0])');
                        assert('isset($validatorMetadata[1])');
                        $attributeName       = $validatorMetadata[0];
                        // Each rule in RedBeanModel must specify one attribute name.
                        // This was just better style, now it is mandatory.
                        assert('strpos($attributeName, " ") === false');
                        $validatorName       = $validatorMetadata[1];
                        $validatorParameters = array_slice($validatorMetadata, 2);
                        if (isset(CValidator::$builtInValidators[$validatorName]))
                        {
                            $validatorName = CValidator::$builtInValidators[$validatorName];
                        }
                        if (isset(self::$yiiValidatorsToRedBeanValidators[$validatorName]))
                        {
                            $validatorName = self::$yiiValidatorsToRedBeanValidators[$validatorName];
                        }
                        $validator = CValidator::createValidator($validatorName, $this, $attributeName, $validatorParameters);
                        switch ($validatorName)
                        {
                            case 'RedBeanModelTypeValidator':
                            case 'TypeValidator':
                                $columnName = strtolower($attributeName);
                                if (array_key_exists($columnName, $hints))
                                {
                                    unset($hints[$columnName]);
                                }
                                if (in_array($validator->type, array('date', 'datetime', 'blob', 'longblob')))
                                {
                                    $hints[$columnName] = $validator->type;
                                }
                                break;
                            case 'CBooleanValidator':
                                $columnName = strtolower($attributeName);
                                $hints[$columnName] = 'boolean';
                                break;
                            case 'RedBeanModelUniqueValidator':
                                if (!$this->isRelation($attributeName))
                                {
                                    $bean->setMeta("buildcommand.unique", array(array($attributeName)));
                                }
                                else
                                {
                                    $relatedModelClassName = $this->relationNameToRelationTypeModelClassNameAndOwns[$attributeName][1];
                                    $relatedModelTableName = self::getTableName($relatedModelClassName);
                                    $columnName = strtolower($attributeName);
                                    if ($columnName != $relatedModelTableName)
                                    {
                                        $columnName .= '_' . $relatedModelTableName;
                                    }
                                    $columnName .= '_id';
                                    $bean->setMeta("buildcommand.unique", array(array($columnName)));
                                }
                                break;
                        }
                        $this->validators[] = $validator;
                    }
                }
                $bean->setMeta('hint', $hints);
            }
        }

        /**
         * Used for mixins.
         */
        protected function runDefaultValidators()
        {
            foreach ($this->validators as $validator)
            {
                if ($validator instanceof CDefaultValueValidator)
                {
                    $validator->validate($this);
                }
            }
        }

        /**
         * For use only by RedBeanModel and RedBeanModels. Beans are
         * never used by the application directly.
         */
        public function getPrimaryBean()
        {
            return end($this->modelClassNameToBean);
        }

        /**
         * Used for optimization.
         */
        public function getClassId($modelClassName)
        {
            assert('array_key_exists($modelClassName, $this->modelClassNameToBean)');
            return intval($this->getClassBean($modelClassName)->id); // Trying to combat the slop.
        }

        public function getClassBean($modelClassName)
        {
            assert('is_string($modelClassName)');
            assert('$modelClassName != ""');
            assert('array_key_exists($modelClassName, $this->modelClassNameToBean)');
            return $this->modelClassNameToBean[$modelClassName];
        }

        /**
         * Used for mixins.
         */
        protected function setClassBean($modelClassName, RedBean_OODBBean $bean)
        {
            assert('is_string($modelClassName)');
            assert('$modelClassName != ""');
            assert('!array_key_exists($modelClassName, $this->modelClassNameToBean)');
            $this->modelClassNameToBean = array_merge(array($modelClassName => $bean),
                                                      $this->modelClassNameToBean);
        }

        public function getModelIdentifier()
        {
            return get_class($this) . strval($this->getPrimaryBean()->id);
        }

        /**
         * Returns metadata for the model.  Attempts to cache metadata, if it is not already cached.
         * @see getDefaultMetadata()
         * @returns An array of metadata.
         */
        public static function getMetadata()
        {
            try
            {
                return GeneralCache::getEntry(get_called_class() . 'Metadata');
            }
            catch (NotFoundException $e)
            {
                $className = get_called_Class();
                $defaultMetadata = $className::getDefaultMetadata();
                $metadata = array();
                foreach (array_reverse(RuntimeUtil::getClassHierarchy($className, 'RedBeanModel')) as $modelClassName)
                {
                    if ($modelClassName::canSaveMetadata())
                    {
                        try
                        {
                            $globalMetadata = GlobalMetadata::getByClassName($modelClassName);
                            $metadata[$modelClassName] = unserialize($globalMetadata->serializedMetadata);
                        }
                        catch (NotFoundException $e)
                        {
                            if (isset($defaultMetadata[$modelClassName]))
                            {
                                $metadata[$modelClassName] = $defaultMetadata[$modelClassName];
                            }
                        }
                    }
                    else
                    {
                        if (isset($defaultMetadata[$modelClassName]))
                        {
                            $metadata[$modelClassName] = $defaultMetadata[$modelClassName];
                        }
                    }
                }
                if (YII_DEBUG)
                {
                    self::assertMetadataIsValid($metadata);
                }
                GeneralCache::cacheEntry(get_called_class() . 'Metadata', $metadata);
                return $metadata;
            }
        }

        /**
         * By default models cannot save their metadata, allowing
         * them to be loaded quickly because the loading of of
         * metadata can be avoided as much as possible.
         * To make a model able to save its metadata override
         * this method to return true. PUT it before the
         * getDefaultMetadata in the derived class.
         */
        public static function canSaveMetadata()
        {
            return false;
        }

        /**
         * Sets metadata for the model.
         * @see getDefaultMetadata()
         * @returns An array of metadata.
         */
        public static function setMetadata(array $metadata)
        {
            if (YII_DEBUG)
            {
                self::assertMetadataIsValid($metadata);
            }
            $className = get_called_class();
            foreach (array_reverse(RuntimeUtil::getClassHierarchy($className, 'RedBeanModel')) as $modelClassName)
            {
                if ($modelClassName::canSaveMetadata())
                {
                    if (isset($metadata[$modelClassName]))
                    {
                        try
                        {
                            $globalMetadata = GlobalMetadata::getByClassName($modelClassName);
                        }
                        catch (NotFoundException $e)
                        {
                            $globalMetadata = new GlobalMetadata();
                            $globalMetadata->className = $modelClassName;
                        }
                        $globalMetadata->serializedMetadata = serialize($metadata[$modelClassName]);
                        $saved = $globalMetadata->save();
                        // TODO: decide how to deal with this properly if it fails.
                        //       ie: throw or return false, or something other than
                        //           this naughty assert.
                        assert('$saved');
                    }
                }
            }
            RedBeanModelsCache::forgetAllByModelType(get_called_class());
            GeneralCache::forgetEntry(get_called_class() . 'Metadata');
        }

        /**
         * Returns the default meta data for the class.
         * It must be appended to the meta data
         * from the parent model, if any.
         */
        public static function getDefaultMetadata()
        {
            return array();
        }

        protected static function assertMetadataIsValid(array $metadata)
        {
            $className = get_called_Class();
            foreach (RuntimeUtil::getClassHierarchy($className, 'RedBeanModel') as $modelClassName)
            {
                if (isset($metadata[$modelClassName]['members']))
                {
                    assert('is_array($metadata[$modelClassName]["members"])');
                    foreach ($metadata[$modelClassName]["members"] as $memberName)
                    {
                        assert('ctype_lower($memberName{0})');
                    }
                }
                if (isset($metadata[$modelClassName]['relations']))
                {
                    assert('is_array($metadata[$modelClassName]["relations"])');
                    foreach ($metadata[$modelClassName]["relations"] as $relationName => $notUsed)
                    {
                        assert('ctype_lower($relationName{0})');
                    }
                }
                if (isset($metadata[$modelClassName]['rules']))
                {
                    assert('is_array($metadata[$modelClassName]["rules"])');
                }
                if (isset($metadata[$modelClassName]['defaultSortAttribute']))
                {
                    assert('is_string($metadata[$modelClassName]["defaultSortAttribute"])');
                }
                if (isset($metadata[$modelClassName]['rollupRelations']))
                {
                    assert('is_array($metadata[$modelClassName]["rollupRelations"])');
                }
                // Todo: add more rules here as I think of them.
            }
        }

        /**
         * Downcasting in general is a bad concept, but when pulling
         * a Person from the database it would require a lot of
         * jumping through hoops to make the RedBeanModel automatically
         * figure out if that person is really a User, Contact, Customer
         * or whatever might be derived from Person. So to avoid that
         * complication and performance hit where it is not necessary
         * this method can be used to convert a model to one of
         * a given set of derivatives. If model is not one
         * of those NotFoundException is thrown.
         */
        public function castDown(array $derivedModelClassNames)
        {
            $bean = $this->getPrimaryBean();
            $thisModelClassName = get_called_class();
            $key = strtolower($thisModelClassName) . '_id';
            foreach ($derivedModelClassNames as $modelClassNames)
            {
                if (is_string($modelClassNames))
                {
                    $nextModelClassName = $modelClassNames;
                    if (get_class($this) == $nextModelClassName)
                    {
                        return $this;
                    }
                    $nextBean = self::findNextDerivativeBean($bean, $thisModelClassName, $nextModelClassName);
                }
                else
                {
                    assert('is_array($modelClassNames)');
                    $targetModelClassName = end($modelClassNames);
                    if (get_class($this) == $targetModelClassName)
                    {
                        return $this;
                    }
                    $currentModelClassName = $thisModelClassName;
                    $nextBean = $bean;
                    foreach ($modelClassNames as $nextModelClassName)
                    {
                        $nextBean = self::findNextDerivativeBean($nextBean, $currentModelClassName, $nextModelClassName);
                        if ($nextBean === null)
                        {
                            break;
                        }
                        $currentModelClassName = $nextModelClassName;
                    }
                }
                if ($nextBean !== null)
                {
                    return self::makeModel($nextBean, $nextModelClassName);
                }
            }
            throw new NotFoundException();
        }

        private static function findNextDerivativeBean($bean, $modelClassName1, $modelClassName2)
        {
            $key = strtolower($modelClassName1) . '_id';
            $tableName = self::getTableName($modelClassName2);
            $beans = R::find($tableName, "$key = :id", array('id' => $bean->id));
            if (count($beans) == 1)
            {
                return reset($beans);
            }
            return null;
        }

        /**
         * Returns whether the given object is of the same type with the
         * same id.
         */
        public function isSame(RedBeanModel $model)
        {
            // The two models are the same if they have the
            // same root model, and if for that model they
            // have the same id.
            $rootId1 = reset($this ->modelClassNameToBean)->id;
            $rootId2 = reset($model->modelClassNameToBean)->id;
            if ($rootId1 == 0)
            {
                $rootId1 = $this->pseudoId;
            }
            if ($rootId2 == 0)
            {
                $rootId2 = $model->pseudoId;
            }
            return $rootId1 == $rootId2 && $rootId1 != 0 &&
                   key($this ->modelClassNameToBean) ==
                   key($model->modelClassNameToBean);
        }

        /**
         * Returns the displayable string for the class. Should be
         * overridden in any model that can provide a meaningful string
         * representation of itself.
         * @return A string.
         */
        public function __toString()
        {
            return Yii::t('Default', '(None)');
        }

        /**
         * Exposes the members and relations of the model as if
         * they were actual attributes of the model. See __set().
         * @param $attributeName A non-empty string that is the name of a
         * member or relation.
         * @see attributeNames()
         * @return A value or model of the type specified as valid for the
         * member or relation by the meta data supplied by the extending
         * class's getMetadata() method.
         */
        public function __get($attributeName)
        {
            return $this->unrestrictedGet($attributeName);
        }

        /**
         * A protected version of __get() for models to talk to themselves
         * to use their dynamically created members from 'members'
         * and 'relations' in its metadata.
         */
        protected function unrestrictedGet($attributeName)
        {
            assert('is_string($attributeName)');
            assert('$attributeName != ""');
            assert("property_exists(\$this, '$attributeName') || \$this->isAttribute('$attributeName')");
            if (property_exists($this, $attributeName))
            {
                return $this->$attributeName;
            }
            elseif ($attributeName == 'id')
            {
                $id = intval($this->getPrimaryBean()->id);
                assert('$id >= 0');
                if ($id == 0)
                {
                    $id = $this->pseudoId;
                }
                return $id;
            }
            elseif ($this->isAttribute($attributeName))
            {
                list($bean, $attributeModelClassName) = $this->attributeNameToBeanAndClassName[$attributeName];
                if (!$this->isRelation($attributeName))
                {
                    $columnName = strtolower($attributeName);
                    return $bean->$columnName;
                }
                else
                {
                    if (!array_key_exists($attributeName, $this->relationNameToRelatedModel))
                    {
                        list($relationType, $relatedModelClassName, $owns) = $this->relationNameToRelationTypeModelClassNameAndOwns[$attributeName];
                        $relatedTableName = self::getTableName($relatedModelClassName);
                        switch ($relationType)
                        {
                            case self::HAS_ONE_BELONGS_TO:
                                $relatedIds = R::$linkManager->getKeys($bean, $relatedTableName);
                                assert('in_array(count($relatedIds), array(0, 1))');
                                if (count($relatedIds) != 1)
                                {
                                    return null;
                                }
                                $relatedBean = R::load($relatedTableName, $relatedIds[0]);
                                $this->relationNameToRelatedModel[$attributeName] = self::makeModel($relatedBean, $relatedModelClassName);
                                break;

                            case self::HAS_ONE:
                            case self::HAS_MANY_BELONGS_TO:
                                if ($relationType == self::HAS_ONE)
                                {
                                    $linkName = strtolower($attributeName);
                                    if ($linkName == strtolower($relatedModelClassName))
                                    {
                                        $linkName = null;
                                    }
                                }
                                else
                                {
                                    $linkName = null;
                                }
                                if ($bean->id > 0 && !in_array($attributeName, $this->unlinkedRelationNames))
                                {
                                    $relatedBean = R::getBean($bean, $relatedTableName, $linkName);
                                    if ($relatedBean !== null && $relatedBean->id > 0)
                                    {
                                        $relatedModel = self::makeModel($relatedBean, $relatedModelClassName, true);
                                    }
                                }
                                if (!isset($relatedModel))
                                {
                                    $relatedModel = new $relatedModelClassName();
                                }
                                $this->relationNameToRelatedModel[$attributeName] = $relatedModel;
                                break;

                            case self::HAS_MANY:
                                $this->relationNameToRelatedModel[$attributeName] = new RedBeanOneToManyRelatedModels($bean, $relatedModelClassName, $attributeModelClassName, $owns);
                                break;

                            case self::MANY_MANY:
                                $this->relationNameToRelatedModel[$attributeName] = new RedBeanManyToManyRelatedModels($bean, $relatedModelClassName);
                                break;

                            default:
                                throw new NotSupportedException();
                        }
                    }
                    return $this->relationNameToRelatedModel[$attributeName];
                }
            }
            else
            {
                throw new NotSupportedException();
            }
        }

        /**
         * Sets the members and relations of the model as if
         * they were actual attributes of the model. For example, if Account
         * extends RedBeanModel and its attributeNames() returns that one it has
         * a member 'name' and a relation 'owner' they are simply
         * accessed as:
         * @code
         *   $account = new Account();
         *   $account->name = 'International Corp';
         *   $account->owner = User::getByUsername('bill');
         *   $account->save();
         * @endcode
         * @param $attributeName A non-empty string that is the name of a
         * member or relation of the model.
         * @param $value A value or model of the type specified as valid for the
         * member or relation by the meta data supplied by the extending
         * class's getMetadata() method.
         */
        public function __set($attributeName, $value)
        {
            if ($attributeName == 'id' ||
                ($this->isAttributeReadOnly($attributeName) && !$this->isAllowedToSetReadOnlyAttribute($attributeName)))
            {
                throw new NotSupportedException();
            }
            else
            {
                if ($this->unrestrictedSet($attributeName, $value))
                {
                    $this->modified = true;
                    $this->onModified();
                }
            }
        }

        /**
         * A protected version of __set() for models to talk to themselves
         * to use their dynamically created members from 'members'
         * and 'relations' in its metadata.
         */
        protected function unrestrictedSet($attributeName, $value)
        {
            assert('is_string($attributeName)');
            assert('$attributeName != ""');
            assert("property_exists(\$this, '$attributeName') || \$this->isAttribute('$attributeName')");
            if (property_exists($this, $attributeName))
            {
                $this->$attributeName = $value;
            }
            elseif ($this->isAttribute($attributeName))
            {
                $bean = $this->attributeNameToBeanAndClassName[$attributeName][0];
                if (!$this->isRelation($attributeName))
                {
                    $columnName = strtolower($attributeName);
                    if ($bean->$columnName !== $value)
                    {
                        $bean->$columnName = $value;
                        return true;
                    }
                }
                else
                {
                    list($relationType, $relatedModelClassName, $owns) = $this->relationNameToRelationTypeModelClassNameAndOwns[$attributeName];
                    $relatedTableName = self::getTableName($relatedModelClassName);
                    $linkName = strtolower($attributeName);
                    if ($linkName == strtolower($relatedModelClassName))
                    {
                        $linkName = null;
                    }
                    switch ($relationType)
                    {
                        case self::HAS_MANY:
                        case self::MANY_MANY:
                            // The many sides of a relation cannot
                            // be assigned, they are changed by the using the
                            // RedBeanOneToManyRelatedModels or
                            // RedBeanManyToManyRelatedModels object
                            // on the 1 or other side of the relationship
                            // respectively.
                            throw new NotSupportedException();
                    }
                    // If the value is null we need to get the related model so that
                    // if there is none we can ignore the null and if there is one
                    // we can act on it.
                    if ($value === null                                         &&
                        !in_array($attributeName, $this->unlinkedRelationNames) &&
                        !isset($this->relationNameToRelatedModel[$attributeName]))
                    {
                        $this->unrestrictedGet($attributeName);
                    }
                    if (isset($this->relationNameToRelatedModel[$attributeName]) &&
                        $value !== null                                          &&
                        $this->relationNameToRelatedModel[$attributeName]->isSame($value))
                    {
                        // If there is a current related model and it is the same
                        // as the one being set then do nothing.
                    }
                    else
                    {
                        if (!in_array($attributeName, $this->unlinkedRelationNames) &&
                            isset($this->relationNameToRelatedModel[$attributeName]))
                        {
                            $this->unlinkedRelationNames[] = $attributeName;
                        }
                        if ($value === null)
                        {
                            unset($this->relationNameToRelatedModel[$attributeName]);
                        }
                        else
                        {
                            assert("\$value instanceof $relatedModelClassName");
                            $this->relationNameToRelatedModel[$attributeName] = $value;
                        }
                    }
                    return true;
                }
            }
            else
            {
                throw new NotSupportedException();
            }
            return false;
        }

        /**
         * Allows testing of the members and relations of the model as if
         * they were actual attributes of the model.
         */
        public function __isset($attributeName)
        {
            assert('is_string($attributeName)');
            assert('$attributeName != ""');
            return $this->isAttribute($attributeName) &&
                   $this->$attributeName !== null ||
                   !$this->isAttribute($attributeName) &&
                   isset($this->$attributeName);
        }

        /**
         * Allows unsetting of the members and relations of the model as if
         * they were actual attributes of the model.
         */
        public function __unset($attributeName)
        {
            assert('is_string($attributeName)');
            assert('$attributeName != ""');
            $this->$attributeName = null;
        }

        /**
         * Returns the member and relation names defined by the extending
         * class's getMetadata() method.
         */
        public function attributeNames()
        {
            return array_keys($this->attributeNameToBeanAndClassName);
        }

        /**
         * Returns true if the named attribute is one of the member or
         * relation names defined by the extending
         * class's getMetadata() method.
         */
        public function isAttribute($attributeName)
        {
            assert('is_string($attributeName)');
            assert('$attributeName != ""');
            return $attributeName == 'id' ||
                   array_key_exists($attributeName, $this->attributeNameToBeanAndClassName);
        }

        /**
         * Returns true if the attribute is read-only.
         */
        public function isAttributeReadOnly($attributeName)
        {
            assert("\$this->isAttribute(\"$attributeName\")");
            foreach ($this->validators as $validator)
            {
                if ($validator instanceof RedBeanModelReadOnlyValidator)
                {
                    if (in_array($attributeName, $validator->attributes, true))
                    {
                        return true;
                    }
                }
            }
            return false;
        }

        /**
         * @param boolean $attributeName
         * @return true/false whether the attributeName specified, it is allowed to be set externally even though it is
         * a read-only attribute.
         */
        public function isAllowedToSetReadOnlyAttribute($attributeName)
        {
            return false;
        }

        /**
         * Returns the model class name for an
         * attribute name defined by the extending class's getMetadata() method.
         * For use by RedBeanModelDataProvider. Is unlikely to be of any
         * use to an application.
         */
        public function getAttributeModelClassName($attributeName)
        {
            assert("\$this->isAttribute(\"$attributeName\")");
            return $this->attributeNameToBeanAndClassName[$attributeName][1];
        }

        /**
         * Returns true if the named attribute is one of the
         * relation names defined by the extending
         * class's getMetadata() method.
         */
        public function isRelation($attributeName)
        {
            assert("\$this->isAttribute('$attributeName')");
            return array_key_exists($attributeName, $this->relationNameToRelationTypeModelClassNameAndOwns);
        }

        /**
         * Returns true if the named attribute is one of the
         * relation names defined by the extending
         * class's getMetadata() method, and specifies RedBeanModel::OWNED.
         */
        public function isOwnedRelation($attributeName)
        {
            assert("\$this->isAttribute('$attributeName')");
            return array_key_exists($attributeName, $this->relationNameToRelationTypeModelClassNameAndOwns) &&
                   $this->relationNameToRelationTypeModelClassNameAndOwns[$attributeName][2];
        }

        /**
         * Returns the relation type
         * relation name defined by the extending class's getMetadata() method.
         */
        public function getRelationType($relationName)
        {
            assert("\$this->isRelation('$relationName')");
            return $this->relationNameToRelationTypeModelClassNameAndOwns[$relationName][0];
        }

        /**
         * Returns the model class name for a
         * relation name defined by the extending class's getMetadata() method.
         * For use by RedBeanModelDataProvider. Is unlikely to be of any
         * use to an application.
         */
        public function getRelationModelClassName($relationName)
        {
            assert("\$this->isRelation('$relationName')");
            return $this->relationNameToRelationTypeModelClassNameAndOwns[$relationName][1];
        }

        /**
         * See the yii documentation. Not used by RedBeanModel.
         * @see getMetadata()
         */
        public function rules()
        {
            throw new NotImplementedException();
        }

        /**
         * See the yii documentation.
         */
        public function behaviors()
        {
            return array();
        }

        /**
         * See the yii documentation.
         * RedBeanModels utilize untranslatedAttributeLabels to store any attribute information, which
         * can then be translated in this method.
         */
        public function attributeLabels()
        {
            $attributeLabels = array();
            foreach ($this->untranslatedAttributeLabels() as $attributeName => $label)
            {
                $attributeLabels[$attributeName] = Yii::t('Default', $label);
            }
            return $attributeLabels;
        }

        /**
         * Array of untranslated attribute labels.
         */
        protected function untranslatedAttributeLabels()
        {
            return array();
        }

        /**
         * Performs validation using the validators specified in the 'rules'
         * meta data by the extending class's getMetadata() method.
         * Validation occurs on a new model or a modified model, but only
         * proceeds to modified related models. Once validated a model
         * will pass validation without revalidating until it is modified.
         * Related models are only validated if the model validates.
         * Cyclic relationships are prevented from causing problems by the
         * validation either stopping at a non-validating model and only
         * proceeding to non-validated models.
         * @see RedBeanModel
         */
        public function validate(array $attributeNames = null)
        {
            if ($this->isValidating) // Prevent cycles.
            {
                return true;
            }
            $this->isValidating = true;
            try
            {
                $this->clearErrors();
                if ($this->beforeValidate())
                {
                    $hasErrors = false;
                    if ($attributeNames === null)
                    {
                        $attributeNames = $this->attributeNamesNotBelongsToOrManyMany;
                    }
                    foreach ($this->getValidators() as $validator)
                    {
                        if (!$validator instanceof CDefaultValueValidator && $validator->applyTo($this->scenarioName))
                        {
                            $validator->validate($this, $attributeNames);
                        }
                    }
                    $relatedModelsHaveErrors = false;
                    foreach ($this->relationNameToRelatedModel as $relationName => $relatedModel)
                    {
                        if ((!$this->$relationName instanceof RedBeanModel) ||
                             !$this->$relationName->isSame($this))
                        {
                            if (in_array($relationName, $attributeNames) &&
                                ($this->$relationName->isModified() ||
                                     $this->isAttributeRequired($relationName) &&
                                     !$this->isSame($this->$relationName))) // Prevent cycles.
                            {
                                if (!$this->$relationName->validate())
                                {
                                    $hasErrors = true;
                                }
                            }
                        }
                    }
                    $this->afterValidate();
                    $hasErrors = $hasErrors || count($this->attributeNameToErrors) > 0;
                    // Put these asserts back if there are suspitions about validate/hasErrors/getErrors
                    // producing inconsistent results. But for now it is commented out because
                    // it makes too big an impact.
                    //assert('$hasErrors == (count($this->getErrors()) > 0)');
                    //assert('$hasErrors == $this->hasErrors()');
                    $this->isValidating = false;
                    return !$hasErrors;
                }
                $this->isValidating = false;
                return false;
            }
            catch (Exception $e)
            {
                $this->isValidating = false;
                throw $e;
            }
        }

        /**
         * See the yii documentation.
         */
        protected function beforeValidate()
        {
            $event = new CModelEvent($this);
            $this->onBeforeValidate($event);
            return $event->isValid;
        }

        /**
         * See the yii documentation.
         */
        protected function afterValidate()
        {
            $this->onAfterValidate(new CEvent($this));
        }

        /**
         * See the yii documentation.
         */
        public function onBeforeValidate(CModelEvent $event)
        {
            $this->raiseEvent('onBeforeValidate', $event);
        }

        /**
         * See the yii documentation.
         */
        public function onAfterValidate($event)
        {
            $this->raiseEvent('onAfterValidate', $event);
        }

        /**
         * See the yii documentation.
         */
        public function getValidatorList()
        {
            return $this->validators;
        }

        /**
         * See the yii documentation.
         */
        public function getValidators($attributeName = null)
        {
            assert("\$attributeName === null || \$this->isAttribute('$attributeName')");
            $validators = array();
            $scenarioName = $this->scenarioName;
            foreach ($this->validators as $validator)
            {
                if ($scenarioName === null || $validator->applyTo($scenarioName))
                {
                    if ($attributeName === null || in_array($attributeName, $validator->attributes, true))
                    {
                        $validators[] = $validator;
                    }
                }
            }
            return $validators;
        }

        /**
         * See the yii documentation.
         */
        public function createValidators()
        {
            throw new NotImplementedException();
        }

        /**
         * Returns true if the attribute value does not already exist in
         * the database. This is used in the unique validator, but on saving
         * RedBean can still throw because the unique constraint on the column
         * has been violated because it was concurrently updated between the
         * Yii validator being called and the save actually occuring.
         */
        public function isUniqueAttributeValue($attributeName, $value)
        {
            assert("\$this->isAttribute('$attributeName')");
            assert('$value !== null');
            if (!$this->isRelation($attributeName))
            {
                $modelClassName = $this->attributeNameToBeanAndClassName[$attributeName][1];
                $tableName = self::getTableName($modelClassName);
                $rows = R::getAll('select id from ' . $tableName . " where $attributeName = ?", array($value));
                return count($rows) == 0 || count($rows) == 1 && $rows[0]['id'] == $this->id;
            }
            else
            {
                $model = $this->$attributeName;
                if ($model->id == 0)
                {
                    return true;
                }
                $modelClassName = $this->relationNameToRelationTypeModelClassNameAndOwns[$attributeName][1];
                $tableName = self::getTableName($modelClassName);
                $rows = R::getAll('select id from ' . $tableName . ' where id = ?', array($model->id));
                return count($rows) == 0 || count($rows) == 1 && $rows[0]['id'] == $this->id;
            }
        }

        /**
         * Saves the model to the database. Models are only saved if they have been
         * modified and related models are saved before this model. If a related model
         * is modified and needs saving the deems the model to be modified and need
         * saving, which ensures that keys are updated.
         * Cyclic relationships are prevented from causing problems by the
         * save only proceeding to non-saved models.
         */
        public function save($runValidation = true, array $attributeNames = null)
        {
            if ($attributeNames !== null)
            {
                throw new NotSupportedException();
            }
            if ($this->isSaving) // Prevent cycles.
            {
                return true;
            }
            $this->isSaving = true;
            try
            {
                if (!$runValidation || $this->validate())
                {
                    if ($this->beforeSave())
                    {
                        $beans = array_values($this->modelClassNameToBean);
                        $this->linkBeans();
                        // The breakLink/link is deferred until the save to avoid
                        // disconnecting or creating an empty row if the model was
                        // never actually saved.
                        foreach ($this->unlinkedRelationNames as $key => $relationName)
                        {
                            $bean                  = $this->attributeNameToBeanAndClassName                [$relationName][0];
                            $relatedModelClassName = $this->relationNameToRelationTypeModelClassNameAndOwns[$relationName][1];
                            $relatedTableName      = self::getTableName($relatedModelClassName);
                            $linkName = strtolower($relationName);
                            if ($linkName == strtolower($relatedModelClassName))
                            {
                                $linkName = null;
                            }
                            R::$linkManager->breakLink($bean, $relatedTableName, $linkName);
                            unset($this->unlinkedRelationNames[$key]);
                        }
                        assert('count($this->unlinkedRelationNames) == 0');
                        foreach ($this->relationNameToRelatedModel as $relationName => $relatedModel)
                        {
                            $relationType = $this->relationNameToRelationTypeModelClassNameAndOwns[$relationName][0];
                            if ($relatedModel instanceof RedBeanModel)
                            {
                                $bean                  = $this->attributeNameToBeanAndClassName                [$relationName][0];
                                $relatedModelClassName = $this->relationNameToRelationTypeModelClassNameAndOwns[$relationName][1];
                                $linkName = strtolower($relationName);
                                if (strtolower($linkName) == strtolower($relatedModelClassName))
                                {
                                    $linkName = null;
                                }
                                elseif ($relationType == RedBeanModel::HAS_MANY_BELONGS_TO)
                                {
                                    $label = 'Relations of type HAS_MANY_BELONGS_TO must have the relation name ' .
                                             'the same as the related model class name. Relation: {relationName} ' .
                                             'Relation model class name: {relationModelClassName}';
                                    throw new NotSupportedException(Yii::t('Default', $label,
                                              array('{relationName}' => $linkName,
                                                    '{relationModelClassName}' => $relatedModelClassName)));
                                }
                                if ($relatedModel->isModified() ||
                                    $relatedModel->id > 0       ||
                                    $this->isAttributeRequired($relationName))
                                {
                                    $relatedModel = $this->relationNameToRelatedModel[$relationName];
                                    $relatedBean  = $relatedModel->getClassBean($relatedModelClassName);
                                    R::$linkManager->link($bean, $relatedBean, $linkName);
                                    if (!RedBeanDatabase::isFrozen())
                                    {
                                        $tableName  = self::getTableName($this->getAttributeModelClassName($relationName));
                                        $columnName = self::getForeignKeyName(get_class($this), $relationName);
                                        RedBean_Plugin_Optimizer_Id::ensureIdColumnIsINT11($tableName, $columnName);
                                    }
                                }
                            }
                            if (!in_array($relationType, array(self::HAS_ONE_BELONGS_TO,
                                                               self::HAS_MANY_BELONGS_TO)))
                            {
                                if ($relatedModel->isModified() ||
                                    $this->isAttributeRequired($relationName))
                                {
                                    // Validation of this model has already done.
                                    if (!$relatedModel->save(false))
                                    {
                                        $this->isSaving = false;
                                        return false;
                                    }
                                }
                            }
                        }
                        $baseModelClassName = null;
                        foreach ($this->modelClassNameToBean as $modelClassName => $bean)
                        {
                            R::store($bean);
                            assert('$bean->id > 0');
                            if (!RedBeanDatabase::isFrozen())
                            {
                                if ($baseModelClassName !== null)
                                {
                                    $tableName  = self::getTableName($modelClassName);
                                    $columnName = self::getTableName($baseModelClassName) . '_id';
                                    RedBean_Plugin_Optimizer_Id::ensureIdColumnIsINT11($tableName, $columnName);
                                }
                                $baseModelClassName = $modelClassName;
                            }
                        }
                        $this->modified = false;
                        $this->afterSave();
                        RedBeanModelsCache::cacheModel($this);
                        $this->isSaving = false;
                        return true;
                    }
                }
                $this->isSaving = false;
                return false;
            }
            catch (Exception $e)
            {
                $this->isSaving = false;
                throw $e;
            }
        }

        protected function beforeSave()
        {
            return true;
        }

        protected function afterSave()
        {
        }

        protected function linkBeans()
        {
            $baseModelClassName = null;
            $baseBean = null;
            foreach ($this->modelClassNameToBean as $modelClassName => $bean)
            {
                if ($baseBean !== null)
                {
                    R::$linkManager->link($bean, $baseBean);
                    if (!RedBeanDatabase::isFrozen())
                    {
                        $tableName  = self::getTableName($modelClassName);
                        $columnName = self::getTableName($baseModelClassName) . '_id';
                        RedBean_Plugin_Optimizer_Id::ensureIdColumnIsINT11($tableName, $columnName);
                    }
                }
                $baseModelClassName = $modelClassName;
                $baseBean = $bean;
            }
        }

        /**
         * Returns true if the model has been modified since it was saved
         * or constructed.
         */
        public function isModified()
        {
            if ($this->modified)
            {
                return true;
            }
            if ($this->isInIsModified) // Prevent cycles.
            {
                return false;
            }
            $this->isInIsModified = true;
            try
            {
                foreach ($this->relationNameToRelatedModel as $relationName => $relatedModel)
                {
                    if ((!$this->$relationName instanceof RedBeanModel) ||
                        !$this->$relationName->isSame($this))
                    {
                        if (!in_array($this->relationNameToRelationTypeModelClassNameAndOwns[$relationName][0],
                                      array(self::HAS_ONE_BELONGS_TO,
                                            self::HAS_MANY_BELONGS_TO,
                                            self::MANY_MANY)))
                        {
                            if ($this->$relationName->isModified() ||
                                $this->isAttributeRequired($relationName) &&
                                $this->$relationName->id <= 0)
                            {
                                $this->isInIsModified = false;
                                return true;
                            }
                        }
                    }
                }
                $this->isInIsModified = false;
                return false;
            }
            catch (Exception $e)
            {
                $this->isInIsModified = false;
                throw $e;
            }
        }

        /**
         * Deletes the model from the database.
         */
        public function delete()
        {
            if ($this->id < 0)
            {
                // If the model was never saved
                // then it doesn't need to be deleted.
                return;
            }
            $modelClassName = get_called_class();
            if (!$modelClassName::isTypeDeletable() ||
                !$this->isDeletable())
            {
                // See comments below on isDeletable.
                throw new NotSupportedException();
            }
            $this->beforeDelete();
            $this->unrestrictedDelete();
            $this->afterDelete();
        }

        protected function beforeDelete()
        {
        }

        protected function afterDelete()
        {
        }

        protected function unrestrictedDelete()
        {
            $this->forget();
            // RedBeanModel only supports cascaded deletes on associations,
            // not on links. So for now at least they are done the slow way.
            foreach (RuntimeUtil::getClassHierarchy(get_class($this), 'RedBeanModel') as $modelClassName)
            {
                $this->deleteOwnedRelatedModels  ($modelClassName);
                $this->deleteForeignRelatedModels($modelClassName);
            }
            foreach ($this->modelClassNameToBean as $modelClassName => $bean)
            {
                R::trash($bean);
            }
            // The model cannot be used anymore.
            $this->deleted = true;
        }

        public function isDeleted()
        {
            return $this->deleted;
        }

        protected function deleteOwnedRelatedModels($modelClassName)
        {
            foreach ($this->relationNameToRelationTypeModelClassNameAndOwns as $relationName => $relationTypeModelClassNameAndOwns)
            {
                assert('count($relationTypeModelClassNameAndOwns) == 3');
                $relationType = $relationTypeModelClassNameAndOwns[0];
                $owns         = $relationTypeModelClassNameAndOwns[2];
                if ($owns)
                {
                    if ((!$this->$relationName instanceof RedBeanModel) ||
                        !$this->$relationName->isSame($this))
                    {
                        assert('in_array($relationType, array(self::HAS_ONE, self::HAS_MANY))');
                        if ($relationType == self::HAS_ONE)
                        {
                            if ($this->$relationName->id > 0)
                            {
                                $this->$relationName->unrestrictedDelete();
                            }
                        }
                        else
                        {
                            foreach ($this->$relationName as $model)
                            {
                                $model->unrestrictedDelete();
                            }
                        }
                    }
                }
            }
        }

        protected function deleteForeignRelatedModels($modelClassName)
        {
            $metadata = $this->getMetadata();
            if (isset($metadata[$modelClassName]['foreignRelations']))
            {
                foreach ($metadata[$modelClassName]['foreignRelations'] as $relatedModelClassName)
                {
                    $relatedModels = $relatedModelClassName::
                                        getByRelatedClassId($relatedModelClassName,
                                                            $this->getClassId($modelClassName),
                                                            $modelClassName);
                    foreach ($relatedModels as $relatedModel)
                    {
                        $relatedModel->unrestrictedDelete();
                    }
                }
            }
        }

        protected static function getByRelatedClassId($relatedModelClassName, $id, $modelClassName = null)
        {
            assert('is_string($relatedModelClassName)');
            assert('$relatedModelClassName != ""');
            assert('is_int($id)');
            assert('$id > 0');
            assert('$modelClassName === null || is_string($modelClassName) && $modelClassName != ""');
            if ($modelClassName === null)
            {
                $modelClassName = get_called_class();
            }
            $tableName = self::getTableName($relatedModelClassName);
            $foreignKeyName = strtolower($modelClassName) . '_id';
            $beans = RedBean_Plugin_Finder::where($tableName, "$foreignKeyName = $id");
            return self::makeModels($beans, $relatedModelClassName);
        }

        /**
         * To be overriden on intermediate derived classes
         * to return false so that deletes are not done on
         * intermediate classes because the object relational
         * mapping will not clean up properly.
         * For example if User is a Person, and Person is
         * a RedBeanModel delete should be called only on User,
         * not on Person. So User must override isDeletable
         * to return false.
         */
        public static function isTypeDeletable()
        {
            return true;
        }

        /**
         * To be overridden by derived classes to prevent
         * deletion.
         */
        public function isDeletable()
        {
            return true;
        }

        /**
         * Forgets about all of the objects so that when they are retrieved
         * again they will be recreated from the database. For use in testing.
         */
        public static function forgetAll()
        {
            RedBeanModelsCache::forgetAll();
        }

        /**
         * Forgets about the object so that if it is retrieved
         * again it will be recreated from the database. For use in testing.
         */
        public function forget()
        {
            RedBeanModelsCache::forgetModel($this);
        }

        /**
         * See the yii documentation.
         */
        public function isAttributeRequired($attributeName)
        {
            assert("\$this->isAttribute('$attributeName')");
            foreach ($this->getValidators($attributeName) as $validator)
            {
                if ($validator instanceof CRequiredValidator)
                {
                    return true;
                }
            }
            return false;
        }

        /**
         * See the yii documentation.
         */
        public function isAttributeSafe($attributeName)
        {
            $attributeNames = $this->getSafeAttributeNames();
            return in_array($attributeName, $attributeNames);
        }

        public static function getModelLabelByTypeAndLanguage($type, $language = null)
        {
            assert('in_array($type, array("Singular", "SingularLowerCase", "Plural", "PluralLowerCase"))');
            if ($type == 'Singular')
            {
               return Yii::t('Default', static::getLabel(),
                        LabelUtil::getTranslationParamsForAllModules(), null, $language);
            }
            if ($type == 'SingularLowerCase')
            {
               return strtolower(Yii::t('Default', static::getLabel(),
                        LabelUtil::getTranslationParamsForAllModules(), null, $language));
            }
            if ($type == 'Plural')
            {
               return Yii::t('Default', static::getPluralLabel(),
                        LabelUtil::getTranslationParamsForAllModules(), null, $language);
            }
            if ($type == 'PluralLowerCase')
            {
               return strtolower(Yii::t('Default', static::getPluralLabel(),
                        LabelUtil::getTranslationParamsForAllModules(), null, $language));
            }
        }

        protected static function getLabel()
        {
            return get_called_class();
        }

        protected static function getPluralLabel()
        {
            return static::getLabel() . 's';
        }

        /**
         * See the yii documentation.
         */
        public function getAttributeLabel($attributeName)
        {
            return $this->getAttributeLabelByLanguage($attributeName, Yii::app()->language);
        }

        /**
         * Given an attributeName and a language, retrieve the translated attribute label. Attempts to find a customized
         * label in the metadata first, before falling back on the standard attribute label for the specified attribute.
         * @return string - translated attribute label
         */
        protected function getAttributeLabelByLanguage($attributeName, $language)
        {
            assert('is_string($attributeName)');
            assert('is_string($language)');
            $labels       = $this->untranslatedAttributeLabels();
            $customLabel  = $this->getTranslatedCustomAttributeLabelByLanguage($attributeName, $language);
            if ($customLabel != null)
            {
                return $customLabel;
            }
            elseif (isset($labels[$attributeName]))
            {
                return Yii::t('Default', $labels[$attributeName], array(), null, $language);
            }
            else
            {
                //should do a T:: wrapper here too.
                return Yii::t('Default', $this->generateAttributeLabel($attributeName), array(), null, $language);
            }
        }

        /**
         * Given an attributeName, attempt to find in the metadata a custom attribute label for the given language.
         * @return string - translated attribute label, if not found return null.
         */
        protected function getTranslatedCustomAttributeLabelByLanguage($attributeName, $language)
        {
            assert('is_string($attributeName)');
            assert('is_string($language)');
            $metadata = $this->getMetadata();
            foreach ($metadata as $modelClassName => $modelClassMetadata)
            {
                if (isset($modelClassMetadata['labels']) &&
                    isset($modelClassMetadata['labels'][$attributeName]) &&
                    isset($modelClassMetadata['labels'][$attributeName][$language]))
                {
                    return $modelClassMetadata['labels'][$attributeName][$language];
                }
            }
            return null;
        }

        /**
         * Given an attributeName, return an array of all attribute labels for each language available.
         * @return array - attribute labels by language for the given attributeName.
         */
        public function getAttributeLabelsForAllSupportedLanguagesByAttributeName($attributeName)
        {
            assert('is_string($attributeName)');
            $attirbuteLabelData = array();
            foreach (Yii::app()->languageHelper->getSupportedLanguagesData() as $language => $name)
            {
                $attirbuteLabelData[$language] = $this->getAttributeLabelByLanguage($attributeName, $language);
            }
            return $attirbuteLabelData;
        }

        /**
         * See the yii documentation. The yii hasErrors() takes an optional
         * attribute name. RedBeanModel's hasErrors() takes an optional attribute
         * name or array of attribute names. See getErrors() for an explanation
         * of this difference.
         */
        public function hasErrors($attributeNameOrNames = null)
        {
            assert('$attributeNameOrNames === null   || ' .
                   'is_string($attributeNameOrNames) || ' .
                   'is_array ($attributeNameOrNames) && AssertUtil::all($attributeNameOrNames, "is_string")');
            if ($this->isInHasErrors) // Prevent cycles.
            {
                return false;
            }
            $this->isInHasErrors = true;
            try
            {
                if (is_string($attributeNameOrNames))
                {
                    $attributeName = $attributeNameOrNames;
                    $relatedAttributeNames = null;
                }
                elseif (is_array($attributeNameOrNames))
                {
                    $attributeName = $attributeNameOrNames[0];
                    if (count($attributeNameOrNames) > 1)
                    {
                        $relatedAttributeNames = array_slice($attributeNameOrNames, 1);
                    }
                    else
                    {
                        $relatedAttributeNames = null;
                    }
                }
                else
                {
                    $attributeName         = null;
                    $relatedAttributeNames = null;
                }
                assert("\$attributeName        === null || is_string('$attributeName')");
                assert('$relatedAttributeNames === null || is_array($relatedAttributeNames)');
                assert('!($attributeName === null && $relatedAttributeNames !== null)');
                if ($attributeName === null)
                {
                    if (count($this->attributeNameToErrors) > 0)
                    {
                        $this->isInHasErrors = false;
                        return true;
                    }
                    foreach ($this->relationNameToRelatedModel as $relationName => $relatedModelOrModels)
                    {
                        if ((!$this->$relationName instanceof RedBeanModel) ||
                             !$this->$relationName->isSame($this))
                        {
                            if (in_array($relationName, $this->attributeNamesNotBelongsToOrManyMany))
                            {
                                if ($relatedModelOrModels->hasErrors($relatedAttributeNames))
                                {
                                    $this->isInHasErrors = false;
                                    return true;
                                }
                            }
                        }
                    }
                    $this->isInHasErrors = false;
                    return false;
                }
                else
                {
                    if (!$this->isRelation($attributeName))
                    {
                        $this->isInHasErrors = false;
                        return array_key_exists($attributeName, $this->attributeNameToErrors);
                    }
                    else
                    {
                        if (in_array($attributeName, $this->attributeNamesNotBelongsToOrManyMany))
                        {
                            $this->isInHasErrors = false;
                            return isset($this->relationNameToRelatedModel[$attributeName]) &&
                                   count($this->relationNameToRelatedModel[$attributeName]->getErrors($relatedAttributeNames)) > 0;
                        }
                    }
                }
                $this->isInHasErrors = false;
                return false;
            }
            catch (Exception $e)
            {
                $this->isInHasErrors = false;
                throw $e;
            }
        }

        /**
         * See the yii documentation. The yii getErrors() takes an optional
         * attribute name. RedBeanModel's getErrors() takes an optional attribute
         * name or array of attribute names.
         * @param @attributeNameOrNames Either null, return all errors on the
         * model and its related models, an attribute name on the model, return
         * errors on that attribute, or an array of relation and attribute names,
         * return errors on a related model's attribute.

         */
        public function getErrors($attributeNameOrNames = null)
        {
            assert('$attributeNameOrNames === null   || ' .
                   'is_string($attributeNameOrNames) || ' .
                   'is_array ($attributeNameOrNames) && AssertUtil::all($attributeNameOrNames, "is_string")');
            if ($this->isInGetErrors) // Prevent cycles.
            {
                return array();
            }
            $this->isInGetErrors = true;
            try
            {
                if (is_string($attributeNameOrNames))
                {
                    $attributeName = $attributeNameOrNames;
                    $relatedAttributeNames = null;
                }
                elseif (is_array($attributeNameOrNames))
                {
                    $attributeName = $attributeNameOrNames[0];
                    if (count($attributeNameOrNames) > 1)
                    {
                        $relatedAttributeNames = array_slice($attributeNameOrNames, 1);
                    }
                    else
                    {
                        $relatedAttributeNames = null;
                    }
                }
                else
                {
                    $attributeName         = null;
                    $relatedAttributeNames = null;
                }
                assert("\$attributeName        === null || is_string('$attributeName')");
                assert('$relatedAttributeNames === null || is_array($relatedAttributeNames)');
                assert('!($attributeName === null && $relatedAttributeNames !== null)');
                if ($attributeName === null)
                {
                    $errors = $this->attributeNameToErrors;
                    foreach ($this->relationNameToRelatedModel as $relationName => $relatedModelOrModels)
                    {
                        if ((!$this->$relationName instanceof RedBeanModel) ||
                            !$this->$relationName->isSame($this))
                        {
                            if (!in_array($this->relationNameToRelationTypeModelClassNameAndOwns[$relationName][0],
                                          array(self::HAS_ONE_BELONGS_TO,
                                                self::HAS_MANY_BELONGS_TO,
                                                self::MANY_MANY)))
                            {
                                $relatedErrors = $relatedModelOrModels->getErrors($relatedAttributeNames);
                                if (count($relatedErrors) > 0)
                                {
                                   $errors[$relationName] = $relatedErrors;
                                }
                            }
                        }
                    }
                    $this->isInGetErrors = false;
                    return $errors;
                }
                else
                {
                    if (isset($this->attributeNameToErrors[$attributeName]))
                    {
                        $this->isInGetErrors = false;
                        return  $this->attributeNameToErrors[$attributeName];
                    }
                    elseif (isset($this->relationNameToRelatedModel[$attributeName]))
                    {
                        if (!in_array($this->relationNameToRelationTypeModelClassNameAndOwns[$attributeName][0],
                                      array(self::HAS_ONE_BELONGS_TO, self::HAS_MANY_BELONGS_TO)))
                        {
                            $this->isInGetErrors = false;
                            return $this->relationNameToRelatedModel[$attributeName]->getErrors($relatedAttributeNames);
                        }
                    }
                }
                $this->isInGetErrors = false;
                return array();
            }
            catch (Exception $e)
            {
                $this->isInGetErrors = false;
                throw $e;
            }
        }

        /**
         * See the yii documentation.
         */
        public function getError($attributeName)
        {
            assert("\$this->isAttribute('$attributeName')");
            return isset($this->attributeNameToErrors[$attributeName]) ? reset($this->attributeNameToErrors[$attributeName]) : null;
        }

        /**
         * See the yii documentation.
         */
        public function addError($attributeName, $errorMessage)
        {
            assert("\$this->isAttribute('$attributeName')");
            if (!isset($this->attributeNameToErrors[$attributeName]))
            {
                $this->attributeNameToErrors[$attributeName] = array();
            }
            $this->attributeNameToErrors[$attributeName][] = $errorMessage;
        }

        /**
         * See the yii documentation.
         */
        public function addErrors(array $errors)
        {
            foreach ($errors as $attributeName => $error)
            {
                assert("\$this->isAttribute('$attributeName')");
                assert('is_array($error) || is_string($error)');
                if (is_array($error))
                {
                    if (!isset($this->attributeNameToErrors[$attributeName]))
                    {
                        $this->attributeNameToErrors[$attributeName] = array();
                    }
                    $this->attributeNameToErrors[$attributeName] =
                            array_merge($this->attributeNameToErrors[$attributeName], $error);
                }
                else
                {
                    $this->attributeNameToErrors[$attributeName][] = $error;
                }
            }
        }

        /**
         * See the yii documentation.
         */
        public function clearErrors($attributeName = null)
        {
            assert("\$attributeName === null || \$this->isAttribute('$attributeName')");
            if ($attributeName === null)
            {
                $this->attributeNameToErrors = array();
            }
            else
            {
                unset($this->attributeNameToErrors[$attributeName]);
            }
        }

        /**
         * See the yii documentation.
         */
        public function generateAttributeLabel($attributeName)
        {
            assert("\$this->isAttribute('$attributeName')");
            return ucfirst(preg_replace('/([A-Z0-9])/', ' \1', $attributeName));
        }

        /**
         * See the yii documentation.
         */
        public function getAttributes(array $attributeNames = null)
        {
            $values = array();
            foreach ($this->attributeNames() as $attributeName)
            {
                $values[$attributeName] = $this->$attributeName;
            }
            if (is_array($attributeNames))
            {
                $values2 = array();
                foreach ($attributeNames as $attributeName)
                {
                    if (isset($values[$attributeName]))
                    {
                        $values2[$attributeName] = $values[$attributeName];
                    }
                }
                return $values2;
            }
            else
            {
                return $values;
            }
        }

        /**
         * See the yii documentation.
         */
        public function setAttributes(array $values, $safeOnly = true)
        {
            assert('is_bool($safeOnly)');
            $attributeNames = array_flip($safeOnly ? $this->getSafeAttributeNames() : $this->attributeNames());
            foreach ($values as $attributeName => $value)
            {
                if ($value !== null)
                {
                    if (!is_array($value))
                    {
                        assert('$attributeName != "id"');
                        if ($attributeName != 'id' && $this->isAttribute($attributeName))
                        {
                            if ($this->isAttributeSafe($attributeName) || !$safeOnly)
                            {
                                $this->$attributeName = $value;
                            }
                            else
                            {
                                $this->onUnsafeAttribute($attributeName, $value);
                            }
                        }
                    }
                    else
                    {
                        if ($this->isRelation($attributeName))
                        {
                            if (count($value) == 1 && array_key_exists('id', $value))
                            {
                                if (empty($value['id']))
                                {
                                    $this->$attributeName = null;
                                }
                                else
                                {
                                    $relatedModelClassName = $this->relationNameToRelationTypeModelClassNameAndOwns[$attributeName][1];
                                    $this->$attributeName = self::getById(intval($value['id']), $relatedModelClassName);
                                }
                            }
                            else
                            {
                                $this->$attributeName->setAttributes($value);
                            }
                        }
                    }
                }
            }
        }

        /**
         * See the yii documentation.
         */
        public function unsetAttributes($attributeNames = null)
        {
            if ($attributeNames === null)
            {
                $attributeNames = $this->attributeNames();
            }
            foreach ($attributeNames as $attributeName)
            {
                $this->$attributeNames = null;
            }
        }

        /**
         * See the yii documentation.
         */
        public function onUnsafeAttribute($name, $value)
        {
            if (YII_DEBUG)
            {
                Yii::log(Yii::t('yii', 'Failed to set unsafe attribute "{attribute}".', array('{attribute}' => $name)), CLogger::LEVEL_WARNING);
            }
        }

        /**
         * See the yii documentation.
         */
        public function getScenario()
        {
            return $this->scenarioName;
        }

        /**
         * See the yii documentation.
         */
        public function setScenario($scenarioName)
        {
            assert('is_string($scenarioName)');
            $this->scenarioName = $scenarioName;
        }

        /**
         * See the yii documentation.
         */
        public function getSafeAttributeNames()
        {
            $attributeNamesToIsSafe = array();
            $unsafeAttributeNames   = array();
            foreach ($this->getValidators() as $validator)
            {
                if (!$validator->safe)
                {
                    foreach ($validator->attributes as $attributeName)
                    {
                        $unsafeAttributeNames[] = $attributeName;
                    }
                }
                else
                {
                    foreach ($validator->attributes as $attributeName)
                    {
                        $attributeNamesToIsSafe[$attributeName] = true;
                    }
                }
            }
            foreach ($unsafeAttributeNames as $attributeName)
            {
                unset($attributeNamesToIsSafe[$attributeName]);
            }
            return array_keys($attributeNamesToIsSafe);
        }

        /**
         * See the yii documentation.
         */
        public function getIterator()
        {
            throw new NotImplementedException();
        }

        /**
         * See the yii documentation.
         */
        public function offsetExists($offset)
        {
            throw new NotImplementedException();
        }

        /**
         * See the yii documentation.
         */
        public function offsetGet($offset)
        {
            throw new NotImplementedException();
        }

        /**
         * See the yii documentation.
         */
        public function offsetSet($offset, $item)
        {
            throw new NotImplementedException();
        }

        /**
         * See the yii documentation.
         */
        public function offsetUnset($offset)
        {
            throw new NotImplementedException();
        }

        /**
         * Creates an instance of the extending model wrapping the given
         * bean. For use only by models. Beans are never used by the
         * application directly.
         * @param $bean A <a href="http://www.redbeanphp.com/">RedBean</a>
         * bean.
         * @param $modelClassName Pass only when getting it at runtime
         *                        gets the wrong name.
         * @return An instance of the type of the extending model.
         */
        public static function makeModel(RedBean_OODBBean $bean, $modelClassName = null, $forceTreatAsCreation = false)
        {
            assert('$modelClassName === null || is_string($modelClassName) && $modelClassName != ""');
            if ($modelClassName === null)
            {
                $modelClassName = get_called_class();
            }
            $modelIdentifier = $modelClassName . strval($bean->id);
            try
            {
                return RedBeanModelsCache::getModel($modelIdentifier);
            }
            catch (NotFoundException $e)
            {
                return new $modelClassName(true, $bean, $forceTreatAsCreation);
            }
        }

        /**
         * Creates an array of instances of the named model type wrapping the
         * given beans. For use only by models. Beans are never used by the
         * application directly.
         * @param $beans An array of <a href="http://www.redbeanphp.com/">RedBean</a>
         * beans.
         * @param $modelClassName Pass only when getting it at runtime
         *                        gets the wrong name.
         * @return An array of instances of the type of the extending model.
         */
        public static function makeModels(array $beans, $modelClassName = null)
        {
            if ($modelClassName === null)
            {
                $modelClassName = get_called_class();
            }
            $models = array();
            foreach ($beans as $bean)
            {
                assert('$bean instanceof RedBean_OODBBean');
                $models[] = self::makeModel($bean, $modelClassName);
            }
            return $models;
        }
    }
?>
