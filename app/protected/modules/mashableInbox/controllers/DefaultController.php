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

    class MashableInboxDefaultController extends ZurmoModuleController
    {
        const MASHABLE_INBOX_ZERO_MODELS_CHECK_FILTER_PATH =
              'application.modules.mashableInbox.controllers.filters.MashableInboxZeroModelsCheckControllerFilter';

        public $pageSize;

        public function filters()
        {
            $modelClassName     = ArrayUtil::getArrayValue(GetUtil::getData(), 'modelClassName');

            if ($modelClassName === null)
            {
                return parent::filters();
            }
            $moduleClassName    = $modelClassName::getModuleClassName();
            if (!is_subclass_of($moduleClassName, 'SecurableModule'))
            {
                return parent::filters();
            }
            return array_merge(parent::filters(),
                array(
                    array(
                        self::getRightsFilterPath(),
                        'moduleClassName' => $moduleClassName,
                        'rightName' => $moduleClassName::getAccessRight(),
                    ),
                    array(
                        self::MASHABLE_INBOX_ZERO_MODELS_CHECK_FILTER_PATH . ' + list',
                        'controller'  => $this,
                    ),
                )
            );
        }

        public function actionList($modelClassName = null)
        {
            assert('is_string($modelClassName) || $modelClassName == null');
            $this->pageSize     = Yii::app()->pagination->resolveActiveForCurrentUserByType(
                                        'listPageSize', get_class($this->getModule()));
            $getData = GetUtil::getData();
            $mashableInboxForm  = $this->getMashableInboxFormWithDefaultValues($modelClassName, $getData);
            if (Yii::app()->request->isAjaxRequest && isset($getData['ajax']))
            {
                $this->renderListViewForAjax($mashableInboxForm, $modelClassName);
            }
            else
            {
                $this->renderMashableInboxPageView($mashableInboxForm, $modelClassName);
            }
        }

        private function getMashableInboxFormWithDefaultValues($modelClassName, $getData)
        {
            $mashableInboxForm = MashableUtil::restoreSelectedOptionsAsStickyData($modelClassName);
            if ($mashableInboxForm->optionForModel == null)
            {
                $mashableInboxForm->optionForModel = 2;
            }
            if ($mashableInboxForm->filteredBy == null)
            {
                $mashableInboxForm->filteredBy = MashableInboxForm::FILTERED_BY_ALL;
            }
            if (isset($getData["MashableInboxForm"]))
            {
                $mashableInboxForm->attributes = $getData["MashableInboxForm"];
            }
            if ($mashableInboxForm->massAction != null)
            {
                $this->resolveAjaxMassAction($modelClassName, $mashableInboxForm);
            }
            MashableUtil::saveSelectedOptionsAsStickyData($mashableInboxForm, $modelClassName);
            return $mashableInboxForm;
        }

        public function actionGetUnreadCount()
        {
            $combinedInboxesModels = MashableUtil::getModelDataForCurrentUserByInterfaceName('MashableInboxInterface');
            foreach ($combinedInboxesModels as $modelClassName => $modelLabel)
            {
                $data[strtolower($modelClassName)] = MashableUtil::
                        getUnreadCountForCurrentUserByModelClassName($modelClassName);
            }
            echo CJSON::encode($data);
        }

        /**
         * Render that page view for the mashableInbox. If $modelClassName is set it will render the model listView
         * otherwise it will render a listView with all mashableInbox models merged
         * @param MashableInboxForm $mashableInboxForm
         * @param string $modelClassName
         */
        private function renderMashableInboxPageView($mashableInboxForm, $modelClassName)
        {
            $actionViewOptions  = array();
            if ($modelClassName !== null)
            {
                $mashableUtilRules  = MashableUtil::createMashableInboxRulesByModel($modelClassName);
                $listView           = $mashableUtilRules->getListView(
                                                $mashableInboxForm->optionForModel,
                                                $mashableInboxForm->filteredBy,
                                                $mashableInboxForm->searchTerm);
                $actionViewOptions  = $mashableUtilRules->getActionViewOptions();
            }
            else
            {
                $listView           = $this->getMashableInboxListView(
                                                $mashableInboxForm,
                                                $this->pageSize);
            }
            $actionBarView          = new MashableInboxActionBarForViews(
                                                $this->getId(),
                                                $this->getModule()->getId(),
                                                $listView,
                                                $actionViewOptions,
                                                $mashableInboxForm,
                                                $modelClassName);
            $view                   = new MashableInboxPageView(ZurmoDefaultViewUtil::
                                                makeStandardViewForCurrentUser($this, $actionBarView));
            echo $view->render();
        }

        /**
         * Render the listView to update after ajax request is made
         * @param MashableInboxForm $mashableInboxForm
         * @param string $modelClassName
         * @param array $getData
         */
        private function renderListViewForAjax($mashableInboxForm, $modelClassName)
        {
            if ($modelClassName !== null)
            {
                $mashableUtilRules  = MashableUtil::createMashableInboxRulesByModel(
                                                      $modelClassName);
                $listView           = $mashableUtilRules->getListView(
                                                      $mashableInboxForm->optionForModel,
                                                      $mashableInboxForm->filteredBy,
                                                      $mashableInboxForm->searchTerm);
            }
            else
            {
                $listView           = $this->getMashableInboxListView(
                                                      $mashableInboxForm,
                                                      $this->pageSize);
            }
            $view = new AjaxPageView($listView);
            echo $view->render();
        }

        /**
         * Resolves the mass action triggered by the ajax request
         * @param string $modelClassName
         * @param MashableInboxForm $mashableInboxForm
         */
        private function resolveAjaxMassAction($modelClassName, $mashableInboxForm)
        {
            if ($modelClassName !== null)
            {
                $selectedIds        = explode(',', $mashableInboxForm->selectedIds); // Not Coding Standard
                foreach ($selectedIds as $modelId)
                {
                   $this->resolveMassActionByModel($mashableInboxForm->massAction,
                                                   $modelClassName,
                                                   $modelId);
                }
            }
            else
            {
                $selectedIds        = explode(',', $mashableInboxForm->selectedIds); // Not Coding Standard
                foreach ($selectedIds as $selectedId)
                {
                   list($modelClassNameForMassAction, $modelId) = explode("_", $selectedId);
                   $this->resolveMassActionByModel($mashableInboxForm->massAction,
                                                   $modelClassNameForMassAction,
                                                   $modelId);
                }
            }
        }

        /**
         * Resolves the mass action triggered by ajax based on the modelClassName
         * @param string $massAction
         * @param string $modelClassName
         * @param integer $modelId
         */
        private function resolveMassActionByModel($massAction, $modelClassName, $modelId)
        {
            $method             = 'resolve' . ucfirst($massAction);
            $mashableUtilRules  = MashableUtil::createMashableInboxRulesByModel($modelClassName);
            $mashableUtilRules->$method((int)$modelId);
        }

        /**
         * Gets the listView that should be rendered based on the mashableInboxForm params
         * @param MashableInboxForm $mashableInboxForm
         * @return \MashableInboxListView
         */
        private function getMashableInboxListView($mashableInboxForm)
        {
            $modelClassNames
                = array_keys(MashableUtil::getModelDataForCurrentUserByInterfaceName('MashableInboxInterface'));
            $modelClassNamesAndSearchAttributeMetadataForMashableInbox
                = MashableUtil::getSearchAttributeMetadataForMashableInboxByModelClassName(
                                                $modelClassNames,
                                                $mashableInboxForm->filteredBy,
                                                $mashableInboxForm->searchTerm);
            $modelClassNamesAndSortAttributes
                = MashableUtil::getSortAttributesByMashableInboxModelClassNames($modelClassNames);
            $dataProvider
                = new RedBeanModelsDataProvider('MashableInbox',
                                                $modelClassNamesAndSortAttributes,
                                                true,
                                                $modelClassNamesAndSearchAttributeMetadataForMashableInbox,
                                                array('pagination' => array('pageSize' => $this->pageSize)));
            $listView
                = new MashableInboxListView($this->getId(),
                                            $this->getModule()->getId(),
                                            'MashableInbox',
                                            $dataProvider,
                                            array(),
                                            null,
                                            array(
                                                'paginationParams' => GetUtil::getData()
                                            ));
            return $listView;
        }
    }
?>