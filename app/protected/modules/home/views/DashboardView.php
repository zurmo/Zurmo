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
     * The base View for a dashboard view
     */
    abstract class DashboardView extends PortletFrameView
    {
        protected $model;
        protected $isDefaultDashboard;

        public function __construct($controllerId, $moduleId, $uniqueLayoutId, $model, $params)
        {
            $this->controllerId        = $controllerId;
            $this->moduleId            = $moduleId;
            $this->uniqueLayoutId      = $uniqueLayoutId;
            $this->model               = $model;
            $this->modelId             = $model->id;
            $this->layoutType          = $model->layoutType;
            $this->isDefaultDashboard  = $model->isDefault;
            $this->params              = $params;
        }

        /**
         * Override to allow for making a default set of portlets
         * via metadata optional.
         *
         */
        protected function getPortlets($uniqueLayoutId, $metadata)
        {
            assert('is_string($uniqueLayoutId)');
            assert('is_array($metadata)');
            $portlets = Portlet::getByLayoutIdAndUserSortedByColumnIdAndPosition($uniqueLayoutId, Yii::app()->user->userModel->id, $this->params);
            if (empty($portlets) && $this->isDefaultDashboard)
            {
                $portlets = Portlet::makePortletsUsingMetadataSortedByColumnIdAndPosition($uniqueLayoutId, $metadata, Yii::app()->user->userModel, $this->params);
                Portlet::savePortlets($portlets);
            }
            return PortletsSecurityUtil::resolvePortletsForCurrentUser($portlets);
        }

        protected function renderContent()
        {
            $actionElementContent = $this->renderActionElementBar(false);
            if ($actionElementContent != null)
            {
                $content  = '<div class="view-toolbar-container clearfix"><div class="view-toolbar">';
                $content .= $actionElementContent;
                $content .= '</div></div>';
            }
            $this->portlets = $this->getPortlets($this->uniqueLayoutId, self::getMetadata());
            $content .= $this->renderPortlets($this->uniqueLayoutId);
            return $content;
        }

        /**
         * Render a toolbar above the form layout. This includes
         * a link to edit the dashboard as well as a link to add
         * portlets to the dashboard
         * @return A string containing the element's content.
          */
        protected function renderActionElementBar($renderedInForm)
        {
            $content = parent::renderActionElementBar($renderedInForm);

            $deleteDashboardLinkActionElement  = new DeleteDashboardLinkActionElement(
                $this->controllerId,
                $this->moduleId,
                $this->modelId,
                array('htmlOptions' => array('confirm' => Yii::t('Default', 'Are you sure want to delete this dashboard?')))
            );
            if (!ActionSecurityUtil::canCurrentUserPerformAction($deleteDashboardLinkActionElement->getActionType(), $this->model))
            {
                return $content;
            }
            if (!$this->isDefaultDashboard)
            {
                $content .= '&#160;|&#160;' . $deleteDashboardLinkActionElement->render();
            }
            return $content;
        }
    }
?>
