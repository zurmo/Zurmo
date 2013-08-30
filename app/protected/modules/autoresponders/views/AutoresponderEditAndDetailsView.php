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

    class AutoresponderEditAndDetailsView extends SecuredEditAndDetailsView
    {
        // TODO: @Shoaibi: Low: Refactor this and EmailTemplateEditAndDetailsView
        public static function getDefaultMetadata()
        {
            $metadata = array(
                'global' => array(
                    'toolbar' => array(
                        'elements' => array(
                            array('type'    => 'AutorespondersCancelLink', 'renderType' => 'Edit'),
                            array('type'    => 'SaveButton', 'renderType' => 'Edit'),
                            array('type'    => 'EditLink', 'renderType' => 'Details'),
                            array('type'    => 'AutoresponderDeleteLink'),
                        ),
                    ),
                    'panels' => array(
                        array(
                            'rows' => array(
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'operationType',
                                                                                'type' => 'AutoresponderOperationType'),
                                            ),
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'null',
                                                                        'type' => 'AutoresponderFromOperationDuration'),
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
                                                                        'type' => 'EnableTrackingCheckBox'),
                                            ),
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            // TODO: @Shoaibi: Low: change this to constant after refactoring
                                            'detailViewOnly' => 2, // using 2 here to mean: "do not render on details"
                                            'elements' => array(
                                                array('attributeName' => 'null',
                                                            'type' => 'AutoresponderContactEmailTemplateNamesDropDown')
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

        protected function renderAfterFormLayout($form)
        {
            $content = $this->renderHtmlAndTextContentElement($this->model, null, $form);
            return $content;
        }

        protected function renderAfterFormLayoutForDetailsContent($form = null)
        {
            return $this->renderHtmlAndTextContentElement($this->model, null, $form) .
                                                                                parent::renderAfterFormLayout($form);
        }

        protected function renderHtmlAndTextContentElement($model, $attribute, $form)
        {
            $element = new EmailTemplateHtmlAndTextContentElement($model, $attribute , $form);
            if ($form !== null)
            {
                $this->resolveElementDuringFormLayoutRender($element);
            }
            return ZurmoHtml::tag('div', array('class' => 'autoresponder-combined-content'), $element->render());
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

        protected function shouldDisplayCell($detailViewOnly)
        {
            // TODO: @Shoaibi: Low: change this to constant after refactoring and port to parent.
            if ($detailViewOnly == 2)
            {
                return ($this->renderType != 'Details');// this if would only be true for contactEmailTemplateNamesDropDown.
            }
            return parent::shouldDisplayCell($detailViewOnly);
        }
    }
?>