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
     * Maps default controller for configuration view and modal map view.
     */
    class MapsDefaultController extends ZurmoModuleController
    {
        public function filters()
        {
            return array(
                array(
                      ZurmoBaseController::RIGHTS_FILTER_PATH . ' + index, ConfigurationView',
                      'moduleClassName'   => 'MapsModule',
                      'rightName'         => MapsModule::RIGHT_ACCESS_MAPS_ADMINISTRATION,
                ),
            );
        }

        public function actionIndex()
        {
            $this->actionConfigurationView();
        }

        /**
         * Admin configuration action for entering the google map api key.
         */
        public function actionConfigurationView()
        {
            $configurationForm          = new MapsConfigurationForm();
            $configurationForm->apiKey  = Yii::app()->mappingHelper->getGeoCodeApiKey();

            $postVariableName           = get_class($configurationForm);
            if (isset($_POST[$postVariableName]))
            {
                $configurationForm->setAttributes($_POST[$postVariableName]);
                if ($configurationForm->validate())
                {
                    ZurmoConfigurationUtil::setByModuleName('MapsModule', 'googleMapApiKey', $configurationForm->apiKey);
                    Yii::app()->user->setFlash('notification',
                                                Zurmo::t('MapsModule', 'Maps configuration saved successfully.')
                    );
                    $this->redirect(Yii::app()->createUrl('maps/default/configurationView'));
                }
            }
            $editView = new MapConfigurationView(
                                    'Edit',
                                    $this->getId(),
                                    $this->getModule()->getId(),
                                    $configurationForm);
            $editView->setCssClasses( array('AdministrativeArea') );
            $view = new ZurmoConfigurationPageView(ZurmoDefaultAdminViewUtil::
                                         makeStandardViewForCurrentUser($this, $editView));
            echo $view->render();
        }

        /**
         * Render modal view for rendering map.
         */
        public function actionMapAndPoint($addressString, $latitude, $longitude)
        {
            $modalMapAddressData = array('query'     => $addressString,
                                         'latitude'  => $latitude,
                                         'longitude' => $longitude);

            //Set ajax mode for modal map render view
            Yii::app()->getClientScript()->setToAjaxMode();

            echo $this->renderModalMapView($this, $modalMapAddressData);
        }

        /**
         * Map modal view for map popup..
         * @return rendered content from view as string.
         */
        protected function renderModalMapView(CController $controller,
                                           $modalMapAddressData,
                                           $stateMetadataAdapterClassName = null)
        {
            $renderAndMapModalView = new AddressMapModalView($controller->getId(),
                                                             $controller->getModule()->getId(),
                                                             $modalMapAddressData,
                                                             'modal'
                                         );

            $view = new ModalView($controller, $renderAndMapModalView);
            return $view->render();
        }
    }
?>