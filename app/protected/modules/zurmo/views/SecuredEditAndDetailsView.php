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
     * Security view for edit and details view.
     * Used to manipulate elements for a form layout
     * based on rights/permissions of the current user
     */
    abstract class SecuredEditAndDetailsView extends EditAndDetailsView
    {
        /**
         * Override to handle security/access resolution on specific elements.
         */
        protected function resolveElementInformationDuringFormLayoutRender(& $elementInformation)
        {
            if ($this->renderType == 'Edit')
            {
                FormLayoutSecurityUtil::resolveElementForEditableRender(
                    $this->model, $elementInformation, Yii::app()->user->userModel);
            }
            elseif ($this->renderType == 'Details')
            {
                FormLayoutSecurityUtil::resolveElementForNonEditableRender(
                    $this->model, $elementInformation, Yii::app()->user->userModel);
            }
            else
            {
                throw new NotSupportedException();
            }
        }

        protected function shouldRenderToolBarElement($element, $elementInformation)
        {
            assert('$element instanceof ActionElement');
            assert('is_array($elementInformation)');
            if (!parent::shouldRenderToolBarElement($element, $elementInformation))
            {
                return false;
            }
            return ActionSecurityUtil::canCurrentUserPerformAction($element->getActionType(), $this->model);
        }

        protected function renderRightSideFormLayoutForEdit($form)
        {
            assert('$form instanceof ZurmoActiveForm');
            $content = parent::renderRightSideFormLayoutForEdit($form);
            if($this->getModel() instanceof OwnedSecurableItem)
            {
                $content .= Yii::t('Default', 'Rights and Permissions') . "\n";
                $element  = new UserElement($this->getModel(), 'owner', $form);
                $element->editableTemplate = '{label}{content}{error}';
                $content .= $element->render();
                $element  = new DerivedExplicitReadWriteModelPermissionsElement($this->getModel(), 'null', $form);
                $element->editableTemplate = '{label}{content}{error}';
                $content .= $element->render();
            }
            return $content;
        }

        protected function renderAfterFormLayoutForDetailsContent()
        {
            $content = parent::renderAfterFormLayoutForDetailsContent();
            if($this->getModel() instanceof OwnedSecurableItem)
            {
                if($content != null)
                {
                    $content .= '<br/>';
                }
                $element  = new DateTimeModifiedUserElement($this->getModel(), 'null');
                $element->nonEditableTemplate = '{label} {content}';
                $content .= $element->render();
                $content .= '&#160;|&#160;';
                $element  = new DateTimeCreatedUserElement($this->getModel(), 'null');
                $element->nonEditableTemplate = '{label} {content}';
                $content .= $element->render();
                $content .= '<br/>';
                $element  = new UserElement($this->getModel(), 'owner');
                $element->nonEditableTemplate = '{label} {content}';
                $content .= $element->render();
                $content .= '&#160;|&#160;';
                $element  = new DerivedExplicitReadWriteModelPermissionsElement($this->getModel(), 'null');
                $element->nonEditableTemplate = '{label} {content}';
                $content .= $element->render();
            }
            return $content;
        }
    }
?>