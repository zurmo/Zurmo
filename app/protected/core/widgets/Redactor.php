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

    class Redactor extends ZurmoWidget
    {
        public $scriptFile = 'redactor.min.js';

        public $cssFile = 'redactor.css';

        public $htmlOptions;

        public $content;

        public $buttons = "['html', 'html', '|', 'formatting', 'bold', 'italic', 'deleted', '|',
                           'unorderedlist', 'orderedlist', 'outdent', 'indent', '|', 'table', 'link', '|',
                           'fontcolor', 'backcolor', '|', 'alignleft', 'aligncenter', 'alignright', 'justify', '|',
                           'horizontalrule']";

        public $source = "false";

        public $paragraphy = "true";

        public $wim = "false";

        public function run()
        {
            $id         = $this->htmlOptions['id'];
            $name       = $this->htmlOptions['name'];
            $javaScript = "
                    $(document).ready(
                        function()
                        {
                            $('#{$id}').redactor(
                            {
                                buttons:    {$this->buttons},
                                source:     {$this->source},
                                paragraphy: {$this->paragraphy},
                                wim:        {$this->wim},
                            });
                        }
                    );";
            Yii::app()->getClientScript()->registerScript(__CLASS__ . '#' . $this->getId(), $javaScript);
            echo "<textarea id='{$id}' name='{$name}'>{$this->content}</textarea>";
        }

        protected function resolvePackagePath()
        {
            if ($this->scriptUrl === null || $this->themeUrl === null)
            {
                $cs = Yii::app()->getClientScript();
                if ($this->scriptUrl === null)
                {
                    $this->scriptUrl = Yii::app()->getAssetManager()->publish(
                                        Yii::getPathOfAlias('application.core.widgets.assets.redactor'));
                }
            }
        }
    }
?>