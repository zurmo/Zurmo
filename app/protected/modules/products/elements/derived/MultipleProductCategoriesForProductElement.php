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
     * User interface element for managing related model relations for product templates. This class supports a HAS_MANY
     * specifically for the 'productCategories' relation. This is utilized by the Product Template model.
     *
     */
    class MultipleProductCategoriesForProductElement extends Element implements DerivedElementInterface
    {
        protected function renderControlNonEditable()
        {
            $content  = null;
            $productCategories = $this->getExistingProductCategoriesRelationsIdsAndLabels();
            foreach ($productCategories as $productCategoryData)
            {
                if ($content != null)
                {
                    $content .= ', ';
                }
                $content .= $productCategoryData['name'];
            }
            return $content;
        }

        protected function renderControlEditable()
        {
            assert('$this->model instanceof Product || $this->model instanceof Product');
            $cClipWidget = new CClipWidget();
            $cClipWidget->beginClip("ModelElement");
            $cClipWidget->widget('application.core.widgets.MultiSelectAutoComplete', array(
                                        'name'                      => $this->getNameForIdField(),
                                        'id'                        => $this->getIdForIdField(),
                                        'jsonEncodedIdsAndLabels'   => CJSON::encode($this->getExistingProductCategoriesRelationsIdsAndLabels()),
                                        'sourceUrl'   => Yii::app()->createUrl('productTemplates/default/autoCompleteAllProductCategoriesForMultiSelectAutoComplete'),
                                        'htmlOptions' => array(
                                        'disabled' => $this->getDisabledValue(),
                            ),
                                        'hintText' => Zurmo::t('ProductsModule', 'Type a ' . ProductCategory::getModelLabelByTypeAndLanguage('SingularLowerCase'),
                                                                LabelUtil::getTranslationParamsForAllModules())
            ));
            $cClipWidget->endClip();
            $content = $cClipWidget->getController()->clips['ModelElement'];
            return $content;
        }

        protected function renderError()
        {
        }

        protected function renderLabel()
        {
            return $this->resolveNonActiveFormFormattedLabel($this->getFormattedAttributeLabel());
        }

        protected function getFormattedAttributeLabel()
        {
            return Yii::app()->format->text(Zurmo::t('ProductsModule', 'Categories'));
        }

        public static function getDisplayName()
        {
            return Zurmo::t('ProductsModule', 'Related ProductsModulePluralLabel',
                       LabelUtil::getTranslationParamsForAllModules());
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

        protected function getNameForIdField()
        {
            return 'ProductCategoriesForm[categoryIds]';
        }

        protected function getIdForIdField()
        {
            return 'ProductCategoriesForm_ProductCategory_ids';
        }

        protected function getExistingProductCategoriesRelationsIdsAndLabels()
        {
            $existingProductCategories = array();
            for ($i = 0; $i < count($this->model->productCategories); $i++)
            {
                $existingProductCategories[] = array('id' => $this->model->productCategories[$i]->id,
                                                     'name' => $this->model->productCategories[$i]->name);
            }
            return $existingProductCategories;
        }

        public static function renderHtmlContentLabelFromProductCategoryAndKeyword($productCategory, $keyword)
        {
            assert('$productCategory instanceof ProductCategory && $productCategory->id > 0');
            assert('$keyword == null || is_string($keyword)');

            if ($productCategory->name != null)
            {
                return strval($productCategory) . '&#160&#160<b>'. '</b>';
            }
            else
            {
                return strval($productCategory);
            }
        }
    }
?>