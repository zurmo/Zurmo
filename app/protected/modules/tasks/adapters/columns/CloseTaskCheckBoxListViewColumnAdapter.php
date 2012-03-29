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

    class CloseTaskCheckBoxListViewColumnAdapter extends CheckBoxListViewColumnAdapter
    {
        public function renderGridViewData()
        {
            return array(
                'name'        => $this->attribute,
                'header'      => Yii::t('Default' , 'Close'),
                'value'       => $this->resolveToRenderCheckBox('Task', '$data->' . 'id'),
                'type'        => 'raw',
                'htmlOptions' => array('class'=>'checkbox-column')
            );
        }

        protected function resolveToRenderCheckBox($modelClassName, $modelId)
        {
            if(!ActionSecurityUtil::canCurrentUserPerformAction( 'Edit', new $modelClassName(false)))
            {
                return '';
            }
            $checkboxId = 'closeTask' . $modelId;
            $content    = '"<label class=\'hasCheckBox\'>" . CHtml::checkBox("' . $checkboxId . '", false,
                                       array("class" => "close-task-checkbox",
                                             "onClick" => "closeOpenTaskByCheckBoxClick(\'' . $checkboxId . '\', \'' . $modelId . '\')")) . "</label>"';

            Yii::app()->clientScript->registerScript('closeTaskCheckBoxScript', "
                function closeOpenTaskByCheckBoxClick(checkboxId, modelId)
                {
                    if($('#' + checkboxId).attr('checked') == 'checked')
                    {
                        $('#' + checkboxId).attr('disabled', true);
                        $('#' + checkboxId).parent().parent().children().css('text-decoration', 'line-through');
                        $.ajax({
                            url : '" . Yii::app()->createUrl('tasks/default/closeTask') . "?id=' + modelId,
                            type : 'GET',
                            dataType : 'json',
                            success : function(data)
                            {
                                //find if there is a latest activities portlet
                                $('.LatestActivtiesForPortletView').each(function(){
                                    $(this).find('.pager').find('.first').find('a').click();
                                });
                            },
                            error : function()
                            {
                                //todo: error call
                            }
                        });
                    }
                }
            ", CClientScript::POS_END);
            return $content;
        }

        //todo make sure live actually works on paged tassks

    }
?>