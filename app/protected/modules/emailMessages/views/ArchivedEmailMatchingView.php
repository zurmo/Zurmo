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
            $content = $this->renderEmailMessageContentAndResolveLink();
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
            $selectContent         = $this->renderSelectContent();
            $createContactLink     = CHtml::link(Yii::t('Default', 'Create ContactsModuleSingularLabel',
                                     LabelUtil::getTranslationParamsForAllModules()), '#',
                                     array('class' => 'contact-create-link'));
            $createContactContent  = Yii::t('Default', 'Create ContactsModuleSingularLabel',
                                     LabelUtil::getTranslationParamsForAllModules());
            $createLeadLink        = CHtml::link(Yii::t('Default', 'Create LeadsModuleSingularLabel',
                                     LabelUtil::getTranslationParamsForAllModules()), '#',
                                     array('class' => 'lead-create-link'));
            $createLeadContent     = Yii::t('Default', 'Create LeadsModuleSingularLabel',
                                     LabelUtil::getTranslationParamsForAllModules());

            $content .= '<div class="matching-actions-and-content" style="display:none;"><div class="email-matching-actions">';
            $content .= $this->renderContactSelectTitleDivContent($selectContent, $createLeadLink,    $createContactLink);
            $content .= $this->renderLeadCreateTitleDivContent($selectLink,       $createLeadContent, $createContactLink);
            $content .= $this->renderContactCreateTitleDivContent($selectLink,    $createLeadLink,    $createContactContent);
            $content .= '</div>';
            $content .= parent::renderContent() . '</div>';
            return '<div id="wrapper-' . $this->uniqueId . '" class="email-archive-item">' . $content .  '</div>';
        }

        protected function renderEmailMessageContentAndResolveLink()
        {
            $rules    = new EmailMessageMashableActivityRules();

            $content  = '<div class="email-matching-summary-content">';
            $content .= $rules->renderRelatedModelsByImportanceContent($this->emailMessage);
            $content .= CHtml::tag('span', array(), strval($this->emailMessage));
            $content .= '</div>';
            $content .= '<div class="email-matching-show-more">';
            $content .= '<span class="icon-down-arrow"></span>Expand';
            $content .= '</div>';
            $content .= '<div class="email-matching-show-less" style="display:none;">';
            $content .= '<span class="icon-up-arrow"></span>Collapse';
            $content .= '</div>';
            return $content;
        }

        public function isUniqueToAPage()
        {
            return false;
        }

        protected function renderScriptsContent()
        {
            Yii::app()->clientScript->registerScript('emailMatchingActions', "
                $('.email-matching-show-more').click( function()
                    {
                        $(this).hide();
                        $(this).parent().find('.email-matching-show-less').show();
                        $(this).parent().find('.matching-actions-and-content').show();
                        $(this).parents('tr').addClass('expanded');
                        return false;
                    }
                );
                $('.email-matching-show-less').click( function()
                    {
                        $(this).hide();
                        $(this).parent().find('.email-matching-show-more').show();
                        $(this).parent().find('.matching-actions-and-content').hide();
                        $(this).parents('tr').removeClass('expanded');
                        return false;
                    }
                );
                $('.contact-select-link').click( function()
                    {
                        $(this).parent().parent().find('.contact-select-title').show();
                        $(this).parent().parent().find('.lead-create-title').hide();
                        $(this).parent().parent().find('.contact-create-title').hide();
                        $(this).parent().parent().parent().find('.AnyContactSelectForEmailMatchingView').show();
                        $(this).parent().parent().parent().find('.ContactInlineCreateForArchivedEmailCreateView').hide();
                        $(this).parent().parent().parent().find('.LeadInlineCreateForArchivedEmailCreateView').hide();
                        return false;
                    }
                );
                $('.lead-create-link').click( function()
                    {
                        $(this).parent().parent().find('.contact-select-title').hide();
                        $(this).parent().parent().find('.lead-create-title').show();
                        $(this).parent().parent().find('.contact-create-title').hide();
                        $(this).parent().parent().parent().find('.AnyContactSelectForEmailMatchingView').hide();
                        $(this).parent().parent().parent().find('.ContactInlineCreateForArchivedEmailCreateView').hide();
                        $(this).parent().parent().parent().find('.LeadInlineCreateForArchivedEmailCreateView').show();
                        return false;
                    }
                );
                $('.contact-create-link').click( function()
                    {
                        $(this).parent().parent().find('.contact-select-title').hide();
                        $(this).parent().parent().find('.lead-create-title').hide();
                        $(this).parent().parent().find('.contact-create-title').show();
                        $(this).parent().parent().parent().find('.AnyContactSelectForEmailMatchingView').hide();
                        $(this).parent().parent().parent().find('.ContactInlineCreateForArchivedEmailCreateView').show();
                        $(this).parent().parent().parent().find('.LeadInlineCreateForArchivedEmailCreateView').hide();
                        return false;
                    }
                );
            ");
        }

        protected function renderSelectLinkContent()
        {
            if ($this->userCanAccessContacts && $this->userCanAccessLeads)
            {
                return CHtml::link(Yii::t('Default', 'Select ContactsModuleSingularLabel / LeadsModuleSingularLabel',
                                LabelUtil::getTranslationParamsForAllModules()), '#',
                                    array('class' => 'contact-select-link'));
            }
            if ($this->userCanAccessContacts)
            {
                return CHtml::link(Yii::t('Default', 'Select ContactsModuleSingularLabel',
                                LabelUtil::getTranslationParamsForAllModules()), '#',
                                    array('class' => 'contact-select-link'));
            }
            else
            {
                return CHtml::link(Yii::t('Default', 'Select LeadsModuleSingularLabel',
                                LabelUtil::getTranslationParamsForAllModules()), '#',
                                    array('class' => 'contact-select-link'));
            }
        }

        protected function renderSelectContent()
        {
            if ($this->userCanAccessContacts && $this->userCanAccessLeads)
            {
                return Yii::t('Default', 'Select ContactsModuleSingularLabel / LeadsModuleSingularLabel',
                                LabelUtil::getTranslationParamsForAllModules());
            }
            if ($this->userCanAccessContacts)
            {
                return Yii::t('Default', 'Select ContactsModuleSingularLabel',
                                LabelUtil::getTranslationParamsForAllModules());
            }
            else
            {
                return Yii::t('Default', 'Select LeadsModuleSingularLabel',
                                LabelUtil::getTranslationParamsForAllModules());
            }
        }

        protected function renderContactSelectTitleDivContent($selectContent, $createLeadLink, $createContactLink)
        {
            assert('is_string($selectContent)');
            assert('is_string($createLeadLink)');
            assert('is_string($createContactLink)');
            $content  = '<div id="contact-select-title-' . $this->uniqueId . '" class="contact-select-title">';
            $content .= $selectContent .  ' ' . Yii::t('Default', 'or') . ' ';
            if ($this->userCanCreateContact && $this->userCanCreateLead)
            {
                $content .= $createLeadLink . ' ' . Yii::t('Default', 'or') . ' ' . $createContactLink;
            }
            elseif ($this->userCanCreateContact)
            {
                $content .= $createContactLink;
            }
            else
            {
                $content .= $createLeadLink;
            }
            $content .= '</div>';
            return $content;
        }

        protected function renderLeadCreateTitleDivContent($selectContent, $createLeadContent, $createContactLink)
        {
            assert('is_string($selectContent)');
            assert('is_string($createLeadContent)');
            assert('is_string($createContactLink)');
            $content  = '<div id="lead-create-title-' . $this->uniqueId . '" class="lead-create-title" style="display:none">';
            $content .= $selectContent . Yii::t('Default', 'or') . ' ';
            $content .= $createLeadContent;
            if ($this->userCanCreateContact)
            {
                $content .= ' ' . Yii::t('Default', 'or') . ' ' . $createContactLink;
            }
            $content .= '</div>';
            return $content;
        }

        protected function renderContactCreateTitleDivContent($selectContent, $createLeadLink, $createContactContent)
        {
            assert('is_string($selectContent)');
            assert('is_string($createLeadLink)');
            assert('is_string($createContactContent)');
            $content  = '<div id="contact-create-title-' . $this->uniqueId . '" class="contact-create-title" style="display:none">';
            $content .= $selectContent . Yii::t('Default', 'or') . ' ';
            if ($this->userCanCreateLead)
            {
                $content .= ' ' . $createLeadLink;
            }
            $content .= ' ' . Yii::t('Default', 'or') . ' ' . $createContactContent;
            $content .= '</div>';
            return $content;
        }
    }
?>