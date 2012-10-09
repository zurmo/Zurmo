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
     * Defines specific rules for mission gamification.
     */
    class MissionGamificationRules extends GamificationRules
    {
        protected $scoreOnUpdate = false;

        /**
         * @var string
         */
        const SCORE_TYPE_TAKE_MISSION          = 'TakeMission';

        /**
         * @var string
         */
        const SCORE_TYPE_COMPLETE_MISSION      = 'CompleteMission';

        /**
         * @var string
         */
        const SCORE_TYPE_ACCEPTED_MISSION      = 'AcceptedMission';

        /**
         * @var string
         */
        const SCORE_CATEGORY_TAKE_MISSION      = 'TakeMission';

        /**
         * @var string
         */
        const SCORE_CATEGORY_COMPLETE_MISSION  = 'CompleteMission';

        /**
         * @var string
         */
        const SCORE_CATEGORY_ACCEPTED_MISSION  = 'AcceptedMission';

        /**
         * (non-PHPdoc)
         * @see GamificationRules::scoreOnSaveModel()
         */
        public function scoreOnSaveModel(CEvent $event)
        {
            parent::scoreOnSaveModel($event);
            //Is the Mission being taken by a user, when previously available.
            if (array_key_exists('status', $event->sender->originalAttributeValues) &&
                $event->sender->originalAttributeValues['status'] == Mission::STATUS_AVAILABLE &&
                $event->sender->status == Mission::STATUS_TAKEN)
            {
                $scoreType = static::SCORE_TYPE_TAKE_MISSION;
                $category  = static::SCORE_CATEGORY_TAKE_MISSION;
                $gameScore = GameScore::resolveToGetByTypeAndPerson($scoreType, $event->sender->takenByUser);
                $gameScore->addValue();
                $saved = $gameScore->save();
                if (!$saved)
                {
                    throw new FailedToSaveModelException();
                }
                GamePointUtil::addPointsByPointData(Yii::app()->user->userModel,
                               static::getPointTypeAndValueDataByCategory($category));
            }
            //If the mission is completed and it was previously taken.
            elseif (array_key_exists('status', $event->sender->originalAttributeValues) &&
                $event->sender->originalAttributeValues['status'] == Mission::STATUS_TAKEN &&
                $event->sender->status == Mission::STATUS_COMPLETED)
            {
                $scoreType = static::SCORE_TYPE_COMPLETE_MISSION;
                $category  = static::SCORE_CATEGORY_COMPLETE_MISSION;
                $gameScore = GameScore::resolveToGetByTypeAndPerson($scoreType, $event->sender->takenByUser);
                $gameScore->addValue();
                $saved = $gameScore->save();
                if (!$saved)
                {
                    throw new FailedToSaveModelException();
                }
                GamePointUtil::addPointsByPointData(Yii::app()->user->userModel,
                               static::getPointTypeAndValueDataByCategory($category));
            }
            //If the mission is accepted and previously completed
            elseif (array_key_exists('status', $event->sender->originalAttributeValues) &&
                $event->sender->originalAttributeValues['status'] == Mission::STATUS_COMPLETED &&
                $event->sender->status == Mission::STATUS_ACCEPTED)
            {
                $scoreType = static::SCORE_TYPE_ACCEPTED_MISSION;
                $category  = static::SCORE_CATEGORY_ACCEPTED_MISSION;
                $gameScore = GameScore::resolveToGetByTypeAndPerson($scoreType, $event->sender->takenByUser);
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

        public static function getPointTypesAndValuesForCreateModel()
        {
            return array(GamePoint::TYPE_COMMUNICATION => 20);
        }

        /**
         * @return Point type/value data for a user taking a mission
         */
        public static function getPointTypesAndValuesForTakeMission()
        {
            return array(GamePoint::TYPE_COMMUNICATION => 10);
        }

        /**
         * @return Point type/value data for a user completing a mission
         */
        public static function getPointTypesAndValuesForCompleteMission()
        {
            return array(GamePoint::TYPE_COMMUNICATION => 10);
        }

        /**
         * @return Point type/value data for a user having a mission accepted
         */
        public static function getPointTypesAndValuesForAcceptedMission()
        {
            return array(GamePoint::TYPE_COMMUNICATION => 40);
        }
    }
?>