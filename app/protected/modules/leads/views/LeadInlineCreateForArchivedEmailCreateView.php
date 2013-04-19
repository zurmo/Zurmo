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
     * An inline edit view for a creating a lead to match an archived email to.
     *
     */
    class LeadInlineCreateForArchivedEmailCreateView extends ContactInlineCreateForArchivedEmailCreateView
    {
        public static function getDefaultMetadata()
        {
            $metadata = array(
                'global' => array(
                    'toolbar' => array(
                        'elements' => array(
                            array('type' => 'CancelLinkForEmailsMatchingList', 'htmlOptions' => array (
                                    'id'    => 'eval:"createLeadCancel" . $this->uniqueId',
                                    'name'  => 'eval:"createLeadCancel" . $this->uniqueId',
                                    'class' => 'eval:"createLeadCancel"')),
                            array('type' => 'SaveButton', 'htmlOptions' => array (
                                    'id'   => 'eval:"save-lead-" . $this->uniqueId',
                                    'name' => 'eval:"save-lead-" . $this->uniqueId')),
                        ),
                    ),
                    'derivedAttributeTypes' => array(
                        'TitleFullName',
                        'LeadStateDropDown',
                    ),
                    'nonPlaceableAttributeNames' => array(
                        'title',
                        'firstName',
                        'lastName',
                        'state',
                        'account',
                        'owner',
                    ),
                    'panelsDisplayType' => FormLayout::PANELS_DISPLAY_TYPE_ALL,
                    'panels' => array(
                        array(
                            'rows' => array(
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'null', 'type' => 'TitleFullName'),
                                            ),
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'null', 'type' => 'LeadStateDropDown'),
                                            ),
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'companyName', 'type' => 'Text'),
                                            ),
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'officePhone', 'type' => 'Phone'),
                                            ),
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'primaryEmail', 'type' => 'EmailAddressInformation'),
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

        public function getFormName()
        {
            return "lead-inline-create-form-" . $this->uniqueId;
        }

        /**
         * Override to set 'Lead' as input prefix. This way it will not collide with existing contact inputs from the
         * contact create form.
         * (non-PHPdoc)
         * @see ContactInlineCreateForArchivedEmailCreateView::resolveElementInformationDuringFormLayoutRender()
         */
        protected function resolveElementInformationDuringFormLayoutRender(& $elementInformation)
        {
            $elementInformation['inputPrefix']  = array('Lead', $this->uniqueId);
        }

        public static function getDisplayDescription()
        {
            return Zurmo::t('LeadsModule', 'Matching Archived Emails');
        }

        public function renderAfterFormLayout($form)
       {
           $this->renderScriptsContent();
        }

        protected function renderScriptsContent()
        {
            return Yii::app()->clientScript->registerScript('LeadInlineCreateCollapseActions', "
                        $('.createLeadCancel').each(function()
                        {
                            $('.createLeadCancel').live('click', function()
                            {
                                $(this).parentsUntil('.email-archive-item').find('.LeadInlineCreateForArchivedEmailCreateView').hide();
                                $(this).closest('.email-archive-item').closest('td').removeClass('active-panel')
                                .find('.z-action-link-active').removeClass('z-action-link-active');
                            });
                        });");
        }

        protected function renderConfigSaveAjax($formName)
        {
            // Begin Not Coding Standard
            return ZurmoHtml::ajax(array(
                    'type' => 'POST',
                    'data' => 'js:$("#' . $formName . '").serialize()',
                    'url'  =>  $this->getValidateAndSaveUrl(),
                    'update' => '#' . $this->uniquePageId,
                    'complete' => "function(XMLHttpRequest, textStatus){
                    $('#wrapper-" . $this->uniqueId . "').parent().parent().parent().remove();
                    $('#" . self::getNotificationBarId() . "').jnotifyAddMessage(
                                       {
                                          text: '" . Zurmo::t('LeadsModule', 'Created LeadsModuleSingularLabel successfully', LabelUtil::getTranslationParamsForAllModules()) . "',
                                          permanent: false,
                                          showIcon: true,
                                       });
                    if ($('.email-archive-item').length==0)
                    {
                        window.location.reload()
                    }
                    }",
                ));
            // End Not Coding Standard
        }
    }
?>
