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

    class CampaignEditView extends SecuredEditView
    {
        public static function getDefaultMetadata()
        {
            $metadata = array(
                'global' => array(
                    'toolbar' => array(
                        'elements' => array(
                            array('type'    => 'CancelLink'),
                            array('type'    => 'SaveButton', 'label' => 'eval:Zurmo::t("CampaignsModule", "Save and Schedule")'),
                            array('type'    => 'CampaignDeleteLink'),
                        ),
                    ),
                    'panels' => array(
                        array(
                            'rows' => array(
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'name', 'type' => 'Text'),
                                            ),
                                        ),
                                    )
                                ),
                                array('cells' =>
                                array(
                                    array(
                                        'elements' => array(
                                            array('attributeName' => 'marketingList', 'type' => 'MarketingList'),
                                        ),
                                    ),
                                )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'fromName', 'type' => 'Text'),
                                            ),
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'fromAddress', 'type' => 'Email'),
                                            ),
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'sendOnDateTime', 'type' => 'DateTime'),
                                            ),
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'subject', 'type' => 'Text'),
                                            ),
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'enableTracking',
                                                      'type'          => 'EnableTrackingCheckBox'),
                                            ),
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'supportsRichText',
                                                      'type'          => 'SupportsRichTextCheckBox'),
                                            ),
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'null',
                                                            'type' => 'CampaignContactEmailTemplateNamesDropDown')
                                            ),
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'null', 'type' => 'Files'),
                                            ),
                                        ),
                                    )
                                ),
                            ),
                        ),
                    ),
                ),
            );
            return $metadata;
        }

        protected function renderContent()
        {
            $this->registerCopyInfoFromMarketingListScript();
            return parent::renderContent();
        }

        protected function renderAfterFormLayout($form)
        {
            $content = $this->renderHtmlAndTextContentElement($this->model, null, $form);
            return $content;
        }

        protected function renderHtmlAndTextContentElement($model, $attribute, $form)
        {
            $element = new EmailTemplateHtmlAndTextContentElement($model, $attribute , $form);
            if ($form !== null)
            {
                $this->resolveElementDuringFormLayoutRender($element);
            }
            $spinner = ZurmoHtml::tag('span', array('class' => 'big-spinner'), '');
            return ZurmoHtml::tag('div', array('class' => 'email-template-combined-content'), $element->render());
        }

        protected function resolveElementDuringFormLayoutRender(& $element)
        {
            if ($this->alwaysShowErrorSummary())
            {
                $element->editableTemplate = str_replace('{error}', '', $element->editableTemplate);
            }
        }

        protected function alwaysShowErrorSummary()
        {
            return true;
        }

        protected function getNewModelTitleLabel()
        {
            return Zurmo::t('Default', 'Create AutorespondersModuleSingularLabel',
                                                                        LabelUtil::getTranslationParamsForAllModules());
        }

        protected function registerCopyInfoFromMarketingListScript()
        {
            $url           = Yii::app()->createUrl('marketingLists/default/getInfoToCopyToCampaign');
            // Begin Not Coding Standard
            Yii::app()->clientScript->registerScript('copyInfoFromMarketingListScript', "
                $('#Campaign_marketingList_id').live('change', function()
                    {
                       if ($('#Campaign_marketingList_id').val())
                          {
                            $.ajax(
                            {
                                url : '" . $url . "?id=' + $('#Campaign_marketingList_id').val(),
                                type : 'GET',
                                dataType: 'json',
                                success : function(data)
                                {
                                    $('#Campaign_fromName').val(data.fromName);
                                    $('#Campaign_fromAddress').val(data.fromAddress)
                                },
                                error : function()
                                {
                                    //todo: error call
                                }
                            }
                            );
                          }
                    }
                );
            ");
            // End Not Coding Standard
        }
    }
?>