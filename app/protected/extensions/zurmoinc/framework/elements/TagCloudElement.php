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

    class TagCloudElement extends MultiSelectDropDownElement
    {
        protected function renderControlEditable()
        {
            $multipleValuesCustomField = $this->model->{$this->attribute};
            assert('$multipleValuesCustomField instanceof MultipleValuesCustomField');
            $dataAndLabels      = CustomFieldDataUtil::
                                  getDataIndexedByDataAndTranslatedLabelsByLanguage(
                                  $multipleValuesCustomField->data, Yii::app()->language);
            $dataToValuesString = static::getDataToValuesString($multipleValuesCustomField->values);
            $dataLabels         = static::getJsonEncodedLabelsByDataAndLabels($multipleValuesCustomField->values,
                                    $dataAndLabels);
            $idForInput         = $this->getIdForSelectInput();
            $autoCompleteUrl    = Yii::app()->createUrl('zurmo/default/autoCompleteCustomFieldData/',
                                                     array('name' => $this->model->{$this->attribute}->data->name));
            $cClipWidget = new CClipWidget();
            $cClipWidget->beginClip("ModelElement");
            $cClipWidget->widget('ext.zurmoinc.framework.widgets.MultiSelectAutoComplete', array(
                'name'        => $this->getNameForSelectInput(),
                'id'          => $this->getIdForSelectInput(),
                'jsonEncodedIdsAndLabels'   => $dataLabels,
                'sourceUrl'   => $autoCompleteUrl,
                'htmlOptions' => array(
                    'disabled' => $this->getDisabledValue(),
                    ),
                'hintText' => Yii::t('Default', 'Type to find a tag')
            ));
            $cClipWidget->endClip();
            $content = $cClipWidget->getController()->clips['ModelElement'];
            return $content;
        }

        protected static function getDataToValuesString(RedBeanOneToManyRelatedModels $customFieldValues)
        {
            $s = '';
            foreach ($customFieldValues as $customFieldValue)
            {
                if ($customFieldValue->value != null)
                {
                    if ($s != null)
                    {
                        $s .= ', ';
                    }
                    $s .= $customFieldValue->value;
                }
            }
            return $s;
        }

        protected static function getJsonEncodedLabelsByDataAndLabels(RedBeanOneToManyRelatedModels $customFieldValues,
                                                                      $dataAndLabels)
        {
            assert('is_array($dataAndLabels)');
            $labels = array();
            foreach ($customFieldValues as $customFieldValue)
            {
                if ($customFieldValue->value != null)
                {
                    $labels[] = array('id' => $customFieldValue->value,
                                      'name' => $dataAndLabels[$customFieldValue->value]);
                }
            }
            return CJSON::encode($labels);
        }
    }
?>