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

    Yii::import('zii.widgets.jui.CJuiWidget');

    /**
     * JuiPortlets displays an portlet widget.
     *
     * JuiPortlets encapsulates the {@link http://jqueryui.com/demos/sortable/ JUI Sortable}
     * plugin.  Extends functionality found at nettuts.com
     *
     * To use this widget, you may insert the following code in a view:
     * @code

        <?php
            $this->widget('ext.zurmoinc.framework.widgets.JuiPortlets', array(
                'uniqueLayoutId' => 'HomeView',
                'items' => array(
                    1 => array(
                        1 => array('title' => 'your title', 'content' => 'your content', editable => true),
                        2 => array('title' => 'your title', 'content' => 'your content', editable => true),
                    ),
                    2 => array(
                        1 => array('title' => 'your title', 'content' => 'your content', editable => true),
                    ),
                    3 => array(
                        1 => array('title' => 'your title', 'content' => 'your content', editable => true),
                        2 => array('title' => 'your title', 'content' => 'your content', editable => true),
                        2 => array('title' => 'your title', 'content' => 'your content', editable => true),
                    ),
                )
            );
        ?>
     * @endcode
     *
     * The above example has 3 columns. In the first column there are 2 portlets. In the
     * second column there is 1 portlet and in the third column there are 3 portlets.
     *
     * If you are using javascript code anywhere in the code, please add "js:" at the
     * start of the js code definition and Yii will use this string as js code.
     *
     */
    class JuiPortlets extends CJuiWidget
    {
        /**
         * @var array list of sortable items (id=>item content).
         * Note that the item contents will not be HTML-encoded.
         */
        public $items = array();

        /**
         * @var string id of the layout.  Used in AJAX to determine
         * which layout we are saving for
         */
        public $uniqueLayoutId;

        public $moduleId;

        public $saveUrl;

        /**
         * @var string id of the layout type.
         * Accepted values include:
         * 1   - 1 column
         * 2e  - 2 column even split
         * 2ls - 2 column larger left side
         * 2rs - 2 column larger right side
         */
        public $layoutType;

        public $cssFile = 'css/widget-juiportlets.css';

        /**
         * Can portlets be collapsed.
         * @var boolean
         */
        public $collapsible = true;

        /**
         * Can portlets be moved to different locations.
         * @var boolean
         */
        public $movable = true;

        /**
         * Override to include proper paths of CSS file and extra JS file
         */
        public $baseJuiPortletsScriptUrl;

        public function init()
        {
            assert('!empty($this->layoutType)');
            assert('!empty($this->uniqueLayoutId)');
            assert('!empty($this->moduleId)');
            assert('!empty($this->saveUrl)');
            assert('is_bool($this->collapsible)');
            assert('is_bool($this->movable)');
            assert('in_array($this->layoutType, array("100", "50,50", "75,25"))'); // Not Coding Standard
            $this->themeUrl = Yii::app()->baseUrl . '/themes';
            $this->theme = Yii::app()->theme->name;
            $this->registerJuiPortletsScripts();
            parent::init();
        }

        /**
         * @param $renderOnClickEvent boolean
         * In the event of a portlet refresh, you do not want to render the onClick event
         * since this will already be rendered in the page.  Doing so would add an extra unneeded
         * modal dialog.
         */
        public static function renderPortlet($item, $uniqueLayoutId, $moduleId, $renderOnClickEvent = true)
        {
            $content = "<div class=\"juiportlet-widget-head\">\n";
            if (isset($item['removable']) && $item['removable'] == true)
            {
                $content .= "<a href=\"#\" class=\"remove\">CLOSE</a>"; //must be CLOSE - do not translate
            }
            $content .= "<h3>" . $item['title'] . "</h3>";
            if (isset($item['editable']) && $item['editable'] == true)
            {
                $content .= JuiPortlets::renderEditLink(
                    $item['id'], $uniqueLayoutId, $moduleId, $renderOnClickEvent) . "\n";
            }
            if (isset($item['collapsed']) && $item['collapsed'])
            {
                $widgetContentStyle = "style=\"display:none;\"";
            }
            else
            {
                $widgetContentStyle = '';
            }
            $content .= "</div>\n";
            $content .= "<div class=\"juiportlet-widget-content\" $widgetContentStyle >\n";
            $content .= $item['content'] . "\n";
            $content .= "</div>\n";
            return $content;
        }

        protected static function renderEditLink($portletId, $uniqueLayoutId, $moduleId, $renderOnClickEvent = true)
        {
            $htmlOptions = array(
                        'class' => 'edit',
                        'id' => $uniqueLayoutId. '_' . $portletId . '_EditLink'
            );
            if (!$renderOnClickEvent)
            {
                return CHtml::link(Yii::t('Default', 'Edit'), '#', $htmlOptions);
            }
            else
            {
                $url = null;
                $ajaxOptions = array();
            }
            $url = Yii::app()->createUrl($moduleId .'/defaultPortlet/ModalConfigEdit/', array(
                'uniqueLayoutId' => $uniqueLayoutId,
                'portletId'      => $portletId,
            ));
            return CHtml::ajaxLink(Yii::t('Default', 'Edit'), $url,
                array(
                    'onclick'  => '$("#modalContainer").dialog("open"); return false;',
                    'update'   => '#modalContainer',
                ),
                $htmlOptions
            );
        }

        /**
         * Run this widget.
         * This method registers necessary javascript and renders the needed HTML code.
         */
        public function run()
        {
            assert('!empty($this->uniqueLayoutId)');
            $id = $this->getId();
            $this->htmlOptions['id'] = $id;
            $columnsClass = '.juiportlet-columns-' . $this->uniqueLayoutId;
            $script  = "juiPortlets.init('" . $this->uniqueLayoutId . "',"; // Not Coding Standard
            $script .= "'" . $this->moduleId . "', '" . $this->saveUrl . "', ";
            if (Yii::app()->request->enableCsrfValidation)
            {
                $script .= "'" . Yii::app()->request->csrfTokenName . "', '" . Yii::app()->request->csrfToken . "', ";
            }
            else
            {
                $script .= "null, null, ";
            }
            $script .= "'" .  $columnsClass . "', '" . $this->collapsible . "', '" . $this->movable . "', ";
            $script .= "'" . Yii::t('Default', 'This portlet will be removed, ok?') . "');";
            Yii::app()->getClientScript()->registerScript(__CLASS__ . '#' . $id, $script);
            $content = "";
            $content .= "<div class=\"juiportlet-columns\"> \n";
            if (!empty($this->items))
            {
                if ($this->layoutType == '100')
                {
                    $totalColumns = 1;
                    $columnStyle[1] = 'juiportlet-column-no-split';
                }
                if ($this->layoutType == '50,50') // Not Coding Standard
                {
                    $totalColumns = 2;
                    $columnStyle[1] = 'juiportlet-column-split-even-2';
                    $columnStyle[2] = 'juiportlet-column-split-even-2';
                }
                if ($this->layoutType == '75,25') // Not Coding Standard
                {
                    $totalColumns = 2;
                    $columnStyle[1] = 'juiportlet-column-split-left-75';
                    $columnStyle[2] = 'juiportlet-column-split-right-25';
                }
                if (count($this->items) < $totalColumns)
                {
                    $keys = array_keys($this->items);
                    if ($keys[0] == $totalColumns)
                    {
                        //TODO: when we expand to 3 or 4 columns, need to fill blank columns when appropriate and it might
                        //be more than just one occurance. We will need some sort of for loop.
                        $this->items = array(1 => array(array('blankColumn' => true))) + $this->items;
                    }
                    else
                    {
                        $this->items[] = array(array('blankColumn' => true));
                    }
                }
                foreach ($this->items as $column => $columnPortlets)
                {
                    $classString  = "juiportlet-columns-" . $this->uniqueLayoutId . " ";
                    $classString .= "juiportlet-widget-column" . $column . " ";
                    if ($totalColumns > 1)
                    {
                        $classString .= "juiportlet-column " . $columnStyle[$column];
                    }
                    $content .= "<ul class=\"". $classString . "\">\n";
                    foreach ($columnPortlets as $position => $item)
                    {
                        if (isset($item['blankColumn']) && $item['blankColumn'])
                        {
                            $content .= "<li>&#160;\n";
                            $content .= "</li>\n";
                        }
                        else
                        {
                            $content .= "<li class=\"juiportlet-widget\" id=\"" . $item['uniqueId'] . "\">\n";
                            $content .= JuiPortlets::renderPortlet($item, $this->uniqueLayoutId, $this->moduleId);
                            $content .= "</li>\n";
                        }
                    }
                    $content .= "</ul>\n";
                }
            }
            $content .= "</div>";
            echo $content;
        }

        /**
         * Registers extra js file specific to JuiPortlets
         */
        protected function registerJuiPortletsScripts()
        {
            if ($this->baseJuiPortletsScriptUrl === null)
            {
                $this->baseJuiPortletsScriptUrl = Yii::app()->getAssetManager()->publish(Yii::getPathOfAlias('ext.zurmoinc.framework.widgets.assets'));
            }
            $cs = Yii::app()->getClientScript();
            $cs->registerScriptFile($this->baseJuiPortletsScriptUrl . '/juiportlets/JuiPortlets.js', CClientScript::POS_END);
        }
    }
?>
