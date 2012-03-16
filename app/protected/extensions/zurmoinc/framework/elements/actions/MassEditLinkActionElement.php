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

    class MassEditLinkActionElement extends LinkActionElement
    {
        public function getActionType()
        {
            return 'MassEdit';
        }

        public function render()
        {
            $gridId = $this->getListViewGridId();
            $name   = $gridId . '-massAction';
            $htmlOptions = array(
                'name' => $name,
                'id'   => $name,
            );
            Yii::app()->clientScript->registerScript($gridId . '-listViewMassActionDropDown', "
                $('#" . $gridId . "-massAction').live('click', function()
                    {
                        if ($('#" . $gridId . "-selectAll').val() == '')
                        {
                            if ($('#" . $gridId . "-selectedIds').val() == '')
                            {
                                alert('" . Yii::t('Default', 'You must select at least one record') . "');
                                $(this).val('');
                                return false;
                            }
                        }
                        var options =
                        {
                            url : $.fn.yiiGridView.getUrl('" . $gridId . "')
                        }
                        if(options.url.split( '?' ).length == 2)
                        {
                            options.url.split( '?' )[0];
                            options.url = options.url.split( '?' )[0] +'/'+ 'massEdit' + '?' + options.url.split( '?' )[1];
                        }
                        else
                        {
                            options.url = options.url +'/'+ 'massEdit';
                        }
                        addListViewSelectedIdsAndSelectAllToUrl('" . $gridId . "', options);
                        var data = '' + 'massEdit=' + '&ajax=&" . $this->getPageVarName() . "=1'; " . // Not Coding Standard
                        "url = $.param.querystring(options.url, data);
                        window.location.href = url;
                        return false;
                    }
                );
            ");
            return CHtml::Link($this->getLabel(), '#', $htmlOptions);
        }

        protected function getDefaultLabel()
        {
            return Yii::t('Default', 'Update');
        }

        protected function getListViewGridId()
        {
            if (!isset($this->params['listViewGridId']))
            {
                throw new NotSupportedException();
            }
            return $this->params['listViewGridId'];
        }

        protected function getPageVarName()
        {
            if (!isset($this->params['pageVarName']))
            {
                throw new NotSupportedException();
            }
            return $this->params['pageVarName'];
        }

        protected function getDefaultRoute()
        {
            return $this->moduleId . '/' . $this->controllerId . '/massEdit/';
        }
    }
?>