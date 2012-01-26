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

    Yii::import('zii.widgets.grid.CGridView');

    /**
     * Extends the yii CGridView to provide additional functionality.
     * @see CGridView class
     */
    class ExtendedGridView extends CGridView
    {
        protected $selectAllOptionsCssClass = 'select-all-options';

        protected $MassActionsCssClass = 'mass-action';

        public $template = "{selectRowsSelectors}{summary}\n{items}\n{massActionSelector}{pager}";

        public $selectAll;

        /**
         * Override to have proper XHTML compliant space value
         */
        public $nullDisplay = '&#160;';

        /**
         * Override to have proper XHTML compliant space value
         */
        public $blankDisplay = '&#160;';

        public $massActionMenu = array();

        /**
         * Override to display select all/none optional
         * dropdown.
         */
        public function renderSelectRowsSelectors()
        {
            Yii::app()->clientScript->registerScriptFile(
                Yii::app()->getAssetManager()->publish(
                    Yii::getPathOfAlias('ext.zurmoinc.framework.views.assets') . '/ListViewUtils.js'
                    ),
                CClientScript::POS_END
            );
            if (($count = $this->dataProvider->getItemCount()) <= 0)
            {
                return;
            }
            if ($this->selectableRows > 1)
            {
                $allLink = CHtml::link(Yii::t('Default', 'All'), '#', array('id'   => $this->id . '-select-all-rows-link'));
                $noneLink = CHtml::link(Yii::t('Default', 'None'), '#', array('id' => $this->id . '-select-none-rows-link'));
                Yii::app()->clientScript->registerScript($this->id . '-listViewSelectAllOptions', "
                    $('#" . $this->id . "-select-all-rows-link').live('click', function()
                        {
                            selectAllResults('" . $this->id . "', '" . $this->id . "-rowSelector');
                            return false;
                        }
                    );
                    $('#" . $this->id . "-select-none-rows-link').live('click', function()
                        {
                            selectNoneResults('" . $this->id . "', '" . $this->id . "-rowSelector');
                            return false;
                        }
                    );
                ");
                echo '<div class="' . $this->selectAllOptionsCssClass . '">' . Yii::t('Default', 'Select') . ':&#160;' . $allLink
                . '&#160;|&#160;' . $noneLink . '</div>' . "\n";
            }
        }

        /**
         * Render a mass action drop down for the list view.
         */
        public function renderMassActionSelector()
        {
            if (($count = $this->dataProvider->getItemCount()) <= 0)
            {
                return;
            }
            if ($this->selectableRows > 0 && $this->massActionMenu > 0)
            {
                echo '&#160;<div class="' . $this->MassActionsCssClass . '">' . $this->renderMassActionDropDownElement() . '</div>' . "\n";
            }
        }

        protected function renderMassActionDropDownElement()
        {
            $name = $this->id . '-massAction';
            $htmlOptions = array(
                'name' => $name,
                'id'   => $name,
            );
            Yii::app()->clientScript->registerScript($this->id . '-listViewMassActionDropDown', "
                $('#" . $this->id . "-massAction').live('change', function()
                    {
                        if ($(this).val() == '')
                        {
                            return false;
                        }
                        if ($('#" . $this->id . "-selectAll').val() == '')
                        {
                            if ($('#" . $this->id . "-selectedIds').val() == '')
                            {
                                alert('" . Yii::t('Default', 'You must select at least one record') . "');
                                $(this).val('');
                                return false;
                            }
                        }
                        var options =
                        {
                            url : $.fn.yiiGridView.getUrl('" . $this->id . "')
                        }
                        options.url = options.url +'/'+ $(this).val();
                        addListViewSelectedIdsAndSelectAllToUrl('" . $this->id . "', options);
                        var data = '' + $(this).val() + '&ajax=&" . $this->dataProvider->getPagination()->pageVar . "=1'; " . // Not Coding Standard
                        "url = $.param.querystring(options.url, data);
                        window.location.href = url;
                        return false;
                    }
                );
            ");
            return CHtml::dropDownList($name, '', $this->massActionMenu, $htmlOptions);
        }
    }
?>
