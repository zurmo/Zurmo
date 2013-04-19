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

    class ZurmoActiveForm extends CActiveForm
    {
        /**
         * Override when utilizing dynamic attributes that are populated on a different form model than the one
         * that is used to make the form.  If left null, it will default to the model specified in the error function
         * @var null or string
         */
        public $modelClassNameForError;

        /**
         * Allows the form to be bound as a live event. This will allow the form to stay active even after an ajax
         * action done through the yiiGridView for example.
         * @var boolean
         */
        public $bindAsLive = false;

        public static function makeErrorsSummaryId($id)
        {
            assert('is_string($id)');
            return $id . '_es_';
        }

        /**
         * Makes errorsData by getting errors from model.  Also resolves for owned related models such as Email. Prior
         * to having this method, things such as currencyValue, emailAddress, and street1 for example were not properly
         * showing validation errors on failure.  This method properly handles Address, Email, and CurrencyValue which
         * are three special related models where there are multiple attributes that are all shown as if they are the
         * attributes on the base model.  Custom fields don't follow this because they only have 'value' to show
         * so are ok without special manipulation.
         * @param $model
         * @return array of errorData
         */
        public static function makeErrorsDataAndResolveForOwnedModelAttributes($model)
        {
            assert('$model instanceof RedBeanModel || $model instanceof CModel');
            $errorData = array();
            foreach ($model->getErrors() as $attribute => $errors)
            {
                if ($model::isRelation($attribute) && $model::isOwnedRelation($attribute) &&
                   in_array($model::getRelationModelClassName($attribute), array('Address', 'Email', 'CurrencyValue')))
                {
                    foreach ($errors as $relatedAttribute => $relatedErrors)
                    {
                        $errorData[ZurmoHtml::activeId($model, $attribute . '[' . $relatedAttribute . ']')] = $relatedErrors;
                    }
                }
                else
                {
                    $errorData[ZurmoHtml::activeId($model, $attribute)] = $errors;
                }
            }
            return $errorData;
        }

        /**
         * Use this method to register dynamically created attributes during an ajax call.  An example is if you
         * add a filter or trigger, the inputs need to be added to the yiiactiveform so that validation handling can work
         * properly.  This method replaces the id and model elements with the correctly needed values.
         * Only adds inputs that have not been added already
         */
        public function renderAddAttributeErrorSettingsScript($formId)
        {
            assert('is_string($formId)');
            $attributes             = $this->getAttributes();
            $encodedErrorAttributes = CJSON::encode(array_values($attributes));
            $script = "
                var settings = $('#" . $formId . "').data('settings');
                $.each(" . $encodedErrorAttributes . ", function(i)
                {
                    var newId = this.id;
                    var alreadyInArray = false;
                    $.each(settings.attributes, function (i)
                    {
                        if (newId == this.id)
                        {
                            alreadyInArray = true;
                        }
                    });
                    if (alreadyInArray == false)
                    {
                        settings.attributes.push(this);
                    }
                });
                $('#" . $formId . "').data('settings', settings);
            ";
            Yii::app()->getClientScript()->registerScript('AddAttributeErrorSettingsScript' . $formId, $script);
        }

        /**
         *
         * Override for special handling of dynamically added attributes.  Allows for overriding the model class name
         * and id.
         * (non-PHPdoc)
         * @see CActiveForm::error()
         */
        public function error($model, $attribute, $htmlOptions = array(), $enableAjaxValidation = true, $enableClientValidation = true, $id = null)
        {
            if (!$this->enableAjaxValidation)
            {
                $enableAjaxValidation = false;
            }
            if (!$this->enableClientValidation)
            {
                $enableClientValidation = false;
            }
            if (!isset($htmlOptions['class']))
            {
                $htmlOptions['class'] = $this->errorMessageCssClass;
            }
            if (!$enableAjaxValidation && !$enableClientValidation)
            {
                return CHtml::error($model, $attribute, $htmlOptions);
            }
            if ($id == null)
            {
                $id = $this->resolveId($model, $attribute);
            }
            $inputID = isset($htmlOptions['inputID']) ? $htmlOptions['inputID'] : $id;
            unset($htmlOptions['inputID']);
            if (!isset($htmlOptions['id']))
            {
                $htmlOptions['id'] = $inputID . '_em_';
            }
            $option = array(
                'id'                   => $id,
                'inputID'              => $inputID,
                'errorID'              => $htmlOptions['id'],
                'model'                => $this->resolveModelClassNameForError($model),
                'name'                 => $attribute,
                'enableAjaxValidation' => $enableAjaxValidation,
            );
            $optionNames = array(
                'validationDelay',
                'validateOnChange',
                'validateOnType',
                'hideErrorMessage',
                'inputContainer',
                'errorCssClass',
                'successCssClass',
                'validatingCssClass',
                'beforeValidateAttribute',
                'afterValidateAttribute',
            );
            foreach ($optionNames as $name)
            {
                if (isset($htmlOptions[$name]))
                {
                    $option[$name] = $htmlOptions[$name];
                    unset($htmlOptions[$name]);
                }
            }
            if ($model instanceof CActiveRecord && !$model->isNewRecord)
            {
                $option['status'] = 1;
            }
            if ($enableClientValidation)
            {
                $validators    = isset($htmlOptions['clientValidation']) ? array($htmlOptions['clientValidation']) : array();
                $attributeName = $attribute;
                if (($pos = strrpos($attribute, ']')) !== false && $pos !== strlen($attribute) - 1) // e.g. [a]name
                {
                    $attributeName = substr($attribute, $pos + 1);
                }
                foreach ($model->getValidators($attributeName) as $validator)
                {
                    if ($validator->enableClientValidation)
                    {
                        if (($js = $validator->clientValidateAttribute($model, $attributeName)) != '')
                        {
                            $validators[] = $js;
                        }
                    }
                }
                if ($validators !== array())
                {
                    $option['clientValidation'] = new CJavaScriptExpression("function(value, messages, attribute) {\n" .
                                                                            implode("\n", $validators) . "\n}");
                }
            }
            $html = CHtml::error($model, $attribute, $htmlOptions);
            if ($html === '')
            {
                if (isset($htmlOptions['style']))
                {
                    $htmlOptions['style'] = rtrim($htmlOptions['style'], ';') . ';display:none';
                }
                else
                {
                    $htmlOptions['style'] = 'display:none';
                }
                $html = CHtml::tag('div', $htmlOptions, '');
            }
            $this->attributes[$inputID] = $option;
            return $html;
        }

        /**
         * Override to handle relation model error summary information.  This information needs to be parsed properly
         * otherwise it will show up as 'Array' for the error text.
         * @see CActiveForm::errorSummary()
         */
        public function errorSummary($models, $header = null, $footer = null, $htmlOptions = array())
        {
            if (!$this->enableAjaxValidation && !$this->enableClientValidation)
            {
                return ZurmoHtml::errorSummary($models, $header, $footer, $htmlOptions);
            }
            if (!isset($htmlOptions['id']))
            {
                $htmlOptions['id'] = static::makeErrorsSummaryId($this->id);
            }
            $html = ZurmoHtml::errorSummary($models, $header, $footer, $htmlOptions);
            if ($html === '')
            {
                if ($header === null)
                {
                    $header = '<p>' . Zurmo::t('yii', 'Please fix the following input errors:') . '</p>';
                }
                if (!isset($htmlOptions['class']))
                {
                    $htmlOptions['class'] = ZurmoHtml::$errorSummaryCss;
                }
                if (isset($htmlOptions['style']))
                {
                    $htmlOptions['style'] = rtrim($htmlOptions['style'], ';') . ';display:none';
                }
                else
                {
                    $htmlOptions['style'] = 'display:none';
                }
                $html = ZurmoHtml::tag('div', $htmlOptions, $header . "\n<ul><li>dummy</li></ul>" . $footer);
            }

            $this->summaryID = $htmlOptions['id'];
            return $html;
        }

        /**
         * Override to allow for optional live binding of yiiactiveform. @see $bindAsLive.
         */
        public function run()
        {
            if (is_array($this->focus))
            {
                $this->focus="#" . ZurmoHtml::activeId($this->focus[0], $this->focus[1]);
            }
            echo ZurmoHtml::endForm();
            $cs = Yii::app()->clientScript;
            $cs->registerScriptFile(
                Yii::app()->getAssetManager()->publish(
                    Yii::getPathOfAlias('application.core.views.assets')
                    ) . '/FormUtils.js',
                CClientScript::POS_END
            );
            if (!$this->enableAjaxValidation && !$this->enableClientValidation || empty($this->attributes))
            {
                if ($this->focus !== null)
                {
                    $cs->registerCoreScript('jquery');
                    $cs->registerScript('CActiveForm#focus', "
                        if (!window.location.hash)
                            { $('" . $this->focus . "').focus(); }
                    ");
                }
                return;
            }
            $options = $this->clientOptions;
            if (isset($this->clientOptions['validationUrl']) && is_array($this->clientOptions['validationUrl']))
            {
                $options['validationUrl'] = ZurmoHtml::normalizeUrl($this->clientOptions['validationUrl']);
            }

            $options['attributes'] = array_values($this->attributes);
            if ($this->summaryID !== null)
            {
                $options['summaryID'] = $this->summaryID;
            }
            if ($this->focus !== null)
            {
                $options['focus'] = $this->focus;
            }

            $options = CJavaScript::encode($options);
            //Not registering via coreScript because it does not properly register when using ajax non-minified
            //on home page myList config view.  Needs a better solution
            $cs->registerScriptFile(
                Yii::app()->getAssetManager()->publish(
                    Yii::getPathOfAlias('system.web.js.source')
                    ) . '/jquery.yii.js',
                CClientScript::POS_END
            );
            $cs->registerScriptFile(
                Yii::app()->getAssetManager()->publish(
                    Yii::getPathOfAlias('system.web.js.source')
                    ) . '/jquery.yiiactiveform.js',
                CClientScript::POS_END
            );
            $id = $this->id;
            if ($this->bindAsLive)
            {
                $cs->registerScript(__CLASS__. '#' . $id, "\$('#$id').live('focus', function(e)
                {
                    if ($(this).data('settings') == undefined)
                    {
                        $(this).yiiactiveform($options);
                    }
                });
                ");
            }
            else
            {
                $cs->registerScript(__CLASS__. '#' . $id, "\$('#$id').yiiactiveform($options);");
            }
        }

        public function getAttributes()
        {
            return $this->attributes;
        }

        /**
         * (non-PHPdoc)
         * @see CActiveForm::radioButtonList()
         */
        public function radioButtonList($model, $attribute, $data, $htmlOptions = array())
        {
            return ZurmoHtml::activeRadioButtonList($model, $attribute, $data, $htmlOptions);
        }

        /**
         * Override to support adding label class = 'hasCheckBox'
         * (non-PHPdoc)
         * @see CActiveForm::checkBox()
         */
        public function checkBox($model, $attribute, $htmlOptions = array())
        {
            return ZurmoHtml::activeCheckBox($model, $attribute, $htmlOptions);
        }

        /**
         * (non-PHPdoc)
         * @see CActiveForm::checkBoxList()
         */
        public function checkBoxList($model, $attribute, $data, $htmlOptions = array())
        {
            return ZurmoHtml::activeCheckBoxList($model, $attribute, $data, $htmlOptions);
        }

        /**
         * (non-PHPdoc)
         * @see CActiveForm::dropDownList()
         */
        public function dropDownList($model, $attribute, $data, $htmlOptions = array())
        {
            return ZurmoHtml::activeDropDownList($model, $attribute, $data, $htmlOptions);
        }

        protected function resolveId($model, $attribute)
        {
            return CHtml::activeId($model, $attribute);
        }

        protected function resolveModelClassNameForError($model)
        {
            if ($this->modelClassNameForError != null)
            {
                return $this->modelClassNameForError;
            }
            return get_class($model);
        }
    }
?>