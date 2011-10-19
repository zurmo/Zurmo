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
     * Some localization in Jquery UI does not match the
     * Yii localization.
     *
     */
    class YiiToJqueryUIDatePickerLocalization
    {
        /**
         * Use this function when mapping to JQuery UI DatePickers
         */
        public static function getLanguage()
        {
            $language = Yii::app()->getLanguage();
            if ($language == 'en' || $language == 'en_us')
            {
                return;
            }
            return $language;
        }

        /**
         * Use this function when mapping the localized date format to
         * the JQuery UI DatePicker date format
         * @return string Jquery UI DatePicker date format
         */
        public static function resolveDateFormat($dateFormat)
        {
            switch($dateFormat)
            {
                case 'dd.MM.yy':    //de format
                    return 'dd.mm.y';
                case 'M/d/yy':      //en format
                    return 'm/d/y';
                case 'dd/MM/yy':    //es,fr,it format
                    return 'dd/mm/y';
                default :
                    throw new NotImplementedException();
            }
        }

        /**
         * Use this function when mapping the localized time format to
         * the JQuery UI DatePicker time format
         * @return string Jquery UI DatePicker time format
         */
        public static function resolveTimeFormat($timeFormat)
        {
            switch($timeFormat)
            {
                case 'HH:mm':       //de,es,fr,it format
                    return 'hh:mm';
                case 'h:mm a':      //en format
                    return 'h:mm TT';
                default :
                    throw new NotImplementedException();
            }
        }
    }
?>