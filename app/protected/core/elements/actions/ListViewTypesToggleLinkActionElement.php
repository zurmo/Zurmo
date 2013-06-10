<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
     *
     * Zurmo is free software; you can redistribute it and/or modify it under
     * the terms of the GNU Affero General Public License version 3 as published by the
     * Free Software Foundation with the addition of the following permission added
     * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
     * IN WHICH THE COPYRIGHT IS OWNED BY ZURMO, ZURMO DISCLAIMS THE WARRANTY
     * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
     *
     * Zurmo is distributed in the hope that it will be useful, but WITHOUT
     * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
     * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
     * details.
     *
     * You should have received a copy of the GNU Affero General Public License along with
     * this program; if not, see http://www.gnu.org/licenses or write to the Free
     * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
     * 02110-1301 USA.
     *
     * You can contact Zurmo, Inc. with a mailing address at 27 North Wacker Drive
     * Suite 370 Chicago, IL 60606. or at email address contact@zurmo.com.
     *
     * The interactive user interfaces in original and modified versions
     * of this program must display Appropriate Legal Notices, as required under
     * Section 5 of the GNU Affero General Public License version 3.
     *
     * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
     * these Appropriate Legal Notices must retain the display of the Zurmo
     * logo and Zurmo copyright notice. If the display of the logo is not reasonably
     * feasible for technical reasons, the Appropriate Legal Notices must display the words
     * "Copyright Zurmo Inc. 2013. All rights reserved".
     ********************************************************************************/

    /**
     * Used to render multiple listview options such as grid, kanban or cards.
     */
    class ListViewTypesToggleLinkActionElement extends LinkActionElement
    {
        const TYPE_KANBAN_BOARD = 'KanbanBoard';

        const TYPE_GRID         = 'Grid';

        /**
         * @return null
         */
        public function getActionType()
        {
            return null;
        }

        /**
         * @return string
         */
        public function render()
        {
            $kanbanBoardUrl = Yii::app()->createUrl($this->moduleId . '/' . $this->controllerId . '/list/',
                              array('kanbanBoard' => true));
            $listUrl        = Yii::app()->createUrl($this->moduleId . '/' . $this->controllerId . '/list/',
                              array('kanbanBoard' => false));
            $content        = null;
            $content       .= ZurmoHtml::link('<span>Kanban</span>', $kanbanBoardUrl,
                                              array('class' => $this->resolveKanbanBoardClass(),
                                                    'title' => Zurmo::t('Core', 'View as Kanban Board')));
            $content       .= ZurmoHtml::link('<span>Grid</span>', $listUrl,
                                              array('class' => $this->resolveGridClass(),
                                                    'title' => Zurmo::t('Core', 'View as Grid')));
            return $content;
        }

        /**
         * Selecting a different type of list view is not supported right now in mobile.
         * @return array|void
         * @throws NotSupportedException
         */
        public function renderMenuItem()
        {
            throw new NotSupportedException();
        }

        /**
         * @return string
         */
        protected function resolveKanbanBoardClass()
        {
            $kanbanBoardClass = 'icon-kanban-board-view-type';
            if ($this->getActive() == static::TYPE_KANBAN_BOARD)
            {
                $kanbanBoardClass .= ' active';
            }
            return $kanbanBoardClass;
        }

        /**
         * @return string
         */
        protected function resolveGridClass()
        {
            $gridClass = 'icon-grid-view-type';
            if ($this->getActive() == static::TYPE_GRID)
            {
                $gridClass .= ' active';
            }
            return $gridClass;
        }

        /**
         * @return string
         */
        protected function getDefaultLabel()
        {
            return Zurmo::t('ReportsModule', 'Toggle Results');
        }

        /**
         * @return null
         */
        protected function getDefaultRoute()
        {
            return null;
        }

        protected function getActive()
        {
            if (!isset($this->params['active']))
            {
                return static::TYPE_GRID;
            }
            return $this->params['active'];
        }
    }
?>