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

    /**
     * Element for rendering sortable list of contact/lead web form attributes
     */
    class SortableContactWebFormAttributesElement extends Element
    {
        /**
         * @return string
         * @throws NotSupportedException
         */
        protected function renderControlNonEditable()
        {
            $attributes = ContactWebFormsUtil::getAllAttributes();
            $contactWebFormAttributes = null;
            if (isset($this->model->serializedData))
            {
                $contactWebFormAttributes = unserialize($this->model->serializedData);
                $allPlacedAttributes = ContactWebFormsUtil::getAllPlacedAttributes($attributes, $contactWebFormAttributes);
                $content = '';
                foreach ($allPlacedAttributes as $attribute)
                {
                    $content .= $attribute['{content}'].'<br/>';
                }
                return $content;
            }
            else
            {
                throw new NotSupportedException();
            }
        }

        /**
         * @return string
         */
        protected function renderControlEditable()
        {
            $attributes = ContactWebFormsUtil::getAllAttributes();
            $contactWebFormAttributes = null;
            if (isset($this->model->serializedData))
            {
                $contactWebFormAttributes = unserialize($this->model->serializedData);
            }
            $clip = $this->form->checkBoxList($this->model,
                                              $this->attribute,
                                              ContactWebFormsUtil::getAllNonPlacedAttributes($attributes,
                                              $contactWebFormAttributes),
            $this->getEditableHtmlOptions());
            $title     = ZurmoHtml::tag('h3', array(), Zurmo::t('ContactWebFormModule', 'Available Fields'));
            $content   = ZurmoHtml::tag('span', array('class' => 'row-description'),
                         Zurmo::t('ContactWebFormModule', 'Check the fields that you like to add to your form, you can then change their order or remove them'));
            $content  .= ZurmoHtml::tag('div', array('class' => 'third'), $title . $clip );

            $cClipWidget = new CClipWidget();
            $cClipWidget->beginClip("attributesList");
            $cClipWidget->widget('application.core.widgets.JuiSortable', array(
                'itemTemplate' => $this->renderItemTemplate(),
                'items'        => ContactWebFormsUtil::getAllPlacedAttributes($attributes, $contactWebFormAttributes),
            ));
            $cClipWidget->endClip();
            $clip       = $cClipWidget->getController()->clips['attributesList'];
            $title      = ZurmoHtml::tag('h3', array(), Zurmo::t('ContactWebFormModule', 'Chosen Fields'));
            $content   .= ZurmoHtml::tag('div', array('class' => 'twoThirds'), $title . $clip );
            $this->registerScript();
            return $content;
        }

        protected function registerScript()
        {
            Yii::app()->clientScript->registerScriptFile(
                Yii::app()->getAssetManager()->publish(
                    Yii::getPathOfAlias('application.modules.contactWebForms.views.assets')) . '/ContactWebFormUtils.js');
        }

        /**
         * @return array
         */
        protected function getEditableHtmlOptions()
        {
            return array(
                'template'  => '<div class="multi-select-checkbox-input"><label class="hasCheckBox">{input}</label>{label}</div>',
                'separator' => '');
        }

        /**
         * @return string
         */
        protected function renderItemTemplate()
        {
            return '<li><div class="dynamic-row"><div>
                        <label for="ContactWebForm_serializedData_{id}">{content}</label>' .
                        '<input type="hidden" name="attributeIndexOrDerivedType[]" value="{id}" />' .
                    '</div>{checkedAndReadOnly}</div></li>';
        }

        protected function renderError()
        {
        }

        /**
         * @return string
         */
        protected function renderLabel()
        {
            return Zurmo::t('ContactWebFormModule', 'Form Layout');
        }
    }
?>