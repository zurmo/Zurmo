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
     * Test controller for use either running tests or demonstrating generic functionality throug the user interface.
     * Only accessible if the debug setting is true.
     */
    class ZurmoTestController extends Controller
    {
        public function filters()
        {
            if (!YII_DEBUG)
            {
                echo Zurmo::t('ZurmoModule', 'This action is only available in debug mode.');
                Yii::app()->end(0, false);
            }
        }

        /**
         * Example of a sequential process.
         * @see TestCompleteSequentialProcessView
         * @see TestSequentialProcess
         * @param string $step
         */
        function actionSequentialProcess($step)
        {
            if (isset($_GET['nextParams']))
            {
                $nextParams = $_GET['nextParams'];
            }
            else
            {
                $nextParams = null;
            }
            Yii::import('application.core.tests.unit.models.*');
            Yii::import('application.core.tests.unit.components.*');
            Yii::import('application.core.tests.unit.views.*');
            assert('$step == null || is_string($step)');
            assert('$nextParams == null || is_array($nextParams)');

            //////Do setup logic here if needed
            $a = new A();
            $b = new B();
            $sequentialProcess = new TestSequentialProcess($a, $b);
            $sequentialProcess->run($step, $nextParams);
            $nextStep          = $sequentialProcess->getNextStep();
            $route             = $this->getModule()->getId() . '/' . $this->getId() . '/sequentialProcess';
            if ($sequentialProcess->isComplete())
            {
                //////Do completion logic here if needed
                $sequenceView = new TestCompleteSequentialProcessView($a, $b);
            }
            else
            {
                $sequenceView = SequentialProcessViewFactory::makeBySequentialProcess($sequentialProcess, $route);
            }
            if ($step == null)
            {
                $gridView     = new GridView(2, 1);
                $titleBarView = new TitleBarView ('Zurmo', 'Test Sequential Process');
                $wrapperView  = new SequentialProcessContainerView($sequenceView, $sequentialProcess->getAllStepsMessage());
                $gridView->setView($titleBarView, 0, 0);
                $gridView->setView($wrapperView, 1, 0);
                $view         = new ZurmoConfigurationPageView(ZurmoDefaultViewUtil::
                                         makeStandardViewForCurrentUser($this, $gridView));
            }
            else
            {
                $view        = new AjaxPageView($sequenceView);
            }
            echo $view->render();
        }
    }
?>