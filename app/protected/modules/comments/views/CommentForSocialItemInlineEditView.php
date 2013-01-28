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
     * An inline edit view for a comment.
     *
     */
    class CommentForSocialItemInlineEditView extends CommentInlineEditView
    {
        protected $viewContainsFileUploadElement = false;

        protected $uniqueId;

        public function __construct($model, $controllerId, $moduleId, $saveActionId, $urlParameters, $uniquePageId, $uniqueId)
        {
            assert('is_int($uniqueId)');
            assert('$model instanceof Comment');
            parent::__construct($model, $controllerId, $moduleId, $saveActionId, $urlParameters, $uniquePageId);
            $this->uniqueId = $uniqueId;
        }

        public function getFormName()
        {
            return "comment-inline-edit-form" . $this->uniquePageId;
        }

        public static function getDefaultMetadata()
        {
            $metadata = array(
                'global' => array(
                    'toolbar' => array(
                        'elements' => array(
                            array('type'        => 'SaveButton', 'label' => "eval:Zurmo::t('CommentsModule', 'Comment')",
                                  'htmlOptions' => array('id'   => 'eval:"saveComment" . $this->model->id',
                                                         'name' => 'eval:"saveComment" . $this->model->id')),
                        ),
                    ),
                    'derivedAttributeTypes' => array(
                        'Files'
                    ),
                    'nonPlaceableAttributeNames' => array(
                        'latestDateTime',
                    ),
                    'panelsDisplayType' => FormLayout::PANELS_DISPLAY_TYPE_ALL,
                    'panels' => array(
                        array(
                            'rows' => array(
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'description', 'type' => 'TextArea', 'rows' => 2),
                                            ),
                                        ),
                                    )
                                ),
                            ),
                        ),
                    ),
                ),
            );
            return $metadata;
        }

        /**
         * Override to allow the comment thread, if it exists to be refreshed.
         * (non-PHPdoc)
         * @see InlineEditView::renderConfigSaveAjax()
         */
        protected function renderConfigSaveAjax($formName)
        {
            // Begin Not Coding Standard
            return ZurmoHtml::ajax(array(
                    'type' => 'POST',
                    'data' => 'js:$("#' . $formName . '").serialize()',
                    'url'  =>  $this->getValidateAndSaveUrl(),
                    'update' => '#' . $this->uniquePageId,
                    'complete' => "function(XMLHttpRequest, textStatus){
                        //find if there is a comment thread to refresh
                        $('#hiddenCommentRefresh" . $this->uniquePageId . "').click();
                    }"
                ));
            // End Not Coding Standard
        }

        /**
         * Override to set the inputIdPrefix so when there are multiple instances of this form on a single page
         * it will be valid xhtml
         * (non-PHPdoc)
         * @see ContactInlineCreateForArchivedEmailCreateView::resolveElementInformationDuringFormLayoutRender()
         */
        protected function resolveElementInformationDuringFormLayoutRender(& $elementInformation)
        {
            $elementInformation['inputIdPrefix']  = array(get_class($this->model), $this->uniqueId);
        }
    }
?>
