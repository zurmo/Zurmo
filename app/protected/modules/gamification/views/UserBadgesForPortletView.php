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
     * Class used to display the badges a given user has.
     */
    class UserBadgesForPortletView extends ConfigurableMetadataView  implements PortletViewInterface
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
            assert('isset($params["badgeData"])');
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
                    'title' => "eval:Yii::t('Default', 'Achievements')",
                ),
            );
            return $metadata;
        }

        public function getTitle()
        {
            $title  = Yii::t('Default', 'Achievements');
            return $title;
        }

        public function renderContent()
        {
            return $this->renderBadgeContent();
        }

        protected function renderBadgeContent()
        {
            $content = null;
            if (count($this->params['badgeData']) > 0)
            {
                $rowBadgeCount = 0;
                $content .= '<div>';
                foreach ($this->params['badgeData'] as $badge)
                {
                    if ($rowBadgeCount == 4)
                    {
                        $content .= '</div><div>';
                        $rowBadgeCount = 0;
                    }
                    //$badgeClassName = $badge->type . 'GameBadgeRules';
                    //$content .= '<div>' . $badge->type . $badgeClassName::getDisplayName() . '-' . $badge->grade . '</div>';
                    $content .= '<div class="badge ' . $badge->type . '"><div class="gloss"></div>' .
                                '<strong class="badge-icon"></strong><span class="badge-grade">' . $badge->grade . '</span></div>';
                    $rowBadgeCount++;
                }
                $content .= '</div>';
            }
            else
            {
                $content .= Yii::t('Default', 'No achievements earned.');
            }
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