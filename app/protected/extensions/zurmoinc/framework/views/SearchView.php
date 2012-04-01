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
     * The base View for a module's search view.
     */
    abstract class SearchView extends ModelView
    {
        protected $gridIdSuffix;
        protected $hideAllSearchPanelsToStart;

        /**
         * Constructs a detail view specifying the controller as
         * well as the model that will have its details displayed.
         */
        public function __construct($model,
            $listModelClassName,
            $gridIdSuffix = null,
            $hideAllSearchPanelsToStart = false
            )
        {
            assert('$model != null');
            assert('is_string($listModelClassName)');
            assert('is_bool($hideAllSearchPanelsToStart)');
            $this->model                      = $model;
            $this->listModelClassName         = $listModelClassName;
            $this->gridIdSuffix               = $gridIdSuffix;
            $this->gridId                     = 'list-view';
            $this->hideAllSearchPanelsToStart = $hideAllSearchPanelsToStart;
        }

        /**
         * Renders content for a view including search form including
         * two panels, the second of which is hidden on default, and
         * bottom panel with a search buttom and 'advanced search' link
         * and form layout.
         * @return A string containing the element's content.
         */
        protected function renderContent()
        {
            $content = '<div class="wide form">';
            $clipWidget = new ClipWidget();
            list($form, $formStart) = $clipWidget->renderBeginWidget(
                                                                'NoRequiredsActiveForm',
                                                                array('id' => $this->getSearchFormId(), 'enableAjaxValidation' => false)
                                                            );
            $content .= $formStart;
            $content .= $this->renderFormLayout($form);
            $content .= $this->renderAfterFormLayout($form);
            $formEnd  = $clipWidget->renderEndWidget();
            $content .= $formEnd;

            $content .= '</div>';
            return $content;
        }

        protected function renderAfterFormLayout($form)
        {
            Yii::app()->clientScript->registerScriptFile(
                Yii::app()->getAssetManager()->publish(
                    Yii::getPathOfAlias('ext.zurmoinc.framework.views.assets') . '/dropDownInteractions.js'));
            Yii::app()->clientScript->registerScriptFile(
                Yii::app()->getAssetManager()->publish(
                    Yii::getPathOfAlias('ext.zurmoinc.framework.views.assets') . '/jquery.dropkick-1.0.0.js'));
        }


        /**
         * Renders the bottom panel of the layout. Includes the search button
         * and the advanced search link that opens/closes the second panel
         * @return A string containing the element's content.
         */
        protected function renderFormBottomPanel()
        {
            $moreSearchOptionsLink = CHtml::link(Yii::t('Default', 'Advanced'), '#', array('id' => 'more-search-link' . $this->gridIdSuffix));
            $clearSearchLink = CHtml::link(Yii::t('Default', 'Clear'), '#', array('id' => 'clear-search-link' . $this->gridIdSuffix));
            $cs = Yii::app()->getClientScript();
            $cs->registerScriptFile(
                Yii::app()->getAssetManager()->publish(
                    Yii::getPathOfAlias('ext.zurmoinc.framework.views.assets') . '/FormUtils.js'
                    ),
                CClientScript::POS_END
            );
            Yii::app()->clientScript->registerScript('search', "
                $('#clear-search-link" . $this->gridIdSuffix . "').clearform(
                    {
                        form: '#" . $this->getSearchFormId() . "'
                    }
                );
                $('#more-search-link" . $this->gridIdSuffix . "').click( function()
                    {
                        $('.search-view-1').toggle();
                        return false;
                    }
                );
                $('#cancel-advanced-search').click(function(){
                    $('.search-view-1').hide();
                });
                $('#clear-search-link" . $this->gridIdSuffix . "').click( function()
                    {
                        $(this).closest('form').submit();
                        return false;
                    }
                );
                $('#search-advanced-search').click( function()
                    {
                        $('.search-view-0').children(\"input[type='text']\").val('');
                        $('.search-view-1').hide();
                        $(this).closest('form').submit();
                        return false;
                    }
                );
                $('#" . $this->getSearchFormId() . "').submit(function()
                    {
                        $('#" . $this->gridId . $this->gridIdSuffix . "-selectedIds').val(null);
                        $('#" . $this->gridId . $this->gridIdSuffix . "-selectAll').val(null);
                        $.fn.yiiGridView.update('" . $this->gridId . $this->gridIdSuffix . "',
                        {
                            data: $(this).serialize() + '&" . $this->listModelClassName . "_page=&" . // Not Coding Standard
                            $this->listModelClassName . "_sort=" .
                            $this->getExtraQueryPartForSearchFormScriptSubmitFunction() ."' " . // Not Coding Standard
                        "}
                        );
                        return false;
                    }
                );
            " . $this->getExtraRenderFormBottomPanelScriptPart());
            $startingDivStyle = null;
            if ($this->hideAllSearchPanelsToStart)
            {
                $startingDivStyle = "style='display:none;'";
            }
            $content  = '<div class="search-form-tools">';
            $content .= $moreSearchOptionsLink . '&#160;|&#160;';
            $content .= $clearSearchLink;
            $content .= $this->renderFormBottomPanelExtraLinks();
            $content .= '</div>';
            return $content;
        }

        /**
         * Override as needed.
         */
        protected function renderFormBottomPanelExtraLinks()
        {
            return null;
        }

        /**
         * Override as needed.
         */
        protected function getExtraQueryPartForSearchFormScriptSubmitFunction()
        {
            return null;
        }

        /**
         * Override as needed.
         */
        protected function getExtraRenderFormBottomPanelScriptPart()
        {
            return null;
        }

        /**
         * Render a search form that has two panels. The
         * second panel is hidden by default in the user interface.
         * @return A string containing the element's content.
         */
        protected function renderFormLayout($form = null)
        {
            $metadata       = self::getMetadata();
            $maxCellsPerRow = $this->getMaxCellsPerRow();
            $content        = '';//'<table>';
            $content       .= TableUtil::getColGroupContent($this->getColumnCount($metadata['global']));
            assert('count($metadata["global"]["panels"]) == 2');
            foreach ($metadata['global']['panels'] as $key => $panel)
            {
                $startingDivStyle = "";
                if ($key == 1 || $this->hideAllSearchPanelsToStart)
                {
                    $startingDivStyle = "style='display:none;'";
                }
                $content .= '<div class="search-view-' . $key . '" ' . $startingDivStyle . '>';
                foreach ($panel['rows'] as $row)
                {
                    //$content .= '<tr>';
                    foreach ($row['cells'] as $cell)
                    {
                        if (!empty($cell['elements']))
                        {
                            foreach ($cell['elements'] as $elementInformation)
                            {
                                if (count($row['cells']) == 1 && count($row['cells']) < $maxCellsPerRow)
                                {
                                    $elementInformation['wide'] = true;
                                }
                                $elementclassname = $elementInformation['type'] . 'Element';
                                $element = new $elementclassname($this->model, $elementInformation['attributeName'], $form, array_slice($elementInformation, 2));
                                $content .= $element->render();
                            }
                        }
                    }
                    //$content .= '</tr>';
                }
                if($key == 1)
                {
                    $content .= '<div class="view-toolbar-container clearfix">';
                    $content .= '<div class="form-toolbar">';
                    $content .= CHtml::button(Yii::t('Default', 'Cancel'), array('id' => 'cancel-advanced-search'));
                    $content .= CHtml::button(Yii::t('Default', 'Search'), array('id' => 'search-advanced-search'));
                    $content .= '</div></div>';
                }
                $content .= '</div>';
            }
            $content .= $this->renderFormBottomPanel();
            //$content .= '</table>';
            return $content;
        }

        /**
         * Returns meta data for use in automatically generating the view.
         * The meta data is comprised of two panels, n rows, and then n cells. Each
         * cell can have 1 or more elements.
         *
         * For search view, there should only be two panels.
         * The second panel is hidden by default in the user interface and is where the 'advanced search'
         * inputs are placed.
         *
         * The element takes 3 parameters.
         * The first parameter is 'attributeName' The
         * second parameter is 'type' and refers to the element type. Using a
         * type of 'Text' would utilize the TextElement class. The third parameter
         * is 'wide' and refers to how many cells the field should span. An example
         * of the 'wide' => true usage would be for a text description field.
         * Here is an example meta data that
         * defines a search layout with two panels. Each panel has 1 row with 2 cells each
         *
         * @code
            <?php
                $metadata = array(
                    'global' => array(
                        'panels' => array(
                            array(
                                'title' => 'Basic Search',
                                'rows' => array(
                                    array('cells' =>
                                        array(
                                            array(
                                                'elements' => array(
                                                    array('attributeName' => 'name', 'type' => 'Text'),
                                                ),
                                            ),
                                            array(
                                                'elements' => array(
                                                    array('attributeName' => 'officePhone', 'type' => 'Text'),
                                                ),
                                            ),
                                        )
                                    ),
                                ),
                            ),
                            array(
                                'title' => 'Advanced Search',
                                'rows' => array(
                                    array('cells' =>
                                        array(
                                            array(
                                                'elements' => array(
                                                    array('attributeName' => 'industry', 'type' => 'DropDown'),
                                                ),
                                            ),
                                            array(
                                                'elements' => array(
                                                    array('attributeName' => 'officeFax', 'type' => 'Text'),
                                                ),
                                            ),
                                        )
                                    ),
                                ),
                            ),
                        ),
                    ),
                );
            ?>
         * @endcode
         *
         */
        public static function getDefaultMetadata()
        {
            return array();
        }

        protected function getColumnCount($metadata)
        {
            $columnCount = 1;
            foreach ($metadata['panels'] as $panel)
            {
                foreach ($panel['rows'] as $row)
                {
                    $tempCount = 0;
                    foreach ($row['cells'] as $cell)
                    {
                        $tempCount++;
                    }
                    if ($tempCount > $columnCount)
                    {
                        $columnCount = $tempCount;
                    }
                }
            }
            return $columnCount;
        }

        public static function getDesignerRulesType()
        {
            return 'SearchView';
        }

        protected function getSearchFormId()
        {
            return 'search-form' . $this->gridIdSuffix;
        }

        protected function getMaxCellsPerRow()
        {
            $designerRulesType = self::getDesignerRulesType();
            $designerRulesClassName = $designerRulesType . 'DesignerRules';
            $designerRules = new $designerRulesClassName();
            return $designerRules->maxCellsPerRow();
        }
    }
?>
