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
     * Renders an action bar specifically for the search and listview.
     */
    class ActionBarForSearchAndListView extends ConfigurableMetadataView
    {
        protected $controllerId;

        protected $moduleId;

        /**
         * Typically the model used for the list view.
         * @var Object
         */
        protected $model;

        /**
         * The unique id of the list view grid.
         * @var string
         */
        protected $listViewGridId;

        /**
         * The variable name used for the pagination of the list view.
         * @var string
         */
        protected $pageVarName;

        /**
         * True false whether the list view rows are selectable and will display a checkbox next to each row.
         * @var boolean
         */
        protected $listViewRowsAreSelectable;

        public function __construct($controllerId, $moduleId, RedBeanModel $model, $listViewGridId, $pageVarName, $listViewRowsAreSelectable)
        {
            assert('is_string($controllerId)');
            assert('is_string($moduleId)');
            assert('is_string($listViewGridId)');
            assert('is_string($pageVarName)');
            assert('is_bool($listViewRowsAreSelectable)');
            $this->controllerId              = $controllerId;
            $this->moduleId                  = $moduleId;
            $this->model                     = $model;
            $this->listViewGridId            = $listViewGridId;
            $this->pageVarName               = $pageVarName;
            $this->listViewRowsAreSelectable = $listViewRowsAreSelectable;
        }

        protected function renderContent()
        {
            $content  = '<div class="view-toolbar-container clearfix"><div class="view-toolbar">';
            $content .= $this->renderActionElementBar(false);
            $content .= '</div></div>';
            return $content;
        }

        public function isUniqueToAPage()
        {
            return true;
        }

        public static function getDefaultMetadata()
        {
            $metadata = array(
                'global' => array(
                    'toolbar' => array(
                        'elements' => array(
                            array('type'  => 'CreateLink',
                            	'htmlOptions' => array('class' => 'icon-create'),
							),
                            array('type'  => 'MassEditLink',
                                  'htmlOptions' => array('class' => 'icon-edit'),
                                  'listViewGridId' => 'eval:$this->listViewGridId',
                                  'pageVarName' => 'eval:$this->pageVarName'),
                        ),
                    ),
                ),
            );
            return $metadata;
        }

        /**
         * Override to check for for MassEdit link. This link should only be rendered if there are selectable rows.
         * @return boolean
         */
        protected function shouldRenderToolBarElement($element, $elementInformation)
        {
            assert('$element instanceof ActionElement');
            assert('is_array($elementInformation)');
            if (!parent::shouldRenderToolBarElement($element, $elementInformation))
            {
                return false;
            }
            if($elementInformation['type'] == 'MassEditLink' && !$this->listViewRowsAreSelectable)
            {
                return false;
            }
            return true;
        }
    }
?>