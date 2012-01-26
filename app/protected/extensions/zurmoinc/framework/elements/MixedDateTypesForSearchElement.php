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
     * Displays a date and dateTime filtering input.  Allows for picking a type of filter and sometimes depending on
     * the filter, entering a specific date value.
     */
    class MixedDateTypesForSearchElement extends Element
    {
        /**
         * Render a date JUI widget
         * @return The element's content as a string.
         */
        protected function renderControlEditable()
        {
            $valueTypeid                       = $this->getEditableInputId($this->attribute,   'type');
            $valueFirstDateId                  = $this->getEditableInputId($this->attribute,   'firstDate');
            $firstDateSpanAreaId               = $valueTypeid . '-first-date-area';
            $valueTypesRequiringFirstDateInput = MixedDateTypesSearchFormAttributeMappingRules::
                                                 getValueTypesRequiringFirstDateInput();
            Yii::app()->clientScript->registerScript('mixedDateTypesForSearch' . $valueTypeid, "
                $('#{$valueTypeid}').change( function()
                    {
                        arr = " . CJSON::encode($valueTypesRequiringFirstDateInput) . ";
                        if ($.inArray($(this).val(), arr) != -1)
                        {
                               $('#{$firstDateSpanAreaId}').show();
                            $('#{$valueFirstDateId}').prop('disabled', false);
                        }
                        else
                        {
                            $('#{$firstDateSpanAreaId}').hide();
                            $('#{$valueFirstDateId}').prop('disabled', true);
                        }
                    }
                );
            ");
            $startingDivStyle  = null;
            $valueType         = ArrayUtil::getArrayValue($this->model->{$this->attribute}, 'type');
            if (!in_array($valueType, $valueTypesRequiringFirstDateInput))
            {
                $startingDivStyle = "style='display:none;'";
            }
            $content  = $this->renderEditableValueTypeContent();
            $content .= '<span id="' . $firstDateSpanAreaId . '" ' . $startingDivStyle . '>';
            $content .= '&#160;' . $this->renderEditableFirstDateContent();
            $content .= '</span>';
            return $content;
        }

        protected function renderEditableValueTypeContent()
        {
            $value     = $this->model->{$this->attribute};
            return       CHtml::dropDownList($this->getEditableInputName($this->attribute, 'type'),
                                             ArrayUtil::getArrayValue($value, 'type'),
                                             $this->getValueTypeDropDownArray(),
                                             $this->getEditableValueTypeHtmlOptions());
        }

        protected function renderEditableFirstDateContent()
        {
            $themePath = Yii::app()->baseUrl . '/themes/' . Yii::app()->theme->name;
            $value     = $this->model->{$this->attribute};
            $cClipWidget = new CClipWidget();
            $cClipWidget->beginClip("EditableDateElement");
            $cClipWidget->widget('ext.zurmoinc.framework.widgets.JuiDatePicker', array(
                'attribute'           => $this->attribute,
                'value'               => ArrayUtil::getArrayValue($value, 'firstDate'),
                'language'            => YiiToJqueryUIDatePickerLocalization::getLanguage(),
                'htmlOptions'         => array(
                    'id'              => $this->getEditableInputId($this->attribute, 'firstDate'),
                    'name'            => $this->getEditableInputName($this->attribute, 'firstDate'),
                ),
                'options'             => array(
                    'showOn'          => 'both',
                    'showButtonPanel' => true,
                    'buttonImage'     => $themePath . '/images/jqueryui/calendar.gif',
                    'buttonImageOnly' => true,
                    'dateFormat'      => YiiToJqueryUIDatePickerLocalization::resolveDateFormat(
                                            DateTimeUtil::getLocaleDateFormat()),
                ),
            ));
            $cClipWidget->endClip();
            return $cClipWidget->getController()->clips['EditableDateElement'];
        }

        protected function getEditableValueTypeHtmlOptions()
        {
            $htmlOptions = array(
                'id'   => $this->getEditableInputId($this->attribute,   'type'),
            );
            $htmlOptions['empty']    = Yii::t('Default', '(None)');
            $htmlOptions['disabled'] = $this->getDisabledValue();
            return $htmlOptions;
        }

        protected function getValueTypeDropDownArray()
        {
            return MixedDateTimeTypesSearchFormAttributeMappingRules::getValidValueTypesAndLabels();
        }

        /**
         * Renders the attribute from the model.
         * @return The element's content.
         */
        protected function renderControlNonEditable()
        {
            throw new NotSupportedException();
        }

        protected function renderLabel()
        {
            $label = $this->getFormattedAttributeLabel();
            if ($this->form === null)
            {
                return $label;
            }
            return CHtml::label($label, false);
        }
    }
?>