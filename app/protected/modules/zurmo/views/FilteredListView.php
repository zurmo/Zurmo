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
     * The base View for a module's filtered list view.
     * This view shows a selector and links for different
     * filtered list options.
     */
    class FilteredListView extends MetaDataView
    {
        protected $controllerId;

        protected $moduleId;

        protected $filteredList;

        protected $filteredListId;

        protected $listModelClassName;

        protected $gridIdSuffix;

        protected $gridId;

        /**
         * Constructs a filtered list view specifying the controller as
         * well as the model that will have its details displayed.
         */
        public function __construct(
            $controllerId,
            $moduleId,
            $filteredList,
            $filteredListId,
            $listModelClassName,
            $gridIdSuffix = null
        )
        {
            assert('is_array($filteredList)');
            assert('is_string($listModelClassName)');
            assert('$filteredListId == null || is_int($filteredListId)');
            $this->controllerId           = $controllerId;
            $this->moduleId               = $moduleId;
            $this->filteredList           = $filteredList;
            $this->filteredListId         = $filteredListId;
            $this->listModelClassName     = $listModelClassName;
            $this->gridIdSuffix           = $gridIdSuffix;
            $this->gridId                 = 'list-view';
        }

        /**
         * @return A string containing the element's content.
         */
        protected function renderContent()
        {
            $content = '<div class="wide form">';
            $clipWidget = new ClipWidget();
            list($form, $formStart) = $clipWidget->renderBeginWidget(
                                                                'NoRequiredsActiveForm',
                                                                array('id' => 'filtered-list-form',
                                                                'enableAjaxValidation' => false)
                                                            );
            $content .= $formStart;
            $content .= $this->renderFormLayout($form);
            $formEnd  = $clipWidget->renderEndWidget();
            $content .= $formEnd;

            $content .= '</div>';
            return $content;
        }

        /**
         * Renders the bottom panel of the layout. Includes the filteredList selector buttons
         * @return A string containing the element's content.
         */
        protected function renderFormLayout($form = null)
        {
            $runFilteredListButton = CHtml::submitButton(Yii::t('Default', 'Load'));
            $searchLink = CHtml::link(Yii::t('Default', 'Go to Search'), '#', array('class' => 'search-link'));
            $createLink = new FilteredListEditLinkActionElement(
                $this->controllerId,
                $this->moduleId,
                null,
                array('label' => Yii::t('Default', 'Create New'))
            );

            $editLink = CHtml::link(Yii::t('Default', 'Edit'), '#',
                array('class' => 'edit-filter-link')
            );
            $editLinkUrl = Yii::app()->createUrl($this->moduleId . '/filteredList/editFilteredList/');
            Yii::app()->getClientScript()->registerScriptFile(
                Yii::app()->getAssetManager()->publish(
                    Yii::getPathOfAlias('ext.zurmoinc.framework.views.assets') . '/FormUtils.js'
                    ),
                CClientScript::POS_END
            );
            Yii::app()->clientScript->registerScript('filteredList', "
                $('.search-link').click( function()
                    {
                        $('#filteredListId').val('');
                        $('.search-view-0').show();
                        $('.search-view-bottom-panel').show();
                        $('.filtered-list-panel').hide();
                        return false;
                    }
                );
                $('.edit-filter-link').click( function()
                    {
                        $('.edit-filter-link').attr('href', '$editLinkUrl' + '&id=' + $('#filteredListId').val());
                    }
                );
                $('#filteredListId').change(function()
                    {
                        if ($('#filteredListId').val() != '')
                        {
                            $('#filtered-list-change-links').show();
                        }
                        else
                        {
                            $('#filtered-list-change-links').hide();
                        }
                    }
                );
                $('#filtered-list-form').submit(function()
                    {
                        $('#" . $this->gridId . $this->gridIdSuffix . "-selectedIds').val(null);
                        $('#" . $this->gridId . $this->gridIdSuffix . "-selectAll').val(null);
                        $.fn.yiiGridView.update('" . $this->gridId . $this->gridIdSuffix . "',
                        {
                            data: $(this).serialize() + '&" . $this->listModelClassName . "_page=&" . $this->listModelClassName . "_sort=' " . // Not Coding Standard
                        "}
                        );
                        return false;
                    }
                );
            ");
            $content  = '<table>';
            $style = null;
            if (empty($this->filteredListId))
            {
                $style = 'style="display:none;"';
            }
            $content .= '<tbody class="filtered-list-panel" ' . $style . '>';
            $content .= '<tr><th style="width:20%">';
            $content .= '' . yii::t('Default', 'Filtered Lists') . ':&#160;';
            $content .= '</th><td>';
            if (!empty($this->filteredList))
            {
                //todo: probably should make an eelement of this and do: (encoding)
                //todo: encode ->text the stuff? where should we do this?
                $content .= CHtml::dropDownList(
                    'filteredListId',
                    $this->filteredListId,
                    $this->filteredList,
                    array('id' => 'filteredListId', 'empty' => Yii::t('Default', 'None'))
                );
                $content .= '&#160;';
                $content .= $runFilteredListButton . '&#160;';
                $startingDivStyle = null;
                if (empty($this->filteredListId))
                {
                    $startingDivStyle = "style='display:none;'";
                }
                $content .= '<span id="filtered-list-change-links" ' . $startingDivStyle . '>';
                $content .= $editLink . '&#160;|&#160;';
                $content .= '</span>';
            }
            $content .= $createLink->render() . '&#160;|&#160;';
            $content .= $searchLink;
            $content .= '</td></tr>';
            $content .= '</tbody>';
            $content .= '</table>';
            return $content;
        }
    }
?>