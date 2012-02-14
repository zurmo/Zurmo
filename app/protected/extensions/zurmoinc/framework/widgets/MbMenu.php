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

    Yii::import('zii.widgets.CMenu');

    /**
     * MbMenu class file.
     *
     * @author Mark van den Broek (mark@heyhoo.nl)
     * @copyright Copyright &copy; 2010 HeyHoo
     *
     */
    class MbMenu extends CMenu
    {
        private $baseUrl;

        protected $themeUrl;

        protected $theme;

        protected $cssFile         = 'css/mbmenu.css';

        protected $cssIeStylesFile = 'css/mbmenu-iestyles.css';

        private $nljs;

        public $activateParents    = true;

        public $navContainerClass  = 'nav-container';

        public $navBarClass        = 'nav-bar';

        /**
         * The javascript needed.
         */
        protected function createJsCode()
        {
            $js  = '';
            $js .= '  $(".nav li").hover('                   . $this->nljs;
            $js .= '    function () {'                       . $this->nljs; // Not Coding Standard
            $js .= '      if ($(this).hasClass("parent")) {' . $this->nljs; // Not Coding Standard
            $js .= '        $(this).addClass("over");'       . $this->nljs;
            $js .= '      }'                                 . $this->nljs;
            $js .= '    },'                                  . $this->nljs; // Not Coding Standard
            $js .= '    function () {'                       . $this->nljs; // Not Coding Standard
            $js .= '      $(this).removeClass("over");'      . $this->nljs;
            $js .= '    }'                                   . $this->nljs;
            $js .= '  );'                                    . $this->nljs;
            return $js;
        }

        /**
        * Give the last items css 'last' style.
        */
        protected function cssLastItems($items)
        {
            $i = max(array_keys($items));
            $item = $items[$i];
            if (isset($item['itemOptions']['class']))
            {
                $items[$i]['itemOptions']['class'] .= ' last';
            }
            else
            {
                $items[$i]['itemOptions']['class'] = 'last';
            }
            foreach ($items as $i => $item)
            {
                if (isset($item['items']))
                {
                    $items[$i]['items'] = $this->cssLastItems($item['items']);
                }
            }
            return array_values($items);
        }

        /**
        * Give the last items css 'parent' style.
        */
        protected function cssParentItems($items)
        {
            foreach ($items as $i => $item)
            {
                if (isset($item['items']))
                {
                    if (isset($item['itemOptions']['class']))
                    {
                        $items[$i]['itemOptions']['class'] .= ' parent';
                    }
                    else
                    {
                    $items[$i]['itemOptions']['class'] = 'parent';
                    }
                    $items[$i]['items'] = $this->cssParentItems($item['items']);
                }
            }
            return array_values($items);
        }

        /**
        * Initialize the widget.
        */
        public function init()
        {
            if (!$this->getId(false))
            {
                $this->setId('nav');
            }
            $this->themeUrl = Yii::app()->baseUrl . '/themes';
            $this->theme = Yii::app()->theme->name;
            $this->nljs = "\n";
            $this->items = $this->cssParentItems($this->items);
            $this->items = $this->cssLastItems($this->items);
            $route = $this->getController()->getRoute();
            $hasActiveChild = null;
            $this->items = $this->normalizeItems(
                $this->items,
                $this->getController()->getRoute(),
                $hasActiveChild
            );
            $this->htmlOptions['class']= 'nav';
        }

        /**
        * Registers the external javascript files.
        */
        public function registerClientScripts()
        {
            // add the script
            $cs = Yii::app()->getClientScript();
            $cs->registerCoreScript('jquery');
            $js = $this->createJsCode();
            $cs->registerScript('mbmenu_' . $this->getId(), $js, CClientScript::POS_READY);
        }

        public function registerCssFile()
        {
            $cs = Yii::app()->getClientScript();
            $cs->registerCssFile($this->themeUrl . '/' . $this->theme . '/' . $this->cssFile, 'screen');
            if (Yii::app()->browser->getName() == 'msie' && Yii::app()->browser->getVersion() < 8)
            {
                $cs->registerCssFile($this->themeUrl . '/' . $this->theme . '/' . $this->cssIeStylesFile, 'screen');
            }
        }

        protected function renderMenuRecursive($items)
        {
            foreach ($items as $item)
            {
                echo CHtml::openTag('li', isset($item['itemOptions']) ? $item['itemOptions'] : array());
                if (isset($item['linkOptions']))
                {
                     $htmlOptions = $item['linkOptions'];
                }
                else
                {
                    $htmlOptions = array();
                }
                if((isset($item['ajaxLinkOptions'])))
                {
                    echo CHtml::ajaxLink('<span>' . $item['label'] . '</span>', $item['url'], $item['ajaxLinkOptions'], $htmlOptions);
                }
                elseif (isset($item['url']))
                {
                    echo CHtml::link('<span>' . $item['label'] . '</span>', $item['url'], $htmlOptions);
                }
                else
                {
                    echo CHtml::link('<span>' . $item['label'] . '</span>', "javascript:void(0);", $htmlOptions);
                }
                if (isset($item['items']) && count($item['items']))
                {
                    echo "\n" . CHtml::openTag('ul', $this->submenuHtmlOptions) . "\n";
                    $this->renderMenuRecursive($item['items']);
                    echo CHtml::closeTag('ul') . "\n";
                }
                echo CHtml::closeTag('li') . "\n";
            }
        }

        protected function normalizeItems($items, $route, &$active, $ischild = 0)
        {
            foreach ($items as $i => $item)
            {
                if (isset($item['visible']) && !$item['visible'])
                {
                    unset($items[$i]);
                    continue;
                }
                if ($this->encodeLabel)
                {
                    $items[$i]['label'] = Yii::app()->format->text($item['label']);
                }
                $hasActiveChild = false;
                if (isset($item['items']))
                {
                    $items[$i]['items'] = $this->normalizeItems($item['items'], $route, $hasActiveChild, 1);
                    if (empty($items[$i]['items']) && $this->hideEmptyItems)
                    {
                        unset($items[$i]['items']);
                    }
                }
                if (!isset($item['active']))
                {
                    if (($this->activateParents && $hasActiveChild) || $this->isItemActive($item, $route))
                    {
                        $active = $items[$i]['active'] = true;
                    }
                    else
                    {
                        $items[$i]['active'] = false;
                    }
                }
                elseif ($item['active'])
                {
                    $active = true;
                }
                if ($items[$i]['active'] && $this->activeCssClass != '' && !$ischild)
                {
                    if (isset($item['itemOptions']['class']))
                    {
                        $items[$i]['itemOptions']['class'] .= ' ' . $this->activeCssClass;
                    }
                    else
                    {
                        $items[$i]['itemOptions']['class'] = $this->activeCssClass;
                    }
                }
            }
            return array_values($items);
        }

        /**
        * Run the widget.
        */
        public function run()
        {
            $this->registerClientScripts();
            $this->registerCssFile();
            $htmlOptions['class'] = $this->navContainerClass;
            //echo CHtml::openTag('div', $htmlOptions) . "\n";
            $htmlOptions['class'] = $this->navBarClass;
            //echo CHtml::openTag('div', $htmlOptions) . "\n";
            parent::run();
            //echo CHtml::closeTag('div');
            //echo CHtml::closeTag('div');
        }
    }
?>
