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
     * Rules for working with charts that can be used for reporting and dashboards
     */
    class ChartRules
    {
        const TYPE_BAR_2D                = 'Bar2D';

        const TYPE_BAR_3D                = 'Bar3D';

        const TYPE_COLUMN_2D             = 'Column2D';

        const TYPE_COLUMN_3D             = 'Column3D';

        const TYPE_DONUT_2D              = 'Donut2D';

        const TYPE_DONUT_3D              = 'Donut3D';

        const TYPE_PIE_2D                = 'Pie2D';

        const TYPE_PIE_3D                = 'Pie3D';

        const TYPE_STACKED_BAR_2D        = 'StackedBar2D';

        const TYPE_STACKED_BAR_3D        = 'StackedBar3D';

        const TYPE_STACKED_COLUMN_2D     = 'StackedColumn2D';

        const TYPE_STACKED_COLUMN_3D     = 'StackedColumn3D';

        const TYPE_STACKED_AREA          = 'StackedArea';

        /**
         * @return array of chart types that require a second series and range to render.
         */
        public static function getChartTypesRequiringSecondInputs()
        {
            return array(self::TYPE_STACKED_BAR_2D,
                         self::TYPE_STACKED_BAR_3D,
                         self::TYPE_STACKED_COLUMN_2D,
                         self::TYPE_STACKED_COLUMN_3D,
                         self::TYPE_STACKED_AREA,
            );
        }

        public static function isStacked($type)
        {
            assert('is_string($type)');
            if (in_array($type, self::getChartTypesRequiringSecondInputs()))
            {
                return true;
            }
            return false;
        }

        public static function getTranslatedTypeLabel($type)
        {
            assert('is_string($type)');
            $labels             = self::translatedTypeLabels();
            if (isset($labels[$type]))
            {
                return $labels[$type];
            }
            throw new NotSupportedException();
        }

        public static function translatedTypeLabels()
        {
            return array(ChartRules::TYPE_BAR_2D             => Zurmo::t('Core', '2D Horizontal Bar Graph'),
                         ChartRules::TYPE_BAR_3D             => Zurmo::t('Core', '3D Horizontal Bar Graph'),
                         ChartRules::TYPE_COLUMN_2D          => Zurmo::t('Core', '2D Vertical Bar Graph'),
                         ChartRules::TYPE_COLUMN_3D          => Zurmo::t('Core', '3D Vertical Bar Graph'),
                         ChartRules::TYPE_DONUT_2D           => Zurmo::t('Core', 'Donut 2D'),
                         ChartRules::TYPE_DONUT_3D           => Zurmo::t('Core', 'Donut 3D'),
                         ChartRules::TYPE_PIE_2D             => Zurmo::t('Core', 'Pie 2D'),
                         ChartRules::TYPE_PIE_3D             => Zurmo::t('Core', 'Pie 3D'),
                         ChartRules::TYPE_STACKED_BAR_2D     => Zurmo::t('Core', 'Stacked Bar 2D'),
                         ChartRules::TYPE_STACKED_BAR_3D     => Zurmo::t('Core', 'Stacked Bar 3D'),
                         ChartRules::TYPE_STACKED_COLUMN_2D  => Zurmo::t('Core', 'Stacked Column 2D'),
                         ChartRules::TYPE_STACKED_COLUMN_3D  => Zurmo::t('Core', 'Stacked Column 3D'),
                         ChartRules::TYPE_STACKED_AREA       => Zurmo::t('Core', 'Stacked Area'),
            );
        }

        public static function availableTypes()
        {
            return array(ChartRules::TYPE_BAR_2D,
                         ChartRules::TYPE_BAR_3D,
                         ChartRules::TYPE_COLUMN_2D,
                         ChartRules::TYPE_COLUMN_3D,
                         ChartRules::TYPE_DONUT_2D,
                         ChartRules::TYPE_DONUT_3D,
                         ChartRules::TYPE_PIE_2D,
                         ChartRules::TYPE_PIE_3D,
                         ChartRules::TYPE_STACKED_BAR_2D,
                         ChartRules::TYPE_STACKED_BAR_3D,
                         ChartRules::TYPE_STACKED_COLUMN_2D,
                         ChartRules::TYPE_STACKED_COLUMN_3D,
                         ChartRules::TYPE_STACKED_AREA,
            );
        }

        public static function getSingleSeriesDataAndLabels()
        {
            $translatedLabels = static::translatedTypeLabels();
            return array(
                ChartRules::TYPE_COLUMN_2D => $translatedLabels[ChartRules::TYPE_COLUMN_2D],
                ChartRules::TYPE_COLUMN_3D => $translatedLabels[ChartRules::TYPE_COLUMN_3D],
                ChartRules::TYPE_BAR_2D    => $translatedLabels[ChartRules::TYPE_BAR_2D],
                ChartRules::TYPE_BAR_3D    => $translatedLabels[ChartRules::TYPE_BAR_3D],
                ChartRules::TYPE_DONUT_2D  => $translatedLabels[ChartRules::TYPE_DONUT_2D],
                ChartRules::TYPE_DONUT_3D  => $translatedLabels[ChartRules::TYPE_DONUT_3D],
                ChartRules::TYPE_PIE_2D    => $translatedLabels[ChartRules::TYPE_PIE_2D],
                ChartRules::TYPE_PIE_3D    => $translatedLabels[ChartRules::TYPE_PIE_3D],
            );
        }
    }
?>