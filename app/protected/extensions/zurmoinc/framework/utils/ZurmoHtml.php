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
     * Override for any functions that need special handling for the zurmo application.
     */
    class ZurmoHtml extends CHtml
    {
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
                    $header = '<p>' . Yii::t('yii', 'Please fix the following input errors:') . '</p>';
                }
                if (!isset($htmlOptions['class']))
                {
                    $htmlOptions['class'] = CHtml::$errorSummaryCss;
                }
                return CHtml::tag('div', $htmlOptions, $header."\n<ul>\n$content</ul>" . $footer);
            }
            else
            {
                return '';
            }
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

            $items = array();
            $baseID = self::getIdByName($name);
            $id = 0;
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
                                                  '{label}'    =>  $label . $selectOption));
            }
            return implode($separator, $items);
        }
    }
?>