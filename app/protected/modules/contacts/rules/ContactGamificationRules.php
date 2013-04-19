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
     * Class defining rules for Contact gamification behavior.
     */
    class ContactGamificationRules extends GamificationRules
    {
        const SCORE_CATEGORY_CREATE_LEAD          = 'CreateLead';

        const SCORE_CATEGORY_UPDATE_LEAD          = 'UpdateLead';

        const SCORE_CATEGORY_CONVERT_LEAD         = 'ConvertLead';

        const SCORE_TYPE_CREATE_LEAD              = 'CreateLead';

        const SCORE_TYPE_UPDATE_LEAD              = 'UpdateLead';

        const SCORE_TYPE_CONVERT_LEAD             = 'ConvertLead';

        public function scoreOnSaveModel(CEvent $event)
        {
            if (Yii::app()->gameHelper->isScoringModelsOnSaveMuted())
            {
                return;
            }
            if (!LeadsUtil::isStateALead($event->sender->state) &&
                array_key_exists('state', $event->sender->originalAttributeValues) &&
                $event->sender->originalAttributeValues['state'][1] > 0 &&
                LeadsUtil::isStateALeadByStateName($event->sender->originalAttributeValues['state'][2]))
            {
                $this->scoreOnSaveWhereLeadIsConverted($event);
            }
            elseif (LeadsUtil::isStateALead($event->sender->state))
            {
                $this->scoreOnSaveWhereStateIsLead($event);
            }
            else
            {
                parent::scoreOnSaveModel($event);
            }
        }

        protected function scoreOnSaveWhereLeadIsConverted(CEvent $event)
        {
            $scoreType = static::SCORE_TYPE_CONVERT_LEAD;
            $category  = static::SCORE_CATEGORY_CONVERT_LEAD;
            $gameScore = GameScore::resolveToGetByTypeAndPerson($scoreType, Yii::app()->user->userModel);
            $gameScore->addValue();
            $saved = $gameScore->save();
            if (!$saved)
            {
                throw new FailedToSaveModelException();
            }
            GamePointUtil::addPointsByPointData(Yii::app()->user->userModel,
                           static::getPointTypeAndValueDataByCategory($category));
        }

        protected function scoreOnSaveWhereStateIsLead(CEvent $event)
        {
            $model                   = $event->sender;
            assert('$model instanceof Item');
            if ($model->getIsNewModel())
            {
                $scoreType           = static::SCORE_TYPE_CREATE_LEAD;
                $category            = static::SCORE_CATEGORY_CREATE_LEAD;
                $gameScore           = GameScore::resolveToGetByTypeAndPerson($scoreType, Yii::app()->user->userModel);
            }
            else
            {
                $scoreType           = static::SCORE_TYPE_UPDATE_LEAD;
                $category            = static::SCORE_CATEGORY_UPDATE_LEAD;
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

        public static function getPointTypesAndValuesForCreateModel()
        {
            return array(GamePoint::TYPE_ACCOUNT_MANAGEMENT => 10);
        }

        public static function getPointTypesAndValuesForUpdateModel()
        {
            return array(GamePoint::TYPE_ACCOUNT_MANAGEMENT => 10);
        }

        public static function getPointTypesAndValuesForConvertLead()
        {
            return array(GamePoint::TYPE_NEW_BUSINESS => 25);
        }

        public static function getPointTypesAndValuesForCreateLead()
        {
            return array(GamePoint::TYPE_NEW_BUSINESS => 10);
        }

        public static function getPointTypesAndValuesForUpdateLead()
        {
            return array(GamePoint::TYPE_NEW_BUSINESS => 10);
        }
    }
?>