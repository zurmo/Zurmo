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

        public $cssFile = false;

        public function init()
        {
            $this->baseScriptUrl = Yii::app()->getAssetManager()->publish(
                                        Yii::getPathOfAlias('application.core.widgets.assets'))
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

        /**
         * Renders the summary-clone changer. When the summary changes, it should update the summary-clone in the
         * searchview if it is available.  The ModalListView does not rely on this because it does not run
         * jquery.globalEval on ajax changes such as pagination.  It instead will call processListViewSummaryClone which
         * is decleared @see ModalListView->getCGridViewAfterAjaxUpdate()
         *
         */
        public function renderSummary()
        {
            parent::renderSummary();
            Yii::app()->clientScript->registerScript('listViewSummaryChangeScript', "
            processListViewSummaryClone('" . $this->id . "', '" . $this->summaryCssClass . "');
            ");
        }
    }
?>
