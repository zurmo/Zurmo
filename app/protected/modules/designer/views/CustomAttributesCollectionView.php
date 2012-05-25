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

    class CustomAttributesCollectionView extends AttributesCollectionView
    {
        protected function renderBeforeTableContent()
        {
            $dropDownContent = CHtml::dropDownList('attributeTypeName', null, $this->getValueTypeDropDownArray());

            $linkContent     = CHtml::button(Yii::t('Default', 'Configure'),
                                                        array('id' => 'attributeTypeNameButton', 'class' => 'configure-custom-field-button'));
            $url             = Yii::app()->createUrl($this->moduleId . '/' . $this->controllerId . '/attributeEdit/',
                                                     array('moduleClassName' => $this->moduleClassName));
            Yii::app()->clientScript->registerScript('attributeTypeCreateLink', "
            $('#attributeTypeNameButton').click( function()
                {
                    if ($('#attributeTypeName').val() == '')
                    {
                        alert('" . Yii::t('Default', 'You must first select a field type') . "');
                    }
                    else
                    {
                        window.location = '" . $url . "&attributeTypeName=' + $('#attributeTypeName').val();
                    }
                }
            );");
            $content = null;
            $content .= '<div class="add-custom-field">';
            $content .= '<h1>' . Yii::t('Default', 'Create Field') . '</h1>';
            $content .= '<div>';
            $content .= $dropDownContent . $linkContent;
            $content .= '</div></div>';
            return $content;
        }

        protected static function getValueTypeDropDownArray()
        {
            $data           = array('' => Yii::t('Default', 'Select a field type'));
            $attributeTypes = ModelAttributeToDesignerTypeUtil::getAvailableCustomAttributeTypes();
            foreach ($attributeTypes as $attributeType)
            {
                $attributeFormClassName = AttributesFormFactory::getFormClassNameByAttributeType($attributeType);
                $data[$attributeType] = $attributeFormClassName::getAttributeTypeDisplayName();
            }
            return $data;
        }
    }
?>
