<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2012 Zurmo Inc.
     *
     * Zurmo is free software; you can redistribute it and/or modify it under
     * the terms of the GNU Affero General Public License version 3 as published by the
     * Free Software Foundation with the addition of the following permission added
     * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
     * IN WHICH THE COPYRIGHT IS OWNED BY ZURMO, ZURMO DISCLAIMS THE WARRANTY
     * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
     *
     * Zurmo is distributed in the hope that it will be useful, but WITHOUT
     * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
     * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
     * details.
     *
     * You should have received a copy of the GNU Affero General Public License along with
     * this program; if not, see http://www.gnu.org/licenses or write to the Free
     * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
     * 02110-1301 USA.
     *
     * You can contact Zurmo, Inc. with a mailing address at 113 McHenry Road Suite 207,
     * Buffalo Grove, IL 60089, USA. or at email address contact@zurmo.com.
     ********************************************************************************/

    class ContactExternalEditAndDetailsView extends EditAndDetailsView
    {
        public $externalViewMetadata;

        protected $hashIndexHiddenField;

        const GOOGLE_WEB_TRACKING_ID_FIELD = 'googleWebTrackingId';

        public function __construct($renderType, $controllerId, $moduleId, $model, $metadata)
        {
            parent::__construct($renderType, $controllerId, $moduleId, $model);
            $this->externalViewMetadata     = $metadata;
            $this->hashIndexHiddenField     = ContactWebFormEntry::HASH_INDEX_HIDDEN_FIELD;
        }

        public static function getDesignerRulesType()
        {
            return null;
        }

        public static function getDefaultMetadata()
        {
            $metadata = array(
                'global' => array(
                    'toolbar' => array(
                        'elements' => array(
                            array('type' => 'SaveButton', 'renderType' => 'Edit', 'label' => 'Save'),
                        ),
                    ),
                    'derivedAttributeTypes' => array(
                        'TitleFullName',
                        'ContactStateDropDown',
                    ),
                    'nonPlaceableAttributeNames' => array(
                        'title',
                        'owner',
                        'state',
                    ),
                    'panelsDisplayType' => FormLayout::PANELS_DISPLAY_TYPE_ALL,
                    'panels' => array(),
                ),
            );
            return $metadata;
        }

        protected function getFormLayoutMetadata()
        {
            return $this->externalViewMetadata;
        }

        protected function resolveActiveFormAjaxValidationOptions()
        {
            $ajaxValidationOptions = array('enableAjaxValidation' => true,
                                           'clientOptions'        => array(
                                                'validateOnSubmit'  => true,
                                                'validateOnChange'  => false,
                                                'beforeValidate'    => 'js:$(this).beforeValidateAction',
                                                'afterValidate'     => 'js:$(this).afterValidateAjaxAction',
                                                'afterValidateAjax' => $this->renderConfigSaveAjax()));
            return array_merge($ajaxValidationOptions,
                               array('action' => $this->getValidateAndSaveUrl()));
        }

        protected function renderConfigSaveAjax()
        {
            $formId = $this->getFormId();
            return ZurmoHtml::ajax(array(
                                         'type'     => 'POST',
                                         'data'     => 'js:$("#' . $formId . '").serialize()',
                                         'url'      =>  $this->getValidateAndSaveUrl(),
                                         'success'  => 'js: function(data)
                                                        {
                                                            if (typeof data.redirectUrl !== \'undefined\' &&
                                                                $(this).isValidUrl(data.redirectUrl))
                                                            {
                                                                window.location.href = data.redirectUrl;
                                                            }
                                                        }'
                                  ));
        }

        protected function getValidateAndSaveUrl()
        {
            return Yii::app()->createAbsoluteUrl($this->moduleId . '/' . $this->controllerId . '/form',
                                        array('id' => Yii::app()->getRequest()->getQuery('id')));
        }

        protected function renderRightSideFormLayoutForEdit($form)
        {
            return null;
        }

        protected function getNewModelTitleLabel()
        {
            return Zurmo::t('ContactsModule', 'Create ContactsModuleSingularLabel',
                LabelUtil::getTranslationParamsForAllModules());
        }

        protected function resolveActionElementInformationDuringRender(& $elementInformation)
        {
            parent::resolveActionElementInformationDuringRender($elementInformation);
            if ($elementInformation['type'] == 'SaveButton')
            {
                $metadata = $this->externalViewMetadata;
                $elementInformation['label'] = $metadata['global']['toolbar']['elements'][0]['label'];
            }
        }

        protected function resolveFormHtmlOptions()
        {
            $data = array('onSubmit' => 'js:jQQ.isolate(function($) { return $(this).attachLoadingOnSubmit("' . static::getFormId() . '") });');
            if ($this->viewContainsFileUploadElement)
            {
                $data['enctype'] = 'multipart/form-data';
            }
            return $data;
        }

        protected function renderAfterFormLayout($form)
        {
            $hashIndexHtmlOptions = array(
                'name'     => $this->hashIndexHiddenField,
                'id'       => $this->hashIndexHiddenField,
                'value'    => md5('ContactWebFormEntry'.time()),
            );
            $content = $form->hiddenField($this->model, $this->hashIndexHiddenField, $hashIndexHtmlOptions);
            $externalRequestTokenHtmlOptions = array(
                'name'     => ZurmoHttpRequest::EXTERNAL_REQUEST_TOKEN,
                'id'       => ZurmoHttpRequest::EXTERNAL_REQUEST_TOKEN,
                'value'    => ZURMO_TOKEN,
            );
            $content .= $form->hiddenField($this->model, ZurmoHttpRequest::EXTERNAL_REQUEST_TOKEN, $externalRequestTokenHtmlOptions);
            $googleWebTrackingIdHtmlOptions = array(
                'name'     => self::GOOGLE_WEB_TRACKING_ID_FIELD,
                'id'       => self::GOOGLE_WEB_TRACKING_ID_FIELD,
                'value'    => '',
            );
            $content .= $form->hiddenField($this->model, self::GOOGLE_WEB_TRACKING_ID_FIELD, $googleWebTrackingIdHtmlOptions);
            return $content;
        }

        protected function renderTitleContent()
        {
            return null;
        }
    }
?>