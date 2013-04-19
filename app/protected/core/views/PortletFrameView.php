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
     * The base View for a portletframe view
     */
    abstract class PortletFrameView extends MetadataView
    {
        protected $portlets;
        protected $uniqueLayoutId;
        protected $params;
        protected $layoutType;

        public function __construct($controllerId, $moduleId, $modelId, $params)
        {
            $this->controllerId                   = $controllerId;
            $this->moduleId                       = $moduleId;
            $this->modelId                        = $modelId;
            $this->uniqueLayoutId                 = get_class($this);
            $this->params                         = $params;
        }

        protected function renderContent()
        {
            $this->portlets = $this->getPortlets($this->uniqueLayoutId, self::getMetadata());
            $this->renderPortlets($this->uniqueLayoutId);
        }

        protected function getPortlets($uniqueLayoutId, $metadata)
        {
            assert('is_string($uniqueLayoutId)');
            assert('is_array($metadata)');
            $portlets = Portlet::getByLayoutIdAndUserSortedByColumnIdAndPosition($uniqueLayoutId, Yii::app()->user->userModel->id, $this->params);
            if (empty($portlets))
            {
                $portlets = Portlet::makePortletsUsingMetadataSortedByColumnIdAndPosition($uniqueLayoutId, $metadata, Yii::app()->user->userModel, $this->params);
                Portlet::savePortlets($portlets);
            }
            return $portlets;
        }

        protected function renderPortlets($uniqueLayoutId, $portletsAreCollapsible = true, $portletsAreMovable = true)
        {
            assert('is_string($uniqueLayoutId)');
            assert('is_bool($portletsAreCollapsible)');
            assert('is_bool($portletsAreMovable)');
            $juiPortletsWidgetItems = array();
            foreach ($this->portlets as $column => $columnPortlets)
            {
                foreach ($columnPortlets as $position => $portlet)
                {
                    $juiPortletsWidgetItems[$column][$position] = array(
                        'id'          => $portlet->id,
                        'uniqueId'    => $portlet->getUniquePortletPageId(),
                        'title'       => $portlet->getTitle(),
                        'content'     => $portlet->renderContent(),
                        'editable'    => $portlet->isEditable(),
                        'collapsed'   => $portlet->collapsed,
                        'removable'   => $this->arePortletsRemovable(),
                        'uniqueClass' => $this->resolveUniqueClass($portlet)
                    );
                }
            }
            $cClipWidget = new CClipWidget();
            $cClipWidget->beginClip("JuiPortlets");
            $cClipWidget->widget('application.core.widgets.JuiPortlets', array(
                'uniqueLayoutId' => $uniqueLayoutId,
                'moduleId'       => $this->moduleId,
                'saveUrl'        => Yii::app()->createUrl($this->moduleId . '/defaultPortlet/SaveLayout'),
                'layoutType'     => $this->getLayoutType(),
                'items'          => $juiPortletsWidgetItems,
                'collapsible'    => $portletsAreCollapsible,
                'movable'        => $portletsAreMovable,
            ));
            $cClipWidget->endClip();
            return $cClipWidget->getController()->clips['JuiPortlets'];
        }

        protected function resolveUniqueClass(Portlet $portlet)
        {
            $content  = null;
            $content .= get_class($portlet->getView());
            foreach ($portlet->getCssClasses() as $cssClass)
            {
                $content .= ' ' . $cssClass;
            }
            return $content;
        }

        protected function getLayoutType()
        {
            return $this->layoutType;
        }

        protected function arePortletsRemovable()
        {
            return true;
        }
    }
?>
