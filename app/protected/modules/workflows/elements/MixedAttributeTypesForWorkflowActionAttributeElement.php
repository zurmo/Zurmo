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
     * base class for displaying a combination of inputs for an attribute row in a workflow action. An example is a date
     * attribute where you can specifically set the date or can choose a dynamic value from a drop down.
     */
    abstract class MixedAttributeTypesForWorkflowActionAttributeElement extends Element
    {
        abstract protected function renderEditableFirstValueContent();

        abstract protected function renderEditableSecondValueContent();

        /**
         * @return The element's content as a string.
         */
        protected function renderControlEditable()
        {
            $firstValueSpanAreaId         = $this->getFirstValueEditableInputId() . '-first-value-area';
            $secondValueSpanAreaId        = $this->getSecondValueEditableInputId() . '-second-value-area';
            $startingDivStyleFirstValue   = null;
            $startingDivStyleSecondValue  = null;
            if (!$this->shouldDisableSecondValueInputs())
            {
                $startingDivStyleFirstValue = "display:none;";
            }
            else
            {
                $startingDivStyleSecondValue = "display:none;";
            }
            $content  = ZurmoHtml::tag('div', array('id'    => $firstValueSpanAreaId,
                                                    'class' => 'first-value-area',
                                                    'style' => $startingDivStyleFirstValue),
                                                    $this->renderEditableFirstValueContent());
            $content .= ZurmoHtml::tag('div', array('id'    => $secondValueSpanAreaId,
                                                    'class' => 'second-value-area',
                                                    'style' => $startingDivStyleSecondValue),
                                                    $this->renderEditableSecondValueContent());
            return $content;
        }

        /**
         * @return bool
         */
        protected function shouldDisableSecondValueInputs()
        {
            if ($this->getActionAttributeType() != WorkflowActionAttributeForm::TYPE_STATIC &&
                $this->getActionAttributeType() != null)
            {
                return false;
            }
            return true;
        }

        /**
         * @return array
         */
        protected function getHtmlOptionsForFirstValue()
        {
            $htmlOptions = array(
                'id'              => $this->getFirstValueEditableInputId(),
                'name'            => $this->getFirstValueEditableInputName(),
            );
            if (!$this->shouldDisableSecondValueInputs())
            {
                $htmlOptions['disabled'] = 'disabled';
            }
            return $htmlOptions;
        }

        /**
         * @return array
         */
        protected function getHtmlOptionsForSecondValue()
        {
            $htmlOptions = array(
                'id'     => $this->getSecondValueEditableInputId(),
                'name'   => $this->getSecondValueEditableInputName(),
            );
            if ($this->shouldDisableSecondValueInputs())
            {
                $htmlOptions['disabled'] = 'disabled';
            }
            return $htmlOptions;
        }

        /**
         * Renders the attribute from the model.
         * @return The element's content.
         * @throws NotSupportedException
         */
        protected function renderControlNonEditable()
        {
            throw new NotSupportedException();
        }

        /**
         * @return A|string
         */
        protected function renderLabel()
        {
            $label = $this->getFormattedAttributeLabel();
            if ($this->form === null)
            {
                return $label;
            }
            return ZurmoHtml::label($label, false);
        }

        /**
         * @return mixed
         */
        protected function getActionAttributeType()
        {
            return $this->model->type;
        }

        /**
         * Render during the Editable render
         * (non-PHPdoc)
         * @see Element::renderError()
         */
        protected function renderError()
        {
        }

        /**
         * @return string
         */
        protected function getFirstValueEditableInputId()
        {
            return $this->getEditableInputId('value');
        }

        /**
         * @return string The value of 'alternateValue' ensures the ids of the inputs remain different
         */
        protected function getSecondValueEditableInputId()
        {
            return $this->getEditableInputId('alternateValue');
        }

        /**
         * @return string the name is still 'value' because the first and second inputs are used alternatively. One
         * is disabled while the other is not and vice versa.
         */
        protected function getFirstValueEditableInputName()
        {
            return $this->getEditableInputName('value');
        }

        /**
         * @return string
         */
        protected function getSecondValueEditableInputName()
        {
            return $this->getEditableInputName('value');
        }
    }
?>