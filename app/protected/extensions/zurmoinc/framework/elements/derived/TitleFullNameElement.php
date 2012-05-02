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
     * Display the fullName of a person
     */
    class TitleFullNameElement extends Element implements DerivedElementInterface
    {
        protected function renderControlEditable()
        {
            $content  = '<div class="hasParallelFields">';
            $content .= $this->renderEditableSalutationContent();
            $content .= $this->renderEditableNameTextField($this->model, $this->form, 'firstName', true). "\n";
            $content .= $this->renderEditableNameTextField($this->model, $this->form, 'lastName', true) . "\n";
            $content .= '</div>';
            return $content;
        }

        protected function renderEditableSalutationContent()
        {
                $params                    = array('addBlank' => true);
                $params['inputPrefix']     = $this->resolveInputPrefix();
                $element                   = new DropDownElement($this->model, 'title', $this->form, $params);
                $element->editableTemplate = '{content}{error}';
                return CHtml::tag('div', array('class' => 'overlay-label-field fifth'), $element->render());
        }

        protected function renderEditableNameTextField($model, $form, $attribute)
        {
            $id          = $this->getEditableInputId($attribute);
            $htmlOptions = array(
                'name' => $this->getEditableInputName($attribute),
                'id'   => $id,
            );
            $label       = $form->labelEx  ($model, $attribute, array('for'   => $id));
            $textField   = $form->textField($model, $attribute, $htmlOptions);
            $error       = $form->error    ($model, $attribute);
            if($model->$attribute != null)
            {
                 $label = null;
            }
            return CHtml::tag('div', array('class' => 'overlay-label-field twoFifths'), $label . $textField . $error);
        }

        protected function renderError()
        {
        }

        /**
         * Render the full name as a non-editable display
         * @return The element's content.
         */
        protected function renderControlNonEditable()
        {
            assert('$this->attribute == "null"');
            assert('$this->model instanceof Person || $this->model instanceof User');
            $title = null;
            if ($this->model->title != null && $this->model->title->value != null)
            {
                $titleDataAndLabels =  $this->getTitleDropDownArray();

                if (isset($titleDataAndLabels[$this->model->title->value]))
                {
                    $titleLabel = $titleDataAndLabels[$this->model->title->value];
                }
                else
                {
                    $titleLabel = $this->model->title;
                }
                $title .= Yii::app()->format->text($titleLabel);
                $title .= ' ';
            }
            return Yii::app()->format->text($title . $this->model);
        }

        protected function renderLabel()
        {
            return $this->resolveNonActiveFormFormattedLabel($this->getFormattedAttributeLabel());
        }

        protected function getFormattedAttributeLabel()
        {
            return Yii::app()->format->text(Yii::app()->format->text(Yii::t('Default', 'Name')));
        }

        public static function getDisplayName()
        {
            return Yii::t('Default', 'Title/First/LastName');
        }

        /**
         * Get the attributeNames of attributes used in
         * the derived element.
         * @return array of model attributeNames used.
         */
        public static function getModelAttributeNames()
        {
            return array(
                'title',
                'firstName',
                'lastName',
            );
        }

        protected function getElementViewMetadata()
        {
            return array(
                array('attributeName' => 'firstName', 'type' => 'Text'),
                array('attributeName' => 'lastName', 'type' => 'Text'),
            );
        }

        protected function getTitleDropDownArray()
        {
            $dropDownModel = $this->model->title;
            $dataAndLabels    = CustomFieldDataUtil::
                                getDataIndexedByDataAndTranslatedLabelsByLanguage($dropDownModel->data, Yii::app()->language);
            return $dataAndLabels;
        }
    }
?>