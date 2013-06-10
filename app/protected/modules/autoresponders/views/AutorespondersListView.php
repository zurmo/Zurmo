<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
     *
     * Zurmo is free software; you can redistribute it and/or modify it under
     * the terms of the GNU Affero General Public License version 3 as published by the
     * Free Software Foundation with the addition of the following permission added
     * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
     * IN WHICH THE COPYRIGHT IS OWNED BY ZURMO, ZURMO DISCLAIMS THE WARRANTY
     * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
     *
     * Zurmo is distributed in the hope that it will be useful, but WITHOUT
     * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
     * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
     * details.
     *
     * You should have received a copy of the GNU Affero General Public License along with
     * this program; if not, see http://www.gnu.org/licenses or write to the Free
     * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
     * 02110-1301 USA.
     *
     * You can contact Zurmo, Inc. with a mailing address at 27 North Wacker Drive
     * Suite 370 Chicago, IL 60606. or at email address contact@zurmo.com.
     *
     * The interactive user interfaces in original and modified versions
     * of this program must display Appropriate Legal Notices, as required under
     * Section 5 of the GNU Affero General Public License version 3.
     *
     * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
     * these Appropriate Legal Notices must retain the display of the Zurmo
     * logo and Zurmo copyright notice. If the display of the logo is not reasonably
     * feasible for technical reasons, the Appropriate Legal Notices must display the words
     * "Copyright Zurmo Inc. 2013. All rights reserved".
     ********************************************************************************/

    class AutorespondersListView extends ListView
    {
        // TODO: @Shoaibi: Low: Possible refactoring with MarketingListMembersListView
        /**
         * Form that has the information for how to display the marketing list member view.
         * @var object MarketingListMembersConfigurationForm
         */
        protected $configurationForm;

        /**
         * Ajax url to use after actions are completed from the user interface for a portlet.
         * @var string
         */
        protected $portletDetailsUrl;

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

        protected  $params;

        /**
         * Associated moduleClassName of the containing view.
         * @var string
         */
        protected $containerModuleClassName;

        public static function getDefaultMetadata()
        {
            $metadata = array(
                'global' => array(
                    'rowMenu' => array(
                        'elements' => array(
                            array('type'                            => 'AutoresponderEditLink',
                                  'redirectUrl'                     => 'eval:$this->redirectUrl'),
                            array('type'                            => 'AutoresponderDeleteListRow'),
                        ),
                    ),
                     'panels' => array(
                        array(
                            'rows' => array(
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'null', 'type' => 'AutoresponderSubject'),
                                            ),
                                        ),
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
            );
            return $metadata;
        }

        public function __construct(RedBeanModelsDataProvider $dataProvider,
                                $configurationForm,
                                $controllerId,
                                $moduleId,
                                $portletDetailsUrl,
                                $redirectUrl,
                                $uniquePageId,
                                $params,
                                $containerModuleClassName)
        {
            assert('is_string($controllerId)');
            assert('is_string($moduleId)');
            assert('is_string($portletDetailsUrl)');
            assert('is_string($redirectUrl)');
            assert('is_string($uniquePageId)');
            assert('is_array($params)');
            assert('is_string($containerModuleClassName)');
            $this->dataProvider             = $dataProvider;
            $this->configurationForm        = $configurationForm;
            $this->controllerId             = $controllerId;
            $this->moduleId                 = $moduleId;
            $this->portletDetailsUrl        = $portletDetailsUrl;
            $this->redirectUrl              = $redirectUrl;
            $this->uniquePageId             = $uniquePageId;
            $this->gridIdSuffix             = $uniquePageId;
            $this->gridId                   = 'list-view';
            $this->params                   = $params;
            $this->containerModuleClassName = $containerModuleClassName;
            $this->rowsAreSelectable        = false;
            $this->selectedIds              = array();
            $this->modelClassName           = 'Autoresponder';
        }

        public function isUniqueToAPage()
        {
            return false;
        }

        public function getContainerModuleClassName()
        {
            return $this->containerModuleClassName;
        }

        protected function renderContent()
        {
            $content = $this->renderConfigurationForm();
            $content .= parent::renderContent();
            return $content;
        }

        protected function renderConfigurationForm()
        {
            $formName   = 'marketing-list-autoresponder-configuration-form';
            $clipWidget = new ClipWidget();
            list($form, $formStart) = $clipWidget->renderBeginWidget(
                'ZurmoActiveForm',
                array(
                    'id' => $formName,
                )
            );
            $content    = $formStart;
            $content   .= $this->renderSummaryCloneContent();
            $content   .= $clipWidget->renderEndWidget();
            return $content;
        }

        protected function getCGridViewLastColumn()
        {
            return array();
        }

        protected static function getPagerCssClass()
        {
            return 'pager horizontal';
        }

        protected function getCGridViewPagerParams()
        {
            return array(
                'firstPageLabel'   => '<span>first</span>',
                'prevPageLabel'    => '<span>previous</span>',
                'nextPageLabel'    => '<span>next</span>',
                'lastPageLabel'    => '<span>last</span>',
                'class'            => 'SimpleListLinkPager',
                'paginationParams' => array_merge(GetUtil::getData(), array('portletId' => $this->getPortletId())),
                'route'            => 'defaultPortlet/details',
            );
        }

        protected function getCGridViewParams()
        {
            return array_merge(parent::getCGridViewParams(),
                        array('hideHeader'     => true,
                              'itemsCssClass'  => 'items stacked-list'));
        }

        protected function getPortletId()
        {
            return ArrayUtil::getArrayValueWithExceptionIfNotFound($this->params, 'portletId');
        }

        protected function renderSummaryCloneContent()
        {
            return ZurmoHtml::tag('div', array('class' => 'list-view-items-summary-clone'), '');
        }
    }
?>