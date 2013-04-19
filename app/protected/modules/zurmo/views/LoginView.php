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

    class LoginView extends View
    {
        private $controller;
        private $formModel;

        public function __construct(CController $controller, CFormModel $formModel, $extraHeaderContent = null)
        {
            assert('is_string($extraHeaderContent) || $extraHeaderContent == null');
            $this->controller         = $controller;
            $this->formModel          = $formModel;
            $this->extraHeaderContent = $extraHeaderContent;
        }

        protected function renderContent()
        {
            list($form, $formStart) = $this->controller->renderBeginWidget(
                'ZurmoActiveForm',
                array(
                    'id'                   => 'login-form',
                    'enableAjaxValidation' => true,
                    'clientOptions' => array(
                        'validateOnSubmit' => true,
                        'validateOnChange' => false,
                        'beforeValidate'   => 'js:beforeValidateAction',
                        'afterValidate'    => 'js:afterValidateAction',
                    ),
                )
            );
            $usernameLabel      = $form->label        ($this->formModel, 'username');
            $usernameTextField  = $form->textField    ($this->formModel, 'username');
            $usernameError      = $form->error        ($this->formModel, 'username');
            $passwordLabel      = $form->label        ($this->formModel, 'password');
            $passwordField      = $form->passwordField($this->formModel, 'password');
            $passwordError      = $form->error        ($this->formModel, 'password');
            $rememberMeCheckBox = $form->checkBox     ($this->formModel, 'rememberMe');
            $rememberMeLabel    = $form->label        ($this->formModel, 'rememberMe');
            $rememberMeError    = $form->error        ($this->formModel, 'rememberMe');
            $element            = new SaveButtonActionElement($this->controller->getId(),
                                                              $this->controller->getModule()->getId(),
                                                              null,
                                                              array('htmlOptions' => array('name'   => 'Login',
                                                                                           'id'     => 'Login'),
                                                                      'label'     => Zurmo::t('ZurmoModule', 'Sign in')));
            $submitButton        = $element->render();
            $fieldsRequiredLabel = Zurmo::t('ZurmoModule', 'Fields with') . ' <span class="required">*</span> ' .
                                   Zurmo::t('ZurmoModule', 'are required.');
            $formEnd             = $this->controller->renderEndWidget();

            $content  = $this->extraHeaderContent;
            $content .= "<div class=\"form\">$formStart"                                            .
                       "<div>$usernameLabel$usernameTextField$usernameError</div>"                 .
                       "<div>$passwordLabel$passwordField$passwordError</div>"                     .
                       "<div class=\"remember-me\">$rememberMeCheckBox$rememberMeLabel$rememberMeError</div>"   .
                       //"<div class=\"clearfix\">$rememberMeLabel$rememberMeError</div>"                               .
                       "<div>$submitButton</div>"                            .
                       "$formEnd</div>";

            Yii::app()->clientScript->registerScript('submitLoginFormOnKeyPressEnterOnPassword', "
                $('#LoginForm_password').keypress(function(e)
                {
                    c = e.which ? e.which : e.keyCode;
                    if (c == 13)
                    {
                        $(this).closest('form').submit();
                    }
                });
            ", CClientScript::POS_END);
            return $content;
        }
    }
?>
