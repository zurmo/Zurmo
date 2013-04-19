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

    class InstallUpgradeController extends ZurmoModuleController
    {
        public function filters()
        {
            $filters = array(
                'upgradeAccessControl',
                'maintananceModeAccessControl'
            );
            return array_merge($filters, parent::filters());
        }

        /**
         * Allow access to all upgrade actions only to Super Administrators.
         * @param CFilterChain $filterChain
         */
        public function filterUpgradeAccessControl($filterChain)
        {
            $group = Group::getByName(Group::SUPER_ADMINISTRATORS_GROUP_NAME);
            if (!$group->users->contains(Yii::app()->user->userModel))
            {
                $messageView = new AccessFailureView();
                $view        = new AccessFailurePageView($messageView);
                echo $view->render();
                Yii::app()->end(0, false);
            }
            $filterChain->run();
        }

         /**
         * Allow access to all upgrade actions only to Super Administrators.
         * @param CFilterChain $filterChain
         */
        public function filterMaintananceModeAccessControl($filterChain)
        {
            if (!Yii::app()->isApplicationInMaintenanceMode())
            {
                $message = Zurmo::t('InstallModule', 'Please set $maintenanceMode = true in perInstance.php config file.');
                $messageView = new AccessFailureView($message);
                $view        = new AccessFailurePageView($messageView);
                echo $view->render();
                Yii::app()->end(0, false);
            }
            $filterChain->run();
        }

        public function actionIndex()
        {
            Yii::app()->gameHelper->muteScoringModelsOnSave();
            $nextView = new UpgradeStartCompleteView($this->getId(), $this->getModule()->getId());
            $view = new InstallPageView($nextView);
            echo $view->render();
            Yii::app()->gameHelper->unmuteScoringModelsOnSave();
        }

        /**
         * Upgrade step one include:
         *
         */
        public function actionStepOne()
        {
            set_time_limit(3600);
            Yii::app()->gameHelper->muteScoringModelsOnSave();
            $nextView = new UpgradeStepOneCompleteView($this->getId(), $this->getModule()->getId());
            $view = new InstallPageView($nextView);
            echo $view->render();

            $template = ZurmoHtml::script("$('#logging-table').prepend('{message}<br/>');");
            $messageStreamer = new MessageStreamer($template);
            $messageStreamer->setExtraRenderBytes(4096);
            $messageStreamer->add(Zurmo::t('InstallModule', 'Starting upgrade process.'));
            UpgradeUtil::runPart1($messageStreamer);
            ForgetAllCacheUtil::forgetAllCaches();
            echo ZurmoHtml::script('$("#progress-table").hide(); $("#upgrade-step-two").show();');
            Yii::app()->gameHelper->unmuteScoringModelsOnSave();
        }

        /**
         * Upgrade step two:
         */
        public function actionStepTwo()
        {
            // Upgrade process can take much time, because upgrade schema script.
            // Set timeout for upgrade to 12 hours.
            set_time_limit(12 * 60 * 60);
            Yii::app()->gameHelper->muteScoringModelsOnSave();
            $nextView = new UpgradeStepTwoCompleteView($this->getId(), $this->getModule()->getId());
            $view = new InstallPageView($nextView);
            echo $view->render();

            $template = ZurmoHtml::script("$('#logging-table').prepend('{message}<br/>');");
            $messageStreamer = new MessageStreamer($template);
            $messageStreamer->setExtraRenderBytes(4096);
            $messageStreamer->add(Zurmo::t('InstallModule', 'Starting upgrade process.'));

            UpgradeUtil::runPart2($messageStreamer);
            ForgetAllCacheUtil::forgetAllCaches();
            echo ZurmoHtml::script('$("#progress-table").hide(); $("#upgrade-step-two").show();');
            Yii::app()->gameHelper->unmuteScoringModelsOnSave();
        }
    }
?>