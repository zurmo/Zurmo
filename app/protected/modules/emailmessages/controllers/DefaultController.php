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

        public function actionConfigurationEdit()
        {
            $configurationForm = OutboundEmailConfigurationFormAdapter::makeFormFromGlobalConfiguration();
            $postVariableName   = get_class($configurationForm);
            if (isset($_POST[$postVariableName]))
            {
                $configurationForm->setAttributes($_POST[$postVariableName]);
                if ($configurationForm->validate())
                {
                    OutboundEmailConfigurationFormAdapter::setConfigurationFromForm($configurationForm);
                    Yii::app()->user->setFlash('notification',
                        Yii::t('Default', 'Outbound email configuration saved successfully.')
                    );
                    $this->redirect(Yii::app()->createUrl('configuration/default/index'));
                }
            }
            $titleBarAndEditView = new TitleBarAndConfigurationEditAndDetailsView(
                                    $this->getId(),
                                    $this->getModule()->getId(),
                                    $configurationForm,
                                    'OutboundEmailConfigurationEditAndDetailsView',
                                    'Edit',
                                    Yii::t('Default', 'Outbound Email Configuration (SMTP)')
            );
            $view = new ZurmoConfigurationPageView($this, $titleBarAndEditView);
            echo $view->render();
        }

        /**
         * Assumes before calling this, the outbound settings have been validated in the form.
         * Todo: When new user interface is complete, this will be re-worked to be on page instead of modal.
         */
        public function actionSendTestMessage()
        {
            $configurationForm = OutboundEmailConfigurationFormAdapter::makeFormFromGlobalConfiguration();
            $postVariableName   = get_class($configurationForm);
            if (isset($_POST[$postVariableName]))
            {
                $configurationForm->setAttributes($_POST[$postVariableName]);
                if($configurationForm->aTestToAddress != null)
                {
                    $emailHelper = new EmailHelper;
                    $emailHelper->outboundHost     = $configurationForm->host;
                    $emailHelper->outboundPort     = $configurationForm->port;
                    $emailHelper->outboundUsername = $configurationForm->username;
                    $emailHelper->outboundPassword = $configurationForm->password;
                    $userToSendMessagesFrom        = User::getById((int)$configurationForm->userIdOfUserToSendNotificationsAs);

                    $emailMessage              = new EmailMessage();
                    $emailMessage->owner       = $userToSendMessagesFrom;
                    $emailMessage->subject     = Yii::t('Default', 'A test email from Zurmo');
                    $emailContent              = new EmailMessageContent();
                    $emailContent->textContent = Yii::t('Default', 'A test text message from Zurmo');
                    $emailContent->htmlContent = Yii::t('Default', 'A test text message from Zurmo');
                    $emailMessage->content     = $emailContent;
                    $sender                    = new EmailMessageSender();
                    $sender->fromAddress       = $emailHelper->resolveFromAddressByUser($userToSendMessagesFrom);
                    $sender->fromName          = strval($userToSendMessagesFrom);
                    $emailMessage->sender      = $sender;
                    $recipient                 = new EmailMessageRecipient();
                    $recipient->toAddress      = $configurationForm->aTestToAddress;
                    $recipient->toName         = 'Test Recipient';
                    $recipient->type           = EmailMessageRecipient::TYPE_TO;
                    $emailMessage->recipients->add($recipient);
                    $box                       = EmailBox::resolveAndGetByName(EmailBox::NOTIFICATIONS_NAME);
                    $emailMessage->folder      = EmailFolder::getByBoxAndType($box, EmailFolder::TYPE_DRAFT);
                    $validated                 = $emailMessage->validate();
                    if(!$validated)
                    {
                        throw new NotSupportedException();
                    }
                    $messageContent  = null;
                    $emailHelper->sendImmediately($emailMessage);
                    if(!$emailMessage->hasSendError())
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
                $view = new ModalView($this,
                                      $messageView,
                                      'modalContainer',
                                      Yii::t('Default', 'Test Message Results'));
                echo $view->render();
            }
            else
            {
                throw new NotSupportedException();
            }
        }
    }
?>