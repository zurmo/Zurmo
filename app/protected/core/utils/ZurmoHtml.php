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
     * Override for any functions that need special handling for the zurmo application.
     */
    class ZurmoHtml extends CHtml
    {
        /**
         * Override CHtml::encode() to avoid double encode,
         * because data are alredy encoded, when stored into database(using HtmlPurifier)
         * @see CHtml::encode()
         */
        public static function encode($text)
        {
            return htmlspecialchars($text, ENT_QUOTES, Yii::app()->charset, false);
        }

        /**
         * Override to handle relation model error summary information.  This information needs to be parsed properly
         * otherwise it will show up as 'Array' for the error text.
         * @see CHtml::errorSummary()
         */
        public static function errorSummary($model, $header = null, $footer = null, $htmlOptions = array())
        {
            $content = '';
            if (!is_array($model))
            {
                $model = array($model);
            }
            if (isset($htmlOptions['firstError']))
            {
                $firstError = $htmlOptions['firstError'];
                unset($htmlOptions['firstError']);
            }
            else
            {
                $firstError = false;
            }
            foreach ($model as $m)
            {
                foreach ($m->getErrors() as $errors)
                {
                    foreach ($errors as $errorOrRelatedError)
                    {
                        if (is_array($errorOrRelatedError))
                        {
                            foreach ($errorOrRelatedError as $relatedError)
                            {
                                if ($relatedError != '')
                                {
                                    $content .= "<li>$relatedError</li>\n";
                                }
                            }
                        }
                        elseif ($errorOrRelatedError != '')
                        {
                            $content .= "<li>$errorOrRelatedError</li>\n";
                        }
                        if ($firstError)
                        {
                            break;
                        }
                    }
                }
            }
            if ($content !== '')
            {
                if ($header === null)
                {
                    $header = '<p>' . Zurmo::t('yii', 'Please fix the following input errors:') . '</p>';
                }
                if (!isset($htmlOptions['class']))
                {
                    $htmlOptions['class'] = parent::$errorSummaryCss;
                }
                return parent::tag('div', $htmlOptions, $header."\n<ul>\n$content</ul>" . $footer);
            }
            else
            {
                return '';
            }
        }

        /**
         * This function overrides the activeRadioButtonList from CHtml to properly call radioButtonList in ZurmoHtml
         */
        public static function activeRadioButtonList($model, $attribute, $data, $htmlOptions = array())
        {
            self::resolveNameID($model, $attribute, $htmlOptions);
            $selection = self::resolveValue($model, $attribute);
            if ($model->hasErrors($attribute))
            {
                self::addErrorCss($htmlOptions);
            }
            $name = $htmlOptions['name'];
            unset($htmlOptions['name']);
            if (array_key_exists('uncheckValue', $htmlOptions))
            {
                $uncheck = $htmlOptions['uncheckValue'];
                unset($htmlOptions['uncheckValue']);
            }
            else
            {
                $uncheck = '';
            }

            if (isset($htmlOptions['id']))
            {
                if (isset($htmlOptions['ignoreIdPrefix']) && $htmlOptions['ignoreIdPrefix'])
                {
                    $hiddenOptions = array('id' => $htmlOptions['id']);
                }
                else
                {
                    $hiddenOptions = array('id' => self::ID_PREFIX . $htmlOptions['id']);
                }
            }
            else
            {
                $hiddenOptions = array('id' => false);
            }
            $hidden = $uncheck !== null ? self::hiddenField($name, $uncheck, $hiddenOptions) : '';
            return $hidden . self::radioButtonList($name, $selection, $data, $htmlOptions);
        }

         /**
          * This function overrides the radioButtonList from CHtml and excepts a new variable which consists of select
          * box to be appended to the label element.
          */
        public static function radioButtonList($name, $select, $data, $htmlOptions = array(),
                                               $dataSelectOption = array())
        {
            $template   =   isset($htmlOptions['template'])?$htmlOptions['template']:'{input} {label}';
            $separator  =   isset($htmlOptions['separator'])?$htmlOptions['separator']:"<br/>\n";
            unset($htmlOptions['template'], $htmlOptions['separator']);

            $labelOptions   =   isset($htmlOptions['labelOptions'])?$htmlOptions['labelOptions']:array();
            unset($htmlOptions['labelOptions']);

            $items  = array();
            $baseID = self::getIdByName($name);
            $id     = 0;
            foreach ($data as $value => $label)
            {
                $checked                =   !strcmp($value, $select);
                $htmlOptions['value']   =   $value;
                $htmlOptions['id']      =   $baseID . '_' . $id++;
                $option                 =   self::radioButton($name, $checked, $htmlOptions);
                $label                  =   self::label($label, $htmlOptions['id'], $labelOptions);
                $selectOption           =   "";
                if (isset($dataSelectOption[$value]))
                {
                    $selectOption       =   str_replace("{bindId}", $htmlOptions['id'], $dataSelectOption[$value]);
                }
                $items[] = strtr($template, array('{input}'    =>  $option,
                                                  '{label}'    =>  $label . $selectOption,
                                                  '{value}'    =>  $value));
            }
            return implode($separator, $items);
        }

        public static function activeCheckBox($model, $attribute, $htmlOptions = array())
        {
            self::resolveNameID($model, $attribute, $htmlOptions);
            if (isset($htmlOptions['disabled']))
            {
                $disabledClass = ' disabled';
            }
            else
            {
                $disabledClass = '';
            }
            if (!isset($htmlOptions['value']))
            {
                $htmlOptions['value'] = 1;
            }
            if (!isset($htmlOptions['checked']) && self::resolveValue($model, $attribute) == $htmlOptions['value'])
            {
                $htmlOptions['checked'] = 'checked';
            }
            self::clientChange('click', $htmlOptions);
            if (array_key_exists('uncheckValue', $htmlOptions))
            {
                $uncheck = $htmlOptions['uncheckValue'];
                unset($htmlOptions['uncheckValue']);
            }
            else
            {
                $uncheck = '0';
            }
            if ($model->{$attribute} == 1)
            {
                $labelClass = ' c_on';
            }
            else
            {
                $labelClass = null;
            }
            $hiddenOptions = isset($htmlOptions['id']) ? array('id' => self::ID_PREFIX . $htmlOptions['id']) : array('id' => false);
            $hidden = $uncheck !== null ? self::hiddenField($htmlOptions['name'], $uncheck, $hiddenOptions) : '';
            return $hidden . parent::tag("label", array("class" => "hasCheckBox" . $labelClass . $disabledClass),
                   self::activeInputField('checkbox', $model, $attribute, $htmlOptions));
        }

        /**
         * Override to add proper styling to checkboxes.
         * @see CHtml::checkBox
         */
        public static function checkBox($name, $checked = false, $htmlOptions = array())
        {
            if ($checked)
            {
                $htmlOptions['checked'] = 'checked';
            }
            else
            {
                unset($htmlOptions['checked']);
            }
            $value = isset($htmlOptions['value']) ? $htmlOptions['value'] : 1;
            self::clientChange('click', $htmlOptions);

            if (array_key_exists('uncheckValue', $htmlOptions))
            {
                $uncheck = $htmlOptions['uncheckValue'];
                unset($htmlOptions['uncheckValue']);
            }
            else
            {
                $uncheck = null;
            }

            if ($uncheck !== null)
            {
                // add a hidden field so that if the radio button is not selected, it still submits a value
                if (isset($htmlOptions['id']) && $htmlOptions['id'] !== false)
                {
                    $uncheckOptions = array('id' => self::ID_PREFIX . $htmlOptions['id']);
                }
                else
                {
                    $uncheckOptions = array('id' => false);
                }
                $hidden = self::hiddenField($name, $uncheck, $uncheckOptions);
            }
            else
            {
                $hidden = '';
            }

            // add a hidden field so that if the checkbox  is not selected, it still submits a value
            if ($checked)
            {
                $labelClass = ' c_on';
            }
            else
            {
                $labelClass = null;
            }
            if (isset($htmlOptions['labelClass']))
            {
                $labelClass .= ' ' . $htmlOptions['labelClass'];
            }
            return $hidden . parent::tag("label", array("class" => "hasCheckBox" . $labelClass), self::inputField('checkbox', $name, $value, $htmlOptions));
        }

        /**
         * Override to support namespacing and unbinding before binding any clientChange click actions.
         * @see CHtml::ajaxLink
         */
        public static function ajaxLink($text, $url, $ajaxOptions = array(), $htmlOptions = array())
        {
            if (!isset($htmlOptions['href']))
            {
                $htmlOptions['href'] = '#';
            }
            $ajaxOptions['url']      = $url;
            $htmlOptions['ajax']     = $ajaxOptions;
            self::clientChange('click', $htmlOptions);
            if (isset($htmlOptions['namespace']))
            {
                unset($htmlOptions['namespace']);
            }
            return self::tag('a', $htmlOptions, $text);
        }

        /**
         * Override to support namespacing.  Namespacing is important because if there is a namespace defined, then whatever
         * binding for the even is occuring, will be first unbinded.  This is important because in an ajax load, you can
         * have things double or triple bound.  This resolves that issue. If you want the binding to have an attempted
         * unbind first, then set the name space.
         * @see CHtml::clientChange();
         */
        protected static function clientChange($event, &$htmlOptions)
        {
            if (!isset($htmlOptions['submit']) && !isset($htmlOptions['confirm']) && !isset($htmlOptions['ajax']))
            {
                return;
            }
            if (isset($htmlOptions['namespace']))
            {
                $namespace = true;
                $event     = $event . '.' . $htmlOptions['namespace'];
                unset($htmlOptions['namespace']);
            }
            else
            {
                $namespace = false;
            }
            if (isset($htmlOptions['live']))
            {
                $live = $htmlOptions['live'];
                unset($htmlOptions['live']);
            }
            else
            {
                $live = self::$liveEvents;
            }
            if (isset($htmlOptions['return']) && $htmlOptions['return'])
            {
                $return = 'return true';
            }
            else
            {
                $return = 'return false';
            }
            if (isset($htmlOptions['on' . $event]))
            {
                $handler = trim($htmlOptions['on' . $event], ';') . ';';
                unset($htmlOptions['on' . $event]);
            }
            else
            {
                $handler = '';
            }

            if (isset($htmlOptions['id']))
            {
                $id = $htmlOptions['id'];
            }
            else
            {
                $id = $htmlOptions['id'] = isset($htmlOptions['name']) ? $htmlOptions['name']: self::ID_PREFIX.self::$count++;
            }
            $cs = Yii::app()->getClientScript();
            $cs->registerCoreScript('jquery');

            if (isset($htmlOptions['submit']))
            {
                $cs->registerCoreScript('yii');
                $request = Yii::app()->getRequest();
                if ($request->enableCsrfValidation && isset($htmlOptions['csrf']) && $htmlOptions['csrf'])
                {
                    $htmlOptions['params'][$request->csrfTokenName] = $request->getCsrfToken();
                }
                if (isset($htmlOptions['params']))
                {
                    $params = CJavaScript::encode($htmlOptions['params']);
                }
                else
                {
                    $params = '{}';
                }
                if ($htmlOptions['submit'] !== '')
                {
                    $url = CJavaScript::quote(self::normalizeUrl($htmlOptions['submit']));
                }
                else
                {
                    $url = '';
                }
                $handler .= "jQuery.yii.submitForm(this, '$url', $params);{$return};";
            }

            if (isset($htmlOptions['ajax']))
            {
                $handler .= self::ajax($htmlOptions['ajax'])."{$return};";
            }
            if (isset($htmlOptions['confirm']))
            {
                $confirm = 'confirm(\''.CJavaScript::quote($htmlOptions['confirm']).'\')';
                if ($handler !== '')
                {
                    $handler = "if ($confirm) {" . $handler . "} else return false;";
                }
                else
                {
                    $handler = "return $confirm;";
                }
            }

            if ($live)
            {
                if ($namespace)
                {
                   $cs->registerScript('Yii.CHtml.#' . $id, "$('body').off('$event', '#$id'); $('body').on('$event', '#$id', function(){{$handler}});");
                }
                else
                {
                    $cs->registerScript('Yii.CHtml.#' . $id, "$('body').on('$event', '#$id', function(){{$handler}});");
                }
            }
            else
            {
                if ($namespace)
                {
                    $cs->registerScript('Yii.CHtml.#' . $id, "$('#$id').off('$event'); $('#$id').on('$event', function(){{$handler}});");
                }
                else
                {
                    $cs->registerScript('Yii.CHtml.#' . $id, "$('#$id').on('$event', function(){{$handler}});");
                }
            }
            unset($htmlOptions['params'],
                  $htmlOptions['submit'],
                  $htmlOptions['ajax'],
                  $htmlOptions['confirm'],
                  $htmlOptions['return'],
                  $htmlOptions['csrf']);
        }

        /**
         * Override to support proper checkbox labeling for when checked.
         * @see CHtml::activeCheckBoxList();
         */
        public static function activeCheckBoxList($model, $attribute, $data, $htmlOptions = array())
        {
            self::resolveNameID($model, $attribute, $htmlOptions);
            $selection = self::resolveValue($model, $attribute);
            if ($model->hasErrors($attribute))
            {
                self::addErrorCss($htmlOptions);
            }
            $name = $htmlOptions['name'];
            unset($htmlOptions['name']);
            if (array_key_exists('uncheckValue', $htmlOptions))
            {
                $uncheck = $htmlOptions['uncheckValue'];
                unset($htmlOptions['uncheckValue']);
            }
            else
            {
                $uncheck = '';
            }
            $hiddenOptions = isset($htmlOptions['id']) ? array('id' => self::ID_PREFIX . $htmlOptions['id']) : array('id' => false);
            $hidden        = $uncheck !== null ? self::hiddenField($name, $uncheck, $hiddenOptions) : '';
            return $hidden . self::checkBoxList($name, $selection, $data, $htmlOptions);
        }

        /**
         * @see CHtml::checkBoxList();
         */
        public static function checkBoxList($name, $select, $data, $htmlOptions = array())
        {
            $template  = isset($htmlOptions['template'])?$htmlOptions['template']:'{input} {label}';
            $separator = isset($htmlOptions['separator'])?$htmlOptions['separator']:"<br/>\n";
            unset($htmlOptions['template'], $htmlOptions['separator']);
            if (substr($name, -2) !== '[]')
            {
                $name .= '[]';
            }
            if (isset($htmlOptions['checkAll']))
            {
                $checkAllLabel     = $htmlOptions['checkAll'];
                $checkAllLast      = isset($htmlOptions['checkAllLast']) && $htmlOptions['checkAllLast'];
            }
            unset($htmlOptions['checkAll'], $htmlOptions['checkAllLast']);

            $labelOptions          = isset($htmlOptions['labelOptions']) ? $htmlOptions['labelOptions']:array();
            unset($htmlOptions['labelOptions']);
            $items                 = array();
            $baseID                = self::getIdByName($name);
            $id                    = 0;
            $checkAll              = true;
            foreach ($data as $value => $label)
            {
                $checked              = !is_array($select) && !strcmp($value, $select) || is_array($select) && in_array($value, $select);
                $checkAll             = $checkAll && $checked;
                $htmlOptions['value'] = $value;
                $htmlOptions['id']    = $baseID . '_' . $id++;
                $option               = self::checkBox($name, $checked, $htmlOptions);
                if (!isset($labelOptions['class']))
                {
                    $labelOptions['class'] = null;
                }
                if ($checked)
                {
                    $labelOptions['class'] . ' c_on';
                }
                $label                = self::label($label, $htmlOptions['id'], $labelOptions);
                $items[]              = strtr($template, array('{input}' => $option, '{label}' => $label));
            }
            if (isset($checkAllLabel))
            {
                $htmlOptions['value'] = 1;
                $htmlOptions['id']    = $id = $baseID . '_all';
                $option   = self::checkBox($id, $checkAll, $htmlOptions);
                $label    = self::label($checkAllLabel, $id, $labelOptions);
                $item     = strtr($template, array('{input}' => $option, '{label}' => $label));
                if ($checkAllLast)
                {
                    $items[] = $item;
                }
                else
                {
                    array_unshift($items, $item);
                }
                $name = strtr($name, array('['=>'\\[',']'=>'\\]')); // Not Coding Standard
                $js   = <<<EOD
    $('#$id').click(function()
    {
        $("input[name='$name']").prop('checked', this.checked);
    });
    $("input[name='$name']").click(function()
    {
        $('#$id').prop('checked', !$("input[name='$name']:not(:checked)").length);
    });
    $('#$id').prop('checked', !$("input[name='$name']:not(:checked)").length);
EOD;
                $cs = Yii::app()->getClientScript();
                $cs->registerCoreScript('jquery');
                $cs->registerScript($id, $js);
            }
            return self::tag('span', array('id' => $baseID), implode($separator, $items));
        }

        /**
         * Override to support proper styling
         * @see CHtml::activeDropDownList();
         */
        public static function activeDropDownList($model, $attribute, $data, $htmlOptions = array())
        {
            static::resolveNameID($model, $attribute, $htmlOptions);
            $selection  = static::resolveValue($model, $attribute);
            $options    = "\n" . static::listOptions($selection, $data, $htmlOptions);
            static::clientChange('change', $htmlOptions);
            if ($model->hasErrors($attribute))
            {
                static::addErrorCss($htmlOptions);
            }
            $multiSelectClass = null;
            if (isset($htmlOptions['multiple']))
            {
                $multiSelectClass .= ' isMultiSelect';
                if (substr($htmlOptions['name'], -2) !== '[]')
                {
                    $htmlOptions['name'] .= '[]';
                }
            }
            $content  = static::tag('span', array('class' => 'select-arrow'), '');
            $content .= static::tag('select', $htmlOptions, $options);
            return static::tag('div', array('class' => 'hasDropDown' . $multiSelectClass), $content);
        }

        /**
         *
         * Override to support proper styling
         * @see CHtml::dropDownList();
         */
        public static function dropDownList($name, $select, $data, $htmlOptions = array())
        {
            $htmlOptions['name'] = $name;
            if (!isset($htmlOptions['id']))
            {
                $htmlOptions['id'] = static::getIdByName($name);
            }
            elseif ($htmlOptions['id'] === false)
            {
                unset($htmlOptions['id']);
            }
            $multiSelectClass = null;
            if (isset($htmlOptions['multiple']))
            {
                $multiSelectClass .= ' isMultiSelect';
            }
            static::clientChange('change', $htmlOptions);
            $options  = "\n" . static::listOptions($select, $data, $htmlOptions);
            $content  = static::tag('span', array('class' => 'select-arrow'), '');
            $content .= static::tag('select', $htmlOptions, $options);
            return static::tag('div', array('class' => 'hasDropDown' . $multiSelectClass), $content);
        }

        /**
         * Return a label wrapped in span
         * @param $label label text
         * @param $class class to be applied to span wrapper, defaults to z-label
         * @return string wrapped label
         */
        public static function wrapLabel($label, $class = 'z-label')
        {
            return static::tag('span', array('class' => $class), $label);
        }

        /**
         * Returns a link wrapped in standard tags
         * @param $label link text
         * @return string wrapped link
         */
        public static function wrapLink($label)
        {
            return static::span('z-spinner') . static::span('z-icon') . static::wrapLabel($label);
        }

        /**
         * Return a span tag with specified class
         * @param $class name of css class to apply
         * @return string span tag
         */
        public static function span($class)
        {
            return static::tag('span', array('class' => $class), null);
        }

        /**
         * @param string $innerContent
         * @param string $content
         * @param null|string $class
         */
        public static function resolveDivWrapperForContent($innerContent, & $content, $class = null)
        {
            if ($class != null)
            {
                $htmlOptions = array('class' => $class);
            }
            else
            {
                $htmlOptions = array();
            }
            if ($innerContent != null)
            {
                $content .= ZurmoHtml::tag('div', $htmlOptions, $innerContent);
            }
        }
    }
?>