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
     * Base view for displaying a global search user interface..
     */
    class GlobalSearchView extends View
    {
        protected $moduleNamesAndLabelsAndAll;

        protected $sourceUrl;

        public function __construct($moduleNamesAndLabelsAndAll, $sourceUrl)
        {
            assert('is_array($moduleNamesAndLabelsAndAll)');
            assert('is_string($sourceUrl)');
            $this->moduleNamesAndLabelsAndAll = $moduleNamesAndLabelsAndAll;
            $this->sourceUrl            = $sourceUrl;
        }

        protected function renderContent()
        {
            $content  = '<div><div style="float:right;">' . $this->renderGlobalSearchContent() . '</div>';
            $content .= '</div>&#160;';
            return $content;
        }

        protected function renderGlobalSearchContent()
        {
            if(count($this->moduleNamesAndLabelsAndAll) == 1)
            {
                return null;
            }
            $content                 = $this->renderGlobalSearchScopingInputContent();
            $hintMessage             = Yii::t('Default', 'Search by name, phone, or e-mail');
            $htmlOptions             = array('class'   => 'global-search global-search-hint',
                                             'onfocus' => '$(this).removeClass("global-search-hint"); $(this).val("");',
                                             'onblur'  => '$(this).val("")');
            $cClipWidget             = new CClipWidget();
            $cClipWidget->beginClip('GlobalSearchElement');
            $cClipWidget->widget('zii.widgets.jui.CJuiAutoComplete', array(
                'name'        => 'globalSearchInput',
                'id'          => 'globalSearchInput',
                'value'       => $hintMessage,
                'source'      => $this->sourceUrl,
                'htmlOptions' => $htmlOptions,
                'options'     => array('select' => 'js: function(event, ui) {if (ui.item.href.length > 0)' .
                                                   '{window.location = ui.item.href;} return false;}')
            ));
            $cClipWidget->endClip();
            $content .= '&#160;' . $cClipWidget->getController()->clips['GlobalSearchElement'];
            // Begin Not Coding Standard
            $script = '$(".ui-autocomplete").position({
                            my: "right top",
                            at: "right bottom",
                            of: $("#globalSearchInput"),
                            collision: "flip flip"});';
            /// End Not Coding Standard
            Yii::app()->clientScript->registerScript('GlobalSearchElementPosition', $script);
            return $content;
        }

        protected function renderGlobalSearchScopingInputContent()
        {
            $imagePath            = Yii::app()->baseUrl . '/themes/default/images/searchIcon.gif';
            $cClipWidget   = new CClipWidget();
            $cClipWidget->beginClip("JuiMultiSelect");
            $cClipWidget->widget('ext.zurmoinc.framework.widgets.JuiMultiSelect', array(
                'dataAndLabels'  => $this->moduleNamesAndLabelsAndAll,
                'selectedValue'  => 'All',
                'inputId'        => 'globalSearchScope',
                'inputName'      => 'globalSearchScope',
                'options'        => array(
                                          'selectedText' => '',
                                          'noneSelectedText' => '', 'header' => false,
                                          'position' => array('my' =>  'right top', 'at' => 'right bottom'))
            ));
            $cClipWidget->endClip();
            $content = $cClipWidget->getController()->clips['JuiMultiSelect'];
            // Begin Not Coding Standard
            $script = '$("#globalSearchScope").multiselect("getButton").width("25");
                       $("#globalSearchScope").multiselect("getButton").html(\'<span>' .
                            CHtml::image($imagePath, 'Search Icon') . '</span>\');
                       $("#globalSearchScope").bind("multiselectclick", function(event, ui){
                            if($("#globalSearchScope").multiselect("widget").find(":checkbox:checked").length == 0)
                            {
                                $("#globalSearchScope").multiselect("widget").find(":checkbox").each(function(){
                                    if(this.value == "All" && !this.checked)
                                    {
                                        this.click();
                                    }
                                });
                            }
                            if(ui.value == "All" && ui.checked)
                            {
                                $("#globalSearchScope").multiselect("widget").find(":checkbox").each(function(){
                                    if(this.value != "All" && this.checked)
                                    {
                                        this.click();
                                    }
                                });
                            }
                            else if(ui.value != "All" && ui.checked)
                            {
                                $("#globalSearchScope").multiselect("widget").find(":checkbox").each(function(){
                                    if(this.value == "All" && this.checked)
                                    {
                                        this.click();
                                    }
                                });
                            }
                        });
                        $("#globalSearchInput").bind("focus", function(event, ui){
                            $("#globalSearchInput").autocomplete("option", "source", "' . $this->sourceUrl . '&" + $.param($("#globalSearchScope").serializeArray()));
                        });
                       ';
            /// End Not Coding Standard
            Yii::app()->clientScript->registerScript('GlobalSearchScopeChanges', $script);
            return $content;
        }
    }
?>
