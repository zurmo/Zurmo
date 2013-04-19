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
     * Base view for displaying a global search user interface..
     */
    class MixedModelsSearchView extends View
    {
        private $moduleNamesAndLabelsAndAll;

        private $sourceUrl;

        private $term;

        private $scopeData;

        private $gridIdSuffix;

        public function __construct($moduleNamesAndLabelsAndAll, $sourceUrl, $term, $scopeData, $gridSuffix = null)
        {
            assert('is_array($moduleNamesAndLabelsAndAll)');
            assert('is_string($sourceUrl)');
            $this->moduleNamesAndLabelsAndAll   = $moduleNamesAndLabelsAndAll;
            $this->sourceUrl                    = $sourceUrl;
            $this->term                         = $term;
            $this->scopeData                    = $scopeData;
            $this->gridIdSuffix                 = $gridSuffix;
        }

        protected function renderContent()
        {
            $titleView = new TitleBarView(Zurmo::t('ZurmoModule', 'Global search'), null, 1);
            $content = $titleView->render();
            $model = new MixedModelsSearchForm();
            $model->setGlobalSearchAttributeNamesAndLabelsAndAll($this->moduleNamesAndLabelsAndAll);
            $content .= "<div class='wide form'>";
            $clipWidget = new ClipWidget();
            list($form, $formStart) = $clipWidget->renderBeginWidget(
                    'NoRequiredsActiveForm',
                        array('id'                   => $this->getSearchFormId(),
                              'action'               => $this->sourceUrl,
                              'enableAjaxValidation' => false,
                              'clientOptions'        => array(),
                              'focus'                => array($model, 'term'),
                              'method'               => 'get',
                            )
                    );
            $content .= $formStart;
            //Scope search
            $content .= "<div class='search-view-0'>";
            $scope = new MixedModelsSearchElement($model, 'term',
                    $form, array( 'htmlOptions' => array ('id' => 'term')));
            $scope->setValue($this->term);
            if (isset($this->scopeData))
            {
                $scope->setSelectedValue($this->scopeData);
            }
            $content .= $scope->render();
            //Search button
            $params                = array();
            $params['label']       = Zurmo::t('ZurmoModule', 'Search');
            $params['htmlOptions'] = array('id' => $this->getSearchFormId() . '-search',
                'onclick' => 'js:$(this).addClass("attachLoadingTarget");');
            $element               = new SaveButtonActionElement(null, null, null, $params);
            $content .= $element->render();
            $content .= "</div>";
            $clipWidget->renderEndWidget();
            $content .= "</div>";
            $this->renderScripts();
            return $content;
        }

        protected function getSearchFormId()
        {
            return 'mixed-models-form' . $this->gridIdSuffix;
        }

        protected function renderScripts()
        {
            $searchFormId = $this->getSearchFormId();
            // Submit form and update all listViews
            // Begin Not Coding Standard
            $script   = "
                            $('#{$searchFormId}').unbind('submit');
                            $('#{$searchFormId}').bind('submit', function(event)
                            {
                                //Makes spinner on button search
                                beforeValidateAction($('#{$searchFormId}'));
                                var listData = $(this).serialize();
                                var list = '';
                                $('div.cgrid-view').each(function(index)
                                {
                                    list = $(this).attr('id');
                                    //get name of module
                                    var array_list = list.split('-');
                                    var module = array_list[2];
                                    //If all or module selected make it visible else invisible
                                    if ($('#MixedModelsSearchForm_anyMixedAttributesScope option[value=All]').prop('selected') ||
                                            $('#MixedModelsSearchForm_anyMixedAttributesScope option[value=' + module + ']').prop('selected'))
                                    {
                                        $('#' + list  + '').parent('div').show().prev().show();
                                    }
                                    else
                                    {
                                        $('#' + list  + '').parent('div').hide().prev().hide();
                                    }
                                    $.fn.yiiGridView.update(list,
                                    {
                                        data: listData,
                                        //Removes spin on search button
                                        complete: function(jqXHR, status)
                                        {
                                            if (status=='success')
                                            {
                                                $('#{$searchFormId}').find('.attachLoadingTarget').removeClass('loading');
                                                $('#{$searchFormId}').find('.attachLoadingTarget').removeClass('loading-ajax-submit');
                                            }
                                        }
                                    });
                                });
                                return false;
                            });
                         ";
            // End Not Coding Standard
            Yii::app()->clientScript->registerScript('mixedModelsSearchAjaxSubmit', $script);
        }
    }
?>