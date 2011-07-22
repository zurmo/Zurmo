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
     * Class for showing a title bar in a view.
     * An example is "Accounts: Search" or
     * "Acccounts: ABC Company"
     */
    class TitleBarView extends View
    {
        /**
         * Rendering the MenuView with overflow would cause
         * some portions of the MenuView to be cut off in the
         * user interface
         */
        const RENDER_CONTENT_IN_DIV_WITH_OVERFLOW = false;

        protected $title;

        protected $description;

        protected $styleSize;

        protected $prependContent;

        public function __construct($title, $description = null, $styleSize = 1, $prependContent = null)
        {
            $this->title          = $title;
            $this->description    = $description;
            $this->styleSize      = $styleSize;
            $this->prependContent = $prependContent;
        }

        protected function renderContent()
        {
            $content  = $this->prependContent . '<h' . $this->styleSize . '>' . $this->title;
            if (!empty($this->description))
            {
                $content  .= ':&#160;' . $this->description;
            }
            return $content . '</h' . $this->styleSize . '>';
        }

        public function isUniqueToAPage()
        {
            return false;
        }
    }
?>