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
     * Base widget class used for rendering a latest activity view.
     */
    class LatestActivitiesBaseViewLayout extends ZurmoWidget
    {
        /**
         * DataProvider to get data from.
         * @var object
         */
        public $dataProvider;

        /**
         * The url to use when performing actions from the user interface.
         * @var string
         */
        public $url;

        /**
         * The url to use as the redirect url when going to another action. This will return the user
         * to the correct page upon canceling or completing an action.
         * @var string
         */
        public $redirectUrl;

        public function init()
        {
            assert('$this->dataProvider instanceof RedBeanModelsDataProvider');
            assert('is_string($this->url)');
        }

        public function run()
        {
            echo $this->renderListViewLayout();
        }

        protected function renderListViewLayout()
        {
            return null;
        }

        /**
         * Renders a paginator if required.  Determines if it is required based on if there are extra pages to show
         * beyond the current page.  This paginator will load the next page and append its data to the existing page
         * making a bigger and bigger list as you keep going to the next page.
         * @return string of pagination content.
         */
        protected function renderPaginationContent()
        {
            $showMoreLinkId = $this->getId(). '-list-view-show-more-link';
            $currentPage = $this->dataProvider->getPagination()->getCurrentPage(false) + 1;
            if ( $this->dataProvider->getPagination()->getPageCount() > $currentPage &&
                $this->dataProvider->getPagination()->getPageCount() > 1)
            {
                $urlScript = 'js:$.param.querystring("' . $this->url . '", "' .
                             $this->dataProvider->getPagination()->pageVar . '=" + $(this).attr("href"))';
                // Begin Not Coding Standard
                return CHtml::ajaxLink(Yii::t('Default', '<span>Show more</span>'), $urlScript,
                    array('type' => 'GET',
                          'success' => 'js:function(data){
                            var id = "#' . $this->getViewContainerId() . '";
                            $("#' . $showMoreLinkId . '").parent().remove();
                            $(id).append($(id, data).html());
                          }'),
                    array('id' => $showMoreLinkId, 'class' => 'vertical-forward-pager', 'href' => ($currentPage + 1)));
                // End Not Coding Standard
            }
        }

        protected function getViewContainerId()
        {
            return $this->getId(). '-list-view';
        }
    }
?>
