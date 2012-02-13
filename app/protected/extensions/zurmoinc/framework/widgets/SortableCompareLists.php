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
     * Render side-by-side multi-select lists
     * that allows you to move items from one side
     * to the other.  Use with CActiveForm forms.
     */
    class SortableCompareLists extends CWidget
    {
        public $model;

        public $form;

        public $leftSideAttributeName;

        public $leftSideDisplayLabel;

        public $rightSideAttributeName;

        public $rightSideDisplayLabel;

        protected $leftSideId;

        protected $rightSideId;

        public function init()
        {
            assert('$this->model instanceof CModel');
            assert('$this->form instanceof ZurmoActiveForm');
            assert('$this->leftSideAttributeName != null');
            assert('$this->rightSideAttributeName != null');
            $this->rightSideId = $this->form->id . '_' . $this->rightSideAttributeName;
            $this->leftSideId  = $this->form->id . '_' . $this->leftSideAttributeName;
            $this->registerCoreScripts();
            parent::init();
        }

        public function run()
        {
            $id = $this->getId();
            $leftHtmlOptions = array('size' => '10', 'multiple' => true, 'style' => 'width: 200px;',
            'id' => $this->leftSideId);
            $leftListContent = $this->form->dropDownList(
                $this->model,
                $this->leftSideAttributeName,
                $this->model->{$this->leftSideAttributeName},
                $leftHtmlOptions
            );
            $rightHtmlOptions = array('size' => '10', 'multiple' => true, 'style' => 'width: 200px;',
            'id' => $this->rightSideId);
            $rightListContent = $this->form->dropDownList(
                $this->model,
                $this->rightSideAttributeName,
                $this->model->{$this->rightSideAttributeName},
                $rightHtmlOptions
            );
            $content  = '<td style="vertical-align:middle"><div style="float: left;">';
            $content .= $this->leftSideDisplayLabel . '<br/>';
            $content .= $leftListContent;
            $content .= '</div><div style="float: left; text-align: center; margin-top:50px; width:50px;">';
            $content .= CHtml::button('>', array('id' => $id . 'moveRight'));
            $content .= '<br/>';
            $content .= CHtml::button('<', array('id' => $id . 'moveLeft'));
            $content .= '</div><div style="float: left;">';
            $content .= $this->rightSideDisplayLabel . '<br/>';
            $content .= $rightListContent;
            $content .= '</div></td>';
            echo $content;
        }

        /**
         * Registers the core script code.s
         */
        protected function registerCoreScripts()
        {
            $id = $this->getId();
            $script = "
                $('#" . $id . "moveRight').click(function()
                {
                    return !$('#" . $this->leftSideId . " option:selected')
                    .remove().appendTo('#" . $this->rightSideId . "');
                });
                $('#" . $id . "moveLeft').click(function()
                {
                    return !$('#" . $this->rightSideId . " option:selected')
                    .remove().appendTo('#" . $this->leftSideId . "');
                });
                $('#" . $this->form->id . "').submit(function()
                {
                 $('#" . $this->leftSideId . " option').each(function(i)
                 {
                  $(this).attr('selected', 'selected');
                 });
                 $('#" . $this->rightSideId . " option').each(function(i)
                {
                  $(this).attr('selected', 'selected');
                 });
                });

            ";
            Yii::app()->getClientScript()->registerScript(__CLASS__ . '#' . $id, $script);
        }
    }
?>
