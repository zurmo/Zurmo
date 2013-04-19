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
     * View to display to users upon first login.  Allows them to confirm their timezone.
     */
    class UserTimeZoneConfirmationView extends EditView
    {
        public static function getDefaultMetadata()
        {
            $metadata = array(
                'global' => array(
                    'toolbar' => array(
                        'elements' => array(
                            array('type' => 'SaveButton'),
                        ),
                    ),
                    'panelsDisplayType' => FormLayout::PANELS_DISPLAY_TYPE_ALL,
                    'panels' => array(
                        array(
                            'rows' => array(
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'timeZone', 'type' => 'TimeZoneStaticDropDown'),
                                            ),
                                        ),
                                    )
                                ),
                            ),
                        ),
                    ),
                ),
            );
            return $metadata;
        }

        public function renderAfterFormLayout($form)
        {
           $this->renderScriptsContent();
        }

        protected function renderScriptsContent()
        {
            return Yii::app()->clientScript->registerScript('timeZoneSelectOptions', "
             var offset = (new Date()).getTimezoneOffset();

             var timezones =
             {
             '-12': 'Pacific/Kwajalein',
             '-11': 'Pacific/Samoa',
             '-10': 'Pacific/Honolulu',
             '-9': 'America/Juneau',
             '-8': 'America/Los_Angeles',
             '-7': 'America/Denver',
             '-6': 'America/Chicago',
             '-5': 'America/New_York',
             '-4': 'America/Caracas',
             '-3.5': 'America/St_Johns',
             '-3': 'America/Argentina/Buenos_Aires',
             '-2': 'Atlantic/Azores',
             '-1': 'Atlantic/Azores',
             '0': 'Europe/London',
             '1': 'Europe/Paris',
             '2': 'Europe/Helsinki',
             '3': 'Europe/Moscow',
             '3.5': 'Asia/Tehran',
             '4': 'Asia/Baku',
             '4.5': 'Asia/Kabul',
             '5': 'Asia/Karachi',
             '5.5': 'Asia/Kolkata',
             '6': 'Asia/Colombo',
             '7': 'Asia/Bangkok',
             '8': 'Asia/Singapore',
             '9': 'Asia/Tokyo',
             '9.5': 'Australia/Darwin',
             '10': 'Pacific/Guam',
             '11': 'Asia/Magadan',
             '12': 'Asia/Kamchatka'
            };
            var userLocalTimeZone = timezones[-offset / 60];
            $('#UserTimeZoneConfirmationForm_timeZone_value option').each(function()
            {
               if ($(this).val() == userLocalTimeZone)
               {
                   $('#UserTimeZoneConfirmationForm_timeZone_value').val(userLocalTimeZone);
                   return false;
               }
            });
         ");
        }
    }
?>
