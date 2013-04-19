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

    class MarketingListMembersListView extends SecuredListView
    {
        // TODO: @Shoaibi/@Amit: Low: There is an extra spacing in div of portlet, check whats that all about.
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

        /**
         * True to show the subscription type filter option.
         * @var boolean
         */
        protected $showFilteredBySubscriptionType = true;

        /**
         * True to show the search term filter option.
         * @var boolean
         */
        protected $showFilteredBySearchTerm = true;

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
                            array('type'                            => 'MarketingListMemberSubscribeLink'),
                            array('type'                            => 'MarketingListMemberUnsubscribeLink'),
                            array('type'                            => 'MarketingListMemberDeleteLink'),
                        ),
                    ),
                     'panels' => array(
                        array(
                            'rows' => array(
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'null', 'type' => 'MarketingListMemberNameAndStatus'),
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
                                MarketingListMembersConfigurationForm $configurationForm,
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
            $this->rowsAreSelectable        = true;
            $this->selectedIds              = array();
            $this->modelClassName           = 'MarketingListMember';
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
            $this->registerScriptsForDynamicMemberCountUpdate();
            return $this->renderConfigurationForm() . parent::renderContent();
        }

        protected static function getGridTemplate()
        {
            $preloader = ZurmoHtml::tag('div', array('class' => 'list-preloader'),
                                ZurmoHtml::tag('span', array('class' => 'z-spinner'), '')
                            );
            return "\n{items}\n{pager}" . $preloader;
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

        protected function getPortletId()
        {
            return ArrayUtil::getArrayValueWithExceptionIfNotFound($this->params, 'portletId');
        }

        protected function renderConfigurationForm()
        {
            $formName   = 'marketing-list-member-configuration-form';
            $clipWidget = new ClipWidget();
            list($form, $formStart) = $clipWidget->renderBeginWidget(
                'ZurmoActiveForm',
                array(
                    'id' => $formName,
                )
            );
            $content  = $formStart;
            $content .= $this->renderConfigurationFormLayout($form);
            $formEnd  = $clipWidget->renderEndWidget();
            $content .= $formEnd;
            $this->registerConfigurationFormLayoutScripts($form);
            return $content;
        }

        protected function renderConfigurationFormLayout($form)
        {
            assert('$form instanceof ZurmoActiveForm');
            $content      = null;
            if ($this->showFilteredBySearchTerm)
            {
                $element                    = new TextElement($this->configurationForm,
                                                                'filteredBySearchTerm',
                                                                $form);
                $element->editableTemplate  =  ZurmoHtml::tag('div',
                                                    array('id' => 'MarketingListMembersConfigurationForm_filteredBySearchTerm_area',
                                                          'class' => 'search-without-scope'),
                                                    '{content}');
                $content                    .= $element->render();
            }
            if ($this->showFilteredBySubscriptionType)
            {
                $element                    = new MarketingListsSubscriptionTypeFilterRadioElement($this->configurationForm,
                                                                                'filteredBySubscriptionType',
                                                                                $form);
                $element->editableTemplate  =  ZurmoHtml::tag('div',
                                                    array('id' => 'MarketingListMembersConfigurationForm_filteredBySubscriptionType_area',
                                                            'class' => 'filters-bar'),
                                                    '{content}');
                $content                    .= $element->render();
            }
            return $content;
        }

        protected function registerConfigurationFormLayoutScripts($form)
        {
            if (!($this->showFilteredBySearchTerm || $this->showFilteredBySubscriptionType))
            {
                return;
            }
            assert('$form instanceof ZurmoActiveForm');
            $urlScript = 'js:$.param.querystring("' . $this->portletDetailsUrl . '", "' .
                         $this->dataProvider->getPagination()->pageVar . '=1")'; // Not Coding Standard
            $ajaxSubmitScript = ZurmoHtml::ajax(array(
                'type'       => 'GET',
                'data'       => 'js:$("#' . $form->getId() . '").serialize()',
                'url'        =>  $urlScript,
                'update'     => '#' . $this->uniquePageId,
                'beforeSend' => 'js:function(){makeSmallLoadingSpinner("' . $this->getGridViewId() . '"); $("#' . $form->getId() . '").parent().children(".cgrid-view").addClass("loading");}',
                'complete'   => 'js:function()
                        {
                                            $("#' . $form->getId() . '").parent().children(".cgrid-view").removeClass("loading");
                                            $("#filter-portlet-model-bar-' . $this->uniquePageId . '").show();
                        }'
            ));
            if ($this->showFilteredBySubscriptionType)
            {
                Yii::app()->clientScript->registerScript($this->uniquePageId . '_filteredBySubscriptionType', "
                    createButtonSetIfNotAlreadyExist('#MarketingListMembersConfigurationForm_filteredBySubscriptionType_area');
                    $('#MarketingListMembersConfigurationForm_filteredBySubscriptionType_area').unbind('change.action').bind('change.action', function(event)
                        {
                            " . $ajaxSubmitScript . "
                        }
                    );
                ");
            }
            if ($this->showFilteredBySearchTerm)
            {
                Yii::app()->clientScript->registerScript($this->uniquePageId . '_filteredBySearchTerm', "
                $('#MarketingListMembersConfigurationForm_filteredBySearchTerm_area').unbind('change.action').bind('change.action', function(event)
                    {
                        " . $ajaxSubmitScript . "
                    }
                );
                $('#MarketingListMembersConfigurationForm_filteredBySearchTerm_area').unbind('keypress.action').bind('keypress.action', function(event)
                    {
                        if (event.which == 13)
                        {
                            " . $ajaxSubmitScript . "
                            return false;
                        }
                    }
                );
                ");
            }
        }

        protected function registerScriptsForDynamicMemberCountUpdate()
        {
            // Begin Not Coding Standard
            Yii::app()->clientScript->registerScript($this->uniquePageId.'_dynamicMemberCountUpdate', '
                function updateMemberStats(newCount, oldContent, elementClass)
                {
                    countStrippedOldContent = oldContent.substr(oldContent.indexOf(" "));
                    $(elementClass).html(newCount + countStrippedOldContent);
                }

                $("#' . $this->getGridViewId() . ' .pager .refresh a").unbind("click.dynamicMemberCountUpdate").bind("click.dynamicMemberCountUpdate", function (event)
                {
                    var modelId                 = "' . $this->getModelId() . '";
                    var subscriberCountClass    = ".' . MarketingListDetailsOverlayView::SUBSCRIBERS_STATS_CLASS . '";
                    var unsubscriberCountClass  = ".' . MarketingListDetailsOverlayView::UNSUBSCRIBERS_STATS_CLASS . '";
                    var subscriberHtml          = $(subscriberCountClass).html();
                    var unsubscriberHtml        = $(unsubscriberCountClass).html();
                    var url                     = "' . MarketingListDetailsOverlayView::getMemberCountUpdateUrl() . '";
                    $.ajax(
                            {
                                url:        url,
                                dataType:   "json",
                                data:       { marketingListId: modelId },
                                success:    function(data, status, request) {
                                                updateMemberStats(data.subscriberCount, subscriberHtml, subscriberCountClass);
                                                updateMemberStats(data.unsubscriberCount, unsubscriberHtml, unsubscriberCountClass);
                                            },
                                error:      function(request, status, error) {
                                                // TODO: @Shoaibi/@Jason: Medium: What should we do here?
                                            },
                            }
                        );
                    });
                ');
            // End Not Coding Standard
        }

        protected function getModelId()
        {
            $model = ArrayUtil::getArrayValueWithExceptionIfNotFound($this->params, 'relationModel');
            return $model->id;
        }
    }
?>
