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
     * User interface element for managing related model relations for any model. This class supports a HAS_ONE
     * only.  If you need to support HAS_MANY models you will need to extend this class.
     *
     */
    abstract class RelatedItemsElement extends ModelsElement implements DerivedElementInterface, ElementActionTypeInterface
    {
        public $editableTemplate = '<th class="hidden-element"></th><td class="hidden-element" colspan="{colspan}"></td></tr>{content}{error}<tr><th class="hidden-element"></th><td class="hidden-element" colspan="{colspan}"></td>';

        public $nonEditableTemplate = '<th class="hidden-element"></th><td class="hidden-element" colspan="{colspan}"></td></tr>{content}<tr><th class="hidden-element"></th><td class="hidden-element" colspan="{colspan}"></td>';

        protected static function getRelatedItemsModelClassNames()
        {
            throw new NotImplementedException();
        }

        protected static function getRelatedItemFormClassName()
        {
            throw new NotImplementedException();
        }

        protected function getRelatedItemsFromModel()
        {
            throw new NotImplementedException();
        }

        protected function renderControlNonEditable()
        {
            return $this->renderNonEditableElementsForRelationsByRelationsData(static::getRelatedItemsModelClassNames());
        }

        protected function renderControlEditable()
        {
            return $this->renderElementsForRelationsByRelationsData(static::getRelatedItemsModelClassNames());
        }

        protected function renderElementsForRelationsByRelationsData($relationModelClassNames)
        {
            $content       = null;
            $formClassName = static::getRelatedItemFormClassName();
            foreach ($relationModelClassNames as $relationModelClassName)
            {
                $relatedItemForm = null;
                //ASSUMES ONLY A SINGLE ATTACHED RELATEDITEM PER RELATION TYPE.
                foreach ($this->getRelatedItemsFromModel() as $item)
                {
                    try
                    {
                        $modelDerivationPathToItem = RuntimeUtil::getModelDerivationPathToItem($relationModelClassName);
                        $castedDownModel           = $item->castDown(array($modelDerivationPathToItem));
                        $relatedItemForm           = new $formClassName($castedDownModel);
                        $relationModel             = $castedDownModel;
                        break;
                    }
                    catch (NotFoundException $e)
                    {
                    }
                }
                if ($relatedItemForm == null)
                {
                    $relationModel     = new $relationModelClassName();
                    $relatedItemForm   = new $formClassName($relationModel);
                }
                $canAccess        = true;
                $modelElementType = RelatedItemRelationToModelElementUtil::resolveModelElementTypeByActionSecurity(
                                              $relationModelClassName, Yii::app()->user->userModel, $canAccess);
                if ($canAccess)
                {
                    $content .= $this->resolveAndRenderEditableInput($relationModel, $relatedItemForm,
                                                                     $relationModelClassName, $modelElementType);
                }
                elseif ($relationModel->id > 0)
                {
                    $content .= $this->renderEditableHiddenInput($relatedItemForm, $relationModelClassName, $modelElementType);
                }
            }
            return $content;
        }

        protected function resolveAndRenderEditableInput($relationModel, $relatedItemForm, $relationModelClassName, $modelElementType)
        {
            $elementInformation = array('attributeName' => $relationModelClassName,
                                        'type'          => $modelElementType);
            FormLayoutSecurityUtil::resolveElementForEditableRender($relatedItemForm, $elementInformation, Yii::app()->user->userModel);
            if ($elementInformation['attributeName'] != null)
            {
                $elementclassname = $elementInformation['type'] . 'Element';
                $element  = new $elementclassname($relatedItemForm, $elementInformation['attributeName'],
                                                  $this->form, array_slice($elementInformation, 2));
                assert('$element instanceof ModelElement');
                $element->editableTemplate = $this->getRelatedItemEditableTemplate();
                return $element->render();
            }
            elseif ($relationModel->id > 0)
            {
                return $this->renderEditableHiddenInput($relatedItemForm, $relationModelClassName, $modelElementType);
            }
        }

        protected function renderEditableHiddenInput($relatedItemForm, $relationModelClassName, $modelElementType)
        {
            $elementInformation = array('attributeName'   => $relationModelClassName,
                                        'type'            => $modelElementType,
                                        'onlyHiddenInput' => true);
            $elementclassname = $elementInformation['type'] . 'Element';
            $element  = new $elementclassname($relatedItemForm, $elementInformation['attributeName'],
                                              $this->form, array_slice($elementInformation, 2));
            assert('$element instanceof ModelElement');
            $element->editableTemplate = $this->getRelatedItemEditableHiddenInputOnlyTemplate();
            return $element->render();
        }

        protected function renderNonEditableElementsForRelationsByRelationsData($relationModelClassNames)
        {
            $content       = null;
            $formClassName = static::getRelatedItemFormClassName();
            foreach ($relationModelClassNames as $relationModelClassName)
            {
                $relatedItemForm = null;
                //ASSUMES ONLY A SINGLE ATTACHED RELATEDITEM PER RELATION TYPE.
                foreach ($this->getRelatedItemsFromModel() as $item)
                {
                    try
                    {
                        $modelDerivationPathToItem = RuntimeUtil::getModelDerivationPathToItem($relationModelClassName);
                        $castedDownModel           = $item->castDown(array($modelDerivationPathToItem));
                        $relatedItemForm           = new $formClassName($castedDownModel);
                        break;
                    }
                    catch (NotFoundException $e)
                    {
                        //do nothing
                    }
                }
                if ($relatedItemForm != null)
                {
                    $canAccess        = true;
                    $modelElementType = RelatedItemRelationToModelElementUtil::resolveModelElementTypeByActionSecurity(
                                             $relationModelClassName, Yii::app()->user->userModel, $canAccess);
                    if ($canAccess)
                    {
                        $elementInformation = array('attributeName' => $relationModelClassName,
                                                    'type'          => $modelElementType);
                        FormLayoutSecurityUtil::resolveElementForNonEditableRender($relatedItemForm, $elementInformation, Yii::app()->user->userModel);
                        if ($elementInformation['attributeName'] != null)
                        {
                            $elementclassname = $elementInformation['type'] . 'Element';
                            $element  = new $elementclassname($relatedItemForm, $elementInformation['attributeName'],
                                                              $this->form, array_slice($elementInformation, 2));
                            assert('$element instanceof ModelElement');
                            $element->nonEditableTemplate = $this->getRelatedItemNonEditableTemplate();
                            $content .= $element->render();
                        }
                    }
                }
            }
            return $content;
        }

        protected function getRelatedItemEditableTemplate()
        {
            $template  = "<tr><th>\n";
            $template .=  "{label}";
            $template .= "</th><td colspan=\"" . $this->getColumnSpan() . "\">\n";
            $template .= '{content}{error}';
            $template .= "</td></tr>\n";
            return $template;
        }

        protected function getRelatedItemEditableHiddenInputOnlyTemplate()
        {
            $template  = "<tr><th>\n";
            $template .= "</th><td colspan=\"" . $this->getColumnSpan() . "\">\n";
            $template .= '{content}{error}';
            $template .= "</td></tr>\n";
            return $template;
        }

        protected function getRelatedItemNonEditableTemplate()
        {
            $template  = "<tr><th>\n";
            $template .=  "{label}";
            $template .= "</th><td colspan=\"" . $this->getColumnSpan() . "\">\n";
            $template .= '{content}';
            $template .= "</td></tr>\n";
            return $template;
        }

        protected function renderError()
        {
        }

        protected function renderLabel()
        {
            return ZurmoHtml::label(Yii::t('Default', 'Related to'), false);
        }

        public static function getDisplayName()
        {
            $content         =  Yii::t('Default', 'Related') . '&#160;';
            $relationContent = null;
            foreach (static::getRelatedItemsModelClassNames() as $relationModelClassName)
            {
                if ($relationContent != null)
                {
                    $relationContent .= ',&#160;';
                }
                $relationContent .= $relationModelClassName::getModelLabelByTypeAndLanguage('Plural');
            }
            return $content . $relationContent;
        }

        /**
         * Get the attributeNames of attributes used in
         * the derived element. For this element, there are no attributes from the model.
         * @return array - empty
         */
        public static function getModelAttributeNames()
        {
            return array();
        }

        /**
         * Gets the action type for the related model's action
         * that is called by the select button or the autocomplete
         * feature in the Editable render.
         */
        public static function getEditableActionType()
        {
            return static::$editableActionType;
        }

        /**
         * Currently RelatedItems is not supported in non editable views.
         */
        public static function getNonEditableActionType()
        {
            throw new NotImplementedException();
        }
    }
?>