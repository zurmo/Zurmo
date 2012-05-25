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
     * View that renders a list of roles in the form of a
     * tree or noded list view.
     */
    class RolesTreeListView extends SecurityTreeListView
    {
        protected function renderContent()
        {
            $content  = $this->renderViewToolBar(false); //why do we need it if its empty?
            $content .= '<div>';
            $content .= '<h1>' . Yii::t('Default', 'Roles') . '</h1>';
            $content .= $this->renderTreeMenu('role', 'roles', Yii::t('Default', 'Role'));
            $content .= '</div>';
            return $content;
        }

        protected function renderTreeListView($data)
        {
            assert('is_array($data)');
            $content  = '<table class="configuration-list">';
            $content .= '<colgroup>';
            $content .= '<col style="width:50%" />';
            $content .= '</colgroup>';
            $content .= '<colgroup>';
            $content .= '<col style="width:25%" />';
            $content .= '</colgroup>';
            $content .= '<col style="width:25%" />';
            $content .= '</colgroup>';
            $content .= '<tbody>';
            $content .= '<tr><th>' . Yii::t('Default', 'Name') . '</th><th>' . Yii::t('Default', 'Users') . '</th><th></th></tr>';
            static::renderTreeListViewNode($content, $data, 0);
            $content .= '</tbody>';
            $content .= '</table>';
            return $content;
        }
    }
?>
