<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2012 Zurmo Inc.
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
     * Display a drop down.
     */
    class DropDownElement extends Element
    {
        /**
         * Renders the editable dropdown content.
         * @return A string containing the element's content.
         */
        protected function renderControlEditable()
        {
            return $this->form->dropDownList(
                $this->model->{$this->attribute},
                'value',
                $this->getDropDownArray(),
                $this->getEditableHtmlOptions()
            );
        }

        protected function renderLabel()
        {
            if ($this->form === null)
            {
                return $this->getFormattedAttributeLabel();
            }
            $id = $this->getIdForSelectInput();
            return $this->form->labelEx($this->model, $this->attribute, array('for' => $id));
        }

        /**
         * Renders the noneditable dropdown content.
         * Takes the model attribute value and converts it into the proper display value
         * based on the corresponding dropdown display label.
         * @return A string containing the element's content.
         */
        protected function renderControlNonEditable()
        {
            $dropDownModel = $this->model->{$this->attribute};
            $dropDownArray = $this->getDropDownArray();
            return Yii::app()->format->text(ArrayUtil::getArrayValue($dropDownArray, $dropDownModel->value));
        }

        protected function convertDropDownModelsToArrayByIdName($dropDownModels)
        {
            $array = array();
            if (!empty($dropDownModels))
            {
                foreach ($dropDownModels as $dropDownModel)
                {
                    $array[$dropDownModel->id] = $dropDownModel;
                }
            }
            return $array;
        }

        protected function getAddBlank()
        {
            if (ArrayUtil::getArrayValue($this->params, 'addBlank'))
            {
                return true;
            }
            else
            {
                return false;
            }
        }

        public function getIdForSelectInput()
        {
            return $this->getEditableInputId($this->attribute, 'value');
        }

        protected function getNameForSelectInput()
        {
            return $this->getEditableInputName($this->attribute, 'value');
        }

        public function getEditableNameIds()
        {
            return array(
                $this->getIdForSelectInput(),
            );
        }

        protected function getEditableHtmlOptions()
        {
            $htmlOptions = array(
                'name' => $this->getNameForSelectInput(),
                'id'   => $this->getIdForSelectInput(),
            );
            if ($this->getAddBlank())
            {
                $htmlOptions['empty'] = Yii::t('Default', '(None)');
            }
            $htmlOptions['disabled'] = $this->getDisabledValue();
            return $htmlOptions;
        }

        protected function getDropDownArray()
        {
            $dropDownModel = $this->model->{$this->attribute};
            $dataAndLabels    = CustomFieldDataUtil::
                                getDataIndexedByDataAndTranslatedLabelsByLanguage($dropDownModel->data, Yii::app()->language);
            return $dataAndLabels;
        }
    }
?>
