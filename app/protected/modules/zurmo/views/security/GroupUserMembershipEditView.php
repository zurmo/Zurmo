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
     * View to show the group user membership interface for
     * adding and removing users from a group.
     */
    class GroupUserMembershipEditView extends EditView
    {
        /**
         * Constructs a user membershipview specifying the controller as
         * well as the model that will have its details displayed.
         */
        public function __construct($controllerId, $moduleId, $model, $modelId)
        {
            assert('$controllerId != null');
            assert('$moduleId != null');
            assert('$model instanceof GroupUserMembershipForm');
            assert('$modelId != null');
            $this->controllerId   = $controllerId;
            $this->moduleId       = $moduleId;
            $this->model          = $model;
            $this->modelClassName = get_class($model);
            $this->modelId        = $modelId;
        }

        /**
         * Render a form layout.
         * Calls appropriate widget for rendering 2 multi-select lists
         * to display both the non-members and members of a group.
         * @param $form
         * @return A string containing the element's content.
          */
        protected function renderFormLayout($form = null)
        {
            assert('$form != null');
            $cClipWidget = new CClipWidget();
            $cClipWidget->beginClip("SortableCompareLists");
            $cClipWidget->widget('ext.zurmoinc.framework.widgets.SortableCompareLists', array(
                'model'                  => $this->model,
                'form'                   => $form,
                'leftSideAttributeName'  => 'userNonMembershipData',
                'leftSideDisplayLabel'   => Yii::t('Default', 'Non Members'),
                'rightSideAttributeName' => 'userMembershipData',
                'rightSideDisplayLabel'  => Yii::t('Default', 'Members'),
            ));
            $cClipWidget->endClip();
            $cellsContent  = $cClipWidget->getController()->clips['SortableCompareLists'];
            $content       = '<table>';
            $content      .= '<tbody>';
            $content      .= '<tr>';
            $content      .= $cellsContent;
            $content      .= '</tr>';
            $content      .= '</tbody>';
            $content      .= '</table>';
            return $content;
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
    }
?>
