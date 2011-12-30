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
     * Element for displaying a calculated number formula
     */
    class CalculatedNumberFormulaElement extends TextAreaElement
    {
        /**
         * Render additional help information besides the text input box.
         * @return The element's content as a string.
         */
        protected function renderControlEditable()
        {
            $content  = parent::renderControlEditable();
            $title        = Yii::t('Default', 'Create a math formula that is calculated from other fields.' .
                                   ' Use the Formula Name from the Available Fields grid below to create your formula.' .
                                   ' Example formula (field1 x field2) / field3');
            $spanContent  = '<span id="formula-tooltip" class="tooltip" title="' . $title . '">';
            $spanContent .= Yii::t('Default', 'How does this work?') . '</span>';
            $content      = $spanContent . '<br/>' . $content;
            $content     .= '<br/>';
            $content     .= $this->renderAvailableAttributesContent();
            $qtip = new QTip();
            $qtip->addQTip("#formula-tooltip");
            return $content;
        }

        protected function renderAvailableAttributesContent()
        {
            $modelClassName = $this->model->getModelClassName();
            $model          = new $modelClassName(false);
            $adapter        = new ModelNumberOrCurrencyAttributesAdapter($model);
            $attributeData  = $adapter->getAttributes();
            if(count($attributeData) > 0)
            {
                $content  = '<b>' . Yii::t('Default', 'Available Fields:') . '</b>';
                $content .= '<table style="width:auto">';
                $content .= '<tr><td><b>' . Yii::t('Default', 'Field Name') . '</b></td>';
                $content .= '<td><b>' . Yii::t('Default', 'Formula Name') . '</b></td></tr>';
                foreach($attributeData as $attributeName => $data)
                {
                    $content .= '<tr><td>' . $data['attributeLabel'] . '</td><td>' . $attributeName . '</td></tr>';
                }
                $content .= '</table>';
            }
            else
            {
                $content  = '<span class="error">' . Yii::t('Default', 'There are no fields in this module to be used in a formula.');
                $content .= '</span>';
            }
            return $content;
        }
    }
?>