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
     * Base class for working with email message recipients.
     */
    abstract class WorkflowEmailMessageRecipientForm extends ConfigurableMetadataModel
    {
        const TYPE_DYNAMIC_TRIGGERED_MODEL_USER             = 'DynamicTriggeredModelUser';

        const TYPE_DYNAMIC_TRIGGERED_MODEL_RELATION_USER    = 'DynamicTriggeredModelRelationUser';

        const TYPE_STATIC_ROLE                              = 'StaticRole';

        const TYPE_DYNAMIC_TRIGGERED_BY_USER                = 'DynamicTriggeredByUser';

        const TYPE_STATIC_USER                              = 'StaticUser';

        const TYPE_STATIC_ADDRESS                           = 'StaticAddress';

        const TYPE_STATIC_GROUP                             = 'StaticGroup';

        /**
         *
         * @param RedBeanModel $model
         * @param User $triggeredByUser
         * @return array of EmailMessageRecipients
         */
        abstract public function makeRecipients(RedBeanModel $model, User $triggeredByUser);

        /**
         * @var string Type of recipient
         */
        public $type;

        /**
         * @var string type of audience, to, occ, or bcc
         */
        public $audienceType;

        /**
         * static user for example would populate this with the stringified name of the user.
         * interface
         * @var string
         */
        protected $stringifiedModelForValue;

        /**
         * Refers to the model that is associated with the workflow rule.
         * @var string
         */
        protected $modelClassName;

        /**
         * @var string
         */
        protected $workflowType;

        /**
         * @throws NotImplementedException if not implemented by a child class
         * @return string label content
         */
        public static function getTypeLabel()
        {
            throw new NotImplementedException();
        }

        /**
         * @return string - If the class name is DynamicTriggeredModelRelationUserWorkflowEmailMessageRecipientForm,
         * then 'DynamicTriggeredModelRelationUser' will be returned.
         */
        public static function getFormType()
        {
            $type = get_called_class();
            $type = substr($type, 0, strlen($type) - strlen('WorkflowEmailMessageRecipientForm'));
            return $type;
        }

        /**
         * @param string $modelClassName
         * @param string $workflowType
         */
        public function __construct($modelClassName, $workflowType)
        {
            assert('is_string($modelClassName)');
            assert('is_string($workflowType)');
            $this->modelClassName     = $modelClassName;
            $this->workflowType       = $workflowType;
        }

        /**
         * Override to properly handle retrieving rule information from the model for the attribute name.
         */
        public function rules()
        {
            return array_merge(parent::rules(), array(
                array('type',                     'type', 'type' => 'string'),
                array('type',                     'required'),
                array('audienceType',             'safe'),
                array('audienceType',             'required'),
            ));
        }

        /**
         * @return array
         */
        public static function getTypeValuesAndLabels()
        {
            $data = array();
            $data[static::TYPE_DYNAMIC_TRIGGERED_MODEL_USER]             =
                DynamicTriggeredModelUserWorkflowEmailMessageRecipientForm::getTypeLabel();
            $data[static::TYPE_DYNAMIC_TRIGGERED_MODEL_RELATION_USER]    =
                DynamicTriggeredModelRelationUserWorkflowEmailMessageRecipientForm::getTypeLabel();
            $data[static::TYPE_STATIC_ROLE]                              =
                StaticRoleWorkflowEmailMessageRecipientForm::getTypeLabel();
            $data[static::TYPE_DYNAMIC_TRIGGERED_BY_USER]                   =
                DynamicTriggeredByUserWorkflowEmailMessageRecipientForm::getTypeLabel();
            $data[static::TYPE_STATIC_USER]                              =
                StaticUserWorkflowEmailMessageRecipientForm::getTypeLabel();
            $data[static::TYPE_STATIC_ADDRESS]                            =
                StaticAddressWorkflowEmailMessageRecipientForm::getTypeLabel();
            $data[static::TYPE_STATIC_GROUP]                             =
                StaticGroupWorkflowEmailMessageRecipientForm::getTypeLabel();
            return $data;
        }
    }
?>