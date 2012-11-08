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

        public $leftSideId;

        public $leftSideName;

        public $leftSideValue;

        public $leftSideData;

        public $rightSideId;

        public $rightSideName;

        public $rightSideValue;

        public $rightSideData;

        public $formId;

        public $allowSorting = false;

        public $multiselectNavigationClasses;

        public function init()
        {
            assert('($this->model instanceof CModel && $this->form instanceof ZurmoActiveForm) ||
                    ( $this->model == null && $this->form == null)');
            assert('is_bool($this->allowSorting)');
            if ($this->rightSideId == null)
            {
                $this->rightSideId = $this->form->id . '_' . $this->rightSideAttributeName;
            }
            if ($this->rightSideName == null)
            {
                $this->rightSideName = $this->rightSideAttributeName;
            }
            if ($this->rightSideValue === null)
            {
                $this->rightSideValue = $this->model->{$this->rightSideAttributeName};
            }
            if ($this->leftSideId == null)
            {
                $this->leftSideId  = $this->form->id . '_' . $this->leftSideAttributeName;
            }
            if ($this->leftSideName == null)
            {
                $this->leftSideName = $this->leftSideAttributeName;
            }
            if ($this->leftSideValue === null)
            {
                $this->leftSideValue = $this->model->{$this->leftSideAttributeName};
            }
            if ($this->formId === null)
            {
                $this->formId = $this->form->id;
            }
            $this->registerCoreScripts();
            parent::init();
        }

        public function run()
        {
            $id               = $this->getId();
            $leftListContent  = $this->resolveLeftSideListBox();
            $rightListContent = $this->resolveRightSideListBox();
            $content  = '<td>';
            $content .= '<div class="multiselect-holder">';

            $content .= '<div class="multiselect-left">';
            $content .= '<label>' . $this->leftSideDisplayLabel . '</label>';
            $content .= $leftListContent;
            $content .= '</div>';

            $content .= '<div class="multiselect-nav">';
            $content .= ZurmoHtml::button( '7', array( 'id' => $id . 'moveRight', 'class' => 'icon-right-arrow' ) ); //used 7, 8 becuase those are rendered as icons with symbly, other option is to make it an A with a SPAN inside it
            $content .= ZurmoHtml::button( '8', array( 'id' => $id . 'moveLeft', 'class' => 'icon-left-arrow' ) );
            $content .= '</div>';

            $content .= '<div class="multiselect-right">';
            $content .= '<label>' . $this->rightSideDisplayLabel . '</label>';
            $content .= $rightListContent;
            $content .= '</div>';

            if ($this->allowSorting)
            {
                $content .= '<div class="multiselect-nav' . $this->resolveMultiselectNavigationClassesContent() . '">';
                $content .= ZurmoHtml::button( '5', array( 'id' => $id . 'moveUp', 'class' => 'icon-up-arrow' ) );     // value "up" in icon font
                $content .= ZurmoHtml::button( '6', array( 'id' => $id . 'moveDown', 'class' => 'icon-down-arrow' ) ); // value "down" in icon font
                $content .= '</div>';
            }
            $content .= '</div>';
            $content .= '</td>';
            echo $content;
        }

        protected function resolveLeftSideListBox()
        {
            $htmlOptions = array('size' => '10', 'multiple' => true, 'class' => 'ignore-style multiple',
                                 'id'   => $this->leftSideId);
            if ($this->model != null)
            {
                return $this->form->dropDownList(
                    $this->model,
                    $this->leftSideName,
                    $this->leftSideValue,
                    $htmlOptions
                );
            }
            else
            {
                return ZurmoHtml::listBox($this->leftSideName, $this->leftSideValue, $this->leftSideData, $htmlOptions);
            }
        }

        protected function resolveRightSideListBox()
        {
            $htmlOptions = array('size' => '10', 'multiple' => true, 'class' => 'ignore-style multiple',
                                 'id'   => $this->rightSideId);
            if ($this->model != null)
            {
                return $this->form->dropDownList(
                    $this->model,
                    $this->rightSideName,
                    $this->rightSideValue,
                    $htmlOptions
                );
            }
            else
            {
                return ZurmoHtml::listBox($this->rightSideName, $this->rightSideValue, $this->rightSideData, $htmlOptions);
            }
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
                $('#" . $this->formId . "').submit(function()
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
                $('#" . $id . "moveUp').click(function()
                {
                    if ($('#" . $this->rightSideId . " option:selected').first().index() > 0)
                    {
                        $('#" . $this->rightSideId . " option:selected').each(function()
                        {
                           $(this).insertBefore($(this).prev());
                        });
                    }
                });
                $('#" . $id . "moveDown').click(function()
                {
                    if ($('#" . $this->rightSideId . " option:selected').last().index() < ($('#" . $this->rightSideId . " option').length - 1))
                    {
                        $($('#" . $this->rightSideId . " option:selected').get().reverse()).each(function(i, selected)
                        {
                            if (!$(this).next().length) return false;
                            $(this).insertAfter($(this).next());
                        });
                    }
                });
            ";
            Yii::app()->getClientScript()->registerScript(__CLASS__ . '#' . $id, $script);
        }

        protected function resolveMultiselectNavigationClassesContent()
        {
            if ($this->multiselectNavigationClasses != null)
            {
                return ' ' . $this->multiselectNavigationClasses;
            }
        }
    }
?>
