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

    class ModulePermissionsEditAndDetailsView extends EditAndDetailsView
    {
        /**
         * View Metadata to be used.
         */
        private $metadata;

        /**
         * Constructs a module permissions view specifying the controller as
         * well as the model that will have its details displayed.
         */
        public function __construct($renderType, $controllerId, $moduleId, $model, $modelId, $metadata)
        {
            assert('$renderType == "Edit" || $renderType == "Details"');
            assert('$controllerId != null');
            assert('$moduleId != null');
            assert('$model instanceof ModulePermissionsForm');
            assert('$modelId != null');
            assert('is_array($metadata) && !empty($metadata)');
            $this->renderType     = $renderType;
            $this->controllerId   = $controllerId;
            $this->moduleId       = $moduleId;
            $this->model          = $model;
            $this->modelClassName = get_class($model);
            $this->modelId        = $modelId;
            $this->metadata       = $metadata;
        }

        public static function getDefaultMetadata()
        {
            $metadata = array(
                'global' => array(
                    'toolbar' => array(
                        'elements' => array(
                            array('type' => 'CancelLink', 'renderType' => 'Edit'),
                            array('type' => 'SaveButton', 'renderType' => 'Edit'),
                        ),
                    ),
                ),
            );
            return $metadata;
        }

        /**
         * Override to produce a form layout that does not follow the
         * standard form layout for EditView.
          */
        protected function renderFormLayout($form = null)
        {
            $content      = '';
            $metadata     = $this->getFormLayoutMetadata();
            $permissions  = ModulePermissionsEditViewUtil::getPermissionsForView();
            assert('count($permissions) > 0');
            foreach ($metadata['global']['panels'] as $panel)
            {
                if (!empty($panel['title']))
                {
                    $content .= '<div class="panelTitle">' . $panel['title'] . '</div>';
                }
                $content .= '<table>';
                $content .= '<colgroup>';
                $content .= '<col style="width:20%" />';
                $width = 80 / count($permissions);
                foreach ($permissions as $permission)
                {
                    $content .= '<col style="width:' . $width . '%" />';
                }
                $content .= '</colgroup>';
                $content .= '<tbody>';
                $content .= '<tr>';
                $content .= '<th>&#160;</th>';
                $permissionNames = ModulePermissionsEditViewUtil::getPermissionNamesForView();
                foreach ($permissionNames as $permission)
                {
                    $content .= '<th>' . yii::t('Default', $permission) . '</th>';
                }
                $content .= '</tr>';
                foreach ($panel['rows'] as $row)
                {
                    assert('!empty($row["title"])');
                    $cellsContent = '<th>' . yii::t('Default', $row['title']) . '</th>';
                    foreach ($row['cells'] as $cell)
                    {
                        if (is_array($cell['elements']) && $this->shouldDisplayCell(ArrayUtil::getArrayValue($cell, 'detailViewOnly')))
                        {
                            foreach ($cell['elements'] as $elementInformation)
                            {
                                $editableTemplate = '<td colspan="{colspan}">{content}{error}</td>';
                                $this->resolveElementInformationDuringFormLayoutRender($elementInformation);
                                $elementclassname = $elementInformation['type'] . 'Element';
                                $element          = new $elementclassname(
                                                            $this->model,
                                                            $elementInformation['attributeName'],
                                                            $form,
                                                            array_slice($elementInformation, 2));
                                $element->editableTemplate = $editableTemplate;
                                $cellsContent    .= $element->render();
                            }
                        }
                    }
                    if (!empty($cellsContent))
                    {
                    $content .= '<tr>';
                    $content .= $cellsContent;
                    $content .= '</tr>';
                    }
                }
                $content .= '</tbody>';
                $content .= '</table>';
            }
            return $content;
        }

        /**
         * This view is not editable in the designer tool.
         */
        public static function getDesignerRulesType()
        {
            return null;
        }

        /**
         * This ModelView metadata validity check is not valid on this view
         * because this view follows a different metadata structure.
         */
        protected static function assertMetadataIsValid(array $metadata)
        {
        }

        public function isUniqueToAPage()
        {
            return true;
        }

        /**
         * Merges view metadata with dynamically
         * generated metadata based on $model
         */
        protected function getFormLayoutMetadata()
        {
            return $this->metadata;
        }
    }
?>