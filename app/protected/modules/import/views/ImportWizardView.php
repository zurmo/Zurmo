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
     * Base View for import wizard user interfaces.  Supports previous and next links in the bottom of each view.
     */
    abstract class ImportWizardView extends EditView
    {
        public function __construct($controllerId, $moduleId, ImportWizardForm $model)
        {
            assert('is_string($controllerId)');
            assert('is_string($moduleId)');
            $this->controllerId = $controllerId;
            $this->moduleId     = $moduleId;
            $this->model        = $model;
        }

        /**
         * Given a form, render the content for the action links at the bottom of the view and return the content as
         * a string.
         * @param object $form
         */
        protected function renderActionLinksContent($form)
        {
            $previousPageLinkContent = $this->renderPreviousPageLinkContent($form);
            $nextPageLinkContent     = $this->renderNextPageLinkContent($form);
            $content  = '<div class="view-toolbar">'; //change to different class?
            if($previousPageLinkContent)
            {
                $content .= '<div id="previous-page-link" style="float:left;">' . $previousPageLinkContent . '</div>';
            }
            if($nextPageLinkContent)
            {
                $content .= '<div id="next-page-link" style="float:right;">' . $nextPageLinkContent . '</div>';
            }
            $content .= '</div>';
            return $content;
        }

        /**
         * Override if the view should show a previous link.
         * @param object $form
         */
        protected function renderPreviousPageLinkContent($form)
        {
            return null;
        }

        /**
         * Override if the view should show a next link.
         * @param object $form
         */
        protected function renderNextPageLinkContent($form)
        {
            return null;
        }

        /**
         * There are no special requirements for this view's metadata.
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