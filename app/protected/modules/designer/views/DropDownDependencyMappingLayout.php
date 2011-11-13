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

    class DropDownDependencyMappingLayout
    {
        protected $dependencyCollection;

        protected $form;

        protected $controllerId;

        protected $moduleId;

        protected $ajaxActionId;

        public function __construct($dependencyCollection,
                                    ZurmoActiveForm $form,
                                    $controllerId,
                                    $moduleId,
                                    $ajaxActionId)
        {
            assert('is_array($dependencyCollection) && count($dependencyCollection) >= 2');
            assert('is_string($controllerId)');
            assert('is_string($moduleId)');
            assert('is_string($ajaxActionId)');
            $this->dependencyCollection = $dependencyCollection;
            $this->form                 = $form;
            $this->controllerId         = $controllerId;
            $this->moduleId             = $moduleId;
            $this->ajaxActionId         = $ajaxActionId;
        }
        public function render()
        {
            return $this->renderContainerContent();
        }

        protected function renderContainerContent()
        {
            $content  = '<table>';
            $content .= '<tr>';
            $content .= $this->renderCollectionContent();
            $content .= '</tr>';
            $content .= '</table>';
            return $content;
        }

        protected function renderCollectionContent()
        {
            $content = '<td>';
            foreach($this->dependencyCollection as $dropDownDependencyCustomFieldMapping)
            {
                assert('$dropDownDependencyCustomFieldMapping instanceof DropDownDependencyCustomFieldMapping');
                $content .= $this->renderDependencyContent($dropDownDependencyCustomFieldMapping);
            }
            $content .= '</td>';
            return $content;
        }

        protected function renderDependencyContent(DropDownDependencyCustomFieldMapping $mapping)
        {
            $content  = '<table>';
            $content .= '<tr>';
            $content .= '<td>';
            $content .= 'desc of column';
            $content .= '</td>';
            $content .= '</tr>';
            $content .= '<tr>';
            $content .= '<td>';
            $content .= 'main select';
            $content .= '</td>';
            $content .= '</tr>';
            $content .= '<tr>';
            $content .= '<td>';
            $content .= 'mapping area';
            $content .= '</td>';
            $content .= '</tr>';
            $content .= '</table>';
            return $content;
        }
    }
?>