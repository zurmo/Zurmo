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
     * Display a collection of address fields
     * Collection includes street1, street2,
     * city, state, postal code, and country.
     */
    class AddressElement extends Element
    {
        /**
         * Renders the noneditable address content.
         * Takes the model attribute value and converts it into
         * at most 6 items which form the collection.
         * @return A string containing the element's content.
         */
        protected function renderControlNonEditable()
        {
            assert('$this->model->{$this->attribute} instanceof Address');
            $addressModel = $this->model->{$this->attribute};
            $street1      = $addressModel->street1;
            $street2      = $addressModel->street2;
            $city         = $addressModel->city;
            $state        = $addressModel->state;
            $postalCode   = $addressModel->postalCode;
            $country      = $addressModel->country;
            $content = null;
            if (!empty($street1))
            {
                $content  .= Yii::app()->format->text($street1) . "<br/>\n";
            }
            if (!empty($street2))
            {
                $content .= Yii::app()->format->text($street2) . "<br/>\n";
            }
            if (!empty($city))
            {
                $content .= Yii::app()->format->text($city) . ' ';
            }
            if (!empty($state))
            {
                $content .= Yii::app()->format->text($state);
            }
            if (!empty($state) && !empty($postalCode))
            {
                $content .= ',&#160;';
            }
            if (!empty($postalCode))
            {
                $content .= Yii::app()->format->text($postalCode) . "<br/>\n";
            }
            if (!empty($country))
            {
                $content .= Yii::app()->format->text($country);
            }
            return $content;
        }

        /**
         * Renders the editable address content.
         * Takes the model attribute value and converts it into
         * at most 6 items.
         * @return A string containing the element's content
         */
        protected function renderControlEditable()
        {
            assert('$this->model->{$this->attribute} instanceof Address');
            $addressModel = $this->model->{$this->attribute};
            $content      = '';
            foreach (array('street1', 'street2', 'city', 'state', 'postalCode', 'country') as $attribute)
            {
                $content .= $this->renderEditableAddressTextField($addressModel, $this->form, $this->attribute, $attribute) . "<br/>\n";
            }
            return $content;
        }

        protected function renderEditableAddressTextField($model, $form, $inputNameIdPrefix, $attribute)
        {
            $id          = get_class($this->model) . '_' . $inputNameIdPrefix . '_' . $attribute;
            $htmlOptions = array(
                'name' => get_class($this->model) . '[' . $inputNameIdPrefix . '][' . $attribute . ']',
                'id'   => $id,
            );
            $label       = $form->labelEx  ($model, $attribute, array('for'   => $id));
            $textField   = $form->textField($model, $attribute, $htmlOptions);
            $error       = $form->error    ($model, $attribute);
            return $label . "<br/>\n" . $textField . $error;
        }

        protected function renderError()
        {
        }

        protected function renderLabel()
        {
            return $this->getFormattedAttributeLabel();
        }
    }
?>
