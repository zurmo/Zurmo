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
     * Controller Class for managing languages.
     *
     */
    class JobsManagerDefaultController extends ZurmoModuleController
    {
        public function filters()
        {
            return array(
                array(
                    ZurmoBaseController::RIGHTS_FILTER_PATH,
                    'moduleClassName' => 'JobsManagerModule',
                    'rightName' => JobsManagerModule::RIGHT_ACCESS_JOBSMANAGER,
               ),
            );
        }

        public function actionIndex()
        {
            $this->actionList();
        }

        public function actionList()
        {
            $this->processListAction();
        }

        protected function processListAction($messageBoxContent = null)
        {
            $view = new JobsManagerTitleBarAndListView(
                            $this->getId(),
                            $this->getModule()->getId(),
                            JobsToJobsCollectionViewUtil::getMonitorJobData(),
                            JobsToJobsCollectionViewUtil::getNonMonitorJobsData(),
                            $messageBoxContent);
            $view = new JobsManagerPageView($this, $view);
            echo $view->render();
        }

        public function actionResetJob($type)
        {
            assert('is_string($type) && $type != ""');
            $jobClassName = $type . 'Job';
            try
            {
                $jobInProcess      = JobInProcess::getByType($type);
                $jobInProcess->delete();
                $messageBoxContent = HtmlNotifyUtil::renderHighlightBoxByMessage(
                                     Yii::t('Default', 'The job {jobName} has been reset.',
                                         array('{jobName}' => $jobClassName::getDisplayName())));
                $this->processListAction($messageBoxContent);
            }
            catch (NotFoundException $e)
            {
                $messageBoxContent = HtmlNotifyUtil::renderHighlightBoxByMessage(
                                 Yii::t('Default', 'The job {jobName} was not found to be stuck and therefore was not reset.',
                                         array('{jobName}' => $jobClassName::getDisplayName())));
                $this->processListAction($messageBoxContent);
            }
        }

        public function actionJobLogsModalList($type)
        {
            assert('is_string($type) && $type != ""');
            $jobClassName = $type . 'Job';
            $searchAttributeData = array();
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName'        => 'type',
                    'operatorType'         => 'equals',
                    'value'                => $type,
                ),
            );
            $searchAttributeData['structure'] = '1';
            $pageSize     = Yii::app()->pagination->resolveActiveForCurrentUserByType('subListPageSize');
            $dataProvider = new RedBeanModelDataProvider( 'JobLog', 'startDateTime', true,
                                                                $searchAttributeData, array(
                                                                    'pagination' => array(
                                                                        'pageSize' => $pageSize,
                                                                    )
                                                                ));
            Yii::app()->getClientScript()->setToAjaxMode();
            $jobLogsListView = new JobLogsModalListView(
                                    $this->getId(),
                                    $this->getModule()->getId(),
                                    'JobLog',
                                    $dataProvider,
                                    'modal');
            $view = new ModalView($this,
                            $jobLogsListView,
                            'modalContainer',
                            Yii::t('Default', 'Job Log for {jobDisplayName}',
                                   array('{jobDisplayName}' => $jobClassName::getDisplayName())));
            echo $view->render();
        }

        public function actionJobLogDetails($id)
        {
            $jobLog = JobLog::getById(intval($id));
            $view = new JobsManagerPageView($this,
                $this->makeTitleBarAndDetailsView($jobLog));
            echo $view->render();
        }
    }
?>