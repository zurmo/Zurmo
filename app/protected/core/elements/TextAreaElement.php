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
     * Display the text area input box.
     */
    class TextAreaElement extends Element
    {
        protected $rows    = 6;

        protected $cols    = 50;

        /**
         * Override from parent class in order to
         * accomodate the 'wide' param option.
         * @return The element's content.
         */
        protected function renderEditable()
        {
            $data = array();
            $data['label'] = $this->renderLabel();
            $data['content'] = $this->renderControlEditable();
            $data['error'] = $this->renderError();
            $data['colspan'] = ArrayUtil::getArrayValue($this->params, 'wide') ? 3 : 1;
            return $this->resolveContentTemplate($this->editableTemplate, $data);
        }

        /**
         * Render A text area with X rows and Y columns.
         */
        protected function renderControlEditable()
        {
            assert('empty($this->model->{$this->attribute}) || is_string($this->model->{$this->attribute}) || is_integer($this->model->{$this->attribute})');
            $htmlOptions             = array('encode' => false);
            $htmlOptions['id']       = $this->getEditableInputId();
            $htmlOptions['name']     = $this->getEditableInputName();
            $htmlOptions['rows']     = $this->getRows();
            $htmlOptions['cols']     = $this->getCols();
            return $this->form->textArea($this->model, $this->attribute, $htmlOptions);
        }

        /**
         * Render the text area as a non-editable display
         * @return The element's content.
         */
        protected function renderControlNonEditable()
        {
            return Yii::app()->format->ntext($this->model->{$this->attribute});
        }

        protected function getRows()
        {
            if (isset($this->params['rows']))
            {
                return $this->params['rows'];
            }
            return $this->rows;
        }

        protected function getCols()
        {
            if (isset($this->params['cols']))
            {
                return $this->params['cols'];
            }
            return $this->cols;
        }
    }
?>
