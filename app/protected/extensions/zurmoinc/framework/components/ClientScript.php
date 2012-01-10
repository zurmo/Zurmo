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
     * Adds support Ajax support where some
     * javascript files are not needed to be renderd
     * based on position
     */
    class ClientScript extends CClientScript
    {
        private $shouldRenderCoreScripts = true;

        /**
         * Used in AJAX calls to make sure
         * .js files are not rendered again
         * if they are not needed
         * TODO: potentially clear scriptFiles, although this was
         * causing an issue with WorldClock.
         */
        public function setToAjaxMode()
        {
            $this->cssFiles    = array();
            $this->setRenderCoreScripts(false);
        }

        private function setRenderCoreScripts($value)
        {
            assert('is_bool($value)');
            $this->shouldRenderCoreScripts = $value;
        }

        public function isAjaxMode()
        {
            return !$this->shouldRenderCoreScripts;
        }

        public function renderCoreScripts()
        {
            if ($this->shouldRenderCoreScripts)
            {
                parent::renderCoreScripts();
            }
        }

        /**
         * Method override to call @see removeAllPageLoadedScriptFilesWhenRenderingInAjaxMode
         * (non-PHPdoc)
         * @see CClientScript::render()
         */
        public function render(& $output)
        {
            if($this->isAjaxMode())
            {
                $this->removeAllPageLoadedScriptFilesWhenRenderingInAjaxMode();
            }
            parent::render($output);
        }

        /**
         * When the page is loading in ajax mode, it is assumed certain script files have always
         * been loaded by the main page.  These need to therefore be removed from the scriptFiles
         * array.
         */
        protected function removeAllPageLoadedScriptFilesWhenRenderingInAjaxMode()
        {
            $filesToRemove = PageView::getScriptFilesThatLoadOnAllPages();
            if (isset($this->scriptFiles[self::POS_BEGIN]))
            {
                foreach ($this->scriptFiles[self::POS_BEGIN] as $key => $scriptFile)
                {
                    if (in_array($scriptFile, $filesToRemove))
                    {
                        unset($this->scriptFiles[self::POS_BEGIN][$key]);
                    }
                }
            }
            if (isset($this->scriptFiles[self::POS_END]))
            {
                foreach ($this->scriptFiles[self::POS_END] as $key => $scriptFile)
                {
                    if (in_array($scriptFile, $filesToRemove))
                    {
                        unset($this->scriptFiles[self::POS_END][$key]);
                    }
                }
            }
        }
    }
?>
