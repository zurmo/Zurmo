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

    class HomeDefaultPortletController extends ZurmoPortletController
    {
        public function actionAddList()
        {
            Yii::app()->getClientScript()->setToAjaxMode();
            $view = new ModalView($this,
                new HomeDashboardPortletSelectionView(
                    $this->getId(),
                    $this->getModule()->getId(),
                    $_GET['dashboardId'],
                    $_GET['uniqueLayoutId']
                    ));
            echo $view->render();
        }

        /**
         * Add portlet to first column, first position
         * and if there are other portlets in the first
         * column, shift their postion by 1 to accomodate
         * the new portlet
         *
         */
        public function actionAdd()
        {
            assert('!empty($_GET["uniqueLayoutId"])');
            assert('!empty($_GET["portletType"])');
            $portletCollection = Portlet::getByLayoutIdAndUserSortedByColumnIdAndPosition($_GET['uniqueLayoutId'], Yii::app()->user->userModel->id, array());
            if (!empty($portletCollection))
            {
                foreach ($portletCollection[1] as $position => $portlet)
                {
                        $portlet->position = $portlet->position + 1;
                        $portlet->save();
                }
            }
            if (!empty($_GET['dashboardId']))
            {
                $dashboardId = $_GET['dashboardId'];
            }
            else
            {
                $dashboardId = '';
            }
            Portlet::makePortletUsingViewType($_GET['portletType'], $_GET['uniqueLayoutId'], Yii::app()->user->userModel);
            $this->redirect(array('default/dashboardDetails', 'id' => $dashboardId));
        }
    }
?>