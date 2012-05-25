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
     * Class used to display the gamification statistics for a given user
     */
    class UserGamificationStatisticsForPortletView extends ConfigurableMetadataView  implements PortletViewInterface
    {
        /**
         * Portlet parameters passed in from the portlet.
         * @var array
         */
        protected $params;

        protected $controllerId;

        protected $moduleId;

        protected $model;

        protected $uniqueLayoutId;

        protected $viewData;

        /**
         * Some extra assertions are made to ensure this view is used in a way that it supports.
         */
        public function __construct($viewData, $params, $uniqueLayoutId)
        {
            assert('is_array($viewData) || $viewData == null');
            assert('isset($params["portletId"])');
            assert('isset($params["relationModuleId"])');
            assert('isset($params["relationModel"]) && $params["relationModel"] instanceof User');
            assert('isset($params["statisticsData"])');
            assert('is_string($uniqueLayoutId)');
            $this->moduleId       = 'users';
            $this->viewData       = $viewData;
            $this->params         = $params;
            $this->uniqueLayoutId = $uniqueLayoutId;
        }

        public static function getDefaultMetadata()
        {
            $metadata = array(
                'perUser' => array(
                    'title' => "eval:Yii::t('Default', 'Overall Statistics')",
                ),
            );
            return $metadata;
        }

        public function renderContent()
        {
            return $this->renderStatisticsContent();
        }

        protected function renderStatisticsContent()
        {
            $content  = '<table class="items">';
            $content .= '<colgroup>';
            $content .= '<col style="width:40%" /><col style="width:30%" /><col style="width:15%" /><col style="width:15%" />';
            $content .= '</colgroup>';
            $content .= '<tbody>';
            $content .= '<tr>';
            $content .= '<th></th>';
            $content .= '<th></th>';
            $content .= '<th>' . Yii::t('Default', 'Points') . '</th>';
            $content .= '<th>' . Yii::t('Default', 'Level') . '</th>';
            $content .= '</tr>';
            foreach ($this->params["statisticsData"] as $statisticItem)
            {
                assert('is_string($statisticItem["levelTypeLabel"])');
                assert('is_numeric($statisticItem["nextLevelPercentageComplete"]) ||
                        $statisticItem["nextLevelPercentageComplete"] === null');
                assert('is_int($statisticItem["points"])');
                assert('is_int($statisticItem["level"])');
                $content .= '<tr>';
                $content .= '<td>' . $statisticItem['levelTypeLabel'] . '</td>';
                if ($statisticItem['nextLevelPercentageComplete'] === null)
                {
                    $content .= '<td></td>';
                }
                else
                {
                    $content .= '<td class="hasPercentCounter"><span class="percentHolder"><span class="percentComplete z_' . $statisticItem['nextLevelPercentageComplete'] . '"><span class="percent">' . $statisticItem['nextLevelPercentageComplete'] . '%</span></span></span></td>';
                }
                $content .= '<td><span class="points">' . $statisticItem['points'] . '</span></td>';
                $content .= '<td>' . $statisticItem['level'] . '</td>';
                $content .= '</tr>';
            }
            $content .= '</tbody>';
            $content .= '</table>';
            return $content;
        }

        public static function canUserConfigure()
        {
            return false;
        }

        /**
         * What kind of PortletRules this view follows
         * @return PortletRulesType as string.
         */
        public static function getPortletRulesType()
        {
            return 'MixedForDetails';
        }

        /**
         * The view's module class name.
         */
        public static function getModuleClassName()
        {
            return 'GamificationModule';
        }
    }
?>