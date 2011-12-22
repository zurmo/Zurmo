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

    class MapsDefaultController extends ZurmoModuleController
    {
        public function filters()
        {
            return array(
                array(
                      ZurmoBaseController::RIGHTS_FILTER_PATH,
                      'moduleClassName'   => 'MapsModule',
                      'rightName'         => MapsModule::RIGHT_ACCESS_MAPS,
                ),
            );
        }

        public function actionIndex()
        {
            $this->actionConfigurationView();
        }

        public function actionConfigurationView()
        {
            $configurationForm          = new MapsConfigurationForm();
            $configurationForm->apiKey  = ZurmoMappingHelper::getGeoCodeApi();

            $postVariableName           = get_class($configurationForm);
            if (isset($_POST[$postVariableName]))
            {
                $configurationForm->setAttributes($_POST[$postVariableName]);
                if ($configurationForm->validate())
                {
                    ZurmoConfigurationUtil::setByModuleName('MapsModule', 'googleMapApiKey', $configurationForm->apiKey);
                    Yii::app()->user->setFlash('notification',
                                                Yii::t('Default', 'Maps configuration saved successfully.')
                    );
                    $this->redirect(Yii::app()->createUrl('maps/default/configurationView'));
                }
            }
            $titleBarAndEditView = new TitleBarAndConfigurationEditAndDetailsView(
                                    $this->getId(),
                                    $this->getModule()->getId(),
                                    $configurationForm,
                                    'AdminConfigurationView',
                                    'Edit',
                                    Yii::t('Default', 'Maps Configuration')
            );
            $view = new ZurmoConfigurationPageView($this, $titleBarAndEditView);
            echo $view->render();
        }

        public function actionRenderAddressMapView()
        {
            $modalMapAddressData = array('query'        =>$_GET['query'], 
                                          'latitude'    =>$_GET['latitude'], 
                                          'longitude'   =>$_GET['longitude']);

            //Set ajax mode for modal map render view
            Yii::app()->getClientScript()->setToAjaxMode();

            echo $this->renderModalMapView($this, $modalMapAddressData, 
                                           Yii::t('Default', 'Address Location on Map',
                                           LabelUtil::getTranslationParamsForAllModules()));
        }

        /**
         * @return rendered content from view as string.
         */
        public function renderModalMapView(CController $controller, 
                                                  $modalMapAddressData,
                                                  $pageTitle = null,
                                                  $stateMetadataAdapterClassName = null)
        {

            $renderAndMapModalView = new AddressGoogleMapModalView(
                $controller->getId(),
                $controller->getModule()->getId(),
                $modalMapAddressData,
                'modal'
            );

            $view = new ModalView($controller,
                                  $renderAndMapModalView,
                                  'modalContainer',
                                  $pageTitle);
            return $view->render();
        }
    }
?>