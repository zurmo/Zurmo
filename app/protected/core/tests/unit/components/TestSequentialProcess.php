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
     * A test sequential process.  This process has 4 steps with stepB having 5 sub steps.
     */
    class TestSequentialProcess extends SequentialProcess
    {
        public function getAllStepsMessage()
        {
            return 'Running a sequential process...';
        }

        protected function steps()
        {
            return array('stepA', 'stepB', 'stepC', 'stepD');
        }

        protected function stepMessages()
        {
            return array('stepA' => 'Step A Message',
                         'stepB' => 'Step B Message',
                         'stepC' => 'Step C Message',
                         'stepD' => 'Step D Message');
        }

        protected function stepA($params)
        {
            //Perform step A.
            $this->nextStep   = 'stepB';
            $this->setNextMessageByStep($this->nextStep);
            return array('subStep' => 1);
        }

        protected function stepB($params)
        {
            assert('isset($params["subStep"])');
            //Perform step B. Step B has a sub-sequence of steps 1 - 5
            //Example setting the pager page
            //$dataProvider->getPagination()->setPageSize($params['page']);
            $this->subSequenceCompletionPercentage = ($params['subStep'] / 10) * 100;
            if ($params['subStep'] == 10)
            {
                $this->nextStep = 'stepC';
                $this->setNextMessageByStep($this->nextStep);
                return null;
            }
            else
            {
                $params['subStep'] = $params['subStep'] + 1;
                $this->nextStep = 'stepB';
                $this->setNextMessageByStep($this->nextStep);
                $this->nextMessage .= ' - Sub Step ' . $params['subStep'] . ' of 10';
                return $params;
            }
        }

        protected function stepC($params)
        {
            //Perform step C.
            $this->nextStep = 'stepD';
            $this->setNextMessageByStep($this->nextStep);
            return null;
        }

        protected function stepD($params)
        {
            //Perform step D.
            $this->nextStep    = null;
            $this->nextMessage = null;
            $this->complete    = true;
            return null;
        }
    }
?>