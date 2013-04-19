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
     * Framework Controller extended by all
     * application controllers
     */
    abstract class Controller extends CController
    {
        public function __construct($id, $module = null)
        {
            parent::__construct($id, $module);
        }

        public function renderBeginWidget($className, $properties = array())
        {
            ob_start();
            $form = $this->beginWidget($className, $properties);
            $content = ob_get_contents();
            ob_end_clean();
            return array($form, $content);
        }

        public function renderEndWidget()
        {
            ob_start();
            $this->endWidget();
            $content = ob_get_contents();
            ob_end_clean();
            return $content;
        }

        /**
         * Utilizes information from the dataCollection object to
         * make a RedBeanDataProvider.  Either looks at saved search information or params in the $_GET array.
         * variables:
         *  modelName_sort
         *  modelName
         *  where modelName is Account for example.
         * Typically utilized by a listView action.
         */
        public function makeRedBeanDataProviderByDataCollection(
            $searchModel,
            $pageSize,
            $stateMetadataAdapterClassName = null,
            $dataCollection = null)
        {
            assert('is_int($pageSize) || $pageSize == null');
            assert('$stateMetadataAdapterClassName == null || is_string($stateMetadataAdapterClassName)');
            assert('$dataCollection instanceof SearchAttributesDataCollection || $dataCollection == null');
            $listModelClassName = get_class($searchModel->getModel());
            if ($dataCollection == null)
            {
                $dataCollection = new SearchAttributesDataCollection($searchModel);
            }
            $searchAttributes          = $dataCollection->resolveSearchAttributesFromSourceData();
            $dataCollection->resolveAnyMixedAttributesScopeForSearchModelFromSourceData();
            $dataCollection->resolveSelectedListAttributesForSearchModelFromSourceData();
            $sanitizedSearchAttributes = GetUtil::sanitizePostByDesignerTypeForSavingModel($searchModel,
                                                                                           $searchAttributes);
            $sortAttribute             = $dataCollection->resolveSortAttributeFromSourceData($listModelClassName);
            $sortDescending            = $dataCollection->resolveSortDescendingFromSourceData($listModelClassName);
            $metadataAdapter           = new SearchDataProviderMetadataAdapter(
                $searchModel,
                Yii::app()->user->userModel->id,
                $sanitizedSearchAttributes
            );
            $metadata                  = static::resolveDynamicSearchMetadata($searchModel, $metadataAdapter->getAdaptedMetadata(),
                                                                              $dataCollection);
            $this->resolveMetadataBeforeMakingDataProvider($metadata);
            return RedBeanModelDataProviderUtil::makeDataProvider(
                $metadata,
                $listModelClassName,
                'RedBeanModelDataProvider',
                $sortAttribute,
                $sortDescending,
                $pageSize,
                $stateMetadataAdapterClassName
            );
        }

        protected function resolveMetadataBeforeMakingDataProvider(& $metadata)
        {
        }

        protected static function resolveDynamicSearchMetadata($searchModel, $metadata, SearchAttributesDataCollection $dataCollection)
        {
            $sanitizedDynamicSearchAttributes          = $dataCollection->getSanitizedDynamicSearchAttributes();
            if ($sanitizedDynamicSearchAttributes == null)
            {
                return $metadata;
            }
            $dynamicStructure                 = $dataCollection->getDynamicStructure();
            if ($sanitizedDynamicSearchAttributes != null)
            {
                $dynamicSearchMetadataAdapter = new DynamicSearchDataProviderMetadataAdapter($metadata,
                                                                                             $searchModel,
                                                                                             Yii::app()->user->userModel->id,
                                                                                             $sanitizedDynamicSearchAttributes,
                                                                                             $dynamicStructure);
                $metadata                     = $dynamicSearchMetadataAdapter->getAdaptedDataProviderMetadata();
            }
            return $metadata;
        }

        protected function makeDetailsAndRelationsView($model, $moduleClassName, $viewClassName, $redirectUrl, $breadCrumbView = null)
        {
            assert('$model instanceof RedBeanModel || $model instanceof CModel');
            assert('$breadCrumbView == null || $breadCrumbView instanceof BreadCrumbView');
            if ($breadCrumbView != null)
            {
                $verticalColumns   = 2;
                $primaryViewColumn = 1;
            }
            else
            {
                $verticalColumns   = 1;
                $primaryViewColumn = 0;
            }

            $params = array(
                'controllerId'     => $this->getId(),
                'relationModuleId' => $this->getModule()->getId(),
                'relationModel'    => $model,
                'redirectUrl'      => $redirectUrl,
            );
            $gridView = new GridView($verticalColumns, 1);
            if ($breadCrumbView != null)
            {
               $gridView->setView($breadCrumbView, 0, 0);
            }
            $gridView->setView(new $viewClassName(  $this->getId(),
                                                    $this->getModule()->getId(),
                                                    $params), $primaryViewColumn, 0);
            return $gridView;
        }

        protected function makeTitleBarAndEditAndDetailsView($model, $renderType,
                                $titleBarAndEditViewClassName = 'TitleBarAndEditAndDetailsView')
        {
            assert('$model != null');
            assert('$renderType == "Edit" || $renderType == "Details"');
            assert('$titleBarAndEditViewClassName != null && is_string($titleBarAndEditViewClassName)');
            return new $titleBarAndEditViewClassName(
                $this->getId(),
                $this->getModule()->getId(),
                $model,
                $this->getModule()->getPluralCamelCasedName(),
                $renderType
            );
        }

        protected function makeEditAndDetailsView($model, $renderType)
        {
            assert('$model != null');
            assert('$renderType == "Edit" || $renderType == "Details"');
            $editViewClassName = get_class($model) . 'EditAndDetailsView';
            return new $editViewClassName($renderType, $this->getId(), $this->getModule()->getId(), $model);
        }

        protected function makeTitleBarAndEditView($model, $titleBarAndEditViewClassName)
        {
            assert('$model != null');
            assert('$titleBarAndEditViewClassName != null && is_string($titleBarAndEditViewClassName)');
            return new $titleBarAndEditViewClassName(
                $this->getId(),
                $this->getModule()->getId(),
                $model,
                $this->getModule()->getPluralCamelCasedName()
            );
        }

        protected function makeTitleBarAndDetailsView($model, $titleBarAndDetailsViewClassName = 'TitleBarAndDetailsView')
        {
            assert('$model != null');
            assert('$titleBarAndDetailsViewClassName != null && is_string($titleBarAndDetailsViewClassName)');
            return new $titleBarAndDetailsViewClassName(
                $this->getId(),
                $this->getModule()->getId(),
                $model,
                $this->getModule()->getPluralCamelCasedName()
            );
        }

        protected static function resolveActiveAttributesFromPostForMassAction($actionId)
        {
            // TODO: @Shoaibi/@Jason: Low: Candidate for MassActionController
            return Yii::app()->request->getPost(ucfirst($actionId), array());
        }

        protected function resolveActiveAttributesFromMassEditPost()
        {
            // TODO: @Shoaibi/@Jason: Low: Deprecated, Better to use resolveActiveAttributesFromPostForMassAction directly inside main code with actionId
            // trigger_error('Deprecated: Recommended to use resolveActiveAttributesFromPostForMassAction.');
            return static::resolveActiveAttributesFromPostForMassAction('massEdit');
        }

        /**
        for mass delete
        */
        protected function resolveActiveAttributesFromMassDeletePost()
        {
            // TODO: @Shoaibi/@Jason: Low: Deprecated, Better to use resolveActiveAttributesFromPostForMassAction directly inside main code with actionId
            // trigger_error('Deprecated: Recommended to use resolveActiveAttributesFromPostForMassAction.');
            return static::resolveActiveAttributesFromPostForMassAction('massDelete');
        }

        protected function makeMassEditView(
            $model,
            $activeAttributes,
            $selectedRecordCount,
            $title)
        {
            // TODO: @Shoaibi/@Jason: Low: Deprecated
            // trigger_error('Deprecated');
            $alertMessage          = static::getMassEditAlertMessage(get_class($model));
            $moduleName            = $this->getModule()->getPluralCamelCasedName();
            $moduleClassName       = $moduleName . 'Module';
            $title                 = Zurmo::t('Core', 'Mass Update') . ': ' . $title;
            $massEditViewClassName = $moduleName . 'MassEditView';
            $view  = new $massEditViewClassName($this->getId(), $this->getModule()->getId(), $model, $activeAttributes,
                                                      $selectedRecordCount, $title, $alertMessage);
            return $view;
        }

        /** for mass delete */
        protected function makeMassDeleteView(
            $model,
            $activeAttributes,
            $selectedRecordCount,
            $title)
        {
            // TODO: @Shoaibi/@Jason: Low: Deprecated
            // trigger_error('Deprecated');
            $moduleName            = $this->getModule()->getPluralCamelCasedName();
            $moduleClassName       = $moduleName . 'Module';
            $title                 = Zurmo::t('Core', 'Mass Delete') . ': ' . $title;
            $massDeleteViewClassName = 'MassDeleteView';
            $selectedIds = GetUtil::getData();
            $view  = new $massDeleteViewClassName($this->getId(), $this->getModule()->getId(), $model, $activeAttributes,
                                                      $selectedRecordCount, $title, null, $moduleClassName, $selectedIds);
            return $view;
        }

        protected static function getSelectedRecordCountByResolvingSelectAllFromGet($dataProvider, $countEmptyStringAsElement = true)
        {
            if (Yii::app()->request->getQuery('selectAll'))
            {
                return intval($dataProvider->calculateTotalItemCount());
            }
            else
            {
                if ($countEmptyStringAsElement)
                {
                    return count(explode(",", trim(Yii::app()->request->getQuery('selectedIds'), ', '))); // Not Coding Standard
                }
                else
                {
                    return count(array_filter(explode(",", trim(Yii::app()->request->getQuery('selectedIds'), " ,")))); // Not Coding Standard
                }
            }
        }

        protected static function getMassActionProgressStartFromGet($pageVariableName, $pageSize)
        {
            // TODO: @Shoaibi/@Jason: Low: Candidate for MassActionController
            $page = Yii::app()->request->getQuery($pageVariableName);
            if ($page == 1)
            {
                return 1;
            }
            elseif ($page > 1)
            {
                return ((($page - 1) * $pageSize) +1);
            }
            else
            {
                throw new NotSupportedException();
            }
        }

        protected function getMassEditProgressStartFromGet($getVariableName, $pageSize)
        {
            // TODO: @Shoaibi/@Jason: Low: Deprecated
            // trigger_error('Deprecated:  Recommended to use getMassActionProgressStartFromGet. Pay close attention to arguments.');
            return static::getMassActionProgressStartFromGet($getVariableName . '_page', $pageSize);
        }

       /**
        for Mass Delete
        */
        protected function getMassDeleteProgressStartFromGet($getVariableName, $pageSize)
        {
            // TODO: @Shoaibi/@Jason: Low: Deprecated
            // trigger_error('Deprecated:  Recommended to use getMassActionProgressStartFromGet.  Pay close attention to arguments.');
            return static::getMassActionProgressStartFromGet($getVariableName . '_page', $pageSize);
        }

        protected function attemptToValidateAjaxFromPost($model, $postVariableName)
        {
            if (isset($_POST['ajax']) && $_POST['ajax'] == 'edit-form')
            {
                $model->setAttributes($_POST[$postVariableName]);
                $model->validate();
                $errorData = ZurmoActiveForm::makeErrorsDataAndResolveForOwnedModelAttributes($model);
                echo CJSON::encode($errorData);
                Yii::app()->end(0, false);
            }
        }

        protected static function getModelsToUpdate($modelClassName, $dataProvider, $selectedRecordCount, $page, $pageSize)
        {
            // TODO: @Shoaibi/@Jason: Low: Candidate for MassActionController
            if ($dataProvider === null)
            {
                $modelsToUpdate = array();
                $IdsToUpdate = explode(",", Yii::app()->request->getQuery('selectedIds')); // Not Coding Standard
                if ($page == 1)
                {
                    $start = 0;
                }
                elseif ($page > 1)
                {
                    $start = ($page - 1) * $pageSize;
                }
                else
                {
                    throw new NotSupportedException();
                }
                if (($pageSize * $page) > $selectedRecordCount)
                {
                    $end = $selectedRecordCount;
                }
                else
                {
                    $end = $pageSize * $page;
                }
                for ($i = $start; $i < $end; ++$i) // Not Coding Standard
                {
                    $modelsToUpdate[] = $modelClassName::getById(intval($IdsToUpdate[$i]));
                }
                return $modelsToUpdate;
            }
            else
            {
                $data = $dataProvider->getData();
                return $data;
            }
        }

        protected function getModelsToSave($modelClassName, $dataProvider, $selectedRecordCount, $page, $pageSize)
        {
            // TODO: @Shoaibi/@Jason: Low: Deprecated
            // trigger_error('Deprecated: Recommended to use getModelsToUpdate.');
            return static::getModelsToUpdate($modelClassName, $dataProvider, $selectedRecordCount, $page, $pageSize);
        }

        /** for mass delete */
        protected function getModelsToDelete($modelClassName, $dataProvider, $selectedRecordCount, $page, $pageSize)
        {
            // TODO: @Shoaibi/@Jason: Low: Deprecated
            // trigger_error('Deprecated: Recommended to use getModelsToUpdate.');
            return static::getModelsToUpdate($modelClassName, $dataProvider, $selectedRecordCount, $page, $pageSize);
        }

        protected static function getMassEditAlertMessage($postVariableName)
        {
            // TODO: @Shoaibi/@Jason: Low: Deprecated
            // trigger_error('Deprecated: Recommended to use resolveMassEditAlertMessage.');
            return static::resolveMassEditAlertMessage($postVariableName);
        }

        protected static function resolveMassEditAlertMessage($postVariableName)
        {
            // TODO: @Shoaibi/@Jason: Low: Candidate for MassActionController
            $form = Yii::app()->request->getPost($postVariableName);
            $save = Yii::app()->request->getPost('save');
            if (!isset($form) && isset($save))
            {
                return Zurmo::t('Core', 'You must select at least one field to modify.');
            }
        }
    }
?>
