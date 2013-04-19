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

    Yii::import('zii.widgets.jui.CJuiWidget');

    /**
     * Class to render a highlight or error message box.
     */
    class JNotify extends CJuiWidget
    {
        private $baseUrl;

        public $showStatusBarOneAtATime = true;

        public $statusBarId             = 'StatusBar';

        public $cssFile                 = null;

        /**
         * Initialize the JNotify Widget
         */
        public function init()
        {
            $this->registerClientScripts();
            $this->themeUrl = Yii::app()->baseUrl . '/themes';
            $this->theme    = Yii::app()->theme->name;
            parent::init();
        }

        protected function registerClientScripts()
        {
            if ($this->baseUrl === null)
            {
                $this->baseUrl = Yii::app()->getAssetManager()->publish(Yii::getPathOfAlias('application.core.widgets.assets'));
            }
            $cs = Yii::app()->getClientScript();
            $cs->registerScriptFile($this->baseUrl . '/jnotify/jquery.jnotify.js');
            $cs->registerScript('jnotify_init', $this->renderJavaScript(), CClientScript::POS_HEAD);
        }

        protected function renderJavaScript()
        {
            $content = <<<END
$(document).ready(function()
{
    $('#{$this->statusBarId}').jnotifyInizialize(
    {
        oneAtTime: false,
        appendType: 'append'
    }
    );
}
);
END;
            return $content;
        }

        /**
         * Add a message to an existing initialized jnotify div.  Registers the addMessage script.
         * @param string $statusBarId
         * @param string $text
         * @param string $scriptId
         */
        public static function addMessage($statusBarId, $text, $scriptId, $type = 'message')
        {
            assert('is_string($statusBarId)');
            assert('is_string($text)');
            assert('is_string($scriptId)');
            assert('$type == "message" || $type == "error"');
            $script = "
            $('#" . $statusBarId . "').jnotifyAddMessage(
            {
                text: '" . CJavaScript::quote($text) . "',
                permanent: false,
                showIcon: true,
                type: '" . $type . "'
            }
            );
            ";
            Yii::app()->clientScript->registerScript($scriptId, $script);
        }
    }
?>
