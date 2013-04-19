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

    class ConfigureModulesMenuView extends MetadataView
    {
        private $linkText;

        protected function renderContent()
        {
            $content      = $this->renderTitleContent();
            $categoryData = $this->getCategoryData();
            foreach ($categoryData as $category => $categoryItems)
            {
                $categoryItems   = static::sortCategoryItems($categoryItems);
                $content        .= $this->renderMenu($categoryItems);
            }
            return $content;
        }

        protected static function sortCategoryItems($categoryItems)
        {
            return ArrayUtil::subValueSort($categoryItems, 'titleLabel', 'asort');
        }

        public function getTitle()
        {
            return Zurmo::t('ConfigurationModule', 'Administration');
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
                $content .= '<h4>' . $item['titleLabel'] . '</h4>';
                $content .= ' - ' . $item['descriptionLabel'];
                $content .= ZurmoHtml::link(ZurmoHtml::wrapLabel($this->getLinkText()),
                                        Yii::app()->createUrl($item['route']));
                $content .= '</li>';
            }
            $content .= '</ul>';
            return $content;
        }

        protected function getCategoriesArray()
        {
            return array(
                ZurmoModule::ADMINISTRATION_CATEGORY_GENERAL   => Zurmo::t('ConfigurationModule', 'General'),
            );
        }

        protected function setLinkText($text)
        {
            $this->linkText = $text;
        }

        protected function getLinkText()
        {
            if (isset($this->linkText))
            {
                return $this->linkText;
            }
            else
            {
                return Zurmo::t('ConfigurationModule', 'Configure');
            }
        }
    }
?>