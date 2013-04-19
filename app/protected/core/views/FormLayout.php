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
     * Helper class for rendering form layouts for views.
     */
    abstract class FormLayout
    {
        /**
         * Display all panels at once for a form.
         * @var integer
         */
        const PANELS_DISPLAY_TYPE_ALL    = 1;

        /**
         * Display the first panel, hide all subsequent panels. Add a show more link, that when clicked will show
         * the hidden panels.
         * @var integer
         */
        const PANELS_DISPLAY_TYPE_FIRST  = 2;

        /**
         * Display all panels tabbed.
         * @see http://jqueryui.com/demos/tabs/
         * @var integer
         */
        const PANELS_DISPLAY_TYPE_TABBED = 3;

        /**
         * View metadata with rendered element content instead of element information.
         * @var array
         */
        protected $metadata;

        /**
         * Unique id to be used as a prefix for form elements that require uniqueness across the entire page.
         * @var string
         */
        protected $uniqueId;

        /**
         * Maximum allowed cells per row.
         * @var integer
         */
        protected $maxCellsPerRow;

        /**
         * Error summary content to display if panels are tabbed and there is more than one panel.
         * @var string
         */
        protected $errorSummaryContent;

        /**
         * @param array $metadata
         * @param integer $maxCellsPerRow
         */
        public function __construct($metadata, $maxCellsPerRow, $errorSummaryContent)
        {
            assert('is_array($metadata)');
            assert('is_int($maxCellsPerRow)');
            assert('is_string($errorSummaryContent) || $errorSummaryContent == null');
            $this->metadata            = $metadata;
            $this->maxCellsPerRow      = $maxCellsPerRow;
            $this->errorSummaryContent = $errorSummaryContent;
            $this->uniqueId       = $this->makeUniqueId();
        }

        /**
         * Make a id that will be unique across all rendered content.
         * @return string
         */
        protected static function makeUniqueId()
        {
            return ZurmoHtml::ID_PREFIX . ZurmoHtml::$count++;
        }

        /**
         * Render a form layout. Override to build form layout content.
         */
        abstract public function render();

        /**
         * Get the maximum column count across all panels from the metadata.
         * @return integer column count.
         */
        public static function getMaximumColumnCountForAllPanels($metadata)
        {
            assert('is_array($metadata)');
            $columnCount = 0;
            foreach ($metadata['global']['panels'] as $panel)
            {
                foreach ($panel['rows'] as $row)
                {
                    $tempCount = 0;
                    foreach ($row['cells'] as $cell)
                    {
                        $tempCount++;
                    }
                    if ($tempCount > $columnCount)
                    {
                        $columnCount = $tempCount;
                    }
                }
            }
            return $columnCount;
        }

        /**
         * Get the maximum column count for a specific panel
         * @return integer column count.
         */
        public static function getMaximumColumnCountForSpecificPanels($panel)
        {
            assert('is_array($panel)');
            $columnCount = 0;
            foreach ($panel['rows'] as $row)
            {
                $tempCount = 0;
                foreach ($row['cells'] as $cell)
                {
                    $tempCount++;
                }
                if ($tempCount > $columnCount)
                {
                    $columnCount = $tempCount;
                }
            }
            return $columnCount;
        }
    }
?>