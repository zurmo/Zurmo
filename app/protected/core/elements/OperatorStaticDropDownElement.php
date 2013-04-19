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
     * Class used by reporting or workflow to show available operator types in a dropdown.
     */
    class OperatorStaticDropDownElement extends DataFromFormStaticDropDownFormElement
    {
        protected function getEditableHtmlOptions()
        {
            $htmlOptions = parent::getEditableHtmlOptions();
            if (isset($htmlOptions['class']))
            {
                $htmlOptions['class'] .= ' operatorType';
            }
            else
            {
                $htmlOptions['class']  = 'operatorType';
            }
            return $htmlOptions;
        }

        protected function renderControlEditable()
        {
            $content = parent::renderControlEditable();
            return $content;
        }

        protected function getDataAndLabelsModelPropertyName()
        {
            return 'getOperatorValuesAndLabels';
        }

        public static function getValueTypesRequiringFirstInput()
        {
            return array(OperatorRules::TYPE_EQUALS,
                         OperatorRules::TYPE_DOES_NOT_EQUAL,
                         OperatorRules::TYPE_GREATER_THAN_OR_EQUAL_TO,
                         OperatorRules::TYPE_LESS_THAN_OR_EQUAL_TO,
                         OperatorRules::TYPE_GREATER_THAN,
                         OperatorRules::TYPE_LESS_THAN,
                         OperatorRules::TYPE_ONE_OF,
                         OperatorRules::TYPE_BETWEEN,
                         OperatorRules::TYPE_STARTS_WITH,
                         OperatorRules::TYPE_ENDS_WITH,
                         OperatorRules::TYPE_CONTAINS,
                         OperatorRules::TYPE_BECOMES,
                         OperatorRules::TYPE_WAS,
                         OperatorRules::TYPE_BECOMES_ONE_OF,
                         OperatorRules::TYPE_WAS_ONE_OF,
                        );
        }

        public static function getValueTypesRequiringSecondInput()
        {
            return array(OperatorRules::TYPE_BETWEEN);
        }

        public static function registerOnLoadAndOnChangeScript()
        {
            Yii::app()->clientScript->registerScript('operatorOnLoadAndOnChangeScript', "
                $('.operatorType').live('change', function()
                    {
                        arr  = " . CJSON::encode(self::getValueTypesRequiringFirstInput()) . ";
                        arr2 = " . CJSON::encode(self::getValueTypesRequiringSecondInput()) . ";
                        var firstValueArea  = $(this).parent().parent().parent().find('.value-data').find('.first-value-area');
                        var secondValueArea = $(this).parent().parent().parent().find('.value-data').find('.second-value-area');
                        if ($.inArray($(this).val(), arr) != -1)
                        {
                            firstValueArea.show();
                            firstValueArea.find(':input, select').prop('disabled', false);
                        }
                        else
                        {
                            firstValueArea.hide();
                            firstValueArea.find(':input, select').prop('disabled', true);
                        }
                        if ($.inArray($(this).val(), arr2) != -1)
                        {
                            secondValueArea.show();
                            secondValueArea.find(':input, select').prop('disabled', false);
                        }
                        else
                        {
                            secondValueArea.hide();
                            secondValueArea.find(':input, select').prop('disabled', true);
                        }
                        arr  = " . CJSON::encode(static::getValuesRequiringMultiSelect()) . ";
                        if (!$(this).hasClass('alwaysMultiple'))
                        {
                            if ($.inArray($(this).val(), arr) != -1)
                            {
                                var newName = $(this).parent().parent().parent().find('.value-data')
                                              .find('.flexible-drop-down').attr('name') + '[]';
                                $(this).parent().parent().parent().find('.value-data').find('.flexible-drop-down')
                                .attr('multiple', 'multiple').addClass('multiple').addClass('ignore-style')
                                .attr('name', newName);
                            }
                            else
                            {
                                var newName = $(this).parent().parent().parent().find('.value-data')
                                              .find('.flexible-drop-down').attr('name');
                                if (newName != undefined)
                                {
                                    $(this).parent().parent().parent().find('.value-data').find('.flexible-drop-down')
                                    .prop('multiple', false).removeClass('multiple').removeClass('ignore-style')
                                    .attr('name', newName.replace('[]', ''));
                                }
                            }
                        }
                    }
                );
            ");
        }

        public static function getValuesRequiringMultiSelect()
        {
            return array(OperatorRules::TYPE_ONE_OF,
                OperatorRules::TYPE_BECOMES_ONE_OF,
                OperatorRules::TYPE_WAS_ONE_OF
            );
        }
    }
?>