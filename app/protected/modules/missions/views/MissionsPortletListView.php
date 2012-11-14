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
     * Missions list view for use on a portlet.
     */
    class MissionsPortletListView extends MissionsListView
    {
        protected $controllerId;

        protected $moduleId;

        protected $dataProvider;

        /**
         * Form that has the information for how to display the missions view.
         * @var object MissionsListConfigurationForm
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

        protected $params;

        /**
         * Associated moduleClassName of the containing view.
         * @var string
         */
        protected $containerModuleClassName;

        public function __construct(RedBeanModelDataProvider      $dataProvider,
                                    MissionsListConfigurationForm $configurationForm,
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
        }

        protected function renderContent()
        {
            $content  = $this->renderConfigurationForm();
            $cClipWidget = new CClipWidget();
            $cClipWidget->beginClip("ListView");
            $cClipWidget->widget($this->getGridViewWidgetPath(), $this->getCGridViewParams());
            $cClipWidget->endClip();
            $content .= $cClipWidget->getController()->clips['ListView'] . "\n";
            return $content;
        }

        protected static function getGridTemplate()
        {
            $preloader = '<div class="list-preloader"><span class="z-spinner"></span></div>';
            return "\n{items}\n{pager}" . $preloader;
        }

        protected function getCGridViewParams()
        {
            return array_merge(parent::getCGridViewParams(), array('hideHeader' => true));
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
                    'paginationParams' => array_merge(GetUtil::getData(), array('portletId' => $this->params['portletId'])),
                    'route'            => 'defaultPortlet/details',
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

        protected function renderConfigurationForm()
        {
            $formName   = 'missions-list-configuration-form';
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
            $innerContent = null;
            $content .= '<div class="filter-portlet-model-bar">';
            $element                       = new MissionsListTypeFilterRadioElement($this->configurationForm,
                                                                                      'type',
                                                                                      $form);
            $element->editableTemplate =  '<div id="MissionsListConfigurationForm_type_area">{content}</div>';
            $content .= $element->render();
            $content .= '</div>' . "\n";
            return $content;
        }

        protected function registerConfigurationFormLayoutScripts($form)
        {
            assert('$form instanceof ZurmoActiveForm');
            $urlScript = 'js:$.param.querystring("' . $this->portletDetailsUrl . '", "' .
                         $this->dataProvider->getPagination()->pageVar . '=1")'; // Not Coding Standard
            $ajaxSubmitScript = ZurmoHtml::ajax(array(
                    'type'       => 'GET',
                    'data'       => 'js:$("#' . $form->getId() . '").serialize()',
                    'url'        =>  $urlScript,
                    'update'     => '#' . $this->uniquePageId,
                    'beforeSend' => 'js:function(){makeSmallLoadingSpinner("' . $this->getGridViewId() . '"); $("#' . $form->getId() . '").parent().children(".cgrid-view").addClass("loading");}',
                    'complete'   => 'js:function(){$("#' . $form->getId() . '").parent().children(".cgrid-view").removeClass("loading");}'
            ));
            Yii::app()->clientScript->registerScript($this->uniquePageId, "
            $('#MissionsListConfigurationForm_type_area').buttonset();
            $('#MissionsListConfigurationForm_type_area').change(function()
                {
                    " . $ajaxSubmitScript . "
                }
            );
            ");
        }

        public function isUniqueToAPage()
        {
            return false;
        }

        public function getOwnedByFilter()
        {
            return $this->configurationForm->ownedByFilter;
        }

        public function getContainerModuleClassName()
        {
            return $this->containerModuleClassName;
        }
    }
?>