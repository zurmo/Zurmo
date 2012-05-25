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
     * Base class defining rules for gamification behavior.
     */
    class GamificationRules
    {
        /**
         * Score category used for when a model is created
         * @var string
         */
        const SCORE_CATEGORY_CREATE_MODEL          = 'CreateModel';

        /**
         * Score category used for when a model is updated
         * @var string
         */
        const SCORE_CATEGORY_UPDATE_MODEL          = 'UpdateModel';

        /**
         * Score category used for when a user logs into the system.
         * @var string
         */
        const SCORE_CATEGORY_LOGIN_USER            = 'LoginUser';

        /**
         * Score category used for when a user performs a mass edit in a module
         * @var string
         */
        const SCORE_CATEGORY_MASS_EDIT             = 'MassEdit';

        /**
         * Score category used for when a user searches in a module
         * @var string
         */
        const SCORE_CATEGORY_SEARCH                = 'Search';

        /**
         * Score category used for when a user imports into a module
         * @var string
         */
        const SCORE_CATEGORY_IMPORT                = 'Import';

        /**
         * Score category used for when a user performs a time sensitive action such as completing a task before the
         * due date.
         * @var string
         */
        const SCORE_CATEGORY_TIME_SENSITIVE_ACTION = 'TimeSensitiveAction';

        /**
         * Given a model class name attach scoring events to that class. Every model will then invoke the scoring event.
         * @param string $modelClassName
         */
        public function attachScoringEventsByModelClassName($modelClassName)
        {
            assert('is_string($modelClassName)');
            $modelClassName::model()->attachEventHandler('onAfterSave', array($this, 'scoreOnSaveModel'));
        }

        /**
         * Given a event, perform the onSave score logic for a model ($event->sender)
         * @param CEvent $event
         */
        public function scoreOnSaveModel(CEvent $event)
        {
            $model                   = $event->sender;
            assert('$model instanceof Item');
            if (Yii::app()->gameHelper->isScoringModelsOnSaveMuted())
            {
                return;
            }
            if ($model->getIsNewModel())
            {
                $scoreType           = static::resolveCreateScoreTypeByModel($model);
                $category            = static::SCORE_CATEGORY_CREATE_MODEL;
                $gameScore           = GameScore::resolveToGetByTypeAndPerson($scoreType, Yii::app()->user->userModel);
            }
            else
            {
                $scoreType           = static::resolveUpdateScoreTypeByModel($model);
                $category            = static::SCORE_CATEGORY_UPDATE_MODEL;
                $gameScore           = GameScore::resolveToGetByTypeAndPerson($scoreType, Yii::app()->user->userModel);
            }
            $gameScore->addValue();
            $saved = $gameScore->save();
            if (!$saved)
            {
                throw new FailedToSaveModelException();
            }
                GamePointUtil::addPointsByPointData(Yii::app()->user->userModel,
                               static::getPointTypeAndValueDataByCategory($category));
        }

        protected static function resolveCreateScoreTypeByModel($model)
        {
            return 'Create' . get_class($model);
        }

        protected static function resolveUpdateScoreTypeByModel($model)
        {
            return 'Update' . get_class($model);
        }

        /**
         * Given a score type and score category @return the corresponding point type and value as an array indexed
         * by the point type.
         * @param string $type
         * @param string $category
         */
        public static function getPointTypeAndValueDataByCategory($category)
        {
            assert('is_string($category)');
            $methodName = 'getPointTypesAndValuesFor' . $category;
            if (method_exists(get_called_class(), $methodName))
            {
                return static::$methodName();
            }
            else
            {
                throw new NotImplementedException();
            }
        }

        /**
         * @return Point type/value data for generically creating a model.
         */
        public static function getPointTypesAndValuesForCreateModel()
        {
            return array(GamePoint::TYPE_USER_ADOPTION => 10);
        }

        /**
         * @return Point type/value data for generically updating a model.
         */
        public static function getPointTypesAndValuesForUpdateModel()
        {
            return array(GamePoint::TYPE_USER_ADOPTION => 10);
        }

        /**
         * @return Point type/value data for a user logging in.
         */
        public static function getPointTypesAndValuesForLoginUser()
        {
            return array(GamePoint::TYPE_USER_ADOPTION => 10);
        }

        /**
         * @return Point type/value data for a user searching in a module.
         */
        public static function getPointTypesAndValuesForSearch()
        {
            return array(GamePoint::TYPE_USER_ADOPTION => 5);
        }

        /**
         * @return Point type/value data for a user performing a mass update in a module.
         */
        public static function getPointTypesAndValuesForMassEdit()
        {
            return array(GamePoint::TYPE_USER_ADOPTION => 15);
        }

        /**
         * @return Point type/value data for a user importing into a module
         */
        public static function getPointTypesAndValuesForImport()
        {
            return array(GamePoint::TYPE_USER_ADOPTION => 25);
        }

        /**
         * @return Point type/value data for a user performing a time-sensitive action
         */
        public static function getPointTypesAndValuesForTimeSensitiveAction()
        {
            return array(GamePoint::TYPE_USER_ADOPTION => 10);
        }

        /**
         * @param string $modelClassName
         */
        public static function scoreOnSearchModels($modelClassName)
        {
            assert('is_string($modelClassName)');
            $scoreType           = 'Search' . $modelClassName;
            $category            = static::SCORE_CATEGORY_SEARCH;
            $gameScore           = GameScore::resolveToGetByTypeAndPerson($scoreType, Yii::app()->user->userModel);
            $gameScore->addValue();
            $saved               = $gameScore->save();
            if (!$saved)
            {
                throw new FailedToSaveModelException();
            }
            GamePointUtil::addPointsByPointData(Yii::app()->user->userModel,
                           static::getPointTypeAndValueDataByCategory($category));
        }

        /**
         * @param string $modelClassName
         */
        public static function scoreOnMassEditModels($modelClassName)
        {
            assert('is_string($modelClassName)');
            $scoreType           = 'MassEdit' . $modelClassName;
            $category            = static::SCORE_CATEGORY_MASS_EDIT;
            $gameScore           = GameScore::resolveToGetByTypeAndPerson($scoreType, Yii::app()->user->userModel);
            $gameScore->addValue();
            $saved               = $gameScore->save();
            if (!$saved)
            {
                throw new FailedToSaveModelException();
            }
            GamePointUtil::addPointsByPointData(Yii::app()->user->userModel,
                           static::getPointTypeAndValueDataByCategory($category));
        }

        /**
         * @param string $modelClassName
         */
        public static function scoreOnImportModels($modelClassName)
        {
            assert('is_string($modelClassName)');
            $scoreType           = 'Import' . $modelClassName;
            $category            = static::SCORE_CATEGORY_IMPORT;
            $gameScore           = GameScore::resolveToGetByTypeAndPerson($scoreType, Yii::app()->user->userModel);
            $gameScore->addValue();
            $saved               = $gameScore->save();
            if (!$saved)
            {
                throw new FailedToSaveModelException();
            }
            GamePointUtil::addPointsByPointData(Yii::app()->user->userModel,
                           static::getPointTypeAndValueDataByCategory($category));
        }
    }
?>