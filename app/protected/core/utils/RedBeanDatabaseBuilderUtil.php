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
     * The purpose of this class is to drill through RedBeanModels,
     * populating them with made up data and saving them, in order
     * to build the database schema for freezing.
     */
    class RedBeanDatabaseBuilderUtil
    {
        const AUTO_BUILD_STATE_KEY = 'autoBuildState';
        const AUTO_BUILD_SAMPLE_MODELS_KEY = 'autoBuildModels';

        const AUTO_BUILD_STATE_INVALID = 'invalid';
        const AUTO_BUILD_STATE_VALID = 'valid';

        protected static $modelClassNamesToSampleModels;

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

            if (!self::isAutoBuildStateValid())
            {
                self::deleteAllSampleModelsFromStatePersisterAndDatabase($messageLogger);
            }
            self::setAutoBuildStateInStatePersister(self::AUTO_BUILD_STATE_INVALID);

            AuditEvent::$isTableOptimized = false;
            self::$modelClassNamesToSampleModels = array();
            foreach ($modelClassNames as $modelClassName)
            {
                $messages[] = array('info' => "Auto building $modelClassName.");
                self::autoBuildSampleModel($modelClassName, $modelClassNames, $messageLogger);
                $messageLogger->addInfoMessage("Auto build of $modelClassName done.");
            }
            foreach (self::$modelClassNamesToSampleModels as $modelClassName => $model)
            {
                if (!$model instanceof OwnedModel && !$model instanceof OwnedCustomField && !$model instanceof OwnedMultipleValuesCustomField)
                {
                    try
                    {
                        $model->setScenario('autoBuildDatabase');
                        $saved = $model->save();

                        if ($saved)
                        {
                            self::setSampleModelInStatePersister(get_class($model), $model->id);
                            $metadata = $model->getMetadata();
                            foreach ($metadata as $unused => $classMetadata)
                            {
                                if (!empty($classMetadata['relations']))
                                {
                                    foreach ($classMetadata['relations'] as $relationName => $relationTypeModelClassNameAndOwns)
                                    {
                                        $relationType          = $relationTypeModelClassNameAndOwns[0];
                                        $relatedModelClassName = $relationTypeModelClassNameAndOwns[1];
                                        $owned                 = isset($relationTypeModelClassNameAndOwns[2]) &&
                                                               $relationTypeModelClassNameAndOwns[2] == RedBeanModel::OWNED;
                                        if (get_class($model) == get_class($model->$relationName) &&
                                                    $model->id == $model->$relationName->id)
                                        {
                                            $messageLogger->addInfoMessage("Unset {$modelClassName}->{$relationName} to avoid recursion and thread stack overrun.");
                                            $model->$relationName = null;
                                            $model->save();
                                        }
                                    }
                                }
                            }
                        }
                        else
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
                            is_subclass_of($modelClassName, 'OwnedMultipleValuesCustomField') ||
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
                        if (!$model->delete())
                        {
                            if ($model->id < 0)
                            {
                                $messageLogger->addInfoMessage(get_class($model) . " Not Deleted but never saved so this is ok. (Most likely it is a - Has Many Owned)");
                            }
                            else
                            {
                                $messageLogger->addErrorMessage("*** Deleting the sample " .  get_class($model) . " failed. It would not delete.");
                            }
                        }
                        else
                        {
                            $messageLogger->addInfoMessage(get_class($model) . " Deleted (Not Owned).");
                        }
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
            AuditEvent::$isTableOptimized = false;
            self::deleteAllSampleModelsFromStatePersister();
            $messageLogger->addInfoMessage("Deleted all sample models from state persister.");
            self::setAutoBuildStateInStatePersister(self::AUTO_BUILD_STATE_VALID);
            $messageLogger->addInfoMessage("set auto-build state in state persister.");
        }

        /**
         * @param array $modelClassNames
         * @param MessageLogger $messageLogger
         */
        public static function manageFrozenStateAndAutoBuildModels(array $modelClassNames, & $messageLogger)
        {
            RedBeanDatabase::unfreeze();
            self::autoBuildModels($modelClassNames, $messageLogger);
            RedBeanDatabase::freeze();
        }

        /**
         * Deletes all sample models from state persister and from database
         * @param MessageLogger $messageLogger
         */
        protected static function deleteAllSampleModelsFromStatePersisterAndDatabase($messageLogger)
        {
            $allSampleModels = self::getSampleModelsFromStatePersister();
            $allSampleModelsDeleted = true;
            if (!empty($allSampleModels))
            {
                foreach ($allSampleModels as $key => $sampleModel)
                {
                    $allSampleModelsDeleted = $allSampleModelsDeleted && self::deleteSampleModelFromStatePersisterAndDatabase(
                                        $sampleModel['modelClassName'],
                                        $sampleModel['modelId'],
                                        $messageLogger);
                }
            }
            if ($allSampleModelsDeleted)
            {
                echo "All sample models from previous autobuild are deleted.";
                $messageLogger->addInfoMessage("All sample models from previous autobuild are deleted, but schema is not updated yet.");
                $messageLogger->addInfoMessage("If you want to update schema, run auto build process again.");
                self::setAutoBuildStateInStatePersister(self::AUTO_BUILD_STATE_VALID);
            }
            else
            {
                $messageLogger->addErrorMessage("All sample models couldn't be deleted.");
            }
            Yii::app()->end();
        }

        /**
         * Store sample model in state persister(database)
         * @param string $modelClassName
         * @param int $modelId
         */
        protected static function setSampleModelInStatePersister($modelClassName, $modelId)
        {
            $statePersister = Yii::app()->getStatePersister();
            $state = $statePersister->load();
            $sampleModel = array(
                'modelClassName' => $modelClassName,
                'modelId'        => $modelId
            );
            $state[self::AUTO_BUILD_SAMPLE_MODELS_KEY][] = $sampleModel;
            $statePersister->save($state);
        }

        /**
         * Get all sample models from state persister
         * @return array | null
         */
        protected static function getSampleModelsFromStatePersister()
        {
            $statePersister = Yii::app()->getStatePersister();
            $state = $statePersister->load();
            if (isset($state[self::AUTO_BUILD_SAMPLE_MODELS_KEY]))
            {
                return $state[self::AUTO_BUILD_SAMPLE_MODELS_KEY];
            }
            else
            {
                return null;
            }
        }

        /**
         * Delete all sample models from state persister. If this function is used,
         * sample models should alreaady be deleted from database.
         */
        protected static function deleteAllSampleModelsFromStatePersister()
        {
            $statePersister = Yii::app()->getStatePersister();
            $state = $statePersister->load();
            unset($state[self::AUTO_BUILD_SAMPLE_MODELS_KEY]);
            $statePersister->save($state);
            return true;
        }

        /**
         * Delete sample model from application state persister and from database
         * @param string $modelClassName
         * @param inf $modelId
         * @param MessageLogger $messageLogger
         * @return boolean
         */
        protected static function deleteSampleModelFromStatePersisterAndDatabase($modelClassName, $modelId, $messageLogger)
        {
            $statePersister = Yii::app()->getStatePersister();
            $state = $statePersister->load();
            $result = true;
            if (isset($state[self::AUTO_BUILD_SAMPLE_MODELS_KEY]))
            {
                $sampleModels = $state[self::AUTO_BUILD_SAMPLE_MODELS_KEY];
                if (!empty($sampleModels))
                {
                    foreach ($sampleModels as $key => $sampleModel)
                    {
                        if ($sampleModel['modelClassName'] == $modelClassName && $sampleModel['modelId'] == $modelId)
                        {
                            try
                            {
                                $model = $sampleModel['modelClassName']::getById($modelId);
                                if ($model)
                                {
                                    if ($model->delete())
                                    {
                                        unset($state[self::AUTO_BUILD_SAMPLE_MODELS_KEY][$key]);
                                        $messageLogger->addInfoMessage("Sample model {$sampleModel['modelClassName']}-> $modelId deleted.");
                                    }
                                    else
                                    {
                                        $messageLogger->addErrorMessage("Couldn't delete sample model {$sampleModel['modelClassName']}-> $modelId deleted.");
                                        $result = false;
                                    }
                                }
                            }
                            catch (NotFoundException $e)
                            {
                                // Do nothing, model is already deleted
                            }
                        }
                    }
                }
            }
            $statePersister->save($state);
            return $result;
        }

        /**
         * Set current state of auto build process in state persister.
         * This state is also used in BeginRequestBehavious, so if state is invalid,
         * we will exit application, and inform user about this case.
         * @param string $value
         */
        protected static function setAutoBuildStateInStatePersister($value)
        {
            $statePersister = Yii::app()->getStatePersister();
            $state = $statePersister->load();
            $state[self::AUTO_BUILD_STATE_KEY] = $value;
            $statePersister->save($state);
        }

        /**
         * Get auto build state from state persister
         * @return string | null
         */
        protected static function getAutoBuildStateFromStatePersister()
        {
            $statePersister = Yii::app()->getStatePersister();
            $state = $statePersister->load();
            if (isset($state[self::AUTO_BUILD_STATE_KEY]))
            {
                return $state[self::AUTO_BUILD_STATE_KEY];
            }
            else
            {
                return null;
            }
        }

        /**
         * Check if auto build process completed sucessfully
         * @return boolean
         */
        public static function isAutoBuildStateValid()
        {
            $autoBuildState = self::getAutoBuildStateFromStatePersister();
            if (!isset($autoBuildState) || $autoBuildState == 'valid')
            {
                return true;
            }
            else
            {
                return false;
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
            $model->setScenario('autoBuildDatabase');
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
                    case 'UserDefaultTimeZoneDefaultValueValidator':
                        $model->$memberName = 'UTC';
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
                    case 'RedBeanModelProbabilityValidator':
                    case 'UsernameLengthValidator':
                    case 'ValidateTimeZone':
                    case 'AtLeastOneContentAreaRequiredValidator':
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
                                $modelClassName = get_class($model);
                                do
                                {
                                    $s = self::getRandomString($minLength, $maxLength);
                                }
                                while ($unique && $modelClassName::getSubset(null, null, null, "$memberName = '$s'"));

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

        protected static function getRandomString($minLength, $maxLength)
        {
            if ($minLength == null)
            {
                $minLength = 1;
            }

            $s = chr(rand(ord('A'), ord('Z')));
            $length = min($minLength, $maxLength);
            while (strlen($s) < $length)
            {
                $s .= chr(rand(ord('a'), ord('z')));
            }
            return $s;
        }
    }
?>
