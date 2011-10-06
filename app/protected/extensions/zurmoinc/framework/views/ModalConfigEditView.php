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

    /**
     * Abstraction for displaying a modal display of a configuration view.
     * @see Portlet
     */
    abstract class ModalConfigEditView extends EditView
    {
        protected $params;

        public function __construct(ConfigurableMetadataModel $model, $params)
        {
            $this->model          = $model;
            $this->modelClassName = get_class($model);
            $this->modelId        = null;
            $this->params         = $params;
        }

        protected function renderContent()
        {
            assert('isset($this->params["controllerId"])');
            assert('isset($this->params["moduleId"])');
            assert('isset($this->params["modalConfigSaveAction"])');
            assert('isset($this->params["uniquePortletPageId"])');

            $formName = 'modal-edit-form';
            $afterValidateAjax = $this->renderConfigSaveAjax(
                $formName,
                $this->params['moduleId'],
                $this->params['controllerId'],
                $this->params['modalConfigSaveAction'],
                $this->params['uniquePortletPageId']
            );
            $content = '<div class="wide form">';
            $clipWidget = new ClipWidget();
            list($form, $formStart) = $clipWidget->renderBeginWidget(
                $this->getActiveFormClassName(),
                array(
                    'id' => $formName,
                    'enableAjaxValidation' => true,
                    'clientOptions' => array(
                        'validateOnSubmit' => true,
                        'validateOnChange' => false,
                        'afterValidate'    => 'js:afterValidateAjaxAction',
                        'afterValidateAjax' => $afterValidateAjax,
                    ),
                )
            );

            $cs = Yii::app()->getClientScript();
            $cs->registerScriptFile(
                Yii::app()->getAssetManager()->publish(
                    Yii::getPathOfAlias('ext.zurmoinc.framework.elements.assets') . '/Modal.js'
                    ),
                CClientScript::POS_END
            );
            $cs->registerScriptFile(
                Yii::app()->getAssetManager()->publish(
                    Yii::getPathOfAlias('ext.zurmoinc.framework.views.assets') . '/FormUtils.js'
                    ),
                CClientScript::POS_END
            );

            $content .= $formStart;
            $content .= $this->renderFormLayout($form);
            $content .= '<div class="modal-view-toolbar">';
            $content .= $this->renderActionElementBar(true);
            $content .= '</div>';
            $formEnd = $clipWidget->renderEndWidget();
            $content .= $formEnd;
            $content .= '</div>';
            return $content;
        }

        protected function renderConfigSaveAjax($formName, $moduleId, $controllerId, $actionSave, $uniquePortletPageId)
        {
            return CHtml::ajax(array(
                    'type' => 'POST',
                    'data' => 'js:$("#' . $formName . '").serialize()',
                    'url'  => Yii::app()->createUrl($moduleId . '/' . $controllerId . '/' . $actionSave, $_GET),
                    'complete' => 'function(XMLHttpRequest, textStatus){$("#modalContainer").dialog("close");
                        juiPortlets.refresh();}',
                    'update' => '#' . $uniquePortletPageId,
                ));
        }

        public function isUniqueToAPage()
        {
            return true;
        }

        public function getPostArrayName()
        {
            return get_class($this->model);
        }

        public function setMetadataFromPost($postArray)
        {
            $this->model->setAttributes($postArray);
        }

        public function validate()
        {
            echo ZurmoActiveForm::validate($this->model);
        }

        public function getViewMetadata()
        {
            return $this->model->getAttributes();
        }

        protected static function getActiveFormClassName()
        {
            return 'ZurmoActiveForm';
        }
    }
?>