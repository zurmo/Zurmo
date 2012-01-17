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

    Yii::import('zii.widgets.jui.CJuiDatePicker');
    class JuiDatePicker extends CJuiDatePicker
    {
        /**
         * @return boolean whether this widget is associated with a data model.
         */
        protected function hasModel()
        {
            return $this->model instanceof RedBeanModel && $this->attribute !== null;
        }

        /**
         * This function overrides the run method from CJuiDatePicker and fixes the jQuery issue for the Datepicker showing
         * wrong language in the portlet views popup.
         */
        public function run()
        {
            list($name, $id) = $this->resolveNameID();

            if (isset($this->htmlOptions['id']))
            {
                $id = $this->htmlOptions['id'];
            }
            else
            {
                $this->htmlOptions['id'] = $id;
            }
            if (isset($this->htmlOptions['name']))
            {
                $name = $this->htmlOptions['name'];
            }
            else
            {
                $this->htmlOptions['name'] = $name;
            }

            if ($this->flat === false)
            {
                if ($this->hasModel())
                {
                    echo CHtml::activeTextField($this->model, $this->attribute, $this->htmlOptions);
                }
                else
                {
                    echo CHtml::textField($name, $this->value, $this->htmlOptions);
                }
            }
            else
            {
                if ($this->hasModel())
                {
                    echo CHtml::activeHiddenField($this->model, $this->attribute, $this->htmlOptions);
                    $attribute = $this->attribute;
                    $this->options['defaultDate'] = $this->model->$attribute;
                }
                else
                {
                    echo CHtml::hiddenField($name, $this->value, $this->htmlOptions);
                    $this->options['defaultDate'] = $this->value;
                }

                if (!isset($this->options['onSelect']))
                {
                    $this->options['onSelect']="js:function( selectedDate ) { jQuery('#{$id}').val(selectedDate);}";
                }

                $id = $this->htmlOptions['id'] = $this->htmlOptions['id'].'_container';
                $this->htmlOptions['name'] = $this->htmlOptions['name'].'_container';

                echo CHtml::tag('div', $this->htmlOptions, '');
            }

            $options = CJavaScript::encode($this->options);
            $js = "jQuery('#{$id}').datepicker($options);";

            if ($this->language!='' && $this->language!='en')
            {
                $this->registerScriptFile($this->i18nScriptFile);
                $js = "jQuery(function(){jQuery('#{$id}').datepicker(jQuery.extend({showMonthAfterYear:false}, jQuery.datepicker.regional['{$this->language}'], {$options}));})";
            }

            $cs = Yii::app()->getClientScript();

            if (isset($this->defaultOptions))
            {
                $this->registerScriptFile($this->i18nScriptFile);
                $cs->registerScript(__CLASS__, $this->defaultOptions!==null?'jQuery.datepicker.setDefaults('.CJavaScript::encode($this->defaultOptions).');':''); // Not Coding Standard
            }
            $cs->registerScript(__CLASS__ . '#' . $id, $js);
        }
    }
?>