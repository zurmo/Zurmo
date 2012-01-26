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
     * Test view override.  Displays a specific message for the test sequential process.
     */
    class TestCompleteSequentialProcessView extends CompleteSequentialProcessView
    {
        /**
         * This construction is just a demonstration.
         * @param object $a
         * @param object $b
         */
        public function __construct($a, $b)
        {
            assert('$a instanceof A');
            assert('$b instanceof B');
        }

        protected function renderContent()
        {
            $content  = '<h1>Sequential Process Complete</h1>';
            $content .= '<div class="view-toolbar">' . "\n";
            $content .= "<h2>A second message for the complete process.</h2>";
            $content .= '</div>';
            $this->registerScripts();
            return $content;
        }
    }
?>