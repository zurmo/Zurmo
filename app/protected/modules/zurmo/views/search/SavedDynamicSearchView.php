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
     * The Zurmo base search view for a module's search view.  Includes extra pieces like filtered lists.
     */
    abstract class SavedDynamicSearchView extends DynamicSearchView
    {
        public function __construct($model,
            $listModelClassName,
            $gridIdSuffix = null,
            $hideAllSearchPanelsToStart = false
            )
        {
            assert('$model instanceof SavedDynamicSearchForm');
            parent::__construct($model, $listModelClassName, $gridIdSuffix = null, $hideAllSearchPanelsToStart);
        }

        protected function renderDynamicAdvancedSearchRows($panel, $maxCellsPerRow,  $form)
        {
            $content  = $this->renderSavedSearchList();
            $content .= parent::renderDynamicAdvancedSearchRows($panel, $maxCellsPerRow,  $form);
            return $content;
        }

        protected function renderSavedSearchList()
        {
            $savedSearches = SavedSearch::getByOwnerAndViewClassName(Yii::app()->user->userModel, get_class($this));
            $idOrName      = static::getSavedSearchListDropDown();
            $htmlOptions   = array('id' => $idOrName, 'empty' => Zurmo::t('ZurmoModule', 'Load a saved search'));
            if (count($savedSearches) == 0)
            {
                $htmlOptions['style'] = "display:none;";
                $htmlOptions['class'] = 'ignore-style';
                $idOrName      = static::getSavedSearchListDropDown();
                $htmlOptions   = array('id' => $idOrName, 'empty' => Zurmo::t('ZurmoModule', 'Load a saved search'));
                $content       = ZurmoHtml::dropDownList($idOrName,
                                                     $this->model->savedSearchId,
                                                     self::resolveSavedSearchesToIdAndLabels($savedSearches),
                                                     $htmlOptions);
                $this->renderSavedSearchDropDownOnChangeScript($idOrName, $this->model->loadSavedSearchUrl);
                return $content;
            }
            $content       = ZurmoHtml::dropDownList($idOrName,
                                                 $this->model->savedSearchId,
                                                 self::resolveSavedSearchesToIdAndLabels($savedSearches),
                                                 $htmlOptions);
            $this->renderSavedSearchDropDownOnChangeScript($idOrName, $this->model->loadSavedSearchUrl);
            return $content;
        }

        protected static function getSavedSearchListDropDown()
        {
            return 'savedSearchId';
        }

        protected static function resolveSavedSearchesToIdAndLabels($savedSearches)
        {
            $data = array();
            foreach ($savedSearches as $savedSearch)
            {
                $data[$savedSearch->id] = strval($savedSearch);
            }
            return $data;
        }

        protected function renderSavedSearchDropDownOnChangeScript($id, $onChangeUrl)
        {
            //To support adicional params if set $onChangeUrl
            $onChangeUrlParams = parse_url($onChangeUrl);
            if (isset($onChangeUrlParams['query']))
            {
                $onChangeUrl .= "&savedSearchId";
            }
            else
            {
                $onChangeUrl .= "?savedSearchId";
            }
            Yii::app()->clientScript->registerScript('savedSearchLoadScript', "
                $('#" . $id . "').unbind('change'); $('#" . $id . "').bind('change', function()
                {
                    if ($(this).val() != '')
                    {
                        savedSearchId = $(this).val();
                        $.ajax(
                        {
                          url: '" . $this->getClearStickySearchUrlAndParams() . "',
                          complete: function(data)
                          {
                              window.location = '" . $onChangeUrl .  "=' + savedSearchId;
                          }
                        });
                    }
                });");
        }

        protected function getClearStickySearchUrlAndParams()
        {
            return Yii::app()->createUrl('zurmo/default/clearStickySearch/', array('key' => get_class($this)));
        }

        protected function getExtraRenderFormBottomPanelScriptPart()
        {
            return parent::getExtraRenderFormBottomPanelScriptPart() .
                    "$('#save-as-advanced-search').click( function()
                    {
                        $('#save-search-area').show();
                        return false;
                    }
                );";
        }

        protected function renderConfigSaveAjax($formName)
        {
            return     "var inputId = '" . static::getSavedSearchListDropDown() . "';
                        if (data.id != undefined)
                        {
                            var existingSearchFound = false;
                            $('#' + inputId + ' > option').each(function()
                            {
                               if (this.value == data.id)
                               {
                                   $('#' + inputId + ' option[value=\'' + this.value + '\']').text(data.name);
                                   existingSearchFound = true;
                               }
                            });
                            if (!existingSearchFound)
                            {
                                $('#' + inputId).removeClass('ignore-style');
                                $('#' + inputId)
                                    .append($('<option></option>')
                                    .attr('value', data.id)
                                    .text(data.name))
                                //$('#' + inputId).val(data.id); Do not select new saved search since it is not sticky at this point.
                                $('#" . get_class($this->model) . "_savedSearchId').val(data.id);
                            }
                        }
                        $('#" . $formName . "').find('.attachLoadingTarget').removeClass('loading');
                        $('#" . $formName . "').find('.attachLoadingTarget').removeClass('loading-ajax-submit');
                        $('#" . $formName . "').find('.attachLoadingTarget').removeClass('attachLoadingTarget');" .
                       parent::renderConfigSaveAjax($formName);
        }

        protected function renderAfterAddExtraRowContent($form)
        {
            $content  = '<strong class="mp-divider"> &middot; </strong>' . ZurmoHtml::link(Zurmo::t('ZurmoModule', 'Save search'), '#', array('id' => 'save-as-advanced-search'));
            $content  = ZurmoHtml::tag('div', array('class' => 'search-save-container'), $content);
            $content .= '<div id="save-search-area" class="view-toolbar-container clearfix" style="display:none;">';
            $content .= $this->renderSaveInputAndSaveButtonContentForAdvancedSearch($form);
            $content .= '</div>';
            return $content;
        }

        protected function renderSaveInputAndSaveButtonContentForAdvancedSearch($form)
        {
            $content               = $form->textField($this->model, 'savedSearchName');
            $content              .= $form->hiddenField($this->model, 'savedSearchId');
            $params['label']       = Zurmo::t('ZurmoModule', 'Save');
            $params['htmlOptions'] = array('id'      => 'save-advanced-search',
                                           'value'   => 'saveSearch',
                                           'onclick' => 'js:$(this).addClass("attachLoadingTarget");');
            $element               = new SaveButtonActionElement(null, null, null, $params);
            $content .= $element->render();
            $content .= $this->renderDeleteLinkContent();
            $content .= $form->error($this->model, 'savedSearchName');
            return $content;
        }

        protected function renderDeleteLinkContent()
        {
            $htmlOptions = array();
            $attribute   = 'savedSearchId';
            ZurmoHtml::resolveNameID($this->model, $attribute, $htmlOptions);
            // Begin Not Coding Standard
            Yii::app()->clientScript->registerScript('deleteSavedSearchAndRemoveFromViewScript', "
                function deleteSavedSearchAndRemoveFromView(modelId)
                {
                        $.ajax({
                            url : '" . Yii::app()->createUrl('zurmo/default/deleteSavedSearch') . "?id=' + modelId,
                            type : 'GET',
                            dataType : 'json',
                            success : function(data)
                            {
                               var inputId = '" . static::getSavedSearchListDropDown() . "';
                               $('#' + inputId + ' > option').each(function(){
                                   if (this.value == modelId)
                                   {
                                       $('#' + inputId + ' option[value=\'' + this.value + '\']').remove();
                                   }
                               });
                               $('#removeSavedSearch').remove();
                               $('#" . $htmlOptions['id'] . "').val('');
                            },
                            error : function()
                            {
                                //todo: error call
                            }
                        });
                }
            ", CClientScript::POS_END);
            // End Not Coding Standard
            if ($this->model->savedSearchId != null)
            {
                $label = Zurmo::t('ZurmoModule', 'Delete') . "<span class='icon'></span>";
                return ZurmoHtml::link($label, "#", array( 'id'      => 'removeSavedSearch',
                                                           'class'   => 'remove',
                                                           'onclick' => "deleteSavedSearchAndRemoveFromView('" . $this->model->savedSearchId . "')"));
            }
        }

        protected function getExtraRenderForClearSearchLinkScript()
        {
            return parent::getExtraRenderForClearSearchLinkScript() .
                    "$('#" . static::getSavedSearchListDropDown() . "').val();
                     $('#" . get_class($this->model) . "_savedSearchId').val('');
                     $('#save-search-area').hide();
                     jQuery.yii.submitForm(this, '', {}); return false;
            ";
        }
    }
?>