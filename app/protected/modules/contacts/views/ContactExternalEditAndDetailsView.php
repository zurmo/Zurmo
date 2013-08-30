<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
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
     * You can contact Zurmo, Inc. with a mailing address at 27 North Wacker Drive
     * Suite 370 Chicago, IL 60606. or at email address contact@zurmo.com.
     *
     * The interactive user interfaces in original and modified versions
     * of this program must display Appropriate Legal Notices, as required under
     * Section 5 of the GNU Affero General Public License version 3.
     *
     * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
     * these Appropriate Legal Notices must retain the display of the Zurmo
     * logo and Zurmo copyright notice. If the display of the logo is not reasonably
     * feasible for technical reasons, the Appropriate Legal Notices must display the words
     * "Copyright Zurmo Inc. 2013. All rights reserved".
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
            $content  = ZurmoHtml::hiddenField($this->hashIndexHiddenField, md5('ContactWebFormEntry'.time()));
            $content .= ZurmoHtml::hiddenField(ZurmoHttpRequest::EXTERNAL_REQUEST_TOKEN, ZURMO_TOKEN);
            $content .= ZurmoHtml::hiddenField(self::GOOGLE_WEB_TRACKING_ID_FIELD);
            return $content;
        }

        protected function renderTitleContent()
        {
            return null;
        }
    }
?>