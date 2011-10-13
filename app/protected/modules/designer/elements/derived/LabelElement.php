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

    class LabelElement extends Element
    {
        protected function renderControlNonEditable()
        {
            $attributeLabel = ModelFormAttributeLabelsUtil::getTranslatedAttributeLabelByLabels(
                                    $this->model->{$this->attribute});
            return Yii::app()->format->text($attributeLabel);
        }

        protected function renderControlEditable()
        {
            $content = null;
            foreach ($this->getElementViewMetadata() as $elementInformation)
            {
                $editableTemplate      = '{content}&#160;' . $elementInformation['label']. '{error}<br/>';
                $elementclassname      = $elementInformation['type'] . 'Element';
                $params                = array_slice($elementInformation, 2);
                $params['inputPrefix'] = $this->resolveInputPrefix();
                $element               = new $elementclassname($this->model,
                                             $elementInformation['attributeName'],
                                             $this->form,
                                             $params);
                $element->editableTemplate = $editableTemplate;
                $content .= $element->render();
            }
            return $content;
        }

        /**
         * Always show attribute label without label tag.
         */
        protected function renderLabel()
        {
            $label = $this->getFormattedAttributeLabel();
            if ($this->form === null)
            {
                return $label;
            }
            return CHtml::label($label, false);
        }

        protected function getElementViewMetadata()
        {
            $metadata = array();
            foreach (Yii::app()->languageHelper->getActiveLanguagesData() as $language => $name)
            {
                $metadata[] = array(    'attributeName' => $this->attribute . '[' . $language . ']',
                                        'type' => 'Text', 'label' => $name);
            }
            return $metadata;
        }
    }
?>