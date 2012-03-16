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
     * Element to render a collection of dropdown elements that are connected together. This element renders a
     * dropDown dependency derived attribute.
     */
    class DropDownDependencyElement extends Element
    {
        /**
         * Instance of metadata associated with the specified attribute
         * @var DropDownDependencyDerivedAttributeMetadata
         */
        protected $dropDownDependencyDerivedAttributeMetadata;

        protected function makeMetadata()
        {
            assert('$this->attribute != null');
            assert('$this->model instanceof RedBeanModel');
            $this->dropDownDependencyDerivedAttributeMetadata = DropDownDependencyDerivedAttributeMetadata::
                                                                getByNameAndModelClassName($this->attribute,
                                                                                           get_class($this->model));
        }

        /**
         * (non-PHPdoc)
         * @see Element::renderNonEditable()
         */
        protected function renderEditable()
        {
            $this->makeMetadata();
            return parent::renderEditable();
        }

        /**
         * (non-PHPdoc)
         * @see Element::renderControlEditable()
         */
        protected function renderControlEditable()
        {
            $unserializedMetadata  = unserialize($this->dropDownDependencyDerivedAttributeMetadata->serializedMetadata);
            $onChangeScript        = null;
            $dependencyData        = array();
            $attributes            = $this->dropDownDependencyDerivedAttributeMetadata->getUsedAttributeNames();
            $content               = "<table> \n";
            $parentInputId         = null;
            $parentAttributeLabel  = null;
            foreach ($attributes as $position => $attribute)
            {
                $element                    = new DropDownElement($this->model,
                                                                  $attribute,
                                                                  $this->form,
                                                                  array('addBlank' => true));

                $element->editableTemplate  = $this->getEditableTemplate();
                $content                   .= $element->render();
                $inputId                    = $element->getIdForSelectInput();
                $onChangeScript .= "$('#" . $inputId . "').change(function()
                {
                    " . $this->getDependencyManagerResolveScriptCall() . "
                }
                );";
                $dependencyData[]         = $this->resolveDependencyData($inputId,
                                                                         $parentInputId,
                                                                         $unserializedMetadata['mappingData'],
                                                                         $position,
                                                                         $this->model->{$attribute}->value,
                                                                         $parentAttributeLabel);
                $parentInputId            = $inputId;
                $parentAttributeLabel     = $this->model->getAttributeLabel($attribute);
            }
            $content   .= "</table> \n";
            $this->resolveScriptContent($onChangeScript, $dependencyData);
            return $content;
        }

        protected static function resolveDependencyData($inputId, $parentInputId, $mappingData, $position,
                                                        $existingValue, $parentAttributeLabel)
        {
            assert('is_string($inputId)');
            assert('$parentInputId == null || is_string($parentInputId)');
            assert('is_array($mappingData)');
            assert('is_int($position)');
            assert('$existingValue == null || is_string($existingValue)');
            assert('is_string($parentAttributeLabel) || $parentAttributeLabel == null');
            $dependencyData = array();
            $dependencyData['inputId']              = $inputId;
            $dependencyData['parentInputId']        = $parentInputId;
            $dependencyData['valueToAlwaysShow']    = $existingValue;
            if ($parentAttributeLabel != null)
            {
                $dependencyData['notReadyToSelectText'] = Yii::t('Default', 'First select the {attributeLabel}',
                                                                 array('{attributeLabel}' => $parentAttributeLabel));
            }
            else
            {
                $dependencyData['notReadyToSelectText'] = null;
            }
            if (isset($mappingData[$position]['valuesToParentValues']))
            {
                $dependencyData['valuesToParentValues'] = $mappingData[$position]['valuesToParentValues'];
            }
            else
            {
                $dependencyData['valuesToParentValues'] = null;
            }
            return $dependencyData;
        }

        protected function resolveScriptContent($onChangeScript, $dependencyData)
        {
            assert('is_string($onChangeScript)');
            assert('is_array($dependencyData)');
            Yii::app()->clientScript->registerScriptFile(
                Yii::app()->getAssetManager()->publish(
                    Yii::getPathOfAlias('ext.zurmoinc.framework.elements.assets') . '/DropDownDependencyManager.js'
                    ),
                CClientScript::POS_END
            );
            $managerObjectName = $this->getDependencyManagerScriptObjectName();
            $suffix            = $this->getDependencyManagerScriptSuffix();
            $script = "
                var " . $managerObjectName . " = new DropDownDependencyManager('" . CJSON::encode($dependencyData) . "');
                " . $this->getDependencyManagerResolveScriptCall() . ";";
            Yii::app()->clientScript->registerScript(
                'dropDownDependencyManager' . $suffix,
                $script,
                CClientScript::POS_END
            );
            Yii::app()->clientScript->registerScript(
                'dropDownDependencyOnChange' . $suffix,
                $onChangeScript,
                CClientScript::POS_END
            );
        }

        protected function getDependencyManagerScriptObjectName()
        {
            return 'DependencyManager' . $this->getDependencyManagerScriptSuffix();
        }

        protected function getDependencyManagerScriptSuffix()
        {
            return $this->dropDownDependencyDerivedAttributeMetadata->name;
        }

        protected function getDependencyManagerResolveScriptCall()
        {
            return $this->getDependencyManagerScriptObjectName() . ".resolveOptions()";
        }

        /**
         * (non-PHPdoc)
         * @see Element::renderNonEditable()
         */
        protected function renderNonEditable()
        {
            $this->makeMetadata();
            return parent::renderNonEditable();
        }

        /**
         * (non-PHPdoc)
         * @see Element::renderControlNonEditable()
         */
        protected function renderControlNonEditable()
        {
            $attributes = $this->dropDownDependencyDerivedAttributeMetadata->getUsedAttributeNames();
            $content    = "<table> \n";
            foreach ($attributes as $attribute)
            {
                $element                        = new DropDownElement($this->model,
                                                                  $attribute,
                                                                  $this->form);
                $element->nonEditableTemplate   = $this->getNonEditableTemplate();
                $content                       .= $element->render();
            }
            $content   .= "</table> \n";
            return $content;
        }

        /**
         * (non-PHPdoc)
         * @see Element::renderLabel()
         */
        protected function renderLabel()
        {
            return $this->dropDownDependencyDerivedAttributeMetadata->getLabelByLanguage(Yii::app()->language);
        }

        /**
         * (non-PHPdoc)
         * @see Element::renderError()
         */
        protected function renderError()
        {
            return null;
        }

        protected function getEditableTemplate()
        {
            $template  = "<tr><td style='border:0px;' nowrap='nowrap'>\n";
            $template .= "{label}";
            $template .= "</td><td width='100%' style='border:0px;'>\n";
            $template .= '&#160;{content}{error}';
            $template .= "</td></tr>\n";
            return $template;
        }

        protected function getNonEditableTemplate()
        {
            $template  = "<tr><td width='100%' style='border:0px;'>\n";
            $template .= '{label}&#160;{content}';
            $template .= "</td></tr>\n";
            return $template;
        }
    }
?>