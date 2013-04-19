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
     * View to show an input to add a primary email to a contact.  This is used when a user tries to compose email
     * to a contact that does not yet have a primary email address
     */
    class ContactRequiresPrimaryEmailFirstModalView extends EditView
    {
        /**
         * Since this edit view shows in a modal, we do not want the wrapper div to display as it is unneeded.
         * @var boolean
         */
        protected $wrapContentInWrapperDiv = false;

        protected function renderTitleContent()
        {
            return null;
        }

        public static function getDefaultMetadata()
        {
            $metadata = array(
                'global' => array(
                    'toolbar' => array(
                        'elements' => array(
                            array('type'  => 'SaveButton'),
                        ),
                    ),
                    'panelsDisplayType' => FormLayout::PANELS_DISPLAY_TYPE_ALL,
                    'panels' => array(
                        array(
                            'rows' => array(
                               array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'emailAddress', 'type' => 'Text'),
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

        protected function renderOperationDescriptionContent()
        {
            $highlight = ZurmoHtml::tag('em', array(),
                         Zurmo::t('EmailMessagesModule', 'There is no primary email associated with {contactName}. Please add one to continue.',
                                 array('{contactName}' => strval($this->model))));
            $message  = ZurmoHtml::tag('strong', array(), $highlight);
            return ZurmoHtml::wrapLabel($message, 'operation-description');
        }

        protected function resolveActiveFormAjaxValidationOptions()
        {
            $afterValidateAjax = $this->renderConfigSaveAjax(
                static::getFormId(),
                $this->moduleId,
                $this->controllerId,
                'populateContactEmailBeforeCreating');
            return array(
                'enableAjaxValidation' => true,
                'clientOptions' => array(
                    'beforeValidate'    => 'js:beforeValidateAction',
                    'afterValidate'     => 'js:afterValidateAjaxAction',
                    'validateOnSubmit'  => true,
                    'validateOnChange'  => false,
                    'inputContainer'    => 'td',
                    'afterValidateAjax' => $afterValidateAjax,
                )
            );
        }

        protected function renderConfigSaveAjax($formName, $moduleId, $controllerId, $actionSave)
        {
            return ZurmoHtml::ajax(array(
                    'type'   => 'POST',
                    'data'   => 'js:$("#' . $formName . '").serialize()',
                    'url'    => Yii::app()->createUrl($moduleId . '/' . $controllerId . '/' . $actionSave, GetUtil::getData()),
                    'update' => '#modalContainer',
                ));
        }
    }
?>