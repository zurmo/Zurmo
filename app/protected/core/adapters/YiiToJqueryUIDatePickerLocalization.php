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
                case 'dd.MM.yy':    //de, ru format
                    return 'dd.mm.y';
                case 'M/d/yy':      //en, fa_ir format
                    return 'm/d/y';
                case 'dd/MM/yy':    //es, fr, it, pt format
                    return 'dd/mm/y';
                case 'd.M.yyyy':    //sk format
                    return 'd.m.yy';
                case 'd.M.yy.':     //sr_yu format
                    return 'd.m.y.';
                case 'd-M-yy':      //hi format
                    return 'd-m-y';
                case 'dd/MM/yyyy':  //vi format
                    return 'dd/mm/yy';
                case 'yy-M-d':      //zh_cn format
                    return 'y-m-dd';
                case 'yy/MM/dd':    //ja format
                case 'yyyy/MM/dd':  //new ja format of Yii 1.11
                    return 'y/mm/dd';
                case 'dd-MM-yy':    //nl format
                        return 'dd-mm-y';
                case 'd/MM/yy':     //en_au format
                        return 'd/mm/y';
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
                case 'HH:mm':       //de, es, fr, it, pt, nl, sr_yu, vi format
                    return 'hh:mm';
                case 'h:mm a':      //en, hi, en_au format
                    return 'h:mm tt';
                case 'H:mm':        //sk, fa_ir, ru, ja format
                    return 'h:mm';
                case 'ah:mm':       //zh_cn format
                    return 'tth:mm';
                default :
                    throw new NotImplementedException();
            }
        }
    }
?>
