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
     * Class that defines the email messages used for a workflow
     */
    class EmailMessageForWorkflowForm extends ConfigurableMetadataModel implements RowKeyInterface
    {
        const SEND_FROM_TYPE_DEFAULT      = 'Default';

        const SEND_FROM_TYPE_CUSTOM       = 'Custom';

        /**
         * Similar to the types defined in ComponentForWorkflowForm like TYPE_EMAIL_MESSAGES.
         */
        const TYPE_EMAIL_MESSAGE_RECIPIENTS = 'EmailMessageRecipients';

        /**
         * Utilized by arrays to define the element that is for the actionAttributes
         */
        const EMAIL_MESSAGE_RECIPIENTS     = 'EmailMessageRecipients';

        /**
         * @var int
         */
        public $emailTemplateId;

        /**
         * @var int
         */
        public $sendAfterDurationSeconds;

        /**
         * @var string
         */
        public $sendFromType;

        /**
         * @var string
         */
        public $sendFromName;

        /**
         * @var string
         */
        public $sendFromAddress;

        /**
         * @var string
         */
        private $_workflowType;

        /**
         * @var array of WorkflowActionAttributeForms indexed by attributeNames
         */
        private $_emailMessageRecipients = array();

        /**
         * Posted data can be using different keys. if you add 3 then remove 2 and add a fourth, the key would be 4.
         * but if this is the only message recipient, then this will save as key 0. this helps to resolve that.
         * @var array
         */
        private $_emailMessageRecipientsRealToTemporaryKeyData = array();

        /**
         * @var string string references the modelClassName of the workflow itself
         */
        private $_modelClassName;

        /**
         * @var int
         */
        private $_rowKey;

        /**
         * @param $attributeName string
         * @return string
         */
        protected static function resolveErrorAttributePrefix($attributeName)
        {
            assert('is_int($attributeName)');
            return self::EMAIL_MESSAGE_RECIPIENTS . '_' .  $attributeName . '_';
        }

        /**
         * @return int
         */
        public function getRowKey()
        {
            return $this->_rowKey;
        }

        /**
         * @param string $modelClassName
         * @param string $workflowType
         * @param int $rowKey
         */
        public function __construct($modelClassName, $workflowType, $rowKey = 0)
        {
            assert('is_string($modelClassName)');
            assert('is_string($workflowType)');
            assert('is_int($rowKey)');
            $this->_modelClassName = $modelClassName;
            $this->_workflowType   = $workflowType;
            $this->_rowKey         = $rowKey;
        }

        /**
         * @return int
         */
        public function getEmailMessageRecipientFormsCount()
        {
            return count($this->_emailMessageRecipients);
        }

        /**
         * @return array
         */
        public function getEmailMessageRecipients()
        {
            return $this->_emailMessageRecipients;
        }

        /**
         * @return string
         */
        public function getWorkflowType()
        {
            return $this->_workflowType;
        }

        /**
         * @return array
         */
        public function rules()
        {
            return array_merge(parent::rules(), array(
                array('emailTemplateId',          'required'),
                array('sendAfterDurationSeconds', 'type', 'type' => 'integer'),
                array('sendFromType',             'type',  'type' => 'string'),
                array('sendFromType',             'validateSendFromType'),
                array('sendFromName',             'type',  'type' => 'string'),
                array('sendFromAddress',          'type',  'type' => 'string'),
            ));
        }

        /**
         * @return array
         */
        public function attributeLabels()
        {
            return array('emailTemplateId'          => Zurmo::t('WorkflowsModule', 'Template'),
                         'sendAfterDurationSeconds' => Zurmo::t('WorkflowsModule', 'Send'),
                         'sendFromType'             => Zurmo::t('WorkflowsModule', 'Send From'),
                         'sendFromName'             => Zurmo::t('WorkflowsModule', 'From Name'),
                         'sendFromAddress'          => Zurmo::t('WorkflowsModule', 'From Address'),
            );
        }

        /**
         * Process all attributes except 'emailMessageRecipients' first
         * @param $values
         * @param bool $safeOnly
         * @throws NotSupportedException if the post values data is malformed
         */
        public function setAttributes($values, $safeOnly = true)
        {
            $recipients = null;
            if (isset($values[self::EMAIL_MESSAGE_RECIPIENTS]))
            {
                $recipients = $values[self::EMAIL_MESSAGE_RECIPIENTS];
                unset($values[self::EMAIL_MESSAGE_RECIPIENTS]);
                $this->_emailMessageRecipients = array();
            }
            parent::setAttributes($values, $safeOnly);
            if ($recipients != null)
            {
                $count = 0;
                foreach ($recipients as $temporaryKey => $recipientData)
                {
                    if (!isset($recipientData['type']))
                    {
                        throw new NotSupportedException();
                    }
                    $form = WorkflowEmailMessageRecipientFormFactory::make($recipientData['type'], $this->_modelClassName,
                            $this->_workflowType);
                    $form->setAttributes($recipientData);
                    $this->_emailMessageRecipients[] = $form;
                    $this->_emailMessageRecipientsRealToTemporaryKeyData[] = $temporaryKey;
                    $count++;
                }
            }
        }

        /**
         * @return bool
         */
        public function validateSendFromType()
        {
            if ($this->sendFromType == self::SEND_FROM_TYPE_CUSTOM)
            {
                $validated = true;
                if ($this->sendFromName == null)
                {
                    $this->addError('sendFromName', Zurmo::t('WorkflowsModule', 'From Name cannot be blank.'));
                    $validated = false;
                }
                if ($this->sendFromAddress == null)
                {
                    $this->addError('sendFromAddress', Zurmo::t('WorkflowsModule', 'From Email Address cannot be blank.'));
                    $validated = false;
                }
                return $validated;
            }
            elseif ($this->sendFromType != self::SEND_FROM_TYPE_DEFAULT)
            {
                $this->addError('type', Zurmo::t('WorkflowsModule', 'Invalid Send From Type'));
            }
            return true;
        }

        /**
         * @return bool
         */
        public function beforeValidate()
        {
            if (!$this->validateRecipients())
            {
                return false;
            }
            return parent::beforeValidate();
        }

        /**
         * @return bool
         */
        public function validateRecipients()
        {
            $passedValidation = true;
            if (count($this->_emailMessageRecipients) == 0)
            {
                $this->addError('recipientsValidation',
                                Zurmo::t('WorkflowsModule', 'At least one recipient must be added'));
                return false;
            }
            foreach ($this->_emailMessageRecipients as $key => $workflowEmailMessageRecipientForm)
            {
                if (!$workflowEmailMessageRecipientForm->validate())
                {
                    foreach ($workflowEmailMessageRecipientForm->getErrors() as $attribute => $errorArray)
                    {
                        assert('is_array($errorArray)');
                        $attributePrefix = static::resolveErrorAttributePrefix($this->resolveTemporaryKeyByRealKey($key));
                        $this->addError( $attributePrefix . $attribute, $errorArray[0]);
                    }
                    $passedValidation = false;
                }
            }
            return $passedValidation;
        }

        /**
         * @return array
         */
        public function getSendFromTypeValuesAndLabels()
        {
            $data                               = array();
            $data[self::SEND_FROM_TYPE_DEFAULT] = Zurmo::t('WorkflowsModule', 'Default System From Name/Address');
            $data[self::SEND_FROM_TYPE_CUSTOM]  = Zurmo::t('WorkflowsModule', 'Custom From Name/Address');
            return $data;
        }

        /**
         * @return array
         */
        public function getSendAfterDurationValuesAndLabels()
        {
            $data = array();
            WorkflowUtil::resolveSendAfterDurationData($data);
            return $data;
        }

        /**
         * @param $key
         * @return integer
         */
        protected function resolveTemporaryKeyByRealKey($key)
        {
            assert(is_int($key)); // Not Coding Standard
            return $this->_emailMessageRecipientsRealToTemporaryKeyData[$key];
        }
    }
?>