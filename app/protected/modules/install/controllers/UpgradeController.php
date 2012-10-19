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
                $message = Yii::t('Default', 'Please set $maintenanceMode = true in perInstance.php config file.');
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
            $messageStreamer->add(Yii::t('Default', 'Starting upgrade process.'));
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
            $messageStreamer->add(Yii::t('Default', 'Starting upgrade process.'));

            UpgradeUtil::runPart2($messageStreamer);
            ForgetAllCacheUtil::forgetAllCaches();
            echo ZurmoHtml::script('$("#progress-table").hide(); $("#upgrade-step-two").show();');
            Yii::app()->gameHelper->unmuteScoringModelsOnSave();
        }
    }
?>