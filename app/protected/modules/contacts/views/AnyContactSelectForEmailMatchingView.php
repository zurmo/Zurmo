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
     * The view for selecting any contact regardless of state
     */
    class AnyContactSelectForEmailMatchingView extends MetadataView
    {
        protected $controllerId;

        protected $moduleId;

        protected $model;

        protected $uniqueId;

        /**
         * Action id to use by ajax for validating and saving the model.
         * @var string
         */
        protected $saveActionId;

        /**
         * Parameters to pass in the url for validation any actions called.
         * @var array
         */
        protected $urlParameters;

        /**
         * Construct the view to display an input to select any contact regardless of state
         */
        public function __construct($controllerId, $moduleId, $model, $uniqueId, $saveActionId, $urlParameters)
        {
            assert('$model != null');
            assert('$model instanceof AnyContactSelectForm || $model instanceof ContactSelectForm || $model instanceof LeadSelectForm');
            assert('is_string($uniqueId) || is_int($uniqueId)');
            assert('is_string($saveActionId)');
            assert('is_array($urlParameters)');
            $this->controllerId   = $controllerId;
            $this->moduleId       = $moduleId;
            $this->model          = $model;
            $this->uniqueId       = $uniqueId;
            $this->saveActionId   = $saveActionId;
            $this->urlParameters  = $urlParameters;
        }

        /**
         * Renders content for a view.
         * @return A string containing the element's content.
         */
        protected function renderContent()
        {
            $this->renderScriptsContent();
            $content = '<div class="wide form">';
            $clipWidget = new ClipWidget();
            $afterValidateAjax = $this->renderConfigSaveAjax($this->getFormId());
            list($form, $formStart) = $clipWidget->renderBeginWidget(
                                                                'ZurmoActiveForm',
                                                                array(
                                                                    'id' => $this->getFormId(),
                                                                    'action' => $this->getValidateAndSaveUrl(),
                                                                    'enableAjaxValidation' => true,
                                                                    'clientOptions' => array(
                                                                        'validateOnSubmit'  => true,
                                                                        'validateOnChange'  => false,
                                                                        'beforeValidate'    => 'js:beforeValidateAction',
                                                                        'afterValidate'     => 'js:afterValidateAjaxAction',
                                                                        'afterValidateAjax' => $afterValidateAjax,
                                                                    ),
                                                                )
                                                            );
            $content .= $formStart;
            $content .= $this->renderFormLayout($form);
            $formEnd  = $clipWidget->renderEndWidget();
            $content .= $formEnd;
            $content .= '</div>';
            return $content;
        }

        protected function renderFormLayout($form = null)
        {
            $content = '<table>';
            $content .= TableUtil::getColGroupContent(1);
            $content .= '<tbody>';
            $content .= '<tr>';
            if ($this->model instanceof AnyContactSelectForm)
            {
                $elementClassName = 'AnyContactNameIdElement';
            }
            elseif ($this->model instanceof ContactSelectForm)
            {
                $elementClassName = 'ContactNameIdElement';
            }
            else
            {
                $elementClassName = 'LeadNameIdElement';
            }
            $params                 = array();
            $params['inputPrefix']  = array(get_class($this->model), $this->uniqueId);
            $element  = new $elementClassName($this->model, 'null', $form, $params);
            $content .= $element->render();
            $content .= '</tr>';
            $content .= '</tbody>';
            $content .= '</table>';
            $content .= '<div class="view-toolbar-container clearfix"><div class="form-toolbar">';
            $cancelElement  =   new CancelLinkForEmailsMatchingListActionElement($this->controllerId, $this->moduleId,
                                                      null,
                                                      array('htmlOptions' =>
                                                          array('name'   => 'anyContactCancel-' . $this->uniqueId,
                                                                'id'     => 'anyContactCancel-' . $this->uniqueId,
                                                                 'class' => 'anyContactCancel')));
            $content .= $cancelElement->render();
            $element  =   new SaveButtonActionElement($this->controllerId, $this->moduleId,
                                                      null,
                                                      array('htmlOptions' =>
                                                          array('name'   => 'ContactSelect-' . $this->uniqueId,
                                                                'id'     => 'ContactSelect-' . $this->uniqueId)));
            $content .= $element->render();
            $content .= '</div></div>';
            return $content;
        }

        protected function renderScriptsContent()
        {
            Yii::app()->clientScript->registerScript('anyContactSelectFormCollapseActions', "
                        $('.anyContactCancel').each(function()
                        {
                            $('.anyContactCancel').live('click', function()
                            {
                                $(this).parentsUntil('.email-archive-item').find('.AnyContactSelectForEmailMatchingView').hide();
                                $(this).closest('.email-archive-item').closest('td').removeClass('active-panel')
                                .find('.z-action-link-active').removeClass('z-action-link-active');
                            });
                        });");
        }

        protected function getFormId()
        {
            return 'select-contact-form-' . $this->uniqueId;
        }

        protected function getValidateAndSaveUrl()
        {
            return Yii::app()->createUrl($this->moduleId . '/' . $this->controllerId . '/' . $this->saveActionId, $this->urlParameters);
        }

        protected function renderConfigSaveAjax($formName)
        {
            // Begin Not Coding Standard
            return ZurmoHtml::ajax(array(
                    'type' => 'POST',
                    'data' => 'js:$("#' . $formName . '").serialize()',
                    'url'  =>  $this->getValidateAndSaveUrl(),
                    'complete' => "function(XMLHttpRequest, textStatus){
                    $('#wrapper-" . $this->uniqueId . "').parent().parent().parent().remove();
                    $('#" . self::getNotificationBarId() . "').jnotifyAddMessage(
                                       {
                                          text: '" . Zurmo::t('ContactsModule', 'Selected successfully') . "',
                                          permanent: false,
                                          showIcon: true,
                                       });
                    if ($('.email-archive-item').length==0)
                    {
                        window.location.reload();
                    }
                    }",
                ));
            // End Not Coding Standard
        }

        public function isUniqueToAPage()
        {
            return false;
        }

        protected function getViewStyle()
        {
            return " style=' display:none;'";
        }

        protected static function getNotificationBarId()
        {
            return 'FlashMessageBar';
        }
    }
?>