<?php
    /**
     *
     * Helper class to manage changing the panelsDisplayType for a layout in the designer user interface.
     */
    class PanelsDisplayTypeLayoutMetadataUtil
    {
        /**
         * Given metadata make a LayoutPanelsTypeForm.
         * @param array $metadata
         * @return object LayoutPanelsTypeForm
         */
        public static function makeFormFromEditableMetadata($metadata)
        {
            assert(isset($metadata['global']['panelsDisplayType']));
            $formModel = new LayoutPanelsTypeForm();
            $panelsDisplayType = $metadata['global']['panelsDisplayType'];
            assert('$panelsDisplayType == FormLayout::PANELS_DISPLAY_TYPE_ALL ||
                    $panelsDisplayType == FormLayout::PANELS_DISPLAY_TYPE_FIRST ||
                    $panelsDisplayType == FormLayout::PANELS_DISPLAY_TYPE_TABBED');
            $formModel->type = $metadata['global']['panelsDisplayType'];
            return $formModel;
        }

        /**
         * Given savable metadata for a layout, populate that array from the posted data.
         * @param array $savableMetadata
         * @return boolean true if the savable metadata was populated successfully.
         */
        public static function populateSaveableMetadataFromPostData(& $savableMetadata, $layoutPanelsTypeFormPostData)
        {
            assert('is_array($savableMetadata)');
            assert('is_array($layoutPanelsTypeFormPostData)');
            $panelsDisplayTypeForm = new LayoutPanelsTypeForm();
            $panelsDisplayTypeForm->setAttributes($layoutPanelsTypeFormPostData);
            $validated = $panelsDisplayTypeForm->validate();
            if (!$validated)
            {
                return false;
            }
            $savableMetadata['panelsDisplayType'] = (int)$panelsDisplayTypeForm->type;
            return true;
        }
    }
?>