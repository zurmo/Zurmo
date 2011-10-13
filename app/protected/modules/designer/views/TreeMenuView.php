<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2011 Zurmo Inc.
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
     * View that renders a menu in the form of a
     * tree widget.
     */
    class TreeMenuView extends DesignerMenuView
    {
        protected $controllerId;
        protected $moduleId;
        protected $activeNodeModuleClassName;

        public function __construct($controllerId, $moduleId, $activeNodeModuleClassName)
        {
            $this->controllerId              = $controllerId;
            $this->moduleId                  = $moduleId;
            $this->activeNodeModuleClassName = $activeNodeModuleClassName;
        }

        protected function renderContent()
        {
            $dataTree = array();
            $modules = Module::getModuleObjects();
            $parentNode = array(
                'text' => $this->makeTreeMenuNodeAjaxLink(
                    Yii::t('Default', 'Modules'),
                    'index',
                    null
                ),
                'expanded' => true
            );
            $moduleNodes = array();
            foreach ($modules as $module)
            {
                $moduleTreeMenuItems = $module->getDesignerMenuItems();
                if ($module->isEnabled() &&
                    !empty($moduleTreeMenuItems))
                {
                    if ($this->activeNodeModuleClassName == get_class($module))
                    {
                        $isNodeExpanded = true;
                    }
                    else
                    {
                        $isNodeExpanded = false;
                    }
                    $node = array(
                        'text' => $this->makeTreeMenuNodeAjaxLink(
                            $module::getModuleLabelByTypeAndLanguage('Plural'),
                            'modulesMenu',
                            get_class($module)
                        ),
                        'expanded' => $isNodeExpanded,
                    );
                    $treeMenuMetaData = self::getMetadata();
                    foreach ($treeMenuMetaData['moduleCategories'] as $categoryData)
                    {
                        if (ArrayUtil::getArrayValue($moduleTreeMenuItems, $categoryData['showFlagName']))
                        {
                            $node['children'][] = array(
                                'text' => $this->makeTreeMenuNodeAjaxLink(
                                    $categoryData['label'],
                                    $categoryData['action'],
                                    get_class($module)
                                ),
                                'expanded' => false,
                            );
                        }
                    }
                    $moduleNodes[] = $node;
                }
            }
            $parentNode['children'] = $moduleNodes;
            $dataTree[] = $parentNode;
            $cClipWidget = new CClipWidget();
            $cClipWidget->beginClip("TreeView");
            $cClipWidget->widget('CTreeView', array(
                'data' => $dataTree,
                'animated'    => 'fast',
                'collapsed'   => true,
                'htmlOptions' => array(
                    'class' => 'treeview-gray',
                ),
            ));
            $cClipWidget->endClip();
            return $cClipWidget->getController()->clips['TreeView'];
        }

        protected function makeTreeMenuNodeAjaxLink($label, $action, $moduleClassName)
        {
            return CHtml::Link($label,
                Yii::app()->createUrl(
                    $this->moduleId . '/' . $this->controllerId . '/' . $action . '/',
                    array(
                        'moduleClassName' => $moduleClassName
                    )
                )
            );
        }
    }
?>
