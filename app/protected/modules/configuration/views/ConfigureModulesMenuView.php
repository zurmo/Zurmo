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

    class ConfigureModulesMenuView extends MetadataView
    {
        protected function renderContent()
        {
            $content  = $this->renderTitleContent();
            $categoryData = $this->getCategoryData();
            $categoryLabels = $this->getCategoriesArray();
            foreach ($categoryData as $category => $categoryItems)
            {
                $content .= $this->renderMenu($categoryItems);
            }
            return $content;
        }

        protected function renderTitleContent()
        {
            return '<h1>' . Yii::t('Default', 'Administration') . '</h1>';
        }

        protected function getCategoryData()
        {
            $categories = array();
            $modules = Module::getModuleObjects();
            foreach ($modules as $module)
            {
                $moduleMenuItems = MenuUtil::getAccessibleConfigureMenuByCurrentUser(get_class($module));
                if ($module->isEnabled() && count($moduleMenuItems) > 0)
                {
                    foreach ($moduleMenuItems as $menuItem)
                    {
                        if (!empty($menuItem['category']))
                        {
                            assert('isset($menuItem["titleLabel"])');
                            assert('isset($menuItem["descriptionLabel"])');
                            assert('isset($menuItem["route"])');
                            $categories[$menuItem['category']][] = $menuItem;
                        }
                        else
                        {
                            throw new NotSupportedException();
                        }
                    }
                }
            }
            return $categories;
        }

        protected function renderMenu($items)
        {
            $content = '<ul class="configuration-list">';
            foreach ($items as $item)
            {
                $content .= '<li>';
                $content .= '<h4>'.$item['titleLabel'].'</h4>';
                $content .= ' - ' . $item['descriptionLabel'];
                $content .= CHtml::link(CHtml::tag('span', array(), Yii::t('Default', 'Configure') ),
                                        Yii::app()->createUrl($item['route']));
                $content .= '</li>';
            }
            $content .= '</ul>';
            return $content;
        }

        protected function getCategoriesArray()
        {
            return array(
                ZurmoModule::ADMINISTRATION_CATEGORY_GENERAL   => Yii::t('Default', 'General'),
            );
        }
    }
?>