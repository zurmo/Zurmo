<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2012 Zurmo Inc.
     *
     * Zurmo is free software; you can redistribute it and/or modify it under
     * the terms of the GNU Affero General Public License version 3 as published by the
     * Free Software Foundation with the addition of the following permission added
     * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
     * IN WHICH THE COPYRIGHT IS OWNED BY ZURMO, ZURMO DISCLAIMS THE WARRANTY
     * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
     *
     * Zurmo is distributed in the hope that it will be useful, but WITHOUT
     * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
     * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
     * details.
     *
     * You should have received a copy of the GNU Affero General Public License along with
     * this program; if not, see http://www.gnu.org/licenses or write to the Free
     * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
     * 02110-1301 USA.
     *
     * You can contact Zurmo, Inc. with a mailing address at 113 McHenry Road Suite 207,
     * Buffalo Grove, IL 60089, USA. or at email address contact@zurmo.com.
     ********************************************************************************/

    class SellPriceFormulaInformationElement extends Element
    {
        /**
         * @return string
         */
        protected function renderControlNonEditable()
        {
            assert('$this->model->{$this->attribute} instanceof SellPriceFormula');
            $sellPriceFormulaModel      = $this->model->{$this->attribute};
            $type                       = $sellPriceFormulaModel->type;
            $discountOrMarkupPercentage     = $sellPriceFormulaModel->discountOrMarkupPercentage;
            $displayedSellPriceFormulaList  = SellPriceFormula::getDisplayedSellPriceFormulaArray();
            $content = '';
            if ($type != null)
            {
                $content = $displayedSellPriceFormulaList[$type];

                if ($type != SellPriceFormula::TYPE_EDITABLE)
                {
                    $content = str_replace('{discount}', $discountOrMarkupPercentage . '%', $content);
                }
            }

            return $content;
        }

        /**
         * Renders the editable sell price formula content.
         * It consist of 2 items
         * Name and discountOrMarkupPercentage where discountOrMarkupPercentage
         * would be dependent on Name selected
         * @return A string containing the element's content.
         */
        protected function renderControlEditable()
        {
            assert('$this->model->{$this->attribute} instanceof SellPriceFormula');
            $this->registerScripts();
            $sellPriceFormulaModel  = $this->model->{$this->attribute};
            $content                = $this->renderNameDropDown($sellPriceFormulaModel, $this->form, $this->attribute, 'type') . "\n";
            $content               .= $this->renderDiscountOrMarkupPercentageTextField($sellPriceFormulaModel, $this->form, $this->attribute, 'discountOrMarkupPercentage') . "\n";
            return $content;
        }

        /**
         * Renders the Sell Price Formula Dropdown
         * @param Redbean $model
         * @param Object $form
         * @param string $inputNameIdPrefix
         * @param string $attribute
         * @return string
         */
        protected function renderNameDropDown($model, $form, $inputNameIdPrefix, $attribute)
        {
            $id                    = $this->getEditableInputId($inputNameIdPrefix, $attribute);
            $discountOrMarkupPercentageTextFieldId = $this->getEditableInputId($inputNameIdPrefix, 'discountOrMarkupPercentage');
            $sellPriceValueId              =  $this->getEditableInputId('sellPrice', 'value');
            $htmlOptions = array(
                'name'      => $this->getEditableInputName($this->attribute, 'type'),
                'id'        => $id,
                'onchange'  => 'showHideDiscountOrMarkupPercentageTextField($(this).val(), \'' . $discountOrMarkupPercentageTextFieldId . '\');
                              enableDisableSellPriceElementBySellPriceFormula($(this).val(), \'' . $sellPriceValueId . '\', "sellPrice");
                              calculateSellPriceBySellPriceFormula()'
            );
            $dropDownField         = $form->dropDownList($model, $attribute, SellPriceFormula::getTypeDropDownArray(), $htmlOptions);
            $error                 = $form->error($model, $attribute, array('inputID' => $id));
            return $dropDownField . $error;
        }

        /**
         * Renders discount text field
         * @param RedBeanModel $model
         * @param Object $form
         * @param string $inputNameIdPrefix
         * @param string $attribute
         * @return string
         */
        protected function renderDiscountOrMarkupPercentageTextField($model, $form, $inputNameIdPrefix, $attribute)
        {
            $id          = $this->getEditableInputId($inputNameIdPrefix, $attribute);
            $htmlOptions = array(
                'name'  => $this->getEditableInputName($inputNameIdPrefix, $attribute),
                'id'    => $id,
                'style' => $this->resolveInputDisplayStyle($model),
                'onkeyup' => 'calculateSellPriceBySellPriceFormula()'
            );
            $textField   = $form->textField($model, $attribute, $htmlOptions);
            $error       = $form->error($model, $attribute, array('inputID' => $id));

            return $textField . $error;
        }

        protected function renderError()
        {
        }

        /**
         * Registers the script required on usage of sell price formula
         */
        protected function registerScripts()
        {
            Yii::app()->clientScript->registerScript(
                'ShowHideDiscountOrMarkupPercentageTextField',
                ProductTemplateElementUtil::getShowHideDiscountOrMarkupPercentageTextFieldScript(),
                CClientScript::POS_END
            );
            Yii::app()->clientScript->registerScript(
                'EnableDisableSellPriceElementBySellPriceFormula',
                ProductTemplateElementUtil::getEnableDisableSellPriceElementBySellPriceFormulaScript(),
                CClientScript::POS_END
            );
            Yii::app()->clientScript->registerScript(
                'CalculateSellPriceBySellPriceFormula',
                ProductTemplateElementUtil::getCalculatedSellPriceBySellPriceFormulaScript(),
                CClientScript::POS_END
            );
            Yii::app()->clientScript->registerScript(
                'BindActionsWithFormFieldsForSellPrice',
                ProductTemplateElementUtil::bindActionsWithFormFieldsForSellPrice(),
                CClientScript::POS_END
            );
        }

        /**
         * Resolves input display style
         * @param RedBean $model
         * @return string
         */
        protected function resolveInputDisplayStyle($model)
        {
            if ($model->type == SellPriceFormula::TYPE_PROFIT_MARGIN || $model->type == SellPriceFormula::TYPE_MARKUP_OVER_COST || $model->type == SellPriceFormula::TYPE_DISCOUNT_FROM_LIST)
            {
                return 'display:block';
            }

            return 'display:none';
        }
    }
?>