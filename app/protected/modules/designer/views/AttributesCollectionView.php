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

    class AttributesCollectionView extends MetadataView
    {
        protected $controllerId;

        protected $moduleId;

        protected $attributesCollection;

        protected $moduleClassName;

        protected $modelClassName;

        public function __construct($controllerId, $moduleId, $attributesCollection, $moduleClassName, $modelClassName)
        {
            $this->controllerId           = $controllerId;
            $this->moduleId               = $moduleId;
            $this->attributesCollection   = $attributesCollection;
            $this->moduleClassName        = $moduleClassName;
            $this->modelClassName         = $modelClassName;
            $this->modelId                = null;
        }

        protected function renderContent()
        {
            $content  = '<div class="horizontal-line">';
            $content .= $this->renderActionElementBar(false);
            $content .= '</div>' . "\n";
            $content .= '<table>';
            $content .= '<colgroup>';
            $content .= '<col style="width:20%" /><col style="width:80%" />';
            $content .= '</colgroup>';
            $content .= '<tbody>';
            $content .= '<tr><th>' . Yii::t('Default', 'Field Name') . '</th><th>' . Yii::t('Default', 'Field Type') . '</th></tr>';
            foreach ($this->attributesCollection as $attributeName => $information)
            {
                $route = $this->moduleId . '/' . $this->controllerId . '/AttributeDetails/';
                $content .= '<tr>';
                $content .= '<td>';
                if ($information['elementType'] == 'EmailAddressInformation' ||
                    $information['elementType'] == 'Address' ||
                    $information['elementType'] == 'User' ||
                    $information['isReadOnly'])
                {
                    //temporary until we figure out how to handle these types.
                    $content .= $information['attributeLabel'];
                }
                else
                {
                    $content .= CHtml::link($information['attributeLabel'], Yii::app()->createUrl($route,
                        array(
                            'moduleClassName' => $this->moduleClassName,
                            'attributeTypeName' => $information['elementType'],
                            'attributeName' => $attributeName,
                        )
                    ));
                }
                $content .= '</td>';
                $attributeFormClassName = AttributesFormFactory::getFormClassNameByAttributeType($information['elementType']);
                $content .= '<td>' . $attributeFormClassName::getAttributeTypeDisplayName() . '</td>';
                $content .= '</tr>';
            }
            $content .= '</tbody>';
            $content .= '</table>';
            return $content;
        }

        public function isUniqueToAPage()
        {
            return false;
        }
    }
?>