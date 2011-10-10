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

    class DesignerDefaultController extends ZurmoBaseController
    {
        public function actionIndex()
        {
            $canvasView = new TitleBarAndDesignerPageMenuView(
                        $this->getId(),
                        $this->getModule()->getId()
            );
            $view = new DesignerPageView($this, $canvasView, null);
            echo $view->render();
        }

        public function actionModulesMenu()
        {
            assert('!empty($_GET["moduleClassName"])');
            $module = new $_GET['moduleClassName'](null, null);
            $breadcrumbLinks = array(
                $module::getModuleLabelByTypeAndLanguage('Plural')
            );
            $canvasView = new TitleBarAndModulesMenuView(
                        $this->getId(),
                        $this->getModule()->getId(),
                        $module,
                        $breadcrumbLinks
            );
            $view = new DesignerPageView($this, $canvasView, $_GET['moduleClassName']);
            echo $view->render();
        }

        public function actionAttributesList()
        {
            assert('!empty($_GET["moduleClassName"])');
            $moduleClassName = $_GET['moduleClassName'];
            $breadcrumbLinks = array(
                $moduleClassName::getModuleLabelByTypeAndLanguage('Plural') =>
                    array('default/modulesMenu', 'moduleClassName' => $_GET['moduleClassName']),
                 yii::t('Default', 'Fields'),
            );
            $overrideClassName = $moduleClassName . 'AttributesListView';
            $overrideClassFile = Yii::app()->getBasePath() . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR .
                                 $moduleClassName::getDirectoryName() .
                                 DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . $overrideClassName.'.php';
            if(is_file($overrideClassFile) && class_exists($overrideClassName))
            {
                $viewClassName = $moduleClassName . 'AttributesListView';
                $canvasView    = new $viewClassName($this->getId(), $this->getModule()->getId(), $breadcrumbLinks);
            }
            else
            {
                $modelClassName  = $moduleClassName::getPrimaryModelName();
                $model           = new $modelClassName();
                $adapter         = new ModelAttributesAdapter($model);
                $canvasView = new StandardAndCustomAttributesListView(
                            $this->getId(),
                            $this->getModule()->getId(),
                            $_GET['moduleClassName'],
                            $moduleClassName::getModuleLabelByTypeAndLanguage('Plural'),
                            $adapter->getStandardAttributes(),
                            $adapter->getCustomAttributes(),
                            $modelClassName,
                            $breadcrumbLinks
                );
            }
            $view = new DesignerPageView($this, $canvasView, $_GET['moduleClassName']);
            echo $view->render();
        }

        public function actionAttributeCreate()
        {
            assert('!empty($_GET["moduleClassName"])');
            $moduleClassName = $_GET['moduleClassName'];
            $modelClassName = $moduleClassName::getPrimaryModelName();
            $model = new $modelClassName();
            $breadcrumbLinks = array(
                $moduleClassName::getModuleLabelByTypeAndLanguage('Plural') =>
                    array('default/modulesMenu',    'moduleClassName' => $_GET['moduleClassName']),
                yii::t('Default', 'Fields') =>
                    array('default/attributesList', 'moduleClassName' => $_GET['moduleClassName']),
                 yii::t('Default', 'Create Field'),
            );
            $canvasView = new TitleBarAndAttributeCreateView(
                        $this->getId(),
                        $this->getModule()->getId(),
                        $_GET['moduleClassName'],
                        $moduleClassName::getModuleLabelByTypeAndLanguage('Plural'),
                        $modelClassName,
                        $breadcrumbLinks
            );
            $view = new DesignerPageView($this, $canvasView, $_GET['moduleClassName']);
            echo $view->render();
        }

        public function actionAttributeEdit()
        {
            assert('!empty($_GET["moduleClassName"])');
            assert('!empty($_GET["attributeTypeName"])');
            $attributeFormClassName = $_GET['attributeTypeName'] . 'AttributeForm';
            $moduleClassName = $_GET['moduleClassName'];
            $modelClassName  = $moduleClassName::getPrimaryModelName();
            $model = new $modelClassName();
            if (!empty($_GET['attributeName']))
            {
                $attributeForm = AttributesFormFactory::createAttributeFormByAttributeName($model, $_GET["attributeName"]);
            }
            else
            {
                $attributeForm   = new $attributeFormClassName();
                $attributeForm->setScenario('createAttribute');
                $attributeForm->setModelClassName($modelClassName);
            }
            if (isset($_POST['ajax']) && $_POST['ajax'] === 'edit-form')
            {
                $this->actionAttributeValidate($attributeForm, $modelClassName);
            }
            if (isset($_POST[get_class($attributeForm)]))
            {
                $this->actionAttributeSave($attributeForm, $model);
            }
            $breadcrumbLinks = array(
                $moduleClassName::getModuleLabelByTypeAndLanguage('Plural') =>
                    array('default/modulesMenu',     'moduleClassName' => $_GET['moduleClassName']),
                yii::t('Default', 'Fields') =>
                    array('default/attributesList',  'moduleClassName' => $_GET['moduleClassName']),
                strval($attributeForm),
            );
            $canvasView = new TitleBarAndAttributeEditView(
                        $this->getId(),
                        $this->getModule()->getId(),
                        $_GET['moduleClassName'],
                        $_GET['attributeTypeName'],
                        $modelClassName,
                        $attributeForm,
                        $breadcrumbLinks
            );
            $view = new DesignerPageView($this, $canvasView, $_GET['moduleClassName']);
            echo $view->render();
        }

        protected function actionAttributeValidate($attributeForm)
        {
            echo ZurmoActiveForm::validate($attributeForm);
            Yii::app()->end(0, false);
        }

        public function actionAttributeDetails()
        {
            assert('!empty($_GET["moduleClassName"])');
            assert('!empty($_GET["attributeTypeName"])');
            assert('!empty($_GET["attributeName"])');
            $moduleClassName = $_GET['moduleClassName'];
            $modelClassName  = $moduleClassName::getPrimaryModelName();
            $model           = new $modelClassName();
            $attributeForm   = AttributesFormFactory::createAttributeFormByAttributeName($model, $_GET["attributeName"]);
            $breadcrumbLinks = array(
                $moduleClassName::getModuleLabelByTypeAndLanguage('Plural') =>
                    array('default/modulesMenu',     'moduleClassName' => $_GET['moduleClassName']),
                yii::t('Default', 'Fields') =>
                    array('default/attributesList',  'moduleClassName' => $_GET['moduleClassName']),
                $attributeForm,
            );
            $canvasView = new TitleBarAndAttributeDetailsView(
                        $this->getId(),
                        $this->getModule()->getId(),
                        $_GET['moduleClassName'],
                        $_GET['attributeTypeName'],
                        $modelClassName,
                        $attributeForm,
                        $breadcrumbLinks
            );
            $view = new DesignerPageView($this, $canvasView, $_GET['moduleClassName']);
            echo $view->render();
        }

        protected function actionAttributeSave($attributeForm, $model)
        {
            assert('!empty($_GET["moduleClassName"])');
            $wasRequired = $attributeForm->isRequired;
            $attributeForm->setAttributes($_POST[get_class($attributeForm)]);
            $modelAttributesAdapterClassName = $attributeForm::getModelAttributeAdapterNameForSavingAttributeFormData();
            $adapter = new $modelAttributesAdapterClassName($model);
            $adapter->setAttributeMetadataFromForm($attributeForm);

            //if wasRequired and now is not... ( make sure you use oldAttributeName to catch proper array alignment)
            //removeAttributeAsMissingRequiredAttribute($moduleClassName, $viewClassName, $attributeName)

            if($attributeForm->isRequired && !$wasRequired)
            {
                RequiredAttributesValidViewUtil::
                resolveToSetAsMissingRequiredAttributesByModelClassName(get_class($model), $attributeForm->attributeName);
            }
            elseif(!$attributeForm->isRequired && $wasRequired)
            {
                RequiredAttributesValidViewUtil::
                removeAttributeAsMissingRequiredAttribute(get_class($model), $attributeForm->attributeName);
            }
            $routeParams = array_merge($_GET, array(
                'attributeName' => $attributeForm->attributeName,
                0 => 'default/attributeDetails'
            ));
            $this->redirect($routeParams);
        }

        public function actionModuleLayoutsList()
        {
            assert('!empty($_GET["moduleClassName"])');
            $moduleClassName = $_GET['moduleClassName'];
            $modelClassName = $moduleClassName::getPrimaryModelName();
            $model = new $modelClassName();
            $viewClassNames = $moduleClassName::getViewClassNames();
            $editableViewsCollection = array();
            foreach ($viewClassNames as $className)
            {
                $classToEvaluate     = new ReflectionClass($className);
                if (is_subclass_of($className, 'MetadataView') && !$classToEvaluate->isAbstract() &&
                    $className::getDesignerRulesType() != null)
                {
                    $designerRulesType = $className::getDesignerRulesType();
                    $designerRulesClassName = $designerRulesType . 'DesignerRules';
                    $designerRules = new $designerRulesClassName();
                    if ($designerRules->allowEditInLayoutTool())
                    {
                        $editableViewsCollection[] = array(
                            'titleLabel' => $designerRules->resolveDisplayNameByView($className),
                            'route' => '/designer/default/layoutEdit',
                            'viewClassName' => $className,
                        );
                    }
                }
            }
            $breadcrumbLinks = array(
                $moduleClassName::getModuleLabelByTypeAndLanguage('Plural') =>
                    array('default/modulesMenu', 'moduleClassName' => $_GET['moduleClassName']),
                 yii::t('Default', 'Layouts'),
            );
            $canvasView = new TitleBarAndModuleEditableMetadataCollectionView(
                        $this->getId(),
                        $this->getModule()->getId(),
                        $_GET['moduleClassName'],
                        $moduleClassName::getModuleLabelByTypeAndLanguage('Plural'),
                        $editableViewsCollection,
                        $breadcrumbLinks
            );
            $view = new DesignerPageView($this, $canvasView, $_GET['moduleClassName']);
            echo $view->render();
        }

        public function actionLayoutEdit()
        {
            assert('!empty($_GET["moduleClassName"])');
            assert('!empty($_GET["viewClassName"])');
            $viewClassName           = $_GET['viewClassName'];
            $moduleClassName         = $_GET['moduleClassName'];
            $modelClassName          = $moduleClassName::getPrimaryModelName();
            $editableMetadata        = $viewClassName::getMetadata();
            $designerRulesType       = $viewClassName::getDesignerRulesType();
            $designerRulesClassName  = $designerRulesType . 'DesignerRules';
            $designerRules           = new $designerRulesClassName();
            $modelAttributesAdapter  = DesignerModelToViewUtil::getModelAttributesAdapter($viewClassName, $modelClassName);
            $attributeCollection     = $modelAttributesAdapter->getAttributes();
            $attributesLayoutAdapter = AttributesLayoutAdapterUtil::makeAttributesLayoutAdapter(
                $attributeCollection,
                $designerRules,
                $editableMetadata
            );
            if (isset($_POST['layout']))
            {
                $layoutMetadataAdapter = new LayoutMetadataAdapter(
                    $viewClassName,
                    $moduleClassName,
                    $editableMetadata,
                    $designerRules,
                    $attributesLayoutAdapter->getPlaceableLayoutAttributes(),
                    $attributesLayoutAdapter->getRequiredDerivedLayoutAttributeTypes()
                );
                $savableMetadata = array();
                if ( $designerRules->canConfigureLayoutPanelsType() &&
                    !PanelsDisplayTypeLayoutMetadataUtil::populateSaveableMetadataFromPostData($savableMetadata,
                        $_POST['LayoutPanelsTypeForm']))
                {
                    echo CJSON::encode(array('message' => Yii::t('Default', 'Invalid panel configuration type'), 'type' => 'error'));
                }
                elseif ($layoutMetadataAdapter->setMetadataFromLayout(ArrayUtil::getArrayValue($_POST, 'layout'), $savableMetadata))
                {
                    if($designerRules->requireAllRequiredFieldsInLayout())
                    {
                        RequiredAttributesValidViewUtil::
                        setAsContainingRequiredAttributes($moduleClassName, $viewClassName);
                    }
                    echo CJSON::encode(array('message' => $layoutMetadataAdapter->getMessage(), 'type' => 'message'));
                }
                else
                {
                    echo CJSON::encode(array('message' => $layoutMetadataAdapter->getMessage(), 'type' => 'error'));
                }
                Yii::app()->end(0, false);
            }
            $breadcrumbLinks = array(
                $moduleClassName::getModuleLabelByTypeAndLanguage('Plural') =>
                    array('default/modulesMenu',     'moduleClassName' => $_GET['moduleClassName']),
                yii::t('Default', 'Layouts') =>
                    array('default/moduleLayoutsList',  'moduleClassName' => $_GET['moduleClassName']),
                 $designerRules->resolveDisplayNameByView($_GET['viewClassName']),
            );
            $canvasView = new MetadataViewEditView(
                        $this->getId(),
                        $this->getModule()->getId(),
                        $_GET['moduleClassName'],
                        $_GET['viewClassName'],
                        $editableMetadata,
                        $designerRules,
                        $attributeCollection,
                        $attributesLayoutAdapter->makeDesignerLayoutAttributes(),
                        $breadcrumbLinks
            );
            $view = new DesignerPageView($this, $canvasView, $_GET['moduleClassName']);
            echo $view->render();
        }

        public function actionModuleEdit()
        {
            assert('!empty($_GET["moduleClassName"])');
            $module = new $_GET['moduleClassName'](null, null);
            $metadata = $module::getMetadata();
            $adapter = new ModuleMetadataToFormAdapter($metadata['global'], get_class($module));
            $moduleForm = $adapter->getModuleForm();
            if (isset($_POST['ajax']) && $_POST['ajax'] === 'edit-form')
            {
                $this->actionModuleValidate($moduleForm);
            }
            if (isset($_POST[get_class($moduleForm)]))
            {
                $this->actionModuleSave($moduleForm, $module);
            }
            $breadcrumbLinks = array(
                $module::getModuleLabelByTypeAndLanguage('Plural') =>
                    array('default/modulesMenu',     'moduleClassName' => $_GET['moduleClassName']),
                    yii::t('Default', 'General Edit'),
            );
            $canvasView = new TitleBarAndModuleEditView(
                        $this->getId(),
                        $this->getModule()->getId(),
                        $module,
                        $moduleForm,
                        $breadcrumbLinks
            );
            $view = new DesignerPageView($this, $canvasView, $_GET['moduleClassName']);
            echo $view->render();
        }

        protected function actionModuleValidate($moduleForm)
        {
            echo ZurmoActiveForm::validate($moduleForm);
            Yii::app()->end(0, false);
        }

        protected function actionModuleSave($moduleForm, $module)
        {
            $moduleForm->setAttributes($_POST[get_class($moduleForm)]);
            $adapter = new ModuleFormToMetadataAdapter($module, $moduleForm);
            $adapter->setMetadata();
            Yii::app()->languageHelper->flushModuleLabelTranslationParameters();
            $routeParams = array_merge($_GET, array(
                'moduleClassName' => get_class($module),
                0 => 'default/modulesMenu'
            ));
            $this->redirect($routeParams);
        }
    }
?>
