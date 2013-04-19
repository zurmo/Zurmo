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
     *  Helps with game logic exuected during a page request. As scores are added, the point information is
     * tabulated in the GamePointManager so it can then update persistent storage in a single request at the end
     * of the page request.
     */
    class GameHelper extends CApplicationComponent
    {
        /**
         * Is gamification enabled or not for the application. When using the command line application, this is set
         * to false for example.
         * @var boolean
         */
        public $enabled = true;

        /**
         * Turn off if you do not want modal notifications to be utilized.  Selenium testing for example needs this
         * off otherwise it will be difficult to execute functional tests correctly.
         * @var boolean
         */
        protected $_modalNotificationsEnabled;

        private static $pointTypesAndValuesByUserIdToAdd = array();

        /**
         * Determines whether scoring models on save should occur or be skipped.  Import or mass edit are examples
         * of when the scoring is muted as it would create unuseful scores.
         * @var boolean
         */
        protected $scoringModelsOnSaveIsMuted = false;
        protected $scoringModelsOnDeleteIsMuted = false;

        public function setModalNotificationsEnabled($value)
        {
            $this->_modalNotificationsEnabled = $value;
        }

        public function getModalNotificationsEnabled()
        {
            if (ZurmoConfigurationUtil::getByModuleName('ZurmoModule', 'gamificationModalNotificationsEnabled') !== null)
            {
                return ZurmoConfigurationUtil::getByModuleName('ZurmoModule', 'gamificationModalNotificationsEnabled');
            }
            else
            {
                if (isset($this->_modalNotificationsEnabled))
                {
                    return $this->_modalNotificationsEnabled;
                }
                else
                {
                    return true;
                }
            }
        }

        public function init()
        {
            $this->initCustom();
        }

        public function resetDeferredPointTypesAndValuesByUserIdToAdd()
        {
            self::$pointTypesAndValuesByUserIdToAdd = array();
        }

        public function getDeferredPointTypesAndValuesByUserIdToAdd()
        {
            return self::$pointTypesAndValuesByUserIdToAdd;
        }

        public function isScoringModelsOnSaveMuted()
        {
            return $this->scoringModelsOnSaveIsMuted;
        }

        public function muteScoringModelsOnSave()
        {
            $this->scoringModelsOnSaveIsMuted = true;
        }

        public function unmuteScoringModelsOnSave()
        {
            $this->scoringModelsOnSaveIsMuted = false;
        }

        public function muteScoringModelsOnDelete()
        {
            $this->scoringModelsOnDeleteIsMuted = true;
        }

        public function unmuteScoringModelsOnDelete()
        {
            $this->scoringModelsOnDeleteIsMuted = false;
        }

        /**
         * Override as needed to customize various aspects of gamification.  A few examples of things you can do here:
         * GeneralGameLevelRules::setLastLevel(100);
           GeneralGameLevelRules::setLevelPointMap($newLevelPointMap);
         */
        public function initCustom()
        {
        }

        /**
         * @param string $modelClassName
         */
        public function triggerSearchModelsEvent($modelClassName)
        {
            assert('is_string($modelClassName)');
            if (is_subclass_of($modelClassName, 'Item') && $modelClassName::getGamificationRulesType() != null)
            {
                $gamificationRulesType      = $modelClassName::getGamificationRulesType();
                $gamificationRulesClassName = $gamificationRulesType . 'Rules';
                $gamificationRulesClassName::scoreOnSearchModels($modelClassName);
            }
        }

        /**
         * @param string $modelClassName
         */
        public function triggerMassEditEvent($modelClassName)
        {
            assert('is_string($modelClassName)');
            if (is_subclass_of($modelClassName, 'Item') && $modelClassName::getGamificationRulesType() != null)
            {
                $gamificationRulesType      = $modelClassName::getGamificationRulesType();
                $gamificationRulesClassName = $gamificationRulesType . 'Rules';
                $gamificationRulesClassName::scoreOnMassEditModels($modelClassName);
            }
        }

        /**
         * @param string $modelClassName(mass delete)
         */
        public function triggerMassDeleteEvent($modelClassName)
        {
            assert('is_string($modelClassName)');
            if (is_subclass_of($modelClassName, 'Item') && $modelClassName::getGamificationRulesType() != null)
            {
                $gamificationRulesType      = $modelClassName::getGamificationRulesType();
                $gamificationRulesClassName = $gamificationRulesType . 'Rules';
                $gamificationRulesClassName::scoreOnMassDeleteModels($modelClassName);
            }
        }

        /**
         * @param string $modelClassName
         */
        public function triggerImportEvent($modelClassName)
        {
            assert('is_string($modelClassName)');
            if (is_subclass_of($modelClassName, 'Item') && $modelClassName::getGamificationRulesType() != null)
            {
                $gamificationRulesType      = $modelClassName::getGamificationRulesType();
                $gamificationRulesClassName = $gamificationRulesType . 'Rules';
                $gamificationRulesClassName::scoreOnImportModels($modelClassName);
            }
        }

        /**
         * Given a user, point type, and value, store the information in the @see $pointTypesAndValuesByUserIdToAdd
         * data array to be processed later at the end of the page request by @see processDeferredPoints
         * @param User $user
         * @param String $type
         * @param Integer $value
         */
        public static function addPointsByUserDeferred(User $user, $type, $value)
        {
            assert('$user->id > 0');
            assert('is_string($type)');
            assert('is_int($value)');
            if (!isset(self::$pointTypesAndValuesByUserIdToAdd[$user->id][$type]))
            {
                self::$pointTypesAndValuesByUserIdToAdd[$user->id][$type] = $value;
            }
            else
            {
                self::$pointTypesAndValuesByUserIdToAdd[$user->id][$type] =
                self::$pointTypesAndValuesByUserIdToAdd[$user->id][$type] + $value;
            }
        }

        /**
         * Process any points that have been added to @see $pointTypesAndValuesByUserIdToAdd throughout the page
         * request.
         */
        public function processDeferredPoints()
        {
            if (!$this->enabled)
            {
                return;
            }
            foreach (self::$pointTypesAndValuesByUserIdToAdd as $userId => $typeAndValues)
            {
                if ($typeAndValues != null)
                {
                    foreach ($typeAndValues as $type => $value)
                    {
                        $gamePoint      = GamePoint::
                                            resolveToGetByTypeAndPerson($type, User::getById($userId));
                        $gamePoint->addValue($value);
                        $saved          = $gamePoint->save();
                        if (!$saved)
                        {
                            throw new NotSupportedException();
                        }
                    }
                }
            }
            $this->resetDeferredPointTypesAndValuesByUserIdToAdd();
        }

        /**
         * Called at the end of the page request.  Processes anylevel changes for the current user.
         */
        public function resolveLevelChange()
        {
            if (!$this->enabled)
            {
                return;
            }
            $pointSumsIndexedByType = GamePoint::getSummationPointsDataByUserIndexedByLevelType(Yii::app()->user->userModel);
            $types                  = array(GameLevel::TYPE_SALES,
                                            GameLevel::TYPE_NEW_BUSINESS,
                                            GameLevel::TYPE_ACCOUNT_MANAGEMENT,
                                            GameLevel::TYPE_TIME_MANAGEMENT,
                                            GameLevel::TYPE_COMMUNICATION,
                                            GameLevel::TYPE_GENERAL);
            $gameLevelsByType       = GameLevel::resolvePersonAndAvailableTypes(Yii::app()->user->userModel, $types);
            foreach ($gameLevelsByType as $type => $gameLevel)
            {
                static::resolveLevelChangeByType($type, $gameLevel, $pointSumsIndexedByType);
            }
        }

        protected function resolveLevelChangeByType($levelType, GameLevel $currentGameLevel, $pointSumsIndexedByType)
        {
            assert('is_string($levelType) && $levelType != null');
            assert('is_array($pointSumsIndexedByType)');
            //If the user has not reached level one, the model has not been saved yet
            if ($currentGameLevel->id < 0)
            {
                $className           = $levelType . 'GameLevelRules';
                $nextLevelPointValue = $className::getMinimumPointsForLevel(1);
                $nextLevelValue      = 1;
            }
            else
            {
                $nextLevelPointValue = GameLevelUtil::getNextLevelPointValueByTypeAndCurrentLevel($levelType,
                                                                                                  $currentGameLevel);
                $nextLevelValue      = GameLevelUtil::getNextLevelByTypeAndCurrentLevel($levelType,
                                                                                        $currentGameLevel);
            }
            if ($nextLevelValue !== false &&
                static::resolveSummationValueByLevelTypeAndPointSums($levelType, $pointSumsIndexedByType) > $nextLevelPointValue)
            {
                $currentGameLevel->value = $nextLevelValue;
                $saved                   = $currentGameLevel->save();
                if (!$saved)
                {
                    throw new FailedToSaveModelException();
                }
                GameLevel::processBonusPointsOnLevelChange($currentGameLevel, Yii::app()->user->userModel);
                if ($levelType == GameLevel::TYPE_GENERAL && $this->modalNotificationsEnabled)
                {
                    static::processLevelChangeGameNotification($nextLevelValue);
                }
            }
        }

        protected static function resolveSummationValueByLevelTypeAndPointSums($levelType, $pointSumsIndexedByType)
        {
            assert('is_string($levelType) && $levelType != null');
            assert('is_array($pointSumsIndexedByType)');
            if ($levelType == GameLevel::TYPE_GENERAL)
            {
                return array_sum($pointSumsIndexedByType);
            }
            else
            {
                if (isset($pointSumsIndexedByType[$levelType]))
                {
                    return $pointSumsIndexedByType[$levelType];
                }
                else
                {
                    return 0;
                }
            }
        }

        protected static function processLevelChangeGameNotification($nextLevelValue)
        {
            assert('is_int($nextLevelValue)');
            $gameNotification           = new GameNotification();
            $gameNotification->user     = Yii::app()->user->userModel;
            $gameNotification->setLevelChangeByNextLevelValue($nextLevelValue);
            $saved = $gameNotification->save();
            if (!$saved)
            {
                throw new FailedToSaveModelException();
            }
        }

        /**
         * Called at the end of the page request.  Processes any new badges or badge grade changes for the current user.
         */
        public function resolveNewBadges()
        {
            if (!$this->enabled)
            {
                return;
            }
            $userBadgesByType     = GameBadge::getAllByPersonIndexedByType(Yii::app()->user->userModel);
            $userPointsByType     = GamePoint::getAllByPersonIndexedByType(Yii::app()->user->userModel);
            $userScoresByType     = GameScore::getAllByPersonIndexedByType(Yii::app()->user->userModel);
            $badgeRulesClassNames = GameBadgeRules::getBadgeRulesData();
            foreach ($badgeRulesClassNames as $badgeRulesClassName)
            {
                $newBadge    = false;
                $gradeChange = false;
                $badgeGrade  = $badgeRulesClassName::
                               badgeGradeUserShouldHaveByPointsAndScores($userPointsByType, $userScoresByType);
                if ($badgeGrade > 0)
                {
                    if (isset($userBadgesByType[$badgeRulesClassName::getType()]))
                    {
                        $gameBadge        = $userBadgesByType[$badgeRulesClassName::getType()];
                        if ($badgeGrade > $gameBadge->grade)
                        {
                            $gameBadge->grade = $badgeGrade;
                            $saved            = $gameBadge->save();
                            if (!$saved)
                            {
                                throw new NotSupportedException();
                            }
                            $gradeChange  = true;
                        }
                    }
                    else
                    {
                        $gameBadge         = new GameBadge();
                        $gameBadge->type   = $badgeRulesClassName::getType();
                        $gameBadge->person = Yii::app()->user->userModel;
                        $gameBadge->grade  = 1;
                        $saved             = $gameBadge->save();
                        if (!$saved)
                        {
                            throw new NotSupportedException();
                        }
                        $newBadge          = true;
                    }
                    if ($gradeChange || $newBadge)
                    {
                        if ($gradeChange)
                        {
                            $gradeChangeOrNewBadge = 'GradeChange';
                        }
                        else
                        {
                            $gradeChangeOrNewBadge = 'NewBadge';
                        }
                        GameBadge::processBonusPoints($gameBadge, Yii::app()->user->userModel, $gradeChangeOrNewBadge);

                        if ($this->modalNotificationsEnabled)
                        {
                            $gameNotification           = new GameNotification();
                            $gameNotification->user     = Yii::app()->user->userModel;
                            if ($newBadge)
                            {
                                $gameNotification->setNewBadgeByType($gameBadge->type);
                            }
                            elseif ($gradeChange)
                            {
                                $gameNotification->setBadgeGradeChangeByTypeAndNewGrade($gameBadge->type, $gameBadge->grade);
                            }
                            $saved = $gameNotification->save();
                            if (!$saved)
                            {
                                throw new FailedToSaveModelException();
                            }
                        }
                    }
                }
            }
        }
    }
?>