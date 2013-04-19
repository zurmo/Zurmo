<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
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
     * You can contact Zurmo, Inc. with a mailing address at 27 North Wacker Drive
     * Suite 370 Chicago, IL 60606. or at email address contact@zurmo.com.
     *
     * The interactive user interfaces in original and modified versions
     * of this program must display Appropriate Legal Notices, as required under
     * Section 5 of the GNU General Public License version 3.
     *
     * In accordance with Section 7(b) of the GNU General Public License version 3,
     * these Appropriate Legal Notices must retain the display of the Zurmo
     * logo and Zurmo copyright notice. If the display of the logo is not reasonably
     * feasible for technical reasons, the Appropriate Legal Notices must display the words
     * "Copyright Zurmo Inc. 2013. All rights reserved".
     ********************************************************************************/

    /**
     * View for displaying a list of workflows for a given module to be re-ordered
     */
    class WorkflowManageOrderListView extends MetadataView
    {
        /**
         * @var array SavedWorkflow models
         */
        protected $savedWorkflows;

        /**
         * @param array $savedWorkflows
         */
        public function __construct(Array $savedWorkflows)
        {
            $this->savedWorkflows = $savedWorkflows;
        }

        /**
         * @return string
         */
        protected function renderContent()
        {
            $items = $this->resolveItemsAndContent();
            return $this->getSortableListContent($items);
        }

        /**
         * @return array
         */
        protected function resolveItemsAndContent()
        {
            $items = array();
            $inputName = Element::resolveInputNamePrefixIntoString(array('SavedWorkflow', 'savedWorkflowIds'));
            foreach ($this->savedWorkflows as $workflow)
            {
                $content   = '<div class="dynamic-row">';
                $content  .= ZurmoHtml::hiddenField($inputName . '[]', $workflow->id);
                $content  .= strval($workflow);
                $content  .= '</div>';
                $items[] = array('content' => $content);
            }
            return $items;
        }

        /**
         * @param array $items
         * @return string
         */
        protected function getSortableListContent(Array $items)
        {
            //unless we refactor getTreeType to getComponentType... but that requires a bigger refactor
            $cClipWidget = new CClipWidget();
            $cClipWidget->beginClip('WorkflowOrderSortable');
            $cClipWidget->widget('application.core.widgets.JuiSortable', array(
                'items' => $items,
                'itemTemplate' => '<li>content</li>',
                'htmlOptions' =>
                array(
                    'id'    => 'workflowRowsUl',
                    'class' => 'sortable',
                ),
                'options' => array(
                    'cancel' => 'li.expanded-row',
                    'placeholder' => 'ui-state-highlight',
                    'containment' => 'parent'
                ),
                'showEmptyList' => false
            ));
            $cClipWidget->endClip();
            return $cClipWidget->getController()->clips['WorkflowOrderSortable'];
        }
    }
?>