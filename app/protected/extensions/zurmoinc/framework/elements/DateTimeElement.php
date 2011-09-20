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

    /**
     * Displays a date/time localized
     * display.
     */
    class DateTimeElement extends Element
    {
        /**
         * Render a datetime JUI widget
         * @return The element's content as a string.
         */
        protected function renderControlEditable()
        {
            $themePath = Yii::app()->baseUrl . '/themes/' . Yii::app()->theme->name;
            $value     = DateTimeUtil::convertDbFormattedDateTimeToLocaleFormattedDisplay(
                            $this->model->{$this->attribute});
            $cClipWidget = new CClipWidget();
            $cClipWidget->beginClip("EditableDateTimeElement");
            $cClipWidget->widget('application.extensions.timepicker.EJuiDateTimePicker', array(
                'attribute'  => $this->attribute,
                'language'   => YiiToJqueryUIDatePickerLocalization::getLanguage(),
                'value'      => $value,
                'htmlOptions' => array(
                    'id'              => $this->getEditableInputId(),
                    'name'            => $this->getEditableInputName(),
                ),
                'options'    => array(
                    'stepMinute'      => 5,
                    'timeText'        => yii::t('Default', 'Time'),
                    'hourText'        => yii::t('Default', 'Hour'),
                    'minuteText'      => yii::t('Default', 'Minute'),
                    'secondText'      => yii::t('Default', 'Second'),
                    'currentText'     => yii::t('Default', 'Now'),
                    'closeText'       => yii::t('Default', 'Done'),
                    'showOn'          => 'both',
                    'buttonImage'     => $themePath . '/images/jqueryui/calendar.gif',
                    'buttonImageOnly' => true,
                    'dateFormat'      => YiiToJqueryUIDatePickerLocalization::resolveDateFormat(
                                            DateTimeUtil::getLocaleDateFormat()),
                    'timeFormat'      => YiiToJqueryUIDatePickerLocalization::resolveTimeFormat(
                                            DateTimeUtil::getLocaleTimeFormat()),
                    'ampm'            => DateTimeUtil::isLocaleTimeDisplayedAs12Hours()
                    //Note: swap time / date format is not supported currently by the EJuiDateTimePicker
                ),
            ));
            $cClipWidget->endClip();
            return $cClipWidget->getController()->clips['EditableDateTimeElement'];
        }

        /**
         * Renders the attribute from the model.
         * @return The element's content.
         */
        protected function renderControlNonEditable()
        {
            if($this->model->{$this->attribute} != null)
            {
                $content = Yii::app()->dateFormatter->formatDateTime($this->model->{$this->attribute}, 'short');
                return CHtml::encode($content);
            }
        }
    }
?>
