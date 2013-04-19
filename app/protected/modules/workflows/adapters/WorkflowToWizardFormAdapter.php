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
     * Helper class for adapting a Workflow to a WorkflowWizardForm
     */
    class WorkflowToWizardFormAdapter
    {
        /**
         * @var Workflow
         */
        protected $workflow;

        /**
         * @param $type
         * @return string
         * @throws NotSupportedException if the type is invalid or null
         */
        public static function getFormClassNameByType($type)
        {
            assert('is_string($type)');
            if ($type == Workflow::TYPE_ON_SAVE)
            {
                return 'OnSaveWorkflowWizardForm';
            }
            elseif ($type == Workflow::TYPE_BY_TIME)
            {
                return 'ByTimeWorkflowWizardForm';
            }
            else
            {
                throw new NotSupportedException();
            }
        }

        /**
         * @param Workflow $workflow
         */
        public function __construct(Workflow $workflow)
        {
            $this->workflow = $workflow;
        }

        /**
         * @return MatrixWorkflowWizardForm|RowsAndColumnsWorkflowWizardForm|SummationWorkflowWizardForm
         * @throws NotSupportedException if the workflow type is invalid or null
         */
        public function makeFormByType()
        {
            if ($this->workflow->getType() == Workflow::TYPE_ON_SAVE)
            {
                return $this->makeOnSaveWizardForm();
            }
            elseif ($this->workflow->getType() == Workflow::TYPE_BY_TIME)
            {
                return $this->makeByTimeWizardForm();
            }
            else
            {
                throw new NotSupportedException();
            }
        }

        /**
         * @return OnSaveWorkflowWizardForm
         */
        public function makeOnSaveWizardForm()
        {
            $formModel       = new OnSaveWorkflowWizardForm();
            $this->setCommonAttributes($formModel);
            return $formModel;
        }

        /**
         * @return ByTimeWorkflowWizardForm
         */
        public function makeByTimeWizardForm()
        {
            $formModel             = new ByTimeWorkflowWizardForm();
            $this->setCommonAttributes($formModel);
            return $formModel;
        }

        /**
         * @param WorkflowWizardForm $formModel
         */
        protected function setCommonAttributes(WorkflowWizardForm $formModel)
        {
            $formModel->id                   = $this->workflow->getId();
            $formModel->isActive             = $this->workflow->getIsActive();
            $formModel->description          = $this->workflow->getDescription();
            $formModel->moduleClassName      = $this->workflow->getModuleClassName();
            $formModel->name                 = $this->workflow->getName();
            $formModel->triggerOn            = $this->workflow->getTriggerOn();
            $formModel->type                 = $this->workflow->getType();
            $formModel->triggersStructure    = $this->workflow->getTriggersStructure();
            $formModel->timeTriggerAttribute = $this->workflow->getTimeTriggerAttribute();
            if ($this->workflow->isNew())
            {
                $formModel->setIsNew();
            }
            $formModel->timeTrigger       = $this->workflow->getTimeTrigger();
            $formModel->triggers          = $this->workflow->getTriggers();
            $formModel->actions           = $this->workflow->getActions();
            $formModel->emailMessages     = $this->workflow->getEmailMessages();
        }
    }
?>