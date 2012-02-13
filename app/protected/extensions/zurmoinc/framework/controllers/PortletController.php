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
     * Framework portlet controller.
     */
    abstract class PortletController extends Controller
    {
        /**
         * Save layout changes including:
         *  collapse/show
         *  position change
         *  removed portlets
         *
         */
        public function actionSaveLayout()
        {
            $portlets = Portlet::getByLayoutIdAndUserSortedById($_POST['portletLayoutConfiguration']['uniqueLayoutId'], Yii::app()->user->userModel->id);
            $portletsStillOnLayout = array();
            if (!empty($_POST['portletLayoutConfiguration']['portlets']))
            {
                foreach ($_POST['portletLayoutConfiguration']['portlets'] as $key => $portletPostData)
                {
                    $idParts = explode("_", $portletPostData['id']);
                    $portlets[$idParts[1]]->column    = $portletPostData['column']   + 1;
                    $portlets[$idParts[1]]->position  = $portletPostData['position'] + 1;
                    $portlets[$idParts[1]]->collapsed = BooleanUtil::boolVal($portletPostData['collapsed']);
                    $portlets[$idParts[1]]->save();
                    $portletsStillOnLayout[$idParts[1]] = $idParts[1];
                }
            }
            foreach ($portlets as $portletId => $portlet)
            {
                if (!isset($portletsStillOnLayout[$portletId]))
                {
                    $portlet->delete();
                }
            }
        }

        /**
         * Called using Ajax. Renders a modal popup
         * of the portlet's configuration view.
         * Also called on 'save' of the modal popup form
         * in order to validate form.
         */
        public function actionModalConfigEdit()
        {
            if (isset($_POST['ajax']) && $_POST['ajax'] === 'modal-edit-form')
            {
                $this->actionModalConfigValidate();
            }

            $portlet = Portlet::getById(intval($_GET['portletId']));
            $portlet->params = array(
                'modalConfigSaveAction' => 'modalConfigSave',
                'controllerId'          => $this->getId(),
                'moduleId'              => $this->getModule()->getId(),
                'uniquePortletPageId'   => $portlet->getUniquePortletPageId(),
            );
            $configurableView = $portlet->getView()->getConfigurationView();
            $view = new ModalView($this,
                $configurableView,
                'modalContainer',
                Yii::t('Default', 'Edit Portlet'));
            echo $view->render();
        }

        protected function actionModalConfigValidate()
        {
            $portlet = Portlet::getById(intval($_GET['portletId']));
            $configurableView = $portlet->getView()->getConfigurationView();
            $configurableView->validate();
            Yii::app()->end(0, false);
        }

        /**
         * Called using Ajax.
         */
        public function actionModalConfigSave($portletId, $uniqueLayoutId)
        {
            $portlet           = Portlet::getById(intval($portletId));
            $configurableView  = $portlet->getView()->getConfigurationView();
            $configurableView->setMetadataFromPost($_POST[$configurableView->getPostArrayName()]);
            $portlet->serializedViewData = serialize($configurableView->getViewMetadata());
            $portlet->save();
            $portlet->forget();
            $this->actionModalRefresh($portletId, $uniqueLayoutId, null);
        }

        /**
         * Refresh portlet contents within a dashboard or details relation view. In the case of details relation view
         * detect if the relationModelId is populated, in which case resolve and populate the relationModel.
         * Resets controller back to default.
         * @param array $portletParams - optional argument which allows you to override the standard parameters.
         */
        public function actionModalRefresh($portletId, $uniqueLayoutId, $redirectUrl, array $portletParams = array())
        {
            $portlet = Portlet::getById(intval($portletId));
            $portlet->params = array_merge(array(
                    'controllerId' => 'default',
                    'moduleId'     => $this->getModule()->getId(),
                    'redirectUrl'  => $redirectUrl), $portletParams);
            if (isset($portlet->params['relationModelId']) && $portlet->params['relationModelId'] != '')
            {
                assert('$portlet->params["relationModuleId"] != ""');
                $modelClassName = Yii::app()->findModule($portlet->params["relationModuleId"])->getPrimaryModelName();
                $portlet->params['relationModel'] = $modelClassName::getById((int)$portlet->params['relationModelId']);
            }
            $view = new AjaxPageView(new PortletRefreshView($portlet, $uniqueLayoutId, $this->getModule()->getId()));
            echo $view->render();
        }
    }
?>
