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
     * Controller for managing configuration actions for email messages
     */
    class EmailMessagesDefaultController extends ZurmoBaseController
    {
        public function filters()
        {
            $moduleClassName = get_class($this->getModule());
            return array(
                array(
                    ZurmoBaseController::RIGHTS_FILTER_PATH . ' + configurationEdit',
                    'moduleClassName' => $moduleClassName,
                    'rightName'       => EmailMessagesModule::RIGHT_ACCESS_CONFIGURATION,
               ),
            );
        }

        public function actionDetails($id, $redirectUrl = null)
        {
            $emailMessage          = EmailMessage::getById(intval($id));
            ControllerSecurityUtil::resolveAccessCanCurrentUserReadModel($emailMessage);
            $detailsView           = new EmailMessageDetailsView($this->getId(), $this->getModule()->getId(), $emailMessage);
            $view              = new EmailMessagesPageView(ZurmoDefaultViewUtil::
                                         makeStandardViewForCurrentUser($this, $detailsView));
            echo $view->render();
        }

        public function actionConfigurationEdit()
        {
            $view = new ConfigurationPageView(ZurmoDefaultAdminViewUtil::
                                                  makeStandardViewForCurrentUser($this, new EmailConfigurationListView()));
            echo $view->render();
        }

        public function actionConfigurationEditOutbound()
        {
            $configurationForm = EmailSmtpConfigurationFormAdapter::makeFormFromGlobalConfiguration();
            $postVariableName   = get_class($configurationForm);
            if (isset($_POST[$postVariableName]))
            {
                $configurationForm->setAttributes($_POST[$postVariableName]);
                if ($configurationForm->validate())
                {
                    EmailSmtpConfigurationFormAdapter::setConfigurationFromForm($configurationForm);
                    Yii::app()->user->setFlash('notification',
                        Yii::t('Default', 'Email configuration saved successfully.')
                    );
                    $this->redirect(Yii::app()->createUrl('configuration/default/index'));
                }
            }
            $editView = new EmailSmtpConfigurationEditAndDetailsView(
                                    'Edit',
                                    $this->getId(),
                                    $this->getModule()->getId(),
                                    $configurationForm);
            $editView->setCssClasses( array('AdministrativeArea') );
            $view = new ZurmoConfigurationPageView(ZurmoDefaultAdminViewUtil::
                                         makeStandardViewForCurrentUser($this, $editView));
            echo $view->render();
        }

        public function actionConfigurationEditArchiving()
        {
            $configurationForm = EmailArchivingConfigurationFormAdapter::makeFormFromGlobalConfiguration();
            $postVariableName   = get_class($configurationForm);
            if (isset($_POST[$postVariableName]))
            {
                $configurationForm->setAttributes($_POST[$postVariableName]);
                if ($configurationForm->validate())
                {
                    EmailArchivingConfigurationFormAdapter::setConfigurationFromForm($configurationForm);
                    Yii::app()->user->setFlash('notification',
                        Yii::t('Default', 'Email configuration saved successfully.')
                    );
                    $this->redirect(Yii::app()->createUrl('configuration/default/index'));
                }
            }
            $editView = new EmailArchivingConfigurationEditAndDetailsView(
                                    'Edit',
                                    $this->getId(),
                                    $this->getModule()->getId(),
                                    $configurationForm);
            $editView->setCssClasses( array('AdministrativeArea') );
            $view = new ZurmoConfigurationPageView(ZurmoDefaultAdminViewUtil::
                                         makeStandardViewForCurrentUser($this, $editView));
            echo $view->render();
        }

        /**
         * Assumes before calling this, the outbound settings have been validated in the form.
         * Todo: When new user interface is complete, this will be re-worked to be on page instead of modal.
         */
        public function actionSendTestMessage()
        {
            $configurationForm = EmailSmtpConfigurationFormAdapter::makeFormFromGlobalConfiguration();
            $postVariableName   = get_class($configurationForm);
            if (isset($_POST[$postVariableName]))
            {
                $configurationForm->setAttributes($_POST[$postVariableName]);
                if ($configurationForm->aTestToAddress != null)
                {
                    $emailHelper = new EmailHelper;
                    $emailHelper->outboundHost     = $configurationForm->host;
                    $emailHelper->outboundPort     = $configurationForm->port;
                    $emailHelper->outboundUsername = $configurationForm->username;
                    $emailHelper->outboundPassword = $configurationForm->password;
                    $emailHelper->outboundSecurity = $configurationForm->security;
                    $userToSendMessagesFrom        = User::getById((int)$configurationForm->userIdOfUserToSendNotificationsAs);

                    $emailMessage = EmailMessageHelper::sendTestEmail($emailHelper, $userToSendMessagesFrom,
                                                                      $configurationForm->aTestToAddress);
                    $messageContent  = null;
                    if (!$emailMessage->hasSendError())
                    {
                        $messageContent .= Yii::t('Default', 'Message successfully sent') . "\n";
                    }
                    else
                    {
                        $messageContent .= Yii::t('Default', 'Message failed to send') . "\n";
                        $messageContent .= $emailMessage->error     . "\n";
                    }
                }
                else
                {
                    $messageContent = Yii::t('Default', 'A test email address must be entered before you can send a test email.') . "\n";
                }
                Yii::app()->getClientScript()->setToAjaxMode();
                $messageView = new TestEmailMessageView($messageContent);
                $view = new ModalView($this, $messageView);
                echo $view->render();
            }
            else
            {
                throw new NotSupportedException();
            }
        }

        /**
        * Assumes before calling this, the inbound settings have been validated in the form.
        */
        public function actionTestImapConnection()
        {
            $configurationForm = EmailArchivingConfigurationFormAdapter::makeFormFromGlobalConfiguration();
            $postVariableName   = get_class($configurationForm);
            if (isset($_POST[$postVariableName]))
            {
                $configurationForm->setAttributes($_POST[$postVariableName]);

                $imap = new ZurmoImap();

                $imap->imapHost     = $configurationForm->imapHost;
                $imap->imapUsername = $configurationForm->imapUsername;
                $imap->imapPassword = $configurationForm->imapPassword;
                $imap->imapPort     = $configurationForm->imapPort;
                $imap->imapSSL      = $configurationForm->imapSSL;
                $imap->imapFolder   = $configurationForm->imapFolder;

                try
                {
                    $connect = $imap->connect();
                }
                catch (Exception $e)
                {
                    $connect = false;
                    $messageContent = Yii::t('Default', 'Could not connect to IMAP server.') . "\n";
                }

                if (isset($connect) && $connect == true)
                {
                    $messageContent = Yii::t('Default', 'Successfully connected to IMAP server.') . "\n";
                }
                else
                {
                    $messageContent = Yii::t('Default', 'Could not connect to IMAP server.') . "\n";
                }

                Yii::app()->getClientScript()->setToAjaxMode();
                $messageView = new TestImapConnectionMessageView($messageContent);
                $view = new ModalView($this,
                                      $messageView,
                                      'modalContainer',
                                      Yii::t('Default', 'Test Message Results')
                );
                echo $view->render();
            }
            else
            {
                throw new NotSupportedException();
            }
        }

        public function actionMatchingList()
        {
            $userCanAccessContacts = RightsUtil::canUserAccessModule('ContactsModule', Yii::app()->user->userModel);
            $userCanAccessLeads    = RightsUtil::canUserAccessModule('LeadsModule', Yii::app()->user->userModel);
            EmailMessagesControllerSecurityUtil::
                resolveCanUserProperlyMatchMessage($userCanAccessContacts, $userCanAccessLeads);
            $pageSize         = Yii::app()->pagination->resolveActiveForCurrentUserByType(
                                'listPageSize', get_class($this->getModule()));
            $emailMessage     = new EmailMessage(false);
            $searchAttributes = array();
            $metadataAdapter  = new ArchivedEmailMatchingSearchDataProviderMetadataAdapter(
                $emailMessage,
                Yii::app()->user->userModel->id,
                $searchAttributes
            );
            $dataProvider = RedBeanModelDataProviderUtil::makeDataProvider(
                $metadataAdapter->getAdaptedMetadata(),
                'EmailMessage',
                'RedBeanModelDataProvider',
                'createdDateTime',
                true,
                $pageSize
            );
            $titleBarAndListView = new TitleBarAndListView(
                                        $this->getId(),
                                        $this->getModule()->getId(),
                                        $emailMessage,
                                        'EmailMessage',
                                        $dataProvider,
                                        'ArchivedEmailMatchingListView',
                                        Yii::t('Default', 'Unmatched Archived Emails'),
                                        array(),
                                        false);
            $view = new EmailMessagesPageView(ZurmoDefaultViewUtil::
                                              makeStandardViewForCurrentUser($this, $titleBarAndListView));
            echo $view->render();
        }

        public function actionCompleteMatch($id)
        {
            //!!!todo security checks?? think about it
            $emailMessage          = EmailMessage::getById((int)$id);
            $userCanAccessContacts = RightsUtil::canUserAccessModule('ContactsModule', Yii::app()->user->userModel);
            $userCanAccessLeads    = RightsUtil::canUserAccessModule('LeadsModule', Yii::app()->user->userModel);
            if (!$userCanAccessContacts && !$userCanAccessLeads)
            {
                throw new NotSupportedException();
            }
            $selectForm            = self::makeSelectForm($userCanAccessLeads, $userCanAccessContacts);

            if (isset($_POST[get_class($selectForm)]))
            {
                if (isset($_POST['ajax']) && $_POST['ajax'] === 'select-contact-form-' . $id)
                {
                    $selectForm->setAttributes($_POST[get_class($selectForm)][$id]);
                    $selectForm->validate();
                    $errorData = array();
                    foreach ($selectForm->getErrors() as $attribute => $errors)
                    {
                            $errorData[ZurmoHtml::activeId($selectForm, $attribute)] = $errors;
                    }
                    echo CJSON::encode($errorData);
                    Yii::app()->end(0, false);
                }
                else
                {
                    $selectForm->setAttributes($_POST[get_class($selectForm)][$id]);
                    $contact = Contact::getById((int)$selectForm->contactId);
                    ArchivedEmailMatchingUtil::resolveContactToSenderOrRecipient($emailMessage, $contact);
                    ArchivedEmailMatchingUtil::resolveEmailAddressToContactIfEmailRelationAvailable($emailMessage, $contact);
                    $emailMessage->folder = EmailFolder::getByBoxAndType($emailMessage->folder->emailBox,
                                                                         EmailFolder::TYPE_ARCHIVED_UNMATCHED);
                    if (!$emailMessage->save())
                    {
                        throw new FailedToSaveModelException();
                    }
                }
            }
            else
            {
                static::attemptToMatchAndSaveLeadOrContact($emailMessage, 'Contact', (int)$id);
                static::attemptToMatchAndSaveLeadOrContact($emailMessage, 'Lead', (int)$id);
            }
        }

        protected static function attemptToMatchAndSaveLeadOrContact($emailMessage, $type, $emailMessageId)
        {
            assert('$type == "Contact" || $type == "Lead"');
            assert('is_int($emailMessageId)');
            if (isset($_POST[$type]))
            {
                if (isset($_POST['ajax']) && $_POST['ajax'] === strtolower($type) . '-inline-create-form-' . $emailMessageId)
                {
                    $contact = new Contact();
                    $contact->setAttributes($_POST[$type][$emailMessageId]);
                    $contact->validate();
                    $errorData = array();
                    foreach ($contact->getErrors() as $attribute => $errors)
                    {
                            $errorData[ZurmoHtml::activeId($contact, $attribute)] = $errors;
                    }
                    echo CJSON::encode($errorData);
                    Yii::app()->end(0, false);
                }
                else
                {
                    $contact = new Contact();
                    $contact->setAttributes($_POST[$type][$emailMessageId]);
                    if (!$contact->save())
                    {
                        throw new FailedToSaveModelException();
                    }
                    ArchivedEmailMatchingUtil::resolveContactToSenderOrRecipient($emailMessage, $contact);
                    $emailMessage->folder = EmailFolder::getByBoxAndType($emailMessage->folder->emailBox,
                                                                         EmailFolder::TYPE_ARCHIVED_UNMATCHED);
                    if (!$emailMessage->save())
                    {
                        throw new FailedToSaveModelException();
                    }
                }
            }
        }

        protected static function makeSelectForm($userCanAccessLeads, $userCanAccessContacts)
        {
            if ($userCanAccessLeads && $userCanAccessContacts)
            {
                $selectForm = new AnyContactSelectForm();
            }
            elseif (!$userCanAccessLeads && $userCanAccessContacts)
            {
                $selectForm = new ContactSelectForm();
            }
            else
            {
                $selectForm = new LeadSelectForm();
            }
            return $selectForm;
        }
    }
?>