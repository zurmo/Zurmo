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
     * Helper class for working with Workflow objects and processing the actions that are triggered on a model
     */
    class WorkflowActionsUtil
    {
        /**
         * Process any workflow actions that are updates to the passed in model.
         * @param Workflow $workflow
         * @param RedBeanModel $model
         * @param User $triggeredByUser
         */
        public static function processBeforeSave(Workflow $workflow,
                                                 RedBeanModel $model,
                                                 User $triggeredByUser)
        {
            foreach ($workflow->getActions() as $action)
            {
                try
                {
                    $helper = new WorkflowActionProcessingHelper($action, $model, $triggeredByUser);
                    $helper->processUpdateSelectAction();
                }
                catch (Exception $e)
                {
                    WorkflowUtil::handleProcessingException($e,
                        'application.modules.workflows.utils.WorkflowActionsUtil.processBeforeSave');
                }
            }
        }

        /**
         * Process any workflow actions that are updating related models, or creating new models.
         * @param Workflow $workflow
         * @param RedBeanModel $model
         * @param User $triggeredByUser
         * @throws NotSupportedException
         */
        public static function processAfterSave(Workflow $workflow,
                                                RedBeanModel $model,
                                                User $triggeredByUser)
        {
            foreach ($workflow->getActions() as $action)
            {
                try
                {
                    $helper = new WorkflowActionProcessingHelper($action, $model, $triggeredByUser);
                    $helper->processNonUpdateSelfAction();
                }
                catch (Exception $e)
                {
                    WorkflowUtil::handleProcessingException($e,
                        'application.modules.workflows.utils.WorkflowActionsUtil.processAfterSave');
                }
            }
        }

        /**
         * Process any workflow actions that are updates to the passed in model during the processing of any
         * ByTimeWorkflowQueue models. @see ByTimeWorkflowInQueueJob.
         * @param Workflow $workflow
         * @param RedBeanModel $model
         * @param User $triggeredByUser
         * @throws FailedToSaveModelException
         */
        public static function processOnByTimeWorkflowInQueueJob(  Workflow $workflow,
                                                                 RedBeanModel $model,
                                                                 User $triggeredByUser)
        {
            foreach ($workflow->getActions() as $action)
            {
                try
                {
                    $helper = new WorkflowActionProcessingHelper($action, $model, $triggeredByUser, false);
                    $helper->processUpdateSelectAction();
                    $helper->processNonUpdateSelfAction();
                }
                catch (Exception $e)
                {
                    WorkflowUtil::handleProcessingException($e,
                        'application.modules.workflows.utils.WorkflowActionsUtil.processOnByTimeWorkflowInQueueJob');
                }
            }
        }

        public static function getWorkflowsMissingRequiredActionAttributes()
        {
            $savedWorkflows = SavedWorkflow::getAll();
            $workflows      = array();
            foreach ($savedWorkflows as $savedWorkflow)
            {
                $workflow        = SavedWorkflowToWorkflowAdapter::makeWorkflowBySavedWorkflow($savedWorkflow);
                $missingRequired = false;
                foreach ($workflow->getActions() as $action)
                {
                    if (!$action->isTypeAnUpdateVariant() && $action->isMissingRequiredActionAttributes())
                    {
                        $missingRequired = true;
                        break;
                    }
                }
                if ($missingRequired)
                {
                    $workflows[] = $workflow;
                }
            }
            return $workflows;
        }
    }
?>