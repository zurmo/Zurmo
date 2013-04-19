<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
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
     * You can contact Zurmo, Inc. with a mailing address at 27 North Wacker Drive
     * Suite 370 Chicago, IL 60606. or at email address contact@zurmo.com.
     *
     * The interactive user interfaces in original and modified versions
     * of this program must display Appropriate Legal Notices, as required under
     * Section 5 of the GNU General Public License version 3.
     *
     * In accordance with Section 7(b) of the GNU General Public License version 3,
     * these Appropriate Legal Notices must retain the display of the Zurmo
     * logo and Zurmo copyright notice. If the display of the logo is not reasonably
     * feasible for technical reasons, the Appropriate Legal Notices must display the words
     * "Copyright Zurmo Inc. 2013. All rights reserved".
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
            $content      = parent::renderControlEditable();
            $content     .= '<div class="field-instructions">' . $this->renderAvailableAttributesContent() . '</div>';

            return $content;
        }

        protected function renderAvailableAttributesContent()
        {
            $modelClassName = $this->model->getModelClassName();
            $model          = new $modelClassName(false);
            $adapter        = new ModelNumberOrCurrencyAttributesAdapter($model);
            $attributeData  = $adapter->getAttributes();
            $title          = Zurmo::t('DesignerModule', 'Create a math formula that is calculated from other fields.' .
                                   ' Use the Formula Name from the Available Fields grid below to create your formula.' .
                                   ' Example formula (field1 * field2) / field3');
            $spanContent    = '<span id="formula-tooltip" class="tooltip" title="' . $title . '">?</span>';
            if (count($attributeData) > 0)
            {
                $content  = '<strong>' . Zurmo::t('DesignerModule', 'Available Fields:') . '</strong> ' . $spanContent;
                $content .= '<table id="available-fields">';
                $content .= '<tr><th>' . Zurmo::t('DesignerModule', 'Field Name') . '</th>';
                $content .= '<th>' . Zurmo::t('DesignerModule', 'Formula Name') . '</th></tr>';
                foreach ($attributeData as $attributeName => $data)
                {
                    $content .= '<tr><td>' . $data['attributeLabel'] . '</td><td>' . $attributeName . '</td></tr>';
                }
                $content .= '</table>';
            }
            else
            {
                $content  = '<span class="error">' . Zurmo::t('DesignerModule', 'There are no fields in this module to be used in a formula.');
                $content .= '</span>';
            }
            $qtip = new ZurmoTip();
            $qtip->addQTip("#formula-tooltip");
            return $content;
        }
    }
?>