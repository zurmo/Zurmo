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
     * A base class for developing widgets.
     */
    abstract class ZurmoWidget extends CWidget
    {
        /**
         * @var string the root URL that contains all JUI JavaScript, CSS, and Image files.
         * Do not append any slash character to the URL.
         */
        public $scriptUrl;

        public $scriptFile;

        public $cssFile;

        public $assetFolderName;

        /**
         * Initializes the widget.
         * This method will publish JUI assets if necessary.
         * It will also register jquery and JUI JavaScript files and the theme CSS file.
         * If you override this method, make sure you call the parent implementation first.
         */
        public function init()
        {
            $this->resolvePackagePath();
            $this->registerCoreScripts();
            parent::init();
        }

        /**
         * Determine the package installation path.
         * This method will identify the JavaScript root URL and theme root URL.
         * If they are not explicitly specified, it will publish the included JUI package
         * and use that to resolve the needed paths.
         */
        protected function resolvePackagePath()
        {
            if ($this->scriptUrl === null || $this->themeUrl === null)
            {
                $cs = Yii::app()->getClientScript();
                if ($this->scriptUrl === null && $this->assetFolderName != null)
                {
                    $this->scriptUrl = Yii::app()->getAssetManager()->publish(
                                        Yii::getPathOfAlias('ext.zurmoinc.framework.widgets.assets')) . '/' . $this->assetFolderName;
                }
            }
        }

        /**
         * Registers the core script files.
         * This method registers jquery and JUI JavaScript files and the theme CSS file.
         */
        protected function registerCoreScripts()
        {
            $cs = Yii::app()->getClientScript();
            if (is_string($this->cssFile))
            {
                $cs->registerCssFile($this->scriptUrl . '/css/' . $this->cssFile);
            }
            elseif (is_array($this->cssFile))
            {
                foreach ($this->cssFile as $cssFile)
                {
                    $cs->registerCssFile($this->scriptUrl . '/css/' . $cssFile);
                }
            }

            $cs->registerCoreScript('jquery');
            if (is_string($this->scriptFile))
            {
                $this->registerScriptFile($this->scriptFile, CClientScript::POS_HEAD);
            }
            elseif (is_array($this->scriptFile))
            {
                foreach ($this->scriptFile as $scriptFile)
                {
                    $this->registerScriptFile($scriptFile);
                }
            }
        }

        /**
         * Registers a JavaScript file under {@link scriptUrl}.
         * Note that by default, the script file will be rendered at the end of a page to improve page loading speed.
         * @param string JavaScript file name
         * @param integer the position of the JavaScript file. Valid values include the following:
         * <ul>
         * <li>CClientScript::POS_HEAD : the script is inserted in the head section right before the title element.</li>
         * <li>CClientScript::POS_BEGIN : the script is inserted at the beginning of the body section.</li>
         * <li>CClientScript::POS_END : the script is inserted at the end of the body section.</li>
         * </ul>
         */
        protected function registerScriptFile($fileName, $position = CClientScript::POS_END)
        {
            Yii::app()->getClientScript()->registerScriptFile($this->scriptUrl . '/' . $fileName, $position);
        }
    }
?>
