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

    class UserDetailsAndRelationsView extends DetailsAndRelationsView
    {
        public function isUniqueToAPage()
        {
            return true;
        }

        public static function getDefaultMetadata()
        {
            $metadata = array(
                'global' => array(
                    'leftTopView' => array(
                        'viewClassName' => 'UserDetailsView',
                    ),
                    'leftBottomView' => array(
                        'showAsTabbed' => false,
                        'columns' => array(
                            array(
                                'rows' => array(
                                    array(
                                        'type' => 'UserLatestActivtiesForPortlet'
                                    )
                                )
                            )
                        )
                    ),
                    'rightTopView' => array(
                        'columns' => array(
                            array(
                                'rows' => array(
                                    array(
                                        'type' => 'UserLeaderboardRankingForPortlet'
                                    ),
                                    array(
                                        'type' => 'UserGamificationStatisticsForPortlet'
                                    ),
                                    array(
                                        'type' => 'UserBadgesForPortlet'
                                    ),
                                )
                            )
                        )
                    )
                )
            );
            return $metadata;
        }

        protected function renderLeftAndRightGridViewContent($leftTopView, $leftBottomView, $rightTopView, $renderRightSide)
        {
            assert('$leftTopView instanceof View');
            assert('$leftBottomView instanceof View');
            assert('$rightTopView instanceof View || $rightTopView == null');
            assert('is_bool($renderRightSide)');
            $actionView = new ActionBarForUserEditAndDetailsView ($this->controllerId, $this->moduleId,
                                                                  $this->params['relationModel'], 'DetailsLink');
            $content  = $actionView->render();
            $leftVerticalGridView  = new GridView(2, 1);
            $leftVerticalGridView->setView($leftTopView, 0, 0);
            $leftVerticalGridView->setView($leftBottomView, 1, 0);
            $content .= $leftVerticalGridView->render();
            if ($renderRightSide)
            {
                $rightVerticalGridView  = new GridView(1, 1);
                $rightVerticalGridView->setView($rightTopView, 0, 0);
                $content .= $rightVerticalGridView->render();
            }
            return $content;
        }
    }
?>
