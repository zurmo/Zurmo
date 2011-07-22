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
     * The Zurmo base search view for a module's search view.  Includes extra pieces like filtered lists.
     */
    abstract class ZurmoSearchView extends SearchView
    {
        protected $showFilteredListLink;

        /**
         * Constructs a detail view specifying the controller as
         * well as the model that will have its details displayed.
         */
        public function __construct($model,
            $listModelClassName,
            $gridIdSuffix = null,
            $showFilteredListLink = false,
            $hideAllSearchPanelsToStart = false
            )
        {
            assert('is_bool($showFilteredListLink)');
            $this->showFilteredListLink = false; // Turn back once filteredLists is completed.  $showFilteredListLink;
            parent::__construct($model, $listModelClassName, $gridIdSuffix = null, $hideAllSearchPanelsToStart = false);
        }

        protected function renderFormBottomPanelExtraLinks()
        {
            $content = null;
            if ($this->showFilteredListLink)
            {
                $filteredListLink = CHtml::link(Yii::t('Default', 'Filtered Lists'), '#', array('class' => 'filtered-list-link'));
                $content = '&#160;|&#160;' . $filteredListLink;
            }
            return $content;
        }

        protected function getExtraQueryPartForSearchFormScriptSubmitFunction()
        {
            return '&filteredListId=';
        }

        protected function getExtraRenderFormBottomPanelScriptPart()
        {
            return "$('.filtered-list-link').click( function()
                    {
                        $('.search-view-0').hide();
                        $('.search-view-1').hide();
                        $('.search-view-bottom-panel').hide();
                        $('.filtered-list-panel').show();
                        return false;
                    }
                );";
        }
    }
?>
