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
     * Adapter to take a GameNotification object and render content for the modal notification dialog box.
     */
    class GameNotificationToModalContentAdapter extends GameNotificationToContentAdapter
    {
        /**
         * @return string content of the notification message content to display.
         */
        public function getMessageContent()
        {
            $data = $this->getAndValidateUnserializedData();
            if ($data['type'] == GameNotification::TYPE_LEVEL_CHANGE)
            {
                $content  = '<h2>' . Zurmo::t('GamificationModule', 'Congratulations!') . '</h2>';
                $content .= '<h3>' . Zurmo::t('GamificationModule', 'You have reached level {nextLevel}',
                                            array('{nextLevel}' => $data['levelValue'])) . '</h3>';
                return $content;
            }
            elseif ($data['type'] == GameNotification::TYPE_NEW_BADGE)
            {
                $gameBadgeRulesClassName = $data['badgeType'] . 'GameBadgeRules';
                $value                   = $gameBadgeRulesClassName::getItemCountByGrade(1);
                $content   = '<h2>' . Zurmo::t('GamificationModule', 'New Badge') . '</h2>';
                $content  .= '<h3>' . $gameBadgeRulesClassName::getPassiveDisplayLabel($value) . '</h3>';
                return $content;
            }
            elseif ($data['type'] == GameNotification::TYPE_BADGE_GRADE_CHANGE)
            {
                $gameBadgeRulesClassName = $data['badgeType'] . 'GameBadgeRules';
                $value                   = $gameBadgeRulesClassName::getItemCountByGrade((int)$data['grade']);
                $content   = '<h2>' . Zurmo::t('GamificationModule', 'New Badge') . '</h2>';
                $content  .= '<h3>' . $gameBadgeRulesClassName::getPassiveDisplayLabel($value) . '</h3>';
                return $content;
            }
            else
            {
                throw new NotSupportedException();
            }
        }
    }
?>