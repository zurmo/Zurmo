<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
     *
     * Zurmo is free software; you can redistribute it and/or modify it under
     * the terms of the GNU Affero General Public License version 3 as published by the
     * Free Software Foundation with the addition of the following permission added
     * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
     * IN WHICH THE COPYRIGHT IS OWNED BY ZURMO, ZURMO DISCLAIMS THE WARRANTY
     * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
     *
     * Zurmo is distributed in the hope that it will be useful, but WITHOUT
     * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
     * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
     * details.
     *
     * You should have received a copy of the GNU Affero General Public License along with
     * this program; if not, see http://www.gnu.org/licenses or write to the Free
     * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
     * 02110-1301 USA.
     *
     * You can contact Zurmo, Inc. with a mailing address at 27 North Wacker Drive
     * Suite 370 Chicago, IL 60606. or at email address contact@zurmo.com.
     *
     * The interactive user interfaces in original and modified versions
     * of this program must display Appropriate Legal Notices, as required under
     * Section 5 of the GNU Affero General Public License version 3.
     *
     * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
     * these Appropriate Legal Notices must retain the display of the Zurmo
     * logo and Zurmo copyright notice. If the display of the logo is not reasonably
     * feasible for technical reasons, the Appropriate Legal Notices must display the words
     * "Copyright Zurmo Inc. 2013. All rights reserved".
     ********************************************************************************/

    /**
     * Helper class for working with autoresponderItem and campaignItem
     */
    abstract class AutoresponderAndCampaignItemsUtil
    {
        public static function processDueItem(OwnedModel $item)
        {
            assert('is_object($item)');
            $itemId                     = $item->id;
            $itemClass                  = get_class($item);
            assert('$itemClass === "AutoresponderItem" || $itemClass === "CampaignItem"');
            $contact                                = $item->contact;
            if (empty($contact) || $contact->id < 0)
            {
                throw new NotFoundException();
            }
            $ownerModelRelationName     = static::resolveItemOwnerModelRelationName($itemClass);
            $itemOwnerModel             = $item->$ownerModelRelationName;
            assert('is_object($itemOwnerModel)');
            assert('get_class($itemOwnerModel) === "Autoresponder" || get_class($itemOwnerModel) === "Campaign"');
            if ($contact->primaryEmail->optOut)
            {
                $activityClass  = $itemClass . 'Activity';
                $personId       = $contact->getClassId('Person');
                $type           = $activityClass::TYPE_SKIP;
                $activityClass::createNewActivity($type, $itemId, $personId);
            }
            else
            {
                $marketingList              = $itemOwnerModel->marketingList;
                assert('is_object($marketingList)');
                assert('get_class($marketingList) === "MarketingList"');
                $textContent                = $itemOwnerModel->textContent;
                $htmlContent                = $itemOwnerModel->htmlContent;
                static::resolveContent($textContent, $htmlContent, $contact, $itemOwnerModel->enableTracking,
                                       (int)$itemId, $itemClass, (int)$marketingList->id);
                try
                {
                    $item->emailMessage = static::resolveEmailMessage($textContent, $htmlContent, $itemOwnerModel,
                                                                        $contact, $marketingList, $itemId, $itemClass);
                }
                catch (MissingRecipientsForEmailMessageException $e)
                {
                    // TODO: @Shoaibi/@Jason: Medium: Do something about it.
                }
            }
            static::markItemAsProcessed($item);
        }

        protected static function resolveContent(& $textContent, & $htmlContent, Contact $contact,
                                                            $enableTracking, $modelId, $modelType, $marketingListId)
        {
            assert('is_int($modelId)');
            assert('is_int($marketingListId)');
            static::resolveContentForMergeTags($textContent, $htmlContent, $contact);
            static::resolveContentForTrackingAndFooter($textContent, $htmlContent, $enableTracking, $modelId,
                                                                                $modelType, $contact, $marketingListId);
        }

        protected static function resolveContentForMergeTags(& $textContent, & $htmlContent, Contact $contact)
        {
            // TODO: @Shoaibi/@Jason: High: we might add support for language
            $language               = null;
            $errorOnFirstMissing    = true;
            $invalidTags            = null; // this could be an empty array, only used when $errorOnFirstMissing is false
            $templateType           = EmailTemplate::TYPE_CONTACT;
            $invalidTags            = array();
            $textMergeTagsUtil      = MergeTagsUtilFactory::make($templateType, $language, $textContent);
            $htmlMergeTagsUtil      = MergeTagsUtilFactory::make($templateType, $language, $htmlContent);
            $resolvedTextContent    = $textMergeTagsUtil->resolveMergeTags($contact,
                                                                            $invalidTags,
                                                                            $language,
                                                                            $errorOnFirstMissing);
            $resolvedHtmlContent    = $htmlMergeTagsUtil->resolveMergeTags($contact,
                                                                            $invalidTags,
                                                                            $language,
                                                                            $errorOnFirstMissing);

            if ($resolvedTextContent === false || $resolvedHtmlContent === false)
            {
                throw new NotSupportedException(Zurmo::t('EmailTemplatesModule', 'Provided content contains few invalid merge tags.'));
            }
            else
            {
                $textContent    = $resolvedTextContent;
                $htmlContent    = $resolvedHtmlContent;
            }
        }

        protected static function resolveContentForTrackingAndFooter(& $textContent, & $htmlContent, $enableTracking, $modelId,
                                                                        $modelType, Contact $contact, $marketingListId)
        {
            assert('is_int($modelId)');
            assert('is_int($marketingListId)');
            $personId                 = $contact->getClassId('Person');
            $activityUtil             = $modelType . 'ActivityUtil';
            $activityUtil::resolveContentForTrackingAndFooter($enableTracking, $textContent, $modelId, $modelType,
                                                                                    $personId, $marketingListId, false);
            $activityUtil::resolveContentForTrackingAndFooter($enableTracking, $htmlContent, $modelId, $modelType,
                                                                                    $personId, $marketingListId, true);
        }

        protected static function resolveEmailMessage($textContent, $htmlContent, Item $itemOwnerModel,
                                                    Contact $contact, MarketingList $marketingList, $itemId, $itemClass)
        {
            $emailMessage                   = new EmailMessage();
            $emailMessage->owner            = $marketingList->owner;
            $emailMessage->subject          = $itemOwnerModel->subject;
            $emailContent                   = new EmailMessageContent();
            $emailContent->textContent      = $textContent;
            $emailContent->htmlContent      = $htmlContent;
            $emailMessage->content          = $emailContent;
            $emailMessage->sender           = static::resolveSender($marketingList);
            static::resolveRecipient($emailMessage, $contact);
            static::resolveAttachments($emailMessage, $itemOwnerModel);
            static::resolveHeaders($emailMessage, $itemId, $itemClass, $contact->getClassId('Person'));
            if ($emailMessage->recipients->count() == 0)
            {
                throw new MissingRecipientsForEmailMessageException();
            }
            $boxName                        = static::resolveEmailBoxName(get_class($itemOwnerModel));
            $box                            = EmailBox::resolveAndGetByName($boxName);
            $emailMessage->folder           = EmailFolder::getByBoxAndType($box, EmailFolder::TYPE_DRAFT);
            Yii::app()->emailHelper->send($emailMessage);
            return $emailMessage;
        }

        protected static function resolveSender(MarketingList $marketingList)
        {
            $sender                         = new EmailMessageSender();
            if (!empty($marketingList->fromName) && !empty($marketingList->fromAddress))
            {
                $sender->fromAddress        = $marketingList->fromAddress;
                $sender->fromName           = $marketingList->fromName;
            }
            else
            {
                $userToSendMessagesFrom     = BaseJobControlUserConfigUtil::getUserToRunAs();
                $sender->fromAddress        = Yii::app()->emailHelper->resolveFromAddressByUser($userToSendMessagesFrom);
                $sender->fromName           = strval($userToSendMessagesFrom);
            }
            return $sender;
        }

        protected static function resolveRecipient(EmailMessage $emailMessage, Contact $contact)
        {
            if ($contact->primaryEmail->emailAddress !== null)
            {
                $recipient                  = new EmailMessageRecipient();
                $recipient->toAddress       = $contact->primaryEmail->emailAddress;
                $recipient->toName          = strval($contact);
                $recipient->type            = EmailMessageRecipient::TYPE_TO;
                $recipient->personOrAccount = $contact;
                $emailMessage->recipients->add($recipient);
            }
        }

        protected static function resolveAttachments(EmailMessage $emailMessage, Item $itemOwnerModel)
        {
            if (!empty($itemOwnerModel->files))
            {
                foreach ($itemOwnerModel->files as $file)
                {
                    $emailMessage->files->add($file);
                }
            }
        }

        protected static function resolveHeaders(EmailMessage $emailMessage, $zurmoItemId, $zurmoItemClass, $zurmoPersonId)
        {
            $headers            = compact('zurmoItemId', 'zurmoItemClass', 'zurmoPersonId');
            $returnPathHeader   = static::resolveReturnPathHeaderValue();
            if ($returnPathHeader)
            {
                $headers['Return-Path'] = $returnPathHeader;
            }
            $emailMessage->headers  = serialize($headers);
        }

        protected static function resolveReturnPathHeaderValue()
        {
            return ZurmoConfigurationUtil::getByModuleName('EmailMessagesModule', 'bounceReturnPath');
        }

        protected static function markItemAsProcessed($item)
        {
            $item->processed   = 1;
            return $item->unrestrictedSave();
        }

        protected static function resolveItemOwnerModelRelationName($itemClass)
        {
            if ($itemClass == 'AutoresponderItem')
            {
                return 'autoresponder';
            }
            else
            {
                return 'campaign';
            }
        }

        protected static function resolveEmailBoxName($itemOwnerModelClassName)
        {
            if ($itemOwnerModelClassName == "Autoresponder")
            {
                return EmailBox::AUTORESPONDERS_NAME;
            }
            else
            {
                return EmailBox::CAMPAIGNS_NAME;
            }
        }
    }
?>