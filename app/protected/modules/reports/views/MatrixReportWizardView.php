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
     * Class for working with the matrix reports in the report wizard
     */
    class MatrixReportWizardView extends ReportWizardView
    {
        /**
         * @return string
         */
        public function getTitle()
        {
            return parent::getTitle() . ' - ' . Zurmo::t('ReportsModule', 'Matrix');
        }

        /**
         * @param WizardActiveForm $form
         * @return string
         */
        protected function renderContainingViews(WizardActiveForm $form)
        {
            $moduleForReportWizardView            = new ModuleForReportWizardView ($this->model, $form);
            $filtersForReportWizardView           = new FiltersForReportWizardView($this->model, $form, true);
            $groupBysForReportWizardView          = new GroupBysForReportWizardView($this->model, $form, true);
            $displayAttributesForReportWizardView = new DisplayAttributesForReportWizardView($this->model, $form, true);
            $generalDataForReportWizardView       = new GeneralDataForReportWizardView($this->model, $form, true);

            $gridView = new GridView(5, 1);
            $gridView->setView($moduleForReportWizardView, 0, 0);
            $gridView->setView($filtersForReportWizardView, 1, 0);
            $gridView->setView($groupBysForReportWizardView, 2, 0);
            $gridView->setView($displayAttributesForReportWizardView, 3, 0);
            $gridView->setView($generalDataForReportWizardView, 4, 0);
            return $gridView->render();
        }

        /**
         * @param string $formName
         * @return string
         */
        protected function renderConfigSaveAjax($formName)
        {
            assert('is_string($formName)');
            return     "linkId = $('#" . $formName . "').find('.attachLoadingTarget').attr('id');
                        if (linkId == '" . ModuleForReportWizardView::getNextPageLinkId() . "')
                        {
                            $('#" . static::getValidationScenarioInputId() . "').val('" .
                                ReportWizardForm::FILTERS_VALIDATION_SCENARIO . "');
                            $('#ModuleForReportWizardView').hide();
                            " . $this->renderTreeViewAjaxScriptContent($formName, 'FiltersForReportWizardView') . "
                            $('#FiltersForReportWizardView').show();
                        }
                        if (linkId == '" . FiltersForReportWizardView::getNextPageLinkId() . "')
                        {
                            $('#" . static::getValidationScenarioInputId() . "').val('" .
                                ReportWizardForm::GROUP_BYS_VALIDATION_SCENARIO . "');
                            $('#FiltersForReportWizardView').hide();
                            " . $this->renderTreeViewAjaxScriptContent($formName, 'GroupBysForReportWizardView') . "
                            $('#GroupBysForReportWizardView').show();
                        }
                        if (linkId == '" . GroupBysForReportWizardView::getNextPageLinkId() . "')
                        {
                            $('#" . static::getValidationScenarioInputId() . "').val('" .
                                ReportWizardForm::DISPLAY_ATTRIBUTES_VALIDATION_SCENARIO . "');
                            $('#GroupBysForReportWizardView').hide();
                            " . $this->renderTreeViewAjaxScriptContent($formName, 'DisplayAttributesForReportWizardView') . "
                            $('#DisplayAttributesForReportWizardView').show();
                        }
                        if (linkId == '" . DisplayAttributesForReportWizardView::getNextPageLinkId() . "')
                        {
                            $('#" . static::getValidationScenarioInputId() . "').val('" .
                                ReportWizardForm::ORDER_BYS_VALIDATION_SCENARIO . "');
                            $('#DisplayAttributesForReportWizardView').hide();
                            $('#GeneralDataForReportWizardView').show();
                        }
                        if (linkId == '" . GeneralDataForReportWizardView::getNextPageLinkId() . "')
                        {
                            " . $this->getSaveAjaxString($formName) . "
                        }
                        else
                        {
                            $('#" . $formName . "').find('.attachLoadingTarget').removeClass('loading');
                            $('#" . $formName . "').find('.attachLoadingTarget').removeClass('loading-ajax-submit');
                            $('#" . $formName . "').find('.attachLoadingTarget').removeClass('attachLoadingTarget');
                        }
                        ";
        }

        protected function registerClickFlowScript()
        {
            Yii::app()->clientScript->registerScript('clickflow', "
                $('#" . ModuleForReportWizardView::getPreviousPageLinkId() . "').unbind('click');
                $('#" . ModuleForReportWizardView::getPreviousPageLinkId() . "').bind('click', function()
                    {
                        url = '" . Yii::app()->createUrl('reports/default/index') . "';
                        window.location.href = url;
                        return false;
                    }
                );
                $('#" . FiltersForReportWizardView::getPreviousPageLinkId() . "').unbind('click');
                $('#" . FiltersForReportWizardView::getPreviousPageLinkId() . "').bind('click', function()
                    {
                        $('#" . static::getValidationScenarioInputId() . "').val('" . ReportWizardForm::MODULE_VALIDATION_SCENARIO . "');
                        $('#" . WizardActiveForm::makeErrorsSummaryId(static::getFormId()) . "').hide();
                        $('#ModuleForReportWizardView').show();
                        $('#FiltersForReportWizardView').hide();
                        return false;
                    }
                );
                $('#" . GroupBysForReportWizardView::getPreviousPageLinkId() . "').unbind('click');
                $('#" . GroupBysForReportWizardView::getPreviousPageLinkId() . "').bind('click', function()
                    {
                        $('#" . static::getValidationScenarioInputId() . "').val('" . ReportWizardForm::FILTERS_VALIDATION_SCENARIO . "');
                        $('#FiltersForReportWizardView').show();
                        $('#GroupBysForReportWizardView').hide();
                        return false;
                    }
                );
                $('#" . DisplayAttributesForReportWizardView::getPreviousPageLinkId() . "').unbind('click');
                $('#" . DisplayAttributesForReportWizardView::getPreviousPageLinkId() . "').bind('click', function()
                    {
                        $('#" . static::getValidationScenarioInputId() . "').val('" . ReportWizardForm::GROUP_BYS_VALIDATION_SCENARIO . "');
                        $('#GroupBysForReportWizardView').show();
                        $('#DisplayAttributesForReportWizardView').hide();
                        return false;
                    }
                );
                $('#" . GeneralDataForReportWizardView::getPreviousPageLinkId() . "').unbind('click');
                $('#" . GeneralDataForReportWizardView::getPreviousPageLinkId() . "').bind('click', function()
                    {
                        $('#" . static::getValidationScenarioInputId() . "').val('" . ReportWizardForm::ORDER_BYS_VALIDATION_SCENARIO . "');
                        $('#DisplayAttributesForReportWizardView').show();
                        $('#GeneralDataForReportWizardView').hide();
                        return false;
                    }
                );
            ");
        }

        protected function registerModuleClassNameChangeScriptExtraPart()
        {
            return  "   $('#GroupBysForReportWizardView').find('.dynamic-rows').find('ul:first').find('li').remove();
                        $('#GroupBysTreeArea').html('');
                        $('." . GroupBysForReportWizardView::getZeroComponentsClassName() . "').show();
                    ";
        }
    }
?>