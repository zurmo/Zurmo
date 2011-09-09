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

    abstract class ZurmoBaseController extends Controller
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
                $filters[] = array(
                        ZurmoBaseController::RIGHTS_FILTER_PATH . ' + create, createFromRelation',
                        'moduleClassName' => $moduleClassName,
                        'rightName' => $moduleClassName::getCreateRight(),
                );
            }
            $filters[] = array(
                ZurmoBaseController::RIGHTS_FILTER_PATH . ' + massEdit, massEditProgressSave',
                'moduleClassName' => 'ZurmoModule',
                'rightName' => ZurmoModule::RIGHT_BULK_WRITE,
            );
            return $filters;
        }

        public function __construct($id, $module = null)
        {
            parent::__construct($id, $module);
        }

        protected function makeSearchFilterListView(
            $searchModel,
            $filteredListModelClassName,
            $pageSize,
            $title,
            $userId,
            $dataProvider
            )
        {
            $listModel = $searchModel->getModel();
            $filteredListData = array();
            //Add back in once filtered lists is completed.
            //$filteredListData = $filteredListModelClassName::getRowsByCreatedUserId($userId);
            $filteredListId = null;
            if (!empty($_GET['filteredListId']) && empty($_POST['search']))
            {
                $filteredListId = (int)$_GET['filteredListId'];
            }
            return new SearchFilterListView(
                $this->getId(),
                $this->getModule()->getId(),
                $searchModel,
                $listModel,
                $this->getModule()->getPluralCamelCasedName(),
                $dataProvider,
                GetUtil::resolveSelectedIdsFromGet(),
                GetUtil::resolveSelectAllFromGet(),
                $filteredListData,
                $filteredListId,
                $title
            );
        }

        protected function makeFilteredListDataProviderFromGet(
            $filteredListId,
            $listModelClassName,
            $filteredListModelClassName,
            $pageSize,
            $userId,
            $stateMetadataAdapterClassName = null)
        {
            assert('is_int($filteredListId)');
            assert('is_string($listModelClassName)');
            assert('$stateMetadataAdapterClassName == null || is_string($stateMetadataAdapterClassName)');
            $filteredList   = $filteredListModelClassName::getById($filteredListId);
            $sortAttribute  = SearchUtil::resolveSortAttributeFromGetArray($listModelClassName);
            $sortDescending = SearchUtil::resolveSortDescendingFromGetArray($listModelClassName);

            $metadataAdapter = new FilteredListDataProviderMetadataAdapter(
                $filteredList,
                $userId,
                unserialize($filteredList->serializedData)
            );
            return RedBeanModelDataProviderUtil::makeDataProvider(
                $metadataAdapter,
                $listModelClassName,
                'FilteredListDataProvider',
                $sortAttribute,
                $sortDescending,
                $pageSize,
                $stateMetadataAdapterClassName
            );
        }

        protected function attemptToSaveFilteredListModelFromPost($id, $modelClassName)
        {
            if ($id != null)
            {
                $filteredList = $modelClassName::getById(intval($id));
            }
            else
            {
                $filteredList = new $modelClassName();
            }
            if (isset($_POST[$modelClassName]))
            {
                $filteredList->setAttributes($_POST[$modelClassName]);
                $filteredList->serializedData = serialize(FilteredListSaveUtil::makeDataFromPost($_POST[$modelClassName]));
                if ($filteredList->save()) //todo: some sort of validation?
                {
                    $this->redirect(array('default/index', 'filteredListId' => $filteredList->id));
                    Yii::app()->end(0, false);
                }
            }
            return $filteredList;
        }

        protected function makeSearchFilterListDataProvider(
            $searchModel,
            $listModelClassName,
            $filteredListModelClassName,
            $pageSize,
            $userId,
            $stateMetadataAdapterClassName = null)
        {
            assert('$searchModel != null');
            assert('$searchModel instanceof RedBeanModel || $searchModel instanceof ModelForm');
            if (!empty($_GET['filteredListId']) && empty($_POST['search']))
            {
                $filteredListId = (int)$_GET['filteredListId'];
                assert('!empty($filteredListModelClassName)');
                $dataProvider = $this->makeFilteredListDataProviderFromGet(
                    $filteredListId,
                    $listModelClassName,
                    $filteredListModelClassName,
                    $pageSize,
                    $userId,
                    $stateMetadataAdapterClassName);
            }
            else
            {
                $dataProvider = $this->makeRedBeanDataProviderFromGet(
                    $searchModel,
                    $listModelClassName,
                    $pageSize,
                    $userId,
                    $stateMetadataAdapterClassName);
            }
            return $dataProvider;
        }

        protected function getDataProviderByResolvingSelectAllFromGet(
            $searchModel,
            $listModelClassName,
            $pageSize,
            $userId,
            $filteredListModelClassName = null,
            $stateMetadataAdapterClassName = null
            )
        {
            assert('$searchModel != null');
            assert('$searchModel instanceof RedBeanModel || $searchModel instanceof ModelForm');
            if ($_GET['selectAll'])
            {
                return $this->makeSearchFilterListDataProvider(
                    $searchModel,
                    $listModelClassName,
                    $filteredListModelClassName,
                    $pageSize,
                    $userId,
                    $stateMetadataAdapterClassName);
            }
            else
            {
                return null;
            }
        }

        /**
         * This method is called after a mass edit form is first submitted.
         * It is called from the actionMassEdit.
         * @see actionMassEdit in the module default controllers.
         */
        protected function processMassEdit(
            $pageSize,
            $activeAttributes,
            $selectedRecordCount,
            $pageViewClassName,
            $listModel,
            $title,
            $dataProvider = null
            )
        {
            assert('$dataProvider == null || $dataProvider instanceof CDataProvider');
            $modelClassName = get_class($listModel);
            $selectedRecordCount = $this->getSelectedRecordCountByResolvingSelectAllFromGet($dataProvider);
            if (isset($_POST[$modelClassName]))
            {
                PostUtil::sanitizePostForSavingMassEdit($modelClassName);
                //Generically test that the changes are valid before attempting to save on each model.
                $sanitizedOwnerPostData = PostUtil::sanitizePostDataToJustHavingElementForSavingModel($_POST[$modelClassName], 'owner');
                $sanitizedPostDataWithoutOwner = PostUtil::removeElementFromPostDataForSavingModel($_POST[$modelClassName], 'owner');
                $massEditPostrDataWithoutOwner = PostUtil::removeElementFromPostDataForSavingModel($_POST['MassEdit'], 'owner');
                $listModel->setAttributes($sanitizedPostDataWithoutOwner);
                if ($listModel->validate(array_keys($massEditPostrDataWithoutOwner)))
                {
                    $passedOwnerValidation = true;
                    if ($sanitizedOwnerPostData != null)
                    {
                        $listModel->setAttributes($sanitizedOwnerPostData);
                        $passedOwnerValidation = $listModel->validate(array('owner'));
                    }
                    if ($passedOwnerValidation)
                    {
                        MassEditInsufficientPermissionSkipSavingUtil::clear($modelClassName);
                        $this->saveMassEdit(
                            get_class($listModel),
                            $modelClassName,
                            $selectedRecordCount,
                            $dataProvider,
                            $_GET[$modelClassName . '_page'],
                            $pageSize
                        );
                        if ($selectedRecordCount > $pageSize)
                        {
                            $view = new $pageViewClassName($this,
                                $this->makeMassEditProgressView(
                                    $listModel,
                                    1,
                                    $selectedRecordCount,
                                    1,
                                    $pageSize,
                                    $title,
                                    null)
                            );
                            echo $view->render();
                            Yii::app()->end(0, false);
                        }
                        else
                        {
                            $skipCount = MassEditInsufficientPermissionSkipSavingUtil::getCount($modelClassName);
                            $successfulCount = MassEditInsufficientPermissionSkipSavingUtil::resolveSuccessfulCountAgainstSkipCount(
                                $selectedRecordCount, $skipCount);
                            MassEditInsufficientPermissionSkipSavingUtil::clear($modelClassName);
                            $notificationContent = Yii::t('Default', 'Successfully updated') . ' ' .
                                                    $successfulCount . ' ' .
                                                    LabelUtil::getUncapitalizedRecordLabelByCount($successfulCount) .
                                                    '.';
                            if ($skipCount > 0)
                            {
                                $notificationContent .= ' ' .
                                    MassEditInsufficientPermissionSkipSavingUtil::getSkipCountMessageContentByModelClassName(
                                        $skipCount, $modelClassName);
                            }
                            Yii::app()->user->setFlash('notification', $notificationContent);
                            $this->redirect(array('default/index'));
                            Yii::app()->end(0, false);
                        }
                    }
                }
            }
            return $listModel;
        }

        /**
         * Called only during a mulitple phase save from mass edit. This occurs if the quantity of models to save
         * is greater than the pageSize. This signals a save that must be conducted in phases where each phase
         * updates a quantity of models no greater than the page size.
         */
        protected function processMassEditProgressSave(
            $modelClassName,
            $pageSize,
            $title,
            $dataProvider = null)
        {
            assert('$dataProvider == null || $dataProvider instanceof CDataProvider');
            $listModel = new $modelClassName(false);
            $selectedRecordCount = $this->getSelectedRecordCountByResolvingSelectAllFromGet($dataProvider);
            PostUtil::sanitizePostForSavingMassEdit($modelClassName);
            $this->saveMassEdit(
                get_class($listModel),
                $modelClassName,
                $selectedRecordCount,
                $dataProvider,
                $_GET[$modelClassName . '_page'],
                $pageSize
            );
            $view = $this->makeMassEditProgressView(
                $listModel,
                $_GET[$modelClassName . '_page'],
                $selectedRecordCount,
                $this->getMassEditProgressStartFromGet(
                    $modelClassName,
                    $pageSize
                ),
                $pageSize,
                $title,
                MassEditInsufficientPermissionSkipSavingUtil::getCount($modelClassName)
            );
            echo $view->renderRefreshJSONScript();
        }

        protected function makeMassEditProgressView(
            $model,
            $page,
            $selectedRecordCount,
            $start,
            $pageSize,
            $title,
            $skipCount)
        {
            assert('$skipCount == null || is_int($skipCount)');
            return new MassEditProgressView(
                $this->getId(),
                $this->getModule()->getId(),
                $model,
                $selectedRecordCount,
                $start,
                $pageSize,
                $page,
                'massEditProgressSave',
                $title,
                $skipCount
            );
        }

        /**
         * Called either from a mass edit save, or a mass edit progress save.
         */
        protected function saveMassEdit($modelClassName, $postVariableName, $selectedRecordCount, $dataProvider, $page, $pageSize)
        {
            $modelsToSave = $this->getModelsToSave($modelClassName, $dataProvider, $selectedRecordCount, $page, $pageSize);
            foreach ($modelsToSave as $modelToSave)
            {
                if (ControllerSecurityUtil::doesCurrentUserHavePermissionOnSecurableItem($modelToSave, Permission::WRITE))
                {
                    $sanitizedOwnerPostData = PostUtil::sanitizePostDataToJustHavingElementForSavingModel($_POST[$postVariableName], 'owner');
                    $sanitizedPostDataWithoutOwner = PostUtil::removeElementFromPostDataForSavingModel($_POST[$postVariableName], 'owner');
                    $modelToSave->setAttributes($sanitizedPostDataWithoutOwner);
                    if ($sanitizedOwnerPostData != null)
                    {
                        $modelToSave->setAttributes($sanitizedOwnerPostData);
                    }
                    $modelToSave->save(false);
                }
                else
                {
                    MassEditInsufficientPermissionSkipSavingUtil::setByModelIdAndName(
                        $modelClassName, $modelToSave->id, $modelToSave->name);
                }
            }
        }

        /**
         * Check if form is posted. If form is posted attempt to save. If save is complete, confirm the current
         * user can still read the model.  If not, then redirect the user to the index action for the module.
         */
        protected function attemptToSaveModelFromPost($model, $redirectUrlParams = null)
        {
            assert('$redirectUrlParams == null || is_array($redirectUrlParams) || is_string($redirectUrlParams)');
            $postVariableName = get_class($model);
            if (isset($_POST[$postVariableName]))
            {
                $postData = $_POST[$postVariableName];
                if($model instanceof SecurableItem)
                {
                    $explicitReadWriteModelPermissions = ExplicitReadWriteModelPermissionsUtil::
                                                         resolveByPostDataAndModelThenMake($_POST[$postVariableName], $model);
                }
                else
                {
                    $explicitReadWriteModelPermissions = null;
                }
                $readyToUsePostData                = ExplicitReadWriteModelPermissionsUtil::
                                                     removeIfExistsFromPostData($_POST[$postVariableName]);
                $sanitizedPostData                 = PostUtil::sanitizePostByDesignerTypeForSavingModel(
                                                     $model, $readyToUsePostData);
                $sanitizedOwnerPostData            = PostUtil::sanitizePostDataToJustHavingElementForSavingModel(
                                                     $sanitizedPostData, 'owner');
                $sanitizedPostDataWithoutOwner     = PostUtil::
                                                     removeElementFromPostDataForSavingModel($sanitizedPostData, 'owner');
                $model->setAttributes($sanitizedPostDataWithoutOwner);
                if ($model->validate())
                {
                    $modelToStringValue = strval($model);
                    if ($sanitizedOwnerPostData != null)
                    {
                        $model->setAttributes($sanitizedOwnerPostData);
                    }
                    if ($model instanceof OwnedSecurableItem)
                    {
                        $passedOwnerValidation = $model->validate(array('owner'));
                    }
                    else
                    {
                        $passedOwnerValidation = true;
                    }
                    if ($passedOwnerValidation && $model->save(false))
                    {
                        if($explicitReadWriteModelPermissions != null)
                        {
                            $success = ExplicitReadWriteModelPermissionsUtil::
                            resolveExplicitReadWriteModelPermissions($model, $explicitReadWriteModelPermissions);
                            //todo: handle if success is false, means adding/removing permissions save failed.
                        }
                        $this->actionAfterSuccessfulModelSave($model, $modelToStringValue, $redirectUrlParams);
                    }
                }
            }
            return $model;
        }

        protected function actionAfterSuccessfulModelSave($model, $modelToStringValue, $redirectUrlParams = null)
        {
            assert('is_string($modelToStringValue)');
            assert('$redirectUrlParams == null || is_array($redirectUrlParams) || is_string($redirectUrlParams)');
            if (ControllerSecurityUtil::doesCurrentUserHavePermissionOnSecurableItem($model, Permission::READ))
            {
                $this->redirectAfterSaveModel($model->id, $redirectUrlParams);
            }
            else
            {
                $notificationContent = Yii::t(
                    'Default',
                    'You no longer have permissions to access {modelName}.',
                    array('{modelName}' => $modelToStringValue)
                );
                Yii::app()->user->setFlash('notification', $notificationContent);
                $this->redirect(array($this->getId() . '/index'));
            }
        }

        protected function redirectAfterSaveModel($modelId, $urlParams = null)
        {
            if ($urlParams == null)
            {
                $urlParams = array($this->getId() . '/details', 'id' => $modelId);
            }
            $this->redirect($urlParams);
        }
    }
?>
