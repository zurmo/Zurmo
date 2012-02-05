<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2011 Zurmo Inc.
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
     * Latest activity view.
     */
    class LatestActivitiesView extends MetadataView
    {
        /**
         * View type for a latest activity list view.
         * @var integer
         */
        const VIEW_TYPE_LISTVIEW = 1;

        /**
         * View type for a latest activity summary view.
         * @var intger
         */
        const VIEW_TYPE_SUMMARY  = 2;

        protected $controllerId;

        protected $moduleId;

        protected $dataProvider;

        /**
         * Form that has the information for how to display the latest activity view.
         * @var object LatestActivitiesConfigurationForm
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

        public function __construct(RedBeanModelsDataProvider $dataProvider,
                                    LatestActivitiesConfigurationForm $configurationForm,
                                    $controllerId,
                                    $moduleId,
                                    $portletDetailsUrl,
                                    $redirectUrl,
                                    $uniquePageId)
        {
            assert('is_string($controllerId)');
            assert('is_string($moduleId)');
            assert('is_string($portletDetailsUrl)');
            assert('is_string($redirectUrl)');
            assert('is_string($uniquePageId)');
            $this->dataProvider           = $dataProvider;
            $this->configurationForm      = $configurationForm;
            $this->controllerId           = $controllerId;
            $this->moduleId               = $moduleId;
            $this->portletDetailsUrl      = $portletDetailsUrl;
            $this->redirectUrl            = $redirectUrl;
            $this->uniquePageId           = $uniquePageId;
        }

        protected function renderContent()
        {
            $content  = $this->renderConfigurationForm();
            $cClipWidget = new CClipWidget();
            $cClipWidget->beginClip("LatestActivtiesViewLayout");
            $cClipWidget->widget($this->getViewLayoutWidgetPath(), array(
                'id'            => $this->uniquePageId,
                'dataProvider'  => $this->dataProvider,
                'url'           => $this->portletDetailsUrl,
                'redirectUrl'   => $this->redirectUrl,
            ));
            $cClipWidget->endClip();
            $content .= $cClipWidget->getController()->clips['LatestActivtiesViewLayout'];
            return $content;
        }

        protected function renderConfigurationForm()
        {
            $formName   = 'latest-activity-configuration-form';
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
            $element                   = new LatestActivitiesRollUpCheckBoxElement($this->configurationForm, 'rollup',
                                                                                 $form);
            $element->editableTemplate = '{content}{label}';
            $rollupElementContent      = $element->render();
            $element                   = new LatestActivitiesOwnedByFilterRadioElement($this->configurationForm,
                                                                                      'ownedByFilter',
                                                                                      $form);
            $element->editableTemplate =  '<div style="float:left;"' .
                                          ' id="LatestActivitiesConfigurationForm_ownedByFilter">{content}</div>';
            $ownedByFilterContent      = $element->render();

            $content  = '<div class="horizontal-line latest-activity-toolbar">';
            $content .= $ownedByFilterContent;
            $content .= '<div class="latest-activity-rollup">' . $rollupElementContent . '</div>';
            $content .= '</div>' . "\n";
            return $content;
        }

        protected function registerConfigurationFormLayoutScripts($form)
        {
            assert('$form instanceof ZurmoActiveForm');
            $urlScript = 'js:$.param.querystring("' . $this->portletDetailsUrl . '", "' .
                         $this->dataProvider->getPagination()->pageVar . '=1")'; // Not Coding Standard
            $ajaxSubmitScript = CHtml::ajax(array(
                    'type' => 'GET',
                    'data' => 'js:$("#' . $form->getId() . '").serialize()',
                    'url'  =>  $urlScript,
                    'update' => '#' . $this->uniquePageId,
            ));
            Yii::app()->clientScript->registerScript($this->uniquePageId, "
            $('#LatestActivitiesConfigurationForm_rollup').button();
            $('#LatestActivitiesConfigurationForm_ownedByFilter').buttonset();
            $('#LatestActivitiesConfigurationForm_rollup').click(function()
                {
                    " . $ajaxSubmitScript . "
                }
            );
            $('#LatestActivitiesConfigurationForm_ownedByFilter').change(function()
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

        protected function getViewLayoutWidgetPath()
        {
            if ($this->configurationForm->viewType == self::VIEW_TYPE_LISTVIEW)
            {
                return 'application.modules.activities.widgets.LatestActivitiesListViewLayout';
            }
            elseif ($this->configurationForm->viewType == self::VIEW_TYPE_SUMMARY)
            {
                return 'application.modules.activities.widgets.LatestActivitiesSummaryViewLayout';
            }
            else
            {
                throw new NotSupportedException();
            }
        }
    }
?>