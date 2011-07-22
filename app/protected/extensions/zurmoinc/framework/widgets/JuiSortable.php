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

    Yii::import('zii.widgets.jui.CJuiSortable');

    /**
     * Extended class to overcome issue when you have
     * a list without any items to start with.  This causes
     * invalid XHTML because you have a UL without any LIs.
     * This solves this problem by loading a filler li.
     */
    class JuiSortable extends CJuiSortable
    {
        /**
         * Set this property if you would like a hidden input field to be generated with
         * $baseInputNameForSortableCollection as the input name.  This is useful if you want to have an empty
         * array passed via POST when a sortable has no rows, but is submitted.  Otherwise without this set, nothing
         * will be passed via POST.  This is needed if you want to validate using CActiveForm that the sortable is
         * required or some other type of validation.
         */
        public $baseInputNameForSortableCollection;

        /**
         * Run this widget.
         * This method registers necessary javascript and renders the needed HTML code.
         */
        public function run()
        {
            $id = $this->getId();
            if (isset($this->htmlOptions['id']))
            {
                $id = $this->htmlOptions['id'];
            }
            else
            {
                $this->htmlOptions['id'] = $id;
            }
            if (empty($this->options))
            {
                $options = '';
            }
            else
            {
                 $options = CJavaScript::encode($this->options);
            }
            if ($this->baseInputNameForSortableCollection != null)
            {
                echo CHtml::hiddenField($this->baseInputNameForSortableCollection);
            }
            Yii::app()->getClientScript()->registerScript(
                __CLASS__ . '#' . $id,
                "jQuery('#{$id}').sortable({$options});");
            echo CHtml::openTag($this->tagName, $this->htmlOptions) . "\n";
            if (empty($this->items))
            {
                echo '<li></li>' . "\n";
            }
            foreach ($this->items as $id => $data)
            {
                echo strtr(
                    $this->itemTemplate,
                    array('{id}' => $id, '{content}' => $data['content'], '{removalContent}' => $data['removalContent'])
                ) . "\n";
            }
            echo CHtml::closeTag($this->tagName);
        }
    }
?>