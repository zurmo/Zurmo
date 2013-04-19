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

            if (!isset($this->options['currentText']))
            {
                $this->options['currentText'] = ZurmoHtml::wrapLabel(Zurmo::t('Core', 'Now'));
            }

            if (!isset($this->options['closeText']))
            {
                $this->options['closeText'] = ZurmoHtml::wrapLabel(Zurmo::t('Core', 'Done'));
            }

            if ($this->flat === false)
            {
                if ($this->hasModel())
                {
                    echo ZurmoHtml::activeTextField($this->model, $this->attribute, $this->htmlOptions);
                }
                else
                {
                    echo ZurmoHtml::textField($name, $this->value, $this->htmlOptions);
                }
            }
            else
            {
                if ($this->hasModel())
                {
                    echo ZurmoHtml::activeHiddenField($this->model, $this->attribute, $this->htmlOptions);
                    $attribute = $this->attribute;
                    $this->options['defaultDate'] = $this->model->$attribute;
                }
                else
                {
                    echo ZurmoHtml::hiddenField($name, $this->value, $this->htmlOptions);
                    $this->options['defaultDate'] = $this->value;
                }

                if (!isset($this->options['onSelect']))
                {
                    $this->options['onSelect']="js:function( selectedDate ) { jQuery('#{$id}').val(selectedDate);}"; // Not Coding Standard
                }

                $id = $this->htmlOptions['id'] = $this->htmlOptions['id'].'_container';
                $this->htmlOptions['name'] = $this->htmlOptions['name'].'_container';

                echo ZurmoHtml::tag('div', $this->htmlOptions, '');
            }

            $options = CJavaScript::encode($this->options);
            $js = "jQuery('#{$id}').datepicker($options);";

            if ($this->language != '' && $this->language != 'en')
            {
                $this->registerScriptFile($this->i18nScriptFile);
                $js = "jQuery(function(){jQuery('#{$id}').datepicker(jQuery.extend({showMonthAfterYear:false}, jQuery.datepicker.regional['{$this->language}'], {$options}));})";
            }

            $cs = Yii::app()->getClientScript();

            if (isset($this->defaultOptions))
            {
                $this->registerScriptFile($this->i18nScriptFile);
                $cs->registerScript(__CLASS__,     $this->defaultOptions !== null?'jQuery.datepicker.setDefaults('.CJavaScript::encode($this->defaultOptions).');':'');
            }
            $cs->registerScript(__CLASS__. '#' . $id, $js);
        }
    }
?>