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
     * Social items list view.
     */
    class SocialItemsListView extends ListView
    {
        protected $controllerId;

        protected $moduleId;

        protected $dataProvider;

        /**
         * Ajax route for calling pagination actions
         * @var string
         */
        protected $paginationRoute;

        /**
         * Params to use when calling pagination actions
         * @var array
         */
        protected $paginationParams;

        /**
         * The url to use as the redirect url when going to another action. This will return the user
         * to the correct page upon canceling or completing an action.
         * @var string
         */
        public $redirectUrl;

        /**
         * Unique identifier used to identify this view on the page.
         * @var string
         */
        protected $uniquePageId;

        protected $params;

        /**
         * If a social item is posted to another user, should that show like 'Jim Smith to Mary Smith' or should it
         * just show 'Jim Smith'.  Set to true if you want it to show 'Jim Smith to Mary Smith'
         * @var boolean
         */
        protected $renderToUserString = false;

        /**
         * Do not show any empty text since it would look strange in the social feed.
         * @var string
         */
        protected $emptyText = '';

        public function __construct(RedBeanModelDataProvider $dataProvider,
                                    $controllerId,
                                    $moduleId,
                                    $paginationRoute,
                                    $paginationParams,
                                    $redirectUrl,
                                    $uniquePageId,
                                    $params,
                                    $renderToUserString = false)
        {
            assert('is_string($controllerId)');
            assert('is_string($moduleId)');
            assert('is_string($paginationRoute)');
            assert('is_array($paginationParams)');
            assert('is_string($redirectUrl)');
            assert('is_string($uniquePageId)');
            assert('is_array($params)');
            assert('is_bool($renderToUserString)');
            $this->dataProvider             = $dataProvider;
            $this->controllerId             = $controllerId;
            $this->moduleId                 = $moduleId;
            $this->paginationRoute          = $paginationRoute;
            $this->paginationParams         = $paginationParams;
            $this->redirectUrl              = $redirectUrl;
            $this->uniquePageId             = $uniquePageId;
            $this->gridIdSuffix             = $uniquePageId;
            $this->gridId                   = 'list-view';
            $this->params                   = $params;
            $this->renderToUserString       = $renderToUserString;
        }

        protected function renderContent()
        {
            $cClipWidget = new CClipWidget();
            $cClipWidget->beginClip("ListView");
            $cClipWidget->widget($this->getGridViewWidgetPath(), $this->getCGridViewParams());
            $cClipWidget->endClip();
            return $cClipWidget->getController()->clips['ListView'] . "\n";
        }

        protected static function getGridTemplate()
        {
            $preloader = '<div class="list-preloader"><span class="z-spinner"></span></div>';
            return "\n{items}\n{pager}" . $preloader;
        }

        public static function getDefaultMetadata()
        {
            $metadata = array(
                'global' => array(
                    'panels' => array(
                        array(
                            'rows' => array(
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'null', 'type' => 'SocialItemAndComments'),
                                            ),
                                        ),
                                    )
                                ),
                            ),
                        ),
                    ),
                ),

            );
            return $metadata;
        }

        protected function getCGridViewParams()
        {
            return array_merge(parent::getCGridViewParams(), array('hideHeader' => true));
        }

        protected function getCGridViewLastColumn()
        {
            return array();
        }

        protected function getCGridViewPagerParams()
        {
            return array(
                    'prevPageLabel'    => '<span>previous</span>',
                    'nextPageLabel'    => '<span>next</span>',
                    'class'            => 'EndlessListLinkPager',
                    'paginationParams' => $this->paginationParams,
                    'route'            => $this->paginationRoute,
                );
        }

        /**
         * Override to not run global eval, since it causes doubling up of ajax requests on the pager.
         * (non-PHPdoc)
         * @see ListView::getCGridViewAfterAjaxUpdate()
         */
        protected function getCGridViewAfterAjaxUpdate()
        {
            // Begin Not Coding Standard
            return 'js:function(id, data) {
                        processAjaxSuccessError(id, data);
                    }';
            // End Not Coding Standard
        }

        public function isUniqueToAPage()
        {
            return false;
        }

        protected function getShowTableOnEmpty()
        {
            return false;
        }

        public function getRenderToUserString()
        {
            return $this->renderToUserString;
        }
    }
?>