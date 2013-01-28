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
     * @see ArchivedEmailMatchingListView.  For each row in the list, this view is used to allow a user to select
     * an existing contact/lead, or create a new contact or lead to match the email message to.
     * Enter description here ...
     */
    class ArchivedEmailMatchingView extends GridView
    {
        protected $cssClasses =  array('DetailsView');

        /**
         * @var string
         */
        protected $controllerId;

        /**
         * @var string
         */
        protected $moduleId;

        /**
         * @var EmailMessage object
         */
        protected $emailMessage;

        /**
         * Variable form depending on user's access/create rights to contacts and leads.
         * @var CFormModel
         */
        protected $selectForm;

        /**
         * @var Contact object
         */
        protected $contact;

        /**
         * @var boolean
         */
        protected $userCanAccessLeads;

        /**
         * @var boolean
         */
        protected $userCanAccessContacts;

        /**
         * @var boolean
         */
        protected $userCanCreateContact;

        /**
         * @var boolean
         */
        protected $userCanCreateLead;

        /**
         * @var string
         */
        protected $uniqueId;

        /**
         * @var string
         */
        protected $saveActionId;

        /**
         * @var array
         */
        protected $urlParameters;

        public function __construct(
                $controllerId,
                $moduleId,
                EmailMessage $emailMessage,
                Contact      $contact,
                $selectForm,
                $userCanAccessLeads,
                $userCanAccessContacts,
                $userCanCreateContact,
                $userCanCreateLead,
                $gridSize)
        {
            assert('is_string($controllerId)');
            assert('is_string($moduleId)');
            assert('$emailMessage->id > 0');
            assert('$selectForm instanceof AnyContactSelectForm || $selectForm instanceof ContactSelectForm || $selectForm instanceof LeadSelectForm');
            assert('is_bool($userCanAccessLeads)');
            assert('is_bool($userCanAccessContacts)');
            assert('is_bool($userCanCreateContact)');
            assert('is_bool($userCanCreateLead)');
            assert('is_int($gridSize)');
            parent::__construct($gridSize, 1);
            $this->controllerId              = $controllerId;
            $this->moduleId                  = $moduleId;
            $this->emailMessage              = $emailMessage;
            $this->selectForm                = $selectForm;
            $this->contact                   = $contact;
            $this->userCanAccessLeads        = $userCanAccessLeads;
            $this->userCanAccessContacts     = $userCanAccessContacts;
            $this->userCanCreateContact      = $userCanCreateContact;
            $this->userCanCreateLead         = $userCanCreateLead;
            $this->gridSize                  = $gridSize;
            $this->uniqueId                  = $emailMessage->id;
            $this->saveActionId              = 'completeMatch';
            $this->urlParameters             = array('id' => $this->emailMessage->id);
        }

        /**
         * Renders content for the view.
         * @return A string containing the element's content.
         */
        protected function renderContent()
        {
            $this->renderScriptsContent();
            $this->setView(new AnyContactSelectForEmailMatchingView($this->controllerId,
                                                                    $this->moduleId,
                                                                    $this->selectForm,
                                                                    $this->uniqueId,
                                                                    $this->saveActionId,
                                                                    $this->urlParameters), 0, 0);
            $row = 1;
           // $content = $this->renderEmailMessageContentAndResolveLink();
            if ($this->userCanCreateContact)
            {
                $this->setView(new ContactInlineCreateForArchivedEmailCreateView(
                                        $this->controllerId,
                                        $this->moduleId,
                                        $this->emailMessage->id,
                                        $this->contact,
                                        $this->uniqueId,
                                        $this->saveActionId,
                                        $this->urlParameters), $row, 0);
                $row++;
            }
            if ($this->userCanCreateLead)
            {
                $this->setView(new LeadInlineCreateForArchivedEmailCreateView(
                                        $this->controllerId,
                                        $this->moduleId,
                                        $this->emailMessage->id,
                                        $this->contact,
                                        $this->uniqueId,
                                        $this->saveActionId,
                                        $this->urlParameters), $row, 0);
            }
            $selectLink            = $this->renderSelectLinkContent();
            $createContactLink     = ZurmoHtml::link(Zurmo::t('EmailMessagesModule', 'Create ContactsModuleSingularLabel',
                                     LabelUtil::getTranslationParamsForAllModules()), '#',
                                     array('class' => 'create-link contact-create-link z-action-link'));
            $createLeadLink        = ZurmoHtml::link(Zurmo::t('EmailMessagesModule', 'Create LeadsModuleSingularLabel',
                                     LabelUtil::getTranslationParamsForAllModules()), '#',
                                     array('class' => 'create-link lead-create-link z-action-link'));
            $deleteLink            = $this->renderDeleteLink();
            $rules    = new EmailMessageMashableActivityRules();
            $content = $rules->renderRelatedModelsByImportanceContent($this->emailMessage);
            $content .= ZurmoHtml::wrapLabel(strval($this->emailMessage), 'email-subject');
            $content .= '<div class="matching-actions-and-content"><div class="email-matching-actions">';
            $content .= $this->renderTitleDivContent($selectLink, $createLeadLink, $createContactLink, $deleteLink);
            $content .= '</div>';
            $content .= parent::renderContent() . '</div>';
            return '<div id="wrapper-' . $this->uniqueId . '" class="email-archive-item">' . $content .  '</div>';
        }

        public function isUniqueToAPage()
        {
            return false;
        }

        protected function renderScriptsContent()
        {
            Yii::app()->clientScript->registerScript('emailMatchingActions', "
                                  $('.select-contact-link').live('click', function ()
                                  {
                                        $(this).closest('td').find('.z-action-link-active').removeClass('z-action-link-active');
                                        $(this).addClass('z-action-link-active');
                                        $(this).closest('td').addClass('active-panel');
                                        $(this).parent().parent().parent().find('.AnyContactSelectForEmailMatchingView').show();
                                        $(this).parent().parent().parent().find('.ContactInlineCreateForArchivedEmailCreateView').hide();
                                        $(this).parent().parent().parent().find('.LeadInlineCreateForArchivedEmailCreateView').hide();
                                   })
                                   $('.contact-create-link').live('click', function ()
                                   {
                                        $(this).closest('td').find('.z-action-link-active').removeClass('z-action-link-active');
                                        $(this).addClass('z-action-link-active');
                                        $(this).closest('td').addClass('active-panel');
                                        $(this).parent().parent().parent().find('.AnyContactSelectForEmailMatchingView').hide();
                                        $(this).parent().parent().parent().find('.ContactInlineCreateForArchivedEmailCreateView').show();
                                        $(this).parent().parent().parent().find('.LeadInlineCreateForArchivedEmailCreateView').hide();
                                   })
                                   $('.lead-create-link').live('click', function ()
                                   {
                                        $(this).closest('td').find('.z-action-link-active').removeClass('z-action-link-active');
                                        $(this).addClass('z-action-link-active');
                                        $(this).closest('td').addClass('active-panel');
                                        $(this).parent().parent().parent().find('.AnyContactSelectForEmailMatchingView').hide();
                                        $(this).parent().parent().parent().find('.ContactInlineCreateForArchivedEmailCreateView').hide();
                                        $(this).parent().parent().parent().find('.LeadInlineCreateForArchivedEmailCreateView').show();
                                   })
            ");
        }

        protected function renderSelectLinkContent()
        {
            if ($this->userCanAccessContacts && $this->userCanAccessLeads)
            {
                return ZurmoHtml::link(Zurmo::t('EmailMessagesModule', 'Select ContactsModuleSingularLabel / LeadsModuleSingularLabel',
                                LabelUtil::getTranslationParamsForAllModules()), '#',
                                    array('class' => 'create-link select-contact-link z-action-link'));
            }
            if ($this->userCanAccessContacts)
            {
                return ZurmoHtml::link(Zurmo::t('EmailMessagesModule', 'Select ContactsModuleSingularLabel',
                                LabelUtil::getTranslationParamsForAllModules()), '#',
                                    array('class' => 'create-link select-contact-link z-action-link'));
            }
            else
            {
                return ZurmoHtml::link(Zurmo::t('EmailMessagesModule', 'Select LeadsModuleSingularLabel',
                                LabelUtil::getTranslationParamsForAllModules()), '#',
                                    array('class' => 'create-link select-contact-link z-action-link'));
            }
        }

        protected function renderSelectContent()
        {
            if ($this->userCanAccessContacts && $this->userCanAccessLeads)
            {
                return Zurmo::t('EmailMessagesModule', 'Select ContactsModuleSingularLabel / LeadsModuleSingularLabel',
                                LabelUtil::getTranslationParamsForAllModules());
            }
            if ($this->userCanAccessContacts)
            {
                return Zurmo::t('EmailMessagesModule', 'Select ContactsModuleSingularLabel',
                                LabelUtil::getTranslationParamsForAllModules());
            }
            else
            {
                return Zurmo::t('EmailMessagesModule', 'Select LeadsModuleSingularLabel',
                                LabelUtil::getTranslationParamsForAllModules());
            }
        }

        protected function renderTitleDivContent($selectLink, $createLeadLink, $createContactLink, $deleteLink)
        {
            assert('is_string($selectLink)');
            assert('is_string($createLeadLink)');
            assert('is_string($createContactLink)');
            assert('is_string($deleteLink)');
            $content  = '<div id="select-title-' . $this->uniqueId . '" class="select-title">';
            $content .= $selectLink . ' &#183; ';
            if ($this->userCanCreateContact && $this->userCanCreateLead)
            {
                $content .= $createLeadLink . ' &#183; ' . $createContactLink;
            }
            elseif ($this->userCanCreateContact)
            {
                $content .= $createContactLink;
            }
            else
            {
                $content .= $createLeadLink;
            }
            $content .= $deleteLink;
            $content .= '</div>';
            return $content;
        }

        protected function renderDeleteLink()
        {
            $htmlOptions = $this->getHtmlOptionsForDelete();
            $route = $this->getDefaultRouteForDelete();
            $ajaxOptions = $this->getAjaxOptionsForDelete();
            $content = ' &#183; ' . ZurmoHtml::ajaxLink(Zurmo::t('EmailMessagesModule', 'Delete'), $route, $ajaxOptions,
                                     $htmlOptions);
            return $content;
        }

        protected function getDefaultRouteForDelete()
        {
            $params = array('id' => $this->uniqueId);
            return Yii::app()->createUrl($this->moduleId . '/' . $this->controllerId . '/delete/', $params);
        }

        protected function getAjaxOptionsForDelete()
        {
            return array('type'     => 'GET',
                         'success'  => "function()
                                       {
                                           $('#wrapper-" . $this->uniqueId . "').parent().parent().remove();
                                           $('#" . self::getNotificationBarId() . "').jnotifyAddMessage(
                                           {
                                              text: '" . Zurmo::t('EmailMessagesModule', 'Deleted successfully') . "',
                                              permanent: false,
                                              showIcon: true,
                                           })
                                       }
            ");
        }

        protected function getHtmlOptionsForDelete()
        {
            $htmlOptions['id']      = 'delete-link-' . $this->uniqueId;
            $htmlOptions['class']   = 'z-action-link';
            $htmlOptions['confirm'] = Zurmo::t('EmailMessagesModule', 'Are you sure you want to delete?');
            return $htmlOptions;
        }

        protected static function getMatchingListUrl()
       {
           return Yii::app()->createUrl('emailMessages/default/matchingList');
       }

        protected static function getNotificationBarId()
        {
            return 'FlashMessageBar';
        }
    }
?>