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
     * Display the model selection. This is a
     * combination of a type-ahead input text field
     * and a selection button which renders a modal list view
     * to search on whichever model element extends this class.
     * Also includes a hidden input for the model id.
     */
    abstract class ModelElement extends Element implements ElementActionTypeInterface
    {
        /**
         * Override in child element with a specific moduleId
         */
        protected static $moduleId;

        /**
         * Override in child element with a custom controllerId
         * if needed
         */
        protected $controllerId = 'default';

        /**
         * Model or form's attributeName for the model 'id'
         */
        protected $idAttributeId = 'id';

        /**
         * The auto complete search action id
         */
        protected static $autoCompleteActionId = 'autoComplete';

        /**
         * The modal popup's action id
         */
        protected static $modalActionId = 'modalList';

        /**
         * The action type of the related model
         * for which the autocomplete/select popup are calling.
         */
        protected static $editableActionType = 'ModalList';

        /**
         * The action type of the related model
         * for which the details link is calling.
         */
        protected static $nonEditableActionType = 'Details';

        protected function renderControlEditable()
        {
            assert('$this->model->{$this->attribute} instanceof RedBeanModel');
            return $this->renderEditableContent();
        }

        /**
         * Render a hidden input, a text input with an auto-complete
         * event, and a select button. These three items together
         * form the Model Editable Element
         * @return The element's content as a string.
         */
        protected function renderEditableContent()
        {
            $cs = Yii::app()->getClientScript();
            $cs->registerCoreScript('bbq');
            $cs->registerScriptFile(
                Yii::app()->getAssetManager()->publish(
                    Yii::getPathOfAlias('ext.zurmoinc.framework.elements.assets') . '/Modal.js'
                    ),
                CClientScript::POS_END
            );
            $idInputHtmlOptions = array(
                'name'     => $this->getNameForHiddenField(),
                'id'       => $this->getIdForHiddenField(),
                'disabled' => $this->getDisabledValue(),
                'value'    => $this->getId(),
            );
            $content       = $this->form->hiddenField($this->model, $this->idAttributeId, $idInputHtmlOptions);
            $inputContent  = $this->renderTextField($this->getIdForHiddenField());
            $inputContent .= '&#160;' . $this->renderSelectLink();
            return $content . CHtml::tag('div', array('class' => 'has-model-select'), $inputContent);
        }

        /**
         * Render a auto-complete text input field.
         * When the field is typed in, it will trigger ajax
         * call to look up against the Model's name
         * @return The element's content as a string.
         */
        protected function renderTextField($idInputName)
        {
            $script = "
                function clearIdFromAutoCompleteField(value, id)
                {
                    if (value == '')
                    {
                        $('#' + id).val('');
                    }
                }
            ";
            Yii::app()->clientScript->registerScript(
                'clearIdFromAutoCompleteField',
                $script,
                CClientScript::POS_END
            );
            $cClipWidget = new CClipWidget();
            $cClipWidget->beginClip("ModelElement");
            $cClipWidget->widget('zii.widgets.jui.CJuiAutoComplete', array(
                'name'    => $this->getNameForTextField(),
                'id'      => $this->getIdForTextField(),
                'value'   => $this->getName(),
                'source'  => Yii::app()->createUrl($this->resolveModuleId() . '/' . $this->getAutoCompleteControllerId()
                                                        . '/' . static::$autoCompleteActionId),
                'options' => array(
                    'select' => 'js:function(event, ui){ jQuery("#' . $idInputName . '").val(ui.item["id"]);}' // Not Coding Standard
                ),
                'htmlOptions' => array(
                    'disabled' => $this->getDisabledValue(),
                    'onblur' => 'clearIdFromAutoCompleteField($(this).val(), \'' . $idInputName . '\');'
                    )
            ));
            $cClipWidget->endClip();
            return $cClipWidget->getController()->clips['ModelElement'];
        }

        protected function getAutoCompleteControllerId()
        {
            return $this->controllerId;
        }

        /**
         * Render a select link. This link calls a modal
         * popup.
         * @return The element's content as a string.
         */
        protected function renderSelectLink()
        {
            $id = $this->getIdForSelectLink();
            $content = CHtml::ajaxLink(Yii::t('Default', '<span>Select</span>'),
                Yii::app()->createUrl($this->resolveModuleId() . '/' . $this->getSelectLinkControllerId() . '/'. static::$modalActionId .'/', array(
                'modalTransferInformation' => $this->getModalTransferInformation()
                )), array(
                    'onclick' => '$("#modalContainer").dialog("open"); return false;',
                    'update' => '#modalContainer',
                    'beforeSend' => 'js:function(){$(\'#' . $id . '\').parent().addClass(\'modal-model-select-link\');}',
                    'complete'   => 'js:function(){$(\'#' . $id . '\').parent().removeClass(\'modal-model-select-link\');}'
                    ),
                    array(
                    'id' => $id,
                    'style' => $this->getSelectLinkStartingStyle(),
                    )
            );
            return $content;
        }

        protected function getSelectLinkControllerId()
        {
            return $this->controllerId;
        }

        /**
         * Renders the attribute from the model.
         * @return The element's content.
         */
        protected function renderControlNonEditable()
        {
            if (!empty($this->model->{$this->attribute}->id) && $this->model->{$this->attribute}->id > 0)
            {
                if ($this->showLinkOnNonEditable())
                {
                    return CHtml::link(
                        Yii::app()->format->text($this->model->{$this->attribute}),
                        Yii::app()->createUrl($this->resolveModuleId() . '/' . $this->controllerId .
                        '/details/', array('id' => $this->model->{$this->attribute}->id))
                    );
                }
                else
                {
                    return Yii::app()->format->text($this->model->{$this->attribute});
                }
            }
        }

        /**
         * Override to support the module labels for the models.
         */
        protected function renderLabel()
        {
            if ($this->form === null)
            {
                return $this->getFormattedAttributeLabel();
            }
            $id = $this->getIdForHiddenField();
            return $this->form->labelEx($this->model, $this->attribute, array('for' => $id));
        }

        protected function getIdForHiddenField()
        {
            return $this->getEditableInputId($this->attribute, 'id');
        }

        protected function getNameForHiddenField()
        {
            return $this->getEditableInputName($this->attribute, 'id');
        }

        protected function getIdForTextField()
        {
            return $this->getEditableInputId($this->attribute, 'name');
        }

        protected function getNameForTextField()
        {
            return $this->getEditableInputId($this->attribute, 'name');
        }

        protected function getIdForSelectLink()
        {
            return $this->getEditableInputId($this->attribute, 'SelectLink');
        }

        /**
         * Get the collection of id/names of inputs and other
         * parts of the element.
         */
        public function getEditableNameIds()
        {
            return array(
                $this->getIdForHiddenField(),
                $this->getIdForTextField(),
                $this->getIdForSelectLink()
            );
        }

        /**
         * @return stringified name if it exists or empty string to
         * avoid (unnamed) being shown.
         */
        protected function getName()
        {
            if (!empty($this->model->{$this->attribute}->id) && $this->model->{$this->attribute}->id > 0)
            {
                return $this->model->{$this->attribute};
            }
            return '';
        }

        /**
         * @return id if a real model, otherwise an empty string to ensure
         * the @see CHtml::activeInputField works properly when resolving the id.
         */
        protected function getId()
        {
            if (!empty($this->model->{$this->attribute}->id) && $this->model->{$this->attribute}->id > 0)
            {
                return $this->model->{$this->attribute}->id;
            }
            return '';
        }

        /**
         * In the case of attributes that are related,
         * the attribute is returned, because that is a related model.
         */
        protected function getResolvedModel()
        {
            return $this->model->{$this->attribute};
        }

        /**
         * @return array
         */
        protected function getModalTransferInformation()
        {
            return array(
                    'sourceIdFieldId' => $this->getIdForHiddenField(),
                    'sourceNameFieldId' => $this->getIdForTextField(),
                    'sourceModelId'     => $this->model->id,
            );
        }

        protected function getSelectLinkStartingStyle()
        {
            if ($this->getDisabledValue() == 'disabled')
            {
                return 'display:none';
            }
            else
            {
                return null;
            }
        }

        /**
         * Determines correct column span based on params 'wide' value
         */
        protected function showLinkOnNonEditable()
        {
            if (isset($this->params['noLink']) && $this->params['noLink'])
            {
                return false;
            }
            return true;
        }

        /**
         * Gets the moduleId statically. You can override this method and get the moduleId in a non-static way
         * since this method is called non-statically.
         */
        protected function resolveModuleId()
        {
            return static::getModuleId();
        }

        public static function getModuleId()
        {
            return static::$moduleId;
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
         * Gets the action type for the related model's action
         * that is called by the link in the nonEditable render.
         */
        public static function getNonEditableActionType()
        {
            return static::$nonEditableActionType;
        }
    }
?>