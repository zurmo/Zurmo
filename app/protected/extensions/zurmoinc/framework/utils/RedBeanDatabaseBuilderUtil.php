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

    /**
     * The purpose of this class is to drill through RedBeanModels,
     * populating them with made up data and saving them, in order
     * to build the database schema for freezing.
     */
    class RedBeanDatabaseBuilderUtil
    {
        protected static $modelClassNamesToSampleModels;
        protected static $uniqueStrings = array();

        /**
         * Auto building of models (and therefore the database) involves...
         *
         *  - Creating each model.
         *  - Setting its members to made up values that conform to the rules specified for the member.
         *  - Setting or adding to its relations while avoiding making new objects of the same types
         *    as have already been made.
         *  - Saving it so that the tables and columns are created.
         *  - Deleting it so that it doesn't leave rows behind.
         *  - (The database is now ready for freezing.)
         *
         * The aim of the auto build is to populate models in a 'valid enough way' to save them
         * such that RedBean creates the tables and columns it needs with the right column types.
         * This means it does not necessarily make models that are valid, for example it will set
         * a model's parent to itself if model and the parent are of the same type. These kinds of
         * inconsistencies do not matter for the purpose of auto building the database, and are
         * semantic information that is not available and not needed for this process. The idea is
         * to create as few models as possible.
         *
         * Call this an empty unfrozen database with all the models required for certain tests, or
         * all the models required for the production database. Then freeze the database.
         *
         * If a model references a non-leaf model in the hierarchy an example of a model subclassed
         * from that type must be included in the $modelClassNames. eg: 'Opportunity' references
         * Permitable via its permissions and an abstract 'Permitable' cannot be created, so 'User'
         * needs be created at the same time since it is concrete and can be used to create an
         * Opportunity.
         * ie: $modelClassNames = array('Opportunity', 'User').
         */

        public static function autoBuildModels(array $modelClassNames, & $messageLogger)
        {
            assert('AssertUtil::all($modelClassNames, "is_string")');
            assert('$messageLogger instanceof MessageLogger');
            self::$modelClassNamesToSampleModels = array();
            self::$uniqueStrings                 = array();
            foreach ($modelClassNames as $modelClassName)
            {
                $messages[] = array('info' => "Auto building $modelClassName.");
                self::autoBuildSampleModel($modelClassName, $modelClassNames, $messageLogger);
                $messageLogger->addInfoMessage("Auto build of $modelClassName done.");
            }
            foreach (self::$modelClassNamesToSampleModels as $modelClassName => $model)
            {
                if (!$model instanceof OwnedModel && !$model instanceof OwnedCustomField)
                {
                    try
                    {
                        if (!$model->save())
                        {
                            $messageLogger->addErrorMessage("*** Saving the sample $modelClassName failed.");
                            $errors = $model->getErrors();
                            if (count($errors) > 0)
                            {
                                $messageLogger->addErrorMessage('The attributes that did not validate probably need more rules, or are not deletable types.');
                                $messageLogger->addErrorMessage(print_r($errors, true));
                            }
                            else
                            {
                                $messageLogger->addErrorMessage('No attributes failed to validate!');
                            }
                        }
                        $messageLogger->addInfoMessage("Auto built $modelClassName saved.");
                    }
                    catch (NotSupportedException $e)
                    {
                        $messageLogger->addErrorMessage("*** Saving the sample $modelClassName failed.");
                        if (is_subclass_of($modelClassName, 'OwnedCustomField') ||
                            is_subclass_of($modelClassName, 'OwnedModel'))
                        {
                            $messageLogger->addErrorMessage('It is OWNED and was probably not saved via its owner, making it not a root model.');
                        }
                        else
                        {
                            $messageLogger->addErrorMessage('The save failed but there were no validation errors.');
                        }
                    }
                }
            }
            foreach (self::$modelClassNamesToSampleModels as $modelClassName => $model)
            {
                try
                {
                    if (!$model->isDeleted())
                    {
                        $messageLogger->addInfoMessage(get_class($model) . " Deleted (Not Owned).");
                        $model->delete();
                    }
                    else
                    {
                        $messageLogger->addInfoMessage(get_class($model) . " Deleted Already (Owned).");
                    }
                    AuditEvent::deleteAllByModel($model);
                    unset(self::$modelClassNamesToSampleModels[$modelClassName]);
                }
                catch (NotSupportedException $e)
                {
                    $messageLogger->addErrorMessage("*** Deleting the sample $modelClassName failed. It is marked not deletable.");
                }
            }
            if (count(self::$modelClassNamesToSampleModels))
            {
                $messageLogger->addErrorMessage('*** Deleting of the sample(s) ' . join(', ', array_keys(self::$modelClassNamesToSampleModels)) . " didn't happen.");
            }
        }

        public static function autoBuildSampleModel($modelClassName, array $modelClassNames, & $messageLogger)
        {
            assert('$messageLogger instanceof MessageLogger');
            if (!empty(self::$modelClassNamesToSampleModels[$modelClassName]))
            {
                return self::$modelClassNamesToSampleModels[$modelClassName];
            }
            $messageLogger->addInfoMessage("$modelClassName Being Created.");
            $model = new $modelClassName();
            self::$modelClassNamesToSampleModels[$modelClassName] = $model;
            $metadata = $model->getMetadata();
            foreach ($metadata as $unused => $classMetadata)
            {
                if (!empty($classMetadata['members']))
                {
                    foreach ($classMetadata['members'] as $memberName)
                    {
                        if (!$model->isAttributeReadOnly($memberName))
                        {
                            $messageLogger->addInfoMessage("Setting $modelClassName->$memberName.");
                            self::setMadeUpMemberValue($model, $memberName);
                        }
                    }
                }
            }
            foreach ($metadata as $unused => $classMetadata)
            {
                if (!empty($classMetadata['relations']))
                {
                    foreach ($classMetadata['relations'] as $relationName => $relationTypeModelClassNameAndOwns)
                    {
                        //Always use the current user to ensure the model can later be saved and removed.
                        if ($relationName == 'owner' && $model instanceof OwnedSecurableItem)
                        {
                            $model->owner = Yii::app()->user->userModel;
                        }
                        elseif (!$model->isAttributeReadOnly($relationName))
                        {
                            $messageLogger->addInfoMessage("Setting $modelClassName->$relationName.");

                            $relationType          = $relationTypeModelClassNameAndOwns[0];
                            $relatedModelClassName = $relationTypeModelClassNameAndOwns[1];
                            $owned                 = isset($relationTypeModelClassNameAndOwns[2]) &&
                                                           $relationTypeModelClassNameAndOwns[2] == RedBeanModel::OWNED;

                            $message = $relationType == RedBeanModel::HAS_ONE_BELONGS_TO   ? "HAS_ONE_BELONGS_TO"  :
                                       ($relationType == RedBeanModel::HAS_MANY_BELONGS_TO ? "HAS_MANY_BELONGS_TO" :
                                        ($relationType == RedBeanModel::HAS_ONE            ? "HAS_ONE"             :
                                         ($relationType == RedBeanModel::HAS_MANY          ? "HAS_MANY"            :
                                          ($relationType == RedBeanModel::MANY_MANY        ? "MANY_MANY"           : '????'))));
                            $messageLogger->addInfoMessage($message);

                            if ($relationType == RedBeanModel::HAS_ONE    &&
                                $model->$relationName->id < 0             &&
                                $relatedModelClassName == $modelClassName &&
                                !$owned)
                            {
                                $messageLogger->addInfoMessage($relatedModelClassName);
                                $messageLogger->addInfoMessage('(Set self)');
                                $model->$relationName = $model;
                            }
                            else
                            {
                                $relatedModel = null;
                                if ($relatedModelClassName::isTypeDeletable() || $owned)
                                {
                                    $messageLogger->addInfoMessage($relatedModelClassName);
                                    $relatedModel = self::autoBuildSampleModel($relatedModelClassName, $modelClassNames, $messageLogger);
                                }
                                else
                                {
                                    foreach ($modelClassNames as $otherModelClassName)
                                    {
                                        if (is_subclass_of($otherModelClassName, $relatedModelClassName) &&
                                            $otherModelClassName::isTypeDeletable())
                                        {
                                            $messageLogger->addInfoMessage("$relatedModelClassName (subst)");
                                            $relatedModel = self::autoBuildSampleModel($otherModelClassName, $modelClassNames, $messageLogger);
                                            break;
                                        }
                                    }
                                }
                                if (isset($relatedModel))
                                {
                                    if (in_array($relationType, array(RedBeanModel::HAS_ONE_BELONGS_TO,
                                                                      RedBeanModel::HAS_MANY_BELONGS_TO,
                                                                      RedBeanModel::HAS_ONE)))
                                    {
                                        $messageLogger->addInfoMessage('(Set)');
                                        $model->$relationName = $relatedModel;
                                    }
                                    elseif ($model->$relationName->count() == 0)
                                    {
                                        assert('in_array($relationType, array(RedBeanModel::HAS_MANY,
                                                                              RedBeanModel::MANY_MANY))');
                                        $messageLogger->addInfoMessage('(Added)');
                                        $model->$relationName->add($relatedModel);
                                    }
                                }
                            }
                        }
                    }
                }
            }
            return $model;
        }

        protected static function setMadeUpMemberValue($model, $memberName)
        {
            $memberSet          = false;
            $minValue           = null;
            $maxValue           = null;
            $minLength          = 1;
            $maxLength          = null;
            $unique             = false;
            $ignoreDefaultValue = false;
            foreach ($model->getValidators($memberName) as $validator)
            {
                switch (get_class($validator))
                {
                    case 'CBooleanValidator':
                        $model->$memberName = 1;
                        $memberSet = true;
                        break;
                    case 'RedBeanModelDateTimeDefaultValueValidator':
                        break;
                    case 'CDefaultValueValidator':
                    case 'RedBeanModelDefaultValueValidator':
                        if ($validator->value === null || $validator->value === '')
                        {
                            throw new NotSupportedException();
                        }
                        $model->$memberName = $validator->value;
                        $memberSet = true;
                        break;

                    case 'CEmailValidator':
                        $model->$memberName = 'someone@somewhere.net';
                        $memberSet = true;
                        break;

                    case 'CInlineValidator':
                        break;

                    case 'RedBeanModelNumberValidator':
                        if ($validator->min !== null)
                        {
                            $minValue = $validator->min;
                        }
                        if ($validator->max !== null)
                        {
                            $maxValue = $validator->max;
                        }
                        break;

                    case 'CStringValidator':
                        if ($validator->min !== null)
                        {
                            $minLength = $validator->min;
                        }
                        if ($validator->max !== null)
                        {
                            $maxLength = $validator->max;
                        }
                        break;

                    case 'RedBeanModelUniqueValidator':
                        $unique = true;
                        break;

                    case 'CUrlValidator':
                        $model->$memberName = 'http://www.example.com';
                        $memberSet = true;
                        break;

                    case 'CRegularExpressionValidator':
                    case 'CRequiredValidator':
                    case 'CSafeValidator':
                    case 'CUnsafeValidator':
                    case 'RedBeanModelCompareDateTimeValidator':
                    case 'RedBeanModelRequiredValidator':
                    case 'UsernameLengthValidator':
                    case 'validateTimeZone':
                        break;

                    case 'RedBeanModelTypeValidator':
                    case 'TypeValidator':
                        if ($validator->type == 'float' || $validator->type == 'integer' || $validator->type == 'string')
                        {
                            //A number or string default value could be set in the rules, but we should ignore this and try
                            //to make the largest sized number possible for this column.
                            $ignoreDefaultValue = true;
                        }
                        break;

                    case 'CCaptchaValidator':
                    case 'CCompareValidator':
                    case 'CDateValidator':
                    case 'CExistValidator':
                    case 'CFileValidator':
                    case 'CFilterValidator':
                    case 'CNumberValidator':
                    case 'CRangeValidator':
                    case 'CUniqueValidator':
                    default:
                        // This just means that supported needs to be
                        // added for a validator that has been used,
                        // not that it can't or shouldn't be added.
                        echo get_class($validator) . "\n";
                        throw new NotSupportedException();
                }

                if ($validator instanceof CStringValidator)
                {
                }
            }
            if (!$memberSet || $ignoreDefaultValue)
            {
                foreach ($model->getValidators($memberName) as $validator)
                {
                    if ($validator instanceof TypeValidator)
                    {
                        switch ($validator->type)
                        {
                            case 'integer':
                                $i = 2147483647;
                                if ($minValue !== null)
                                {
                                    $i = max($i, $minValue);
                                }
                                if ($maxValue !== null)
                                {
                                    $i = min($i, $maxValue);
                                }
                                $model->$memberName = $i;
                                break;

                            case 'float':
                                $f = 3.14;
                                if ($minValue !== null)
                                {
                                    $f = max($f, $minValue);
                                }
                                if ($maxValue !== null)
                                {
                                    $f = min($f, $maxValue);
                                }
                                $model->$memberName = $f;
                                break;

                            case 'date':
                                $model->$memberName = '2000-01-01';
                                break;

                            case 'time':
                                $model->$memberName = '12:00';
                                break;

                            case 'datetime':
                                $model->$memberName = '2000-01-01 12:00:00';
                                break;

                            case 'array';
                                throw new NotSupportedException();

                            case 'string':
                            default:
                                // Makes a string like 'Diald' respecting
                                // the minimum and maximum length rules
                                // and if that does not validate guesses
                                // that it should be all lowercase or all
                                // uppercase. If it still doesn't validate,
                                // well, we'll see...
                                $s = self::getUniqueString($minLength, $maxLength, $unique);
                                $model->$memberName = $s;
                                if (!$model->validate(array($memberName)))
                                {
                                    $model->$memberName = strtolower($model->$memberName);
                                    if (!$model->validate(array($memberName)))
                                    {
                                        $model->$memberName = strtoupper($model->$memberName);
                                        if (!$model->validate(array($memberName)))
                                        {
                                            $model->$memberName = $s;
                                        }
                                    }
                                }
                        }
                    }
                }
            }
        }

        protected static function getUniqueString($minLength, $maxLength, $unique)
        {
            if ($maxLength == null)
            {
                $maxLength = 1024;
            }
            do
            {
                $s = chr(rand(ord('A'), ord('Z')));
                $length = max($minLength, $maxLength);
                if ($maxLength !== null)
                {
                    $length = min($length, $maxLength);
                }
                while (strlen($s) < $length)
                {
                    $s .= chr(rand(ord('a'), ord('z')));
                }
            } while ($unique && in_array($s, self::$uniqueStrings));
            $uniqueStrings[] = $s;
            return $s;
        }
    }
?>
