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
     * A view that displays the gamification leaderboard
     *
     */
    class LeaderboardView extends MetadataView
    {
        protected $controllerId;

        protected $moduleId;

        protected $leaderboardData;

        protected $activeActionElementType;

        protected $cssClasses = array('ListView');

        public function __construct($controllerId, $moduleId, $leaderboardData, $activeActionElementType)
        {
            assert('is_string($controllerId)');
            assert('is_string($moduleId)');
            assert('is_array($leaderboardData)');
            assert('is_string($activeActionElementType)');
            $this->controllerId            = $controllerId;
            $this->moduleId                = $moduleId;
            $this->leaderboardData         = $leaderboardData;
            $this->activeActionElementType = $activeActionElementType;
        }

        protected function renderContent()
        {
            $content  = '<div class="view-toolbar-container clearfix"><div class="view-toolbar">';
            $content .= $this->renderActionElementBar(false);
            $content .= '</div></div>';
            $content .= '<div class="cgrid-view">';
            $content .= $this->renderLeaderboardContent();
            $content .= '</div>';
            return $content;
        }

        protected function renderLeaderboardContent()
        {
            $content  = '<table class="items">';
            $content .= '<colgroup>';
            $content .= '<col style="width:10%" /><col style="width:80%" /><col style="width:10%" />';
            $content .= '</colgroup>';
            $content .= '<tbody>';
            $content .= '<tr><th>' . Zurmo::t('GamificationModule', 'Rank') . '</th>';
            $content .= '<th>' . Zurmo::t('GamificationModule', 'User') . '</th>';
            $content .= '<th>' . Zurmo::t('GamificationModule', 'Points') . '</th>';
            $content .= '</tr>';
            foreach ($this->leaderboardData as $userId => $leaderboardData)
            {
                assert('is_string($leaderboardData["rank"])');
                assert('is_string($leaderboardData["userLabel"])');
                assert('is_int($leaderboardData["points"])');

                $userUrl  = Yii::app()->createUrl('/users/default/details', array('id' => $userId));
                $user        = User::getById($userId);
                $avatarImage = $user->getAvatarImage(24);

                $content .= '<tr>';
                $content .= '<td><span class="ranking">' . $leaderboardData['rank'] . '</span></td>';
                $content .= '<td class="user-label">' .
                            ZurmoHtml::link($avatarImage . '<span>' . $leaderboardData['userLabel'] . '</span>', $userUrl) .
                            '</td>';
                $content .= '<td><span class="points">' . $leaderboardData['points'] . '</span></td>';
                $content .= '</tr>';
            }
            $content .= '</tbody>';
            $content .= '</table>';
            return $content;
        }

        public static function getDefaultMetadata()
        {
            $metadata = array(
                'global' => array(
                    'toolbar' => array(
                        'elements' => array(
                            array(
                                'type'            => 'LeaderboardWeeklyLink',
                                'htmlOptions'     => array( 'class' => 'icon-leaderboard-weekly' )
                            ),
                            array(
                                'type'            => 'LeaderboardMonthlyLink',
                                'htmlOptions'     => array( 'class' => 'icon-leaderboard-monthly' )
                            ),
                            array(
                                'type'            => 'LeaderboardOverallLink',
                                'htmlOptions'     => array( 'class' => 'icon-leaderboard-overall' )
                            ),
                        ),
                    ),
                ),
            );
            return $metadata;
        }

        public function isUniqueToAPage()
        {
            return true;
        }

        protected function resolveActionElementInformationDuringRender(& $elementInformation)
        {
            parent::resolveActionElementInformationDuringRender($elementInformation);
            if ($elementInformation['type'] == $this->activeActionElementType)
            {
                $elementInformation['htmlOptions']['class'] .= ' active';
            }
        }
    }
