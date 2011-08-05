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
     * Component to run a sequential process.  After a step is run, information regarding the next step is populated
     * including any parameters and messages. Sequential processes can also include sub processes.  An example would be
     * if a specific step has a loop over models via a data provider.
     */
    abstract class SequentialProcess extends CComponent
    {
        /**
         * Method to get the message that is utilized for all steps in a sequence.
         * @return string message content.
         */
        abstract public function getAllStepsMessage();

        /**
         * @return array of steps in the sequence
         */
        abstract protected function steps();

        /**
         * Array indexed by step.  The value represents the step specific message.
         * @return array of step messages.
         */
        abstract protected function stepMessages();

        /**
         * If all the steps are completed, this is set to true.
         */
        protected $complete = false;

        /**
         * Next step in the sequence.
         * @var string
         */
        protected $nextStep;

        /**
         * Parameters for the next step in the sequence.
         * @var array
         */
        protected $nextParams;

        /**
         * Message for the next step in the sequence.
         * @var string
         */
        protected $nextMessage;

        /**
         * Value 0 to 100 of how far along in the process the sequence is.
         * @var integer
         */
        protected $completionPercentage;

        /**
         * Utilized to provide additional clarity in how far in a process a sequence is. Used by a step that has
         * sub steps to process.
         * @var integer
         */
        protected $subSequenceCompletionPercentage = 0;

        public function __construct()
        {
            assert('count($this->steps()) > 0');
            assert('count($this->stepMessages()) > 0');
            assert('count($this->stepMessages()) == count($this->steps())');
        }

        /**
         * Given a step, run it and setup for the next step in the process.
         * @param string or null $step
         * @param array or null $params
         */
        public function run($step, $params)
        {
            assert('$step == null || is_string($step)');
            assert('$params == null || is_array($params)');
            if($step == null)
            {
                $steps          = $this->steps();
                $this->nextStep = array_shift($steps);
                $this->setNextMessageByStep($this->nextStep);
                return;
            }
            $this->nextParams = $this->{$step}($params);
            $this->resolveNextInformationByCurrentStep($step);
        }

        protected function resolveNextInformationByCurrentStep($step)
        {
            assert('is_string($step)');
            $steps        = $this->steps();
            $currentKey   = array_search($step, $steps);
            $nextKey      = $currentKey + 1;
            if(!key_exists($nextKey, $steps))
            {
                $this->complete = true;
            }
            else
            {
                $extraPercentage = ($this->subSequenceCompletionPercentage / 100) * (100 / count($steps));
                $this->completionPercentage = ((($currentKey + 1) / count($steps)) * 100) + $extraPercentage;
            }
        }

        protected function setNextMessageByStep($step)
        {
            assert('is_string($step)');
            $stepMessages      = $this->stepMessages();
            $this->nextMessage = $stepMessages[$step];
        }

        public function isComplete()
        {
            return $this->complete;
        }

        public function getNextStep()
        {
            return $this->nextStep;
        }

        public function getNextParams()
        {
            return $this->nextParams;
        }

        public function getNextMessage()
        {
            return $this->nextMessage;
        }

        public function getCompletionPercentage()
        {
            return $this->completionPercentage;
        }

        public function getViewClassNameByStep($step)
        {
            return 'SequentialProcessView';
        }
    }
?>