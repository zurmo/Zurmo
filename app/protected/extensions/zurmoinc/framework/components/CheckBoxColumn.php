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
     * Override class for CCheckBoxColumn utilized by
     * yii CGridView extension. Adds functionality
     * for checking/unchecking entire results of a list.
     * @see CGridView class
     */
    class CheckBoxColumn extends CCheckBoxColumn
    {
        public function init()
        {
            if (isset($this->checkBoxHtmlOptions['name']))
            {
                $name = $this->checkBoxHtmlOptions['name'];
            }
            else
            {
                $name = $this->id;
                if (substr($name, -2) !== '[]')
                {
                    $name .= '[]';
                }
                $this->checkBoxHtmlOptions['name'] = $name;
            }
            $name = strtr($name, array('[' => "\\[", ']' => "\\]"));
            if ($this->grid->selectableRows == 1)
            {
                $one = "\n\tjQuery(\"input:not(#\"+$(this).attr('id')+\")[name = '$name']\").attr('checked', false);"; // Not Coding Standard
            }
            else
            {
                $one = '';
            }
            // Begin Not Coding Standard
            $js = <<<END
jQuery('#{$this->id}_all').live('click', function()
{
    var checked = this.checked;

    //custom checkbox style
    if (this.checked){
        jQuery(this).parent().addClass('c_on');
    }
    else
    {
        jQuery(this).parent().removeClass('c_on');
    }

    jQuery("input[name='$name']").each(function()
    {
        this.checked = checked;
        updateListViewSelectedIds('{$this->grid->id}', $(this).val(), checked);

        //custom checkbox style
        if (this.checked){
            jQuery(this).parent().addClass('c_on');
        }
        else
        {
            jQuery(this).parent().removeClass('c_on');
        }
    });
});
jQuery("input[name='$name']").live('click', function()
{
    jQuery('#{$this->id}_all').attr( 'checked', jQuery("input[name='$name']").length == jQuery("input[name='$name']:checked").length);{$one}
    updateListViewSelectedIds('{$this->grid->id}', $(this).val(), $(this).attr('checked'));

    //custom checkbox style
    if ( jQuery('#{$this->id}_all').attr( 'checked') === 'checked' ){
        jQuery('#{$this->id}_all').parent().addClass('c_on');
    }
    else
    {
        jQuery('#{$this->id}_all').parent().removeClass('c_on');
    }
    if ( this.checked )
    {
        jQuery(this).parent().addClass('c_on');
    }
    else
    {
        jQuery(this).parent().removeClass('c_on');
    }
});
END;
            // End Not Coding Standard
            Yii::app()->getClientScript()->registerScript(__CLASS__ . '#' . $this->id, $js);
        }

        /**
         * Renders the header cell content.
         * Override in order to allow for disabled and checked by default
         * in the scenario where selectAll is selected.
         */
        protected function renderHeaderCellContent()
        {
            if ($this->grid->selectableRows>1)
            {
                $checked = false;
                $disabled = '';
                $htmlOptions = array('disabled' => $disabled);
                echo CHtml::tag("label",
                                array("class" => "hasCheckBox"),
                                CHtml::checkBox($this->id . '_all', $checked, $htmlOptions));
            }
            else
            {
                parent::renderHeaderCellContent();
            }
        }

        public function renderDataCellContentFromOutsideClass($row, $data)
        {
            $this->renderDataCellContent($row, $data);
        }

        /**
         * Override to support adding the label wrapper on the checkbox
         * (non-PHPdoc)
         * @see CCheckBoxColumn::renderDataCellContent()
         */
        protected function renderDataCellContent($row, $data)
        {
            if ($this->value !== null)
            {
                $value = $this->evaluateExpression($this->value, array('data' => $data, 'row' => $row));
            }
            elseif ($this->name !== null)
            {
                $value = CHtml::value($data, $this->name);
            }
            else
            {
                $value = $this->grid->dataProvider->keys[$row];
            }

            $checked = false;
            if ($this->checked !== null)
            {
                $checked = $this->evaluateExpression($this->checked, array('data' => $data, 'row' => $row));
            }
            $options                  = $this->checkBoxHtmlOptions;
            $name                     = $options['name'];
            unset($options['name']);
            $options['value']         = $value;
            $options['id']            = $this->id . '_' . $row;
            echo CHtml::tag("label",
                            array("class" => "hasCheckBox"),
                            CHtml::checkBox($name, $checked, $options));
        }
    }
?>
