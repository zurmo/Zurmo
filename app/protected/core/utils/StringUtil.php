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
     * Helper functionality for working with Strings
     */
    class StringUtil
    {
        /**
         * Given a string and a length, return the chopped string if it is larger than the length.
         * @param string $string
         * @param integer $length
         */
        public static function getChoppedStringContent($string, $length)
        {
            assert('is_string($string)');
            assert('is_int($length)');
            if (strlen($string) > $length)
            {
                return substr($string, 0, ($length - 3)) . '...';
            }
            else
            {
                return $string;
            }
        }

        /**
         * Given an integer, resolve the integer with an ordinal suffix and return the content as as string.
         * @param integer $number
         */
        public static function resolveOrdinalIntegerAsStringContent($integer)
        {
            assert('is_int($integer)');
            $ends = array('th', 'st', 'nd', 'rd', 'th', 'th', 'th', 'th', 'th', 'th');
            if (($integer %100) >= 11 && ($integer%100) <= 13)
            {
               return $integer. 'th';
            }
            else
            {
               return $integer. $ends[$integer % 10];
            }
        }

        public static function renderFluidTitleContent($title)
        {
            assert('$title == null || is_string($title)');
            if ($title != null)
            {
                // Begin Not Coding Standard
                Yii::app()->clientScript->registerScript('TruncateTitleText', "
                    $(function() {
                        $('.truncated-title').ThreeDots({ max_rows:1 });
                    });");
                // End Not Coding Standard
                $innerContent = ZurmoHtml::wrapLabel(strip_tags($title), 'ellipsis-content');
                $content      = ZurmoHtml::wrapLabel($innerContent, 'truncated-title');
                return          ZurmoHtml::tag('h1', array(), $content);
            }
        }
    }