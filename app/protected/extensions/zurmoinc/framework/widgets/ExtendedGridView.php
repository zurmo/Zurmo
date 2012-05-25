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

    Yii::import('zii.widgets.grid.CGridView');

    /**
     * Extends the yii CGridView to provide additional functionality.
     * @see CGridView class
     */
    class ExtendedGridView extends CGridView
    {
        public $template = "{selectRowsSelectors}{summary}\n{items}\n{pager}";

        /**
         * Override to have proper XHTML compliant space value
         */
        public $nullDisplay = '&#160;';

        /**
         * Override to have proper XHTML compliant space value
         */
        public $blankDisplay = '&#160;';

        //extendedGridView

        public function init()
        {
            $this->baseScriptUrl = Yii::app()->getAssetManager()->publish(
                                        Yii::getPathOfAlias('application.extensions.zurmoinc.framework.widgets.assets'))
                                        . '/extendedGridView';
            parent::init();
        }

        /**
         * Renders the top pager content
         */
        public function renderTopPager()
        {
            if (!$this->enablePagination)
            {
                return;
            }
            $pager = array();
            $class = 'TopLinkPager';
            if (is_array($this->pager))
            {
                $pager = $this->pager;
                if (isset($pager['class']))
                {
                    throw new NotSupportedException();
                }
            }
            else
            {
                throw new NotSupportedException();
            }
            $pager['pages'] = $this->dataProvider->getPagination();
            if ($pager['pages']->getPageCount() > 1)
            {
                echo '<div class="' . $this->pagerCssClass . '">';
                $this->widget($class, $pager);
                echo '</div>';
            }
        }

        /**
         * Renders the bottom pager content
         */
        public function renderBottomPager()
        {
            if (!$this->enablePagination)
            {
                return;
            }
            $pager = array();
            $class = 'BottomLinkPager';
            if (is_array($this->pager))
            {
                $pager = $this->pager;
                if (isset($pager['class']))
                {
                    throw new NotSupportedException();
                }
            }
            else
            {
                throw new NotSupportedException();
            }
            $pager['pages'] = $this->dataProvider->getPagination();
            if ($pager['pages']->getPageCount() > 1)
            {
                echo '<div class="' . $this->pagerCssClass . '">';
                $this->widget($class, $pager);
                echo '</div>';
            }
        }

        /**
         * Override to always render pager div if paging is enabled.
         * (non-PHPdoc)
         * @see CBaseListView::renderPager()
         */
        public function renderPager()
        {
            if (!$this->enablePagination)
            {
                return;
            }
            $pager = array();
            $class = 'CLinkPager';
            if (is_string($this->pager))
            {
                $class = $this->pager;
            }
            elseif (is_array($this->pager))
            {
                $pager = $this->pager;
                if (isset($pager['class']))
                {
                    $class = $pager['class'];
                    unset($pager['class']);
                }
            }
            $pager['pages'] = $this->dataProvider->getPagination();
            echo '<div class="' . $this->pagerCssClass . '">';
            $this->widget($class, $pager);
            echo '</div>';
        }
    }
?>
