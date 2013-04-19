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

    class MenuView extends View
    {
        protected $items;

        protected $useMinimalDynamicLabelMbMenu;

        protected $cssClasses;

        protected $showCount;

        public function __construct(array $items, $useMinimalDynamicLabelMbMenu = false, $showCount = 6)
        {
            assert('is_int($showCount)');
            $this->items = $items;
            $this->useMinimalDynamicLabelMbMenu = $useMinimalDynamicLabelMbMenu;
            $this->showCount  = $showCount;
            $this->cssClasses = $this->resolveMenuClassForNoHiddenItems();
        }

        /**
         * Will attempt to get the menu items from cache, otherwise from the appropriate storage, and cache the information
         * for the next call to this method.
         * @see View::renderContent()
         */
        protected function renderContent()
        {
            if (count($this->items) == 0)
            {
                return null;
            }
            if ($this->useMinimalDynamicLabelMbMenu)
            {
                $widgetName = 'MinimalDynamicLabelMbMenu';
            }
            else
            {
                $widgetName = 'MbMenu';
            }
            $widgetPath = 'application.core.widgets.' . $widgetName;
            $cClipWidget = new CClipWidget();
            $cClipWidget->beginClip("Tabs");
            $cClipWidget->widget($widgetPath, array(
                'items'         => static::resolveForHiddenItems($this->items, $this->showCount),
                'labelPrefix'   => 'em',
                'linkPrefix'    => 'span',
            ));
            $cClipWidget->endClip();
            $content  = $cClipWidget->getController()->clips['Tabs'];
            $content .= $this->resolveToggleForHiddenItems();
            return $content;
        }

        protected function resolveForHiddenItems($items, $showCount)
        {
            assert('is_array($items)');
            assert('is_int($showCount)');
            $count = 1;
            foreach ($this->items as $key => $item)
            {
                if ($count > $showCount && !ArrayUtil::getArrayValue($item, 'active'))
                {
                    $items[$key]['itemOptions']['class'] = 'hidden-nav-item';
                }
                $count++;
            }
            return $items;
        }

        protected function resolveToggleForHiddenItems()
        {
            if (count($this->items) > $this->showCount)
            {
                return ZurmoHtml::tag('a', array('class' => 'toggle-hidden-nav-items'), '');
            }
        }

        protected function resolveMenuClassForNoHiddenItems()
        {
            if ((count($this->items) < $this->showCount) || static::areAllItemsVisible($this->items, $this->showCount))
            {
                return array('hasNoHiddenItems');
            }
            else
            {
                return array();
            }
        }

        protected static function areAllItemsVisible($items, $showCount)
        {
            $count = 1;
            foreach ($items as $item)
            {
                if ($count > $showCount && !ArrayUtil::getArrayValue($item, 'active'))
                {
                    return false;
                }
                $count++;
            }
            return true;
        }
    }
?>