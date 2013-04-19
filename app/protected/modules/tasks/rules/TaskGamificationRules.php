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
     * Defines specific rules for task gamification.
     */
    class TaskGamificationRules extends GamificationRules
    {
        const SCORE_TYPE_COMPLETED_TASK_ON_TIME = 'CompletedTaskOnTime';

        public function attachScoringEventsByModelClassName($modelClassName)
        {
            assert('is_string($modelClassName)');
            parent::attachScoringEventsByModelClassName($modelClassName);
            $modelClassName::model()->attachEventHandler('onAfterSave', array($this, 'scoreCompletedOnTime'));
        }

        /**
         * @param CEvent $event
         */
        public function scoreCompletedOnTime(CEvent $event)
        {
            $model                      = $event->sender;
            assert('$model instanceof Item');
            if (!$model->getIsNewModel() && array_key_exists('completed', $model->originalAttributeValues) &&
                $model->completed == true && $model->dueDateTime != null)
            {
                $completedTimestamp = DateTimeUtil::convertDbFormatDateTimeToTimestamp($model->completedDateTime);
                $dueTimestamp       = DateTimeUtil::convertDbFormatDateTimeToTimestamp($model->dueDateTime);

                if ($completedTimestamp <= $dueTimestamp)
                {
                    $scoreType           = static::SCORE_TYPE_COMPLETED_TASK_ON_TIME;
                    $category            = static::SCORE_CATEGORY_TIME_SENSITIVE_ACTION;
                    $gameScore           = GameScore::resolveToGetByTypeAndPerson($scoreType, Yii::app()->user->userModel);
                    $gameScore->addValue();
                    $saved = $gameScore->save();
                    if (!$saved)
                    {
                        throw new FailedToSaveModelException();
                    }
                    GamePointUtil::addPointsByPointData(Yii::app()->user->userModel,
                                   static::getPointTypeAndValueDataByCategory($category));
                }
            }
        }

        /**
         * @see parent::getPointTypesAndValuesForCreateModel()
         */
        public static function getPointTypesAndValuesForCreateModel()
        {
            return array(GamePoint::TYPE_TIME_MANAGEMENT => 10);
        }

        /**
         * @see parent::getPointTypesAndValuesForUpdateModel()
         */
        public static function getPointTypesAndValuesForUpdateModel()
        {
            return array(GamePoint::TYPE_TIME_MANAGEMENT => 10);
        }

        /**
         * @see parent::getPointTypesAndValuesForTimeSensitiveAction()
         */
        public static function getPointTypesAndValuesForTimeSensitiveAction()
        {
            return array(GamePoint::TYPE_TIME_MANAGEMENT => 10);
        }
    }
?>