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

    /**
     * Base element for working with durations
     */
    abstract class DurationElement extends Element
    {
        protected $intervalAttributeName = 'durationInterval';

        protected $signAttributeName     = 'durationSign';

        protected $typeAttributeName     = 'durationType';

        protected function renderControlEditable()
        {
            $content  = $this->renderEditableDurationIntervalTextField() . "\n";
            $content .= $this->renderEditableDurationTypeDropDownField() . "\n";
            $cssClass = 'twoFields';
            if ($this->signAttributeName != null)
            {
                $content .= $this->renderEditableDurationSignDropDownField() . "\n";
                $cssClass = 'threeFields';
            }
            $errorId  = $this->getEditableInputId($this->intervalAttributeName);
            $content .= $this->form->error($this->model, $this->intervalAttributeName, array('inputID' => $errorId), true, true);
            $content  = $this->resolveEditableWrapper($cssClass, $content);
            return $content;
        }

        protected function resolveEditableWrapper($cssClass, $content)
        {
            return ZurmoHtml::tag('div', array('class' => 'operation-duration-fields ' . $cssClass), $content);
        }

        protected function renderEditableDurationIntervalTextField()
        {
            $id = $this->getEditableInputId($this->intervalAttributeName);
            $htmlOptions = array(
                'name'     => $this->getEditableInputName($this->intervalAttributeName),
                'id'       => $id,
                'disabled' => $this->getDisabledValue(),
            );
            return $this->form->textField($this->model, $this->intervalAttributeName, $htmlOptions);
        }

        protected function renderEditableDurationSignDropDownField()
        {
            $dropDownArray = $this->getDurationSignDropDownArray();
            $id = $this->getEditableInputId($this->signAttributeName);
            $htmlOptions = array(
                'name'     => $this->getEditableInputName($this->signAttributeName),
                'id'       => $id,
                'disabled' => $this->getDisabledValue(),
            );
            return $this->form->dropDownList($this->model, $this->signAttributeName, $dropDownArray, $htmlOptions);
        }

        protected function renderEditableDurationTypeDropDownField()
        {
            $dropDownArray = $this->getDurationTypeDropDownArray();
            $id = $this->getEditableInputId($this->typeAttributeName);
            $htmlOptions = array(
                'name'     => $this->getEditableInputName($this->typeAttributeName),
                'id'       => $id,
                'disabled' => $this->getDisabledValue(),
            );
            return $this->form->dropDownList($this->model, $this->typeAttributeName, $dropDownArray, $htmlOptions);
        }

        protected function getDurationSignDropDownArray()
        {
            return array(TimeDurationUtil::DURATION_SIGN_POSITIVE => Zurmo::t('WorkflowsModule', 'After'),
                         TimeDurationUtil::DURATION_SIGN_NEGATIVE => Zurmo::t('WorkflowsModule', 'Before'));
        }

        protected function getDurationTypeDropDownArray()
        {
            return TimeDurationUtil::getValueAndLabels();
        }

        protected function renderControlNonEditable()
        {
            throw new NotImplementedException();
        }

        protected function renderError()
        {
        }

        protected function renderLabel()
        {
            if ($this->form === null)
            {
                return $this->getFormattedAttributeLabel();
            }
            $id = $this->getEditableInputId($this->intervalAttributeName);
            return $this->form->labelEx($this->model, $this->intervalAttributeName, array('for' => $id));
        }

        protected function getFormattedAttributeLabel()
        {
            return Yii::app()->format->text($this->model->getAttributeLabel($this->intervalAttributeName));
        }
    }
?>