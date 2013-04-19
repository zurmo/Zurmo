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

    class ZurmoDefaultController extends ZurmoBaseController
    {
        public function filters()
        {
            $moduleClassName = get_class($this->getModule());
            return array(
                array(
                    ZurmoBaseController::RIGHTS_FILTER_PATH . ' + logout, index, about',
                    'moduleClassName' => $moduleClassName,
                    'rightName'       => $moduleClassName::getAccessRight(),
               ),
                array(
                    ZurmoBaseController::RIGHTS_FILTER_PATH . ' + configurationEdit',
                    'moduleClassName' => $moduleClassName,
                    'rightName'       => ZurmoModule::RIGHT_ACCESS_GLOBAL_CONFIGURATION,
               ),
            );
        }

        public function actionIndex()
        {
            $this->redirect(Yii::app()->homeUrl);
        }

        public function actionLogin()
        {
            $formModel = new LoginForm();
            if (isset($_POST['ajax']) && $_POST['ajax'] == 'login-form')
            {
                echo ZurmoActiveForm::validate($formModel);
                Yii::app()->end(0, false);
            }
            elseif (isset($_POST['LoginForm']))
            {
                $formModel->attributes = $_POST['LoginForm'];
                if ($formModel->validate() && $formModel->login())
                {
                    $this->redirect(Yii::app()->user->returnUrl);
                }
            }
            $extraHeaderContent = ZurmoConfigurationUtil::getByModuleName('ZurmoModule', 'loginViewExtraHeaderContent');
            $view = new LoginPageView($this, $formModel, $extraHeaderContent);
            echo $view->render();
        }

        public function actionLogout()
        {
            Yii::app()->user->logout();
            $this->redirect(Yii::app()->homeUrl);
        }

        public function actionError()
        {
            if ($error = Yii::app()->errorHandler->error)
            {
                if (Yii::app()->request->isAjaxRequest)
                {
                    echo $error['message'];
                }
                else
                {
                    $view = new ErrorPageView($error['message']);
                    echo $view->render();
                }
            }
        }

        public function actionUnsupportedBrowser($name)
        {
            if ($name == '')
            {
                $name = 'not detected';
            }
            $view = new UnsupportedBrowserPageView($name);
            echo $view->render();
        }

        public function actionAbout()
        {
            $view = new AboutPageView(ZurmoDefaultViewUtil::
                                         makeStandardViewForCurrentUser($this, new AboutView()));
            echo $view->render();
        }

        public function actionConfigurationEdit()
        {
            $configurationForm = ZurmoConfigurationFormAdapter::makeFormFromGlobalConfiguration();
            $postVariableName   = get_class($configurationForm);
            if (isset($_POST[$postVariableName]))
            {
                $configurationForm->setAttributes($_POST[$postVariableName]);
                if ($configurationForm->validate())
                {
                    ZurmoConfigurationFormAdapter::setConfigurationFromForm($configurationForm);
                    Yii::app()->user->setFlash('notification',
                        Zurmo::t('ZurmoModule', 'Global configuration saved successfully.')
                    );
                    $this->redirect(Yii::app()->createUrl('configuration/default/index'));
                }
            }
            $editView = new ZurmoConfigurationEditAndDetailsView(
                                    'Edit',
                                    $this->getId(),
                                    $this->getModule()->getId(),
                                    $configurationForm);
            $editView->setCssClasses( array('AdministrativeArea') );
            $view = new ZurmoConfigurationPageView(ZurmoDefaultAdminViewUtil::
                                         makeStandardViewForCurrentUser($this, $editView));
            echo $view->render();
        }

        public function actionGlobalSearchAutoComplete($term)
        {
            $scopeData = GlobalSearchUtil::resolveGlobalSearchScopeFromGetData($_GET);
            $pageSize  = Yii::app()->pagination->resolveActiveForCurrentUserByType(
                            'autoCompleteListPageSize', get_class($this->getModule()));
            $autoCompleteResults = ModelAutoCompleteUtil::getGlobalSearchResultsByPartialTerm(
                                           $term,
                                           $pageSize,
                                           Yii::app()->user->userModel,
                                           $scopeData
                                        );
            $autoCompleteResults = array_merge(
                    $autoCompleteResults,
                    array(
                        array('href'      => Yii::app()->createUrl(
                                                '/zurmo/default/globallist',
                                                array('MixedModelsSearchForm' =>
                                                    array('term'                    => $_GET['term'],
                                                          'anyMixedAttributesScope' => ArrayUtil::getArrayValue(
                                                              GetUtil::getData(), 'globalSearchScope')))
                                                ),
                              'label'     => 'All results', 'iconClass' => 'autocomplete-icon-AllResults'))
              );
            echo CJSON::encode($autoCompleteResults);
        }

        /*
         * Given a string return all result from the global search in a view
         */
        public function actionGlobalList()
        {
            if (!isset($_GET['MixedModelsSearchForm']['anyMixedAttributesScope']) ||
                    in_array('All', $_GET['MixedModelsSearchForm']['anyMixedAttributesScope']))
            {
                $scopeData = null;
            }
            else
            {
                $scopeData = $_GET['MixedModelsSearchForm']['anyMixedAttributesScope'];
            }
            $term = $_GET['MixedModelsSearchForm']['term'];
            $pageSize = Yii::app()->pagination->resolveActiveForCurrentUserByType(
                            'listPageSize', get_class($this->getModule()));
            $dataCollection = new MixedModelsSearchResultsDataCollection($term, $pageSize,
                    Yii::app()->user->userModel);
            if (Yii::app()->request->getIsAjaxRequest() && isset($_GET["ajax"]))
            {
                $selectedModule = $_GET["ajax"];
                $selectedModule = str_replace('list-view-', '', $selectedModule);
                $view = $dataCollection->getListView($selectedModule);
            }
            else
            {
                $listView = new MixedModelsSearchAndListView(
                                $dataCollection->getViews(),
                                $term,
                                $scopeData
                            );
                $view = new MixedModelsSearchPageView(ZurmoDefaultViewUtil::
                           makeStandardViewForCurrentUser($this, $listView));
            }
            echo $view->render();
        }

        /**
         * Given a name of a customFieldData object and a term to search on return a JSON encoded
         * array of autocomplete search results.
         * @param string $name - Name of CustomFieldData
         * @param string $term - term to search on
         */
        public function actionAutoCompleteCustomFieldData($name, $term)
        {
            assert('is_string($name)');
            assert('is_string($term)');
            $autoCompleteResults = ModelAutoCompleteUtil::getCustomFieldDataByPartialName(
                                       $name, $term);
            if (count($autoCompleteResults) == 0)
            {
                $data = 'No Results Found';
                $autoCompleteResults[] = array('id'    => '',
                                               'name' => $data
                );
            }
            echo CJSON::encode($autoCompleteResults);
        }

        public function actionDynamicSearchAddExtraRow($viewClassName, $modelClassName, $formModelClassName, $rowNumber, $suffix = null)
        {
            echo DynamicSearchUtil::renderDynamicSearchRowContent($viewClassName,
                                                                  $modelClassName,
                                                                  $formModelClassName,
                                                                  (int)$rowNumber,
                                                                  null,
                                                                  null,
                                                                  $suffix,
                                                                  true);
        }

        public function actionDynamicSearchAttributeInput($viewClassName, $modelClassName, $formModelClassName, $rowNumber,
                                                          $attributeIndexOrDerivedType, $suffix = null)
        {
            if ($attributeIndexOrDerivedType == null)
            {
                Yii::app()->end(0, false);
            }
            $content = DynamicSearchUtil::renderDynamicSearchAttributeInput( $viewClassName,
                                                                             $modelClassName,
                                                                             $formModelClassName,
                                                                             (int)$rowNumber,
                                                                             $attributeIndexOrDerivedType,
                                                                             array(),
                                                                             $suffix);
            Yii::app()->getClientScript()->setToAjaxMode();
            Yii::app()->getClientScript()->render($content);
            echo $content;
        }

        public function actionValidateDynamicSearch($viewClassName, $modelClassName, $formModelClassName)
        {
            if (isset($_POST['ajax']) && $_POST['ajax'] === 'search-form' && isset($_POST[$formModelClassName]))
            {
                $model                     = new $modelClassName(false);
                $searchForm                = new $formModelClassName($model);
                //$rawPostFormData           = $_POST[$formModelClassName];
                if (isset($_POST[$formModelClassName][SearchForm::ANY_MIXED_ATTRIBUTES_SCOPE_NAME]))
                {
                    $searchForm->setAnyMixedAttributesScope($_POST[$formModelClassName][SearchForm::ANY_MIXED_ATTRIBUTES_SCOPE_NAME]);
                    unset($_POST[$formModelClassName][SearchForm::ANY_MIXED_ATTRIBUTES_SCOPE_NAME]);
                }
                if (isset($_POST[$formModelClassName][SearchForm::SELECTED_LIST_ATTRIBUTES]))
                {
                    unset($_POST[$formModelClassName][SearchForm::SELECTED_LIST_ATTRIBUTES]);
                }
                $sanitizedSearchData = $this->resolveAndSanitizeDynamicSearchAttributesByPostData(
                                                                $_POST[$formModelClassName], $searchForm);
                $searchForm->setAttributes($sanitizedSearchData);
                if (isset($_POST['save']) && $_POST['save'] == 'saveSearch')
                {
                    $searchForm->setScenario('validateSaveSearch');
                    if ($searchForm->validate())
                    {
                        $savedSearch = $this->processSaveSearch($searchForm, $viewClassName);
                        echo CJSON::encode(array('id' => $savedSearch->id, 'name' => $savedSearch->name));
                        Yii::app()->end(0, false);
                    }
                }
                else
                {
                    $searchForm->setScenario('validateDynamic');
                }
                if (!$searchForm->validate())
                {
                     $errorData = array();
                    foreach ($searchForm->getErrors() as $attribute => $errors)
                    {
                            $errorData[ZurmoHtml::activeId($searchForm, $attribute)] = $errors;
                    }
                    echo CJSON::encode($errorData);
                    Yii::app()->end(0, false);
                }
            }
        }

        protected function processSaveSearch($searchForm, $viewClassName)
        {
            $savedSearch = SavedSearchUtil::makeSavedSearchBySearchForm($searchForm, $viewClassName);
            if (!$savedSearch->save())
            {
                throw new FailedToSaveModelException();
            }
            return $savedSearch;
        }

        public function actionDeleteSavedSearch($id)
        {
            $savedSearch = SavedSearch::GetById(intval($id));
            ControllerSecurityUtil::resolveAccessCanCurrentUserDeleteModel($savedSearch);
            $savedSearch->delete();
        }

        protected function resolveAndSanitizeDynamicSearchAttributesByPostData($postData, DynamicSearchForm $searchForm)
        {
            if (isset($postData['dynamicClauses']))
            {
                $dynamicSearchAttributes          = SearchUtil::getSearchAttributesFromSearchArray($postData['dynamicClauses']);
                $sanitizedDynamicSearchAttributes = SearchUtil::
                                                    sanitizeDynamicSearchAttributesByDesignerTypeForSavingModel(
                                                        $searchForm, $dynamicSearchAttributes);
                $postData['dynamicClauses']       = $sanitizedDynamicSearchAttributes;
            }
            return $postData;
        }

        public function actionClearStickySearch($key)
        {
            StickySearchUtil::clearDataByKey($key);
        }

        /**
         * Change user interface. Available options: desktop, mobile and tablet.
         */
        public function actionUserInterface()
        {
            Yii::import('application.extensions.userinterface.UserInterface');
            if (isset($_GET['userInterface']))
            {
                if (in_array($_GET['userInterface'], array(UserInterface::DESKTOP, UserInterface::MOBILE, UserInterface::TABLET)))
                {
                    Yii::app()->userInterface->resolveSelectedUserInterfaceType($_GET['userInterface']);
                }
                $this->redirect(Yii::app()->createUrl('home/default/'));
            }
        }

        public function actionGetUpdatesForRefresh($unreadMashableInbox)
        {
            $newUnreadMashableInbox = MashableUtil::getUnreadCountMashableInboxForCurrentUser();
            if ($newUnreadMashableInbox > $unreadMashableInbox)
            {
                $data['unreadMashableInbox'] = $newUnreadMashableInbox;
                $data['imgUrl']              = Yii::app()->request->hostinfo . Yii::app()->theme->baseUrl . '/images/z-logo-60x60.png';
                $data['title']               = Zurmo::t('ZurmoModule', 'ZurmoCRM - Item update');
                $data['message']             = Zurmo::t('ZurmoModule', 'There is an item with unread changes.');
                echo CJSON::encode($data);
            }
            else
            {
                echo CJSON::encode(null);
            }
        }

        public function actionUploadLogo($filesVariableName)
        {
            assert('is_string($filesVariableName)');
            try
            {
                $uploadedFile = CUploadedFile::getInstanceByName($filesVariableName);
                assert('$uploadedFile instanceof CUploadedFile');

                $logoFilePath   = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $uploadedFile->getName();
                $thumbFilePath  = sys_get_temp_dir() . DIRECTORY_SEPARATOR .
                                                      ZurmoConfigurationForm::LOGO_THUMB_FILE_NAME_PREFIX . $uploadedFile->getName();
                $uploadedFile->saveAs($logoFilePath);
                ZurmoConfigurationFormAdapter::resizeLogoImageFile($logoFilePath, $thumbFilePath,
                                                                   ZurmoConfigurationForm::DEFAULT_LOGO_THUMBNAIL_WIDTH,
                                                                   ZurmoConfigurationForm::DEFAULT_LOGO_THUMBNAIL_HEIGHT);
                Yii::app()->user->setState('logoFileName', $uploadedFile->getName());
                $logoFileData = array('name'            => $uploadedFile->getName(),
                                      'type'            => $uploadedFile->getType(),
                                      'size'            => $uploadedFile->getSize(),
                                      'thumbnail_url'   => Yii::app()->createUrl('zurmo/default/thumbnail/',
                                                                                 array('filePath' => $thumbFilePath)));
            }
            catch (FailedFileUploadException $e)
            {
                $logoFileData = array('error' => Zurmo::t('ZurmoModule', 'Error') . ' ' . $e->getMessage());
            }
            echo CJSON::encode(array($logoFileData));
            Yii::app()->end(0, false);
        }

        public function actionDeleteLogo()
        {
            Yii::app()->user->setState('logoFileName', null);
            Yii::app()->user->setState('deleteCustomLogo', true);
            Yii::app()->end(0, false);
        }

        public function actionLogo($id)
        {
            $logo = FileModel::getById($id);
            header("Content-Type:   $logo->type");
            header("Content-Length: $logo->size");
            header("Content-Name:   $logo->name");
            echo $logo->fileContent->content;
        }

        public function actionThumbnail($filePath)
        {
            header("Content-Type:   ZurmoFileHelper::getMimeType($filePath)");
            echo file_get_contents($filePath);
        }
    }
?>