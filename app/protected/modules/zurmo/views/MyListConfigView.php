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
     * The base configuration view class for my list type views.
     */
    class MyListConfigView extends ModalConfigEditView
    {
        /**
         * Stores incoming search attribute information from a post. @see setMetadataFromPost()
         * @var array
         */
        private $searchAttributes;

        public function __construct(ConfigurableMetadataModel $model, $searchModel, $params)
        {
            assert('$searchModel instanceof SearchForm || $searchModel instanceof RedBeanModel');
            $this->model          = $model;
            $this->searchModel    = $searchModel;
            $this->modelClassName = get_class($model);
            $this->modelId        = null;
            $this->params         = $params;
        }

        /**
         * An override to utilize a special ActiveForm that will not display any indications in the user interface
         * that an attribute is required.
         */
        protected static function getActiveFormClassName()
        {
            return 'NoRequiredsActiveForm';
        }

        /**
         * Supports both validating both models.  The MyListForm model and the SearchModel
         * @see ModalConfigEditView::validate()
         */
        public function validate()
        {
            $this->model->setAttributes(ArrayUtil::getArrayValue($_POST, $this->getPostArrayName()));
            $this->searchModel->setAttributes(ArrayUtil::getArrayValue($_POST, $this->getSearchModelPostArrayName()));
            echo NoRequiredsActiveForm::validate(array($this->model, $this->searchModel), null, false);
        }

        /**
         * Supports setting metadata on both models.  The MyListForm model and the SearchModel
         * @see ModalConfigEditView::setMetadataFromPost()
         */
        public function setMetadataFromPost($postArray)
        {
            parent::setMetadataFromPost($postArray);
            $sanitizedPostArray     = PostUtil::sanitizePostByDesignerTypeForSavingModel(
                                      $this->searchModel,
                                      ArrayUtil::getArrayValue($_POST, $this->getSearchModelPostArrayName()));

            $searchAttributes                   = SearchUtil::
                                                  getSearchAttributesFromSearchArrayForSavingExistingSearchCriteria
                                                  ($sanitizedPostArray);
           $searchAttributesAdaptedToSetInModel = SearchUtil::adaptSearchAttributesToSetInRedBeanModel(
                                                      $searchAttributes, $this->searchModel);
           $this->searchAttributes              = $searchAttributesAdaptedToSetInModel;
        }

        /**
         * Supports getting metadata on both models.  The MyListForm model and the SearchModel
         * @see ModalConfigEditView::getViewMetadata()
         */
        public function getViewMetadata()
        {
            $viewMetadata = array();
            if ($this->searchAttributes != null)
            {
                $viewMetadata['searchAttributes'] = $this->searchAttributes;
            }
            $viewMetadata = array_merge($viewMetadata, $this->model->getAttributes());
            return $viewMetadata;
        }

        public function getSearchModelPostArrayName()
        {
            return get_class($this->searchModel);
        }

        public static function getDesignerRulesType()
        {
            return 'MyListConfigView';
        }

        /**
         * Override because the config form is using the searchModel as its model and not the formModel.
         * @see DetailsView::getModel()
         */
        protected function getModel()
        {
            return $this->searchModel;
        }

        /**
         * Override to support the 'title' from MyListForm being rendered into the view. This is special because this
         * view can be modified in the designer tool, however the MyListForm is not compatible with the SearchForm in
         * designer so this special override is in place to manually ensure the MyListForm attributes, currently only
         * 'title' can be placed.
         * @see DetailsView::afterResolveMetadataWithRenderedElements()
         */
        protected function afterResolveMetadataWithRenderedElements(& $metadataWithRenderedElements, $form)
        {
            assert('is_array($metadataWithRenderedElements)');
            assert('$form == null || $form instanceof ZurmoActiveForm');
            $element             = new TextElement($this->model, 'title', $form);
            $titleData           = array();
            $titleData['rows'][0]['cells'][0]['elements'][0] = $element->render();
            array_unshift($metadataWithRenderedElements['global']['panels'], $titleData);
        }

        /**
         * Override to add a display description.  An example would be 'My Contacts'.  This display description
         * can then be used by external classes interfacing with the view in order to display information to the user in
         * the user interface.
         */
        public static function getDisplayDescription()
        {
            return null;
        }
    }
?>