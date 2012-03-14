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

    class ModulesMenuView extends DesignerMenuView
    {
        protected $controllerId;

        protected $moduleId;

        protected $module;

        public function __construct($controllerId, $moduleId, Module $module)
        {
            assert('is_string($controllerId)');
            assert('is_string($moduleId)');
            $this->controllerId           = $controllerId;
            $this->moduleId               = $moduleId;
            $this->module                 = $module;
        }

        protected function renderContent()
        {
            $content  = $this->renderTitleContent();
            $content .= '<table>';
            $content .= '<colgroup>';
            $content .= '<col style="width:100%" />';
            $content .= '</colgroup>';
            $content .= '<tbody>';

            $moduleMenuItems = $this->module->getDesignerMenuItems();
            $menuMetaData = self::getMetadata();
            foreach ($menuMetaData['moduleCategories'] as $categoryData)
            {
                if (ArrayUtil::getArrayValue($moduleMenuItems, $categoryData['showFlagName']))
                {
                    $route = $this->moduleId . '/' . $this->controllerId . '/' . $categoryData['action'] .'/';
                    $content .= '<tr>';
                    $content .= '<td>';
                    $content .= CHtml::link(
                        $categoryData['label'],
                        Yii::app()->createUrl($route,
                            array(
                                'moduleClassName' => get_class($this->module),
                            )
                        ));
                    $content .= '</td>';
                    $content .= '</tr>';
                }
            }
            $content .= '</tbody>';
            $content .= '</table>';
            return $content;
        }

        protected function renderTitleContent()
        {
            $module = $this->module;
            return '<h1>' . $module::getModuleLabelByTypeAndLanguage('Plural') . ': ' . Yii::t('Default', 'Menu') . '</h1>';
        }

        public function isUniqueToAPage()
        {
            return false;
        }
    }
?>