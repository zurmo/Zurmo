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

    abstract class ZurmoPortletController extends PortletController
    {
        const RIGHTS_FILTER_PATH = 'application.modules.zurmo.controllers.filters.RightsControllerFilter';

        public function filters()
        {
            $moduleClassName = get_class($this->getModule());
            $filters = array();
            if (is_subclass_of($moduleClassName, 'SecurableModule'))
            {
                $filters[] = array(
                        ZurmoBaseController::RIGHTS_FILTER_PATH,
                        'moduleClassName' => $moduleClassName,
                        'rightName' => $moduleClassName::getAccessRight(),
                );
            }
            return $filters;
        }

        public function actionDetails($id)
        {
            $id              = intval($id);
            $modelName       = $this->getModule()->getPrimaryModelName();
            $model           = $modelName::getById($id);
            ControllerSecurityUtil::resolveAccessCanCurrentUserReadModel($model, true);
            $portlet         = Portlet::getById(intval($_GET['portletId']));

            $portlet->params = array(
                    'controllerId' => 'default',
                    'relationModuleId' => $this->getModule()->getId(),
                    'relationModel'    => $model,
                    'redirectUrl'      => Yii::app()->request->getRequestUri(),
            );
            $portletView = $portlet->getView();
            if (!RightsUtil::canUserAccessModule($portletView::getModuleClassName(),Yii::app()->user->userModel))
            {
                $messageView = new AccessFailureView();
                $view        = new AccessFailurePageView($messageView);
                echo $view->render();
                Yii::app()->end(0, false);
            }
            $view            = new AjaxPageView($portletView);
            echo $view->render();
        }

        /**
         * In a detail view, after you hit select from a sub view a modal listview will appear. If you select a row
         * in that view, then this action is called. This action will relate the selected model to the detail view model.
         * Then it will redirect to a portlet action that refreshes the portlet. Some parameters are passed to that
         * redirect that ensure continuity on futher actions that rely on existing $_GET information.
         * @param string $modelId
         * @param string $portletId
         * @param string $uniqueLayoutId
         * @param string $relationAttributeName
         * @param string $relationModelId
         * @param string $relationModuleId
         */
        public function actionSelectFromRelatedListSave($modelId, $portletId, $uniqueLayoutId,
                                                        $relationAttributeName, $relationModelId, $relationModuleId)
        {
            $relationModelClassName = Yii::app()->getModule($relationModuleId)->getPrimaryModelName();
            $relationModel          = $relationModelClassName::getById((int)$relationModelId);
            $modelClassName         = $this->getModule()->getPrimaryModelName();
            $model                  = $modelClassName::getById((int)$modelId);
            $redirectUrl            = $this->createUrl('/' . $relationModuleId . '/default/details',
                                                        array('id' => $relationModelId));
            if (!$model->$relationAttributeName->contains($relationModel))
            {
                $model->$relationAttributeName->add($relationModel);
            }
            $model->save();
            $this->redirect(array('/' . $relationModuleId . '/defaultPortlet/modalRefresh',
                                'id'             => $relationModelId,
                                'portletId'      => $portletId,
                                'uniqueLayoutId' => $uniqueLayoutId,
                                'redirectUrl'    => $redirectUrl,
                                'portletParams'  => array(  'relationModuleId' => $relationModuleId,
                                                            'relationModelId'  => $relationModelId)));
        }

        public function actionMakeChartXML($portletId, $chartLibraryName)
        {
            $portlet = Portlet::getById(intval($portletId));
            assert('$portlet->getView() instanceof ChartView');
            $chartDataProviderType = $portlet->getView()->getChartDataProviderType();
            $chartDataProvider     = ChartDataProviderFactory::createByType($chartDataProviderType);
            ControllerSecurityUtil::resolveCanCurrentUserAccessModule(
                                        $chartDataProvider->getModel()->getModuleClassName(), true);
            $adapterClassName      = 'ChartDataProviderTo' . $chartLibraryName . 'ChartAdapter';
            $fusionChart = $adapterClassName::makeChartByChartDataProvider($chartDataProvider,
                                $portlet->getView()->getChartParams());
            echo $fusionChart->getXML();
        }
    }
?>
