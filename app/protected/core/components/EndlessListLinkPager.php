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

    /**
     * Provides a pager used for displaying lists with a simple pagination of previous and next.
     */
    class EndlessListLinkPager extends LinkPager
    {
        public function init()
        {
            parent::init();
            $this->htmlOptions['class'] = 'endless-list-pager';
        }

        /**
         * Set the header to empty
         * @var string
         */
        public $header = '';

        /**
         * Creates the page buttons.
         * @return array a list of page buttons (in HTML code).
         */
        protected function createPageButtons()
        {
            if (($pageCount = $this->getPageCount()) <= 1)
            {
                return array();
            }

            list($beginPage, $endPage) = $this->getPageRange();
            $currentPage = $this->getCurrentPage(false); // currentPage is calculated in getPageRange()
            $buttons = array();

            // next page
            if (($page = $currentPage + 1) >= $pageCount - 1)
            {
                $page = $pageCount-1;
            }
            $buttons[] = $this->createPageButton($this->nextPageLabel, $page, self::CSS_NEXT_PAGE, $currentPage >= $pageCount-1, false);

            return $buttons;
        }

        /**
         * Override to add special update support for appending instead of just overwriting existing data.
         * (non-PHPdoc)
         * @see CLinkPager::createPageButton()
         */
        protected function createPageButton($label, $page, $class, $hidden, $selected)
        {
            if ($hidden || $selected)
            {
                $class.=' '.($hidden ? self::CSS_HIDDEN_PAGE : self::CSS_SELECTED_PAGE);
            }
            $gridId =  $this->getOwner()->getId();
            $pagerId = $gridId . "-endless-page";
            // Begin Not Coding Standard
            Yii::app()->clientScript->registerScript('pagerEndlessLink', "
                $('#" . $pagerId . "').unbind('click');
                $('#" . $pagerId . "').bind('click', function(event)
                    {
                        $.fn.yiiGridView.update('" . $gridId . "',
                        {
                            url: '" . $this->createPageUrl($page) . "',
                            type: 'GET',
                            success: function(data,status) {
                                var id = '" . $gridId . "';
                                var settings = $.fn.yiiGridView.settings[id];
                                $.each(settings.ajaxUpdate, function(i,v) {
                                    var id='#'+v;
                                    $(id).find('tbody:first').append($(id, data).find('tbody:first').html());
                                    $(id).find('.endless-list-pager').replaceWith($(id, data).find('.endless-list-pager'));
                                });
                                var \$data = $(data);
                                jQuery.globalEval(\$data.filter('script').last().text());
                                if (settings.afterAjaxUpdate !== undefined)
                                    settings.afterAjaxUpdate(id, data);
                                $('#'+id).removeClass(settings.loadingClass);
                                //$.fn.yiiGridView.selectCheckedRows(id);
                            },
                        });
                        return false;
                    }
                );");
            // End Not Coding Standard
            $nextPage = $page + 1;
            $htmlOptions = array('id' => $pagerId, 'class' => 'vertical-forward-pager');
            return '<li class="' . $class . '">' . ZurmoHtml::link($label, '#', $htmlOptions) . '</li>';
        }
    }
?>
