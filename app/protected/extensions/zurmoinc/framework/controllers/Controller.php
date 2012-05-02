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
         * Utilizes information from the $_GET variable to
         * make a RedBeanDataProvider.  Looks for the following $_GET
         * variables:
         *  modelName_sort
         *  modelName
         *  where modelName is Account for example.
         * Typically utilized by a listView action.
         */
        public function makeRedBeanDataProviderFromGet(
            $searchModel,
            $listModelClassName,
            $pageSize,
            $userId,
            $stateMetadataAdapterClassName = null)
        {
            assert('is_int($pageSize)');
            assert('$stateMetadataAdapterClassName == null || is_string($stateMetadataAdapterClassName)');
            $searchAttributes          = SearchUtil::resolveSearchAttributesFromGetArray(get_class($searchModel));
            SearchUtil::resolveAnyMixedAttributesScopeForSearchModelFromGetArray($searchModel, get_class($searchModel));
            $sanitizedSearchAttributes = GetUtil::sanitizePostByDesignerTypeForSavingModel($searchModel,
                                                                                           $searchAttributes);
            $sortAttribute             = SearchUtil::resolveSortAttributeFromGetArray($listModelClassName);
            $sortDescending            = SearchUtil::resolveSortDescendingFromGetArray($listModelClassName);
            $metadataAdapter = new SearchDataProviderMetadataAdapter(
                $searchModel,
                $userId,
                $sanitizedSearchAttributes
            );

            return RedBeanModelDataProviderUtil::makeDataProvider(
                $metadataAdapter,
                $listModelClassName,
                'RedBeanModelDataProvider',
                $sortAttribute,
                $sortDescending,
                $pageSize,
                $stateMetadataAdapterClassName
            );
        }

        protected function makeSearchAndListView(
            $searchModel,
            $listModel,
            $searchAndListViewClassName,
            $moduleClassName,
            $pageSize,
            $userId,
            $stateMetadataAdapterClassName = null)
        {
            $dataProvider = $this->makeRedBeanDataProviderFromGet(
                $searchModel,
                get_class($listModel),
                $pageSize,
                $userId,
                $stateMetadataAdapterClassName
            );
            return new $searchAndListViewClassName(
                $this->getId(),
                $this->getModule()->getId(),
                $searchModel,
                $listModel,
                $moduleClassName,
                $dataProvider,
                GetUtil::resolveSelectedIdsFromGet(),
                GetUtil::resolveSelectAllFromGet()
            );
        }

        protected function makeDetailsAndRelationsView($model, $moduleClassName, $viewClassName, $redirectUrl)
        {
            assert('$model instanceof RedBeanModel || $model instanceof CModel');
            $params = array(
                'controllerId'     => $this->getId(),
                'relationModuleId' => $this->getModule()->getId(),
                'relationModel'    => $model,
                'redirectUrl'      => $redirectUrl,
            );
            $gridView = new GridView(1, 1);
            $gridView->setView(new $viewClassName(  $this->getId(),
                                                    $this->getModule()->getId(),
                                                    $params), 0, 0);
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

        protected function resolveActiveAttributesFromMassEditPost()
        {
            if (isset($_POST['MassEdit']))
            {
                return $_POST['MassEdit'];
            }
            else
            {
                return array();
            }
        }

        protected function makeMassEditView(
            $model,
            $activeAttributes,
            $selectedRecordCount,
            $title)
        {
            $alertMessage          = $this->getMassEditAlertMessage(get_class($model));
            $moduleName            = $this->getModule()->getPluralCamelCasedName();
            $moduleClassName       = $moduleName . 'Module';
            $title                 = Yii::t('Default', 'Mass Update') . ': ' . $title;
            $massEditViewClassName = $moduleName . 'MassEditView';
            $view  = new $massEditViewClassName($this->getId(), $this->getModule()->getId(), $model, $activeAttributes,
                                                      $selectedRecordCount, $title, $alertMessage);
            return $view;
        }

        protected function getSelectedRecordCountByResolvingSelectAllFromGet($dataProvider)
        {
            if ($_GET['selectAll'])
            {
                return intval($dataProvider->calculateTotalItemCount());
            }
            else
            {
                return count(explode(",", $_GET['selectedIds'])); // Not Coding Standard
            }
        }

        protected function getMassEditProgressStartFromGet($getVariableName, $pageSize)
        {
            if ($_GET[$getVariableName . '_page'] == 1)
            {
                return 1;
            }
            elseif ($_GET[$getVariableName . '_page']>1)
            {
                return ((($_GET[$getVariableName . '_page'] - 1) * $pageSize) +1);
            }
            else
            {
                throw new NotSupportedException();
            }
        }

        protected function attemptToValidateAjaxFromPost($model, $postVariableName)
        {
            if (isset($_POST['ajax']) && $_POST['ajax'] == 'edit-form')
            {
                $model->setAttributes($_POST[$postVariableName]);
                $model->validate();
                $errorData = array();
                foreach ($model->getErrors() as $attribute => $errors)
                {
                        $errorData[CHtml::activeId($model, $attribute)] = $errors;
                }
                echo CJSON::encode($errorData);
                Yii::app()->end(0, false);
            }
        }

        protected function getModelsToSave($modelClassName, $dataProvider, $selectedRecordCount, $page, $pageSize)
        {
            if ($dataProvider === null)
            {
                $modelsToSave = array();
                $IdsToSave = explode(",", $_GET['selectedIds']); // Not Coding Standard
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
                for ($i = $start; $i < $end; ++$i)
                {
                    eval('$modelsToSave[] = ' . $modelClassName . '::getById(intval(' . $IdsToSave[$i] . '));');
                    //$modelsToSave[] = $modelClassName::getById(intval($IdsToSave[$i]));
                }
                return $modelsToSave;
            }
            else
            {
                return $dataProvider->getData();
            }
        }

        protected function getMassEditAlertMessage($postVariableName)
        {
            if (!isset($_POST[$postVariableName]) && isset($_POST['save']))
            {
                    return Yii::t('Default', 'You must select at least one field to modify.');
            }
        }
    }
?>
