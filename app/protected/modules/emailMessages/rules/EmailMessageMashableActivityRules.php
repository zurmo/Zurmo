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
     * Generic rules for the email message model.
     */
    class EmailMessageMashableActivityRules extends MashableActivityRules
    {
        public function resolveSearchAttributesDataByRelatedItemId($relationItemId)
        {
            assert('is_int($relationItemId)');
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName'        => 'sender',
                    'relatedAttributeName' => 'personOrAccount',
                    'operatorType'         => 'equals',
                    'value'                => $relationItemId,
                ),
                2 => array(
                    'attributeName'        => 'recipients',
                    'relatedAttributeName' => 'personOrAccount',
                    'operatorType'         => 'equals',
                    'value'                => $relationItemId,
                )
            );
            $searchAttributeData['structure'] = '(1 or 2)';
            return $this->resolveSearchAttributeDataForLatestActivities($searchAttributeData);
        }

        public function resolveSearchAttributesDataByRelatedItemIds($relationItemIds)
        {
            assert('is_array($relationItemIds)');
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName'        => 'sender',
                    'relatedAttributeName' => 'personOrAccount',
                    'operatorType'         => 'oneOf',
                    'value'                => $relationItemIds,
                ),
                2 => array(
                    'attributeName'        => 'recipients',
                    'relatedAttributeName' => 'personOrAccount',
                    'operatorType'         => 'oneOf',
                    'value'                => $relationItemIds,
                )
            );
            $searchAttributeData['structure'] = '1 or 2';
            return $this->resolveSearchAttributeDataForLatestActivities($searchAttributeData);
        }

        public function resolveSearchAttributeDataForLatestActivities($searchAttributeData)
        {
            assert('is_array($searchAttributeData)');
            return $searchAttributeData;
        }

        public function getLatestActivitiesOrderByAttributeName()
        {
            return 'modifiedDateTime';
        }

        public function getLatestActivityExtraDisplayStringByModel($model)
        {
            return FileModelDisplayUtil::renderFileDataDetailsWithDownloadLinksContent($model, 'files');
        }

        /**
         * (non-PHPdoc)
         * @see MashableActivityRules::getSummaryContentTemplate()
         */
        public function getSummaryContentTemplate($ownedByFilter, $viewModuleClassName)
        {
            assert('is_string($ownedByFilter)');
            assert('is_string($viewModuleClassName)');
            return "<span class='less-pronounced-text'>" .
                   "{relatedModelsByImportanceContent} " .
                   "</span><br/><span>{modelStringContent}</span><span>{extraContent}</span>";
        }

        public function renderRelatedModelsByImportanceContent(RedBeanModel $model)
        {
            $content = null;
            if ($model->sender != null  && $model->sender->id > 0)
            {
                $content .= Zurmo::t('EmailMessagesModule', '<span class="email-from"><strong>From:</strong> {senderContent}</span>',
                                    array('{senderContent}' => static::getSenderContent($model->sender)));
            }
            if ($model->recipients->count() > 0)
            {
                if ($content != null)
                {
                    $content .= ' ';
                }
                $content .= Zurmo::t('EmailMessagesModule', '<span class="email-to"><strong>To:</strong> {recipientContent}</span>',
                                    array('{recipientContent}' => static::getRecipientsContent($model->recipients)));
            }
            return $content;
        }

        public static function getSenderContent(EmailMessageSender $emailMessageSender)
        {
            $existingModels  = array();
            if ($emailMessageSender->personOrAccount->id < 0)
            {
                return $emailMessageSender->fromAddress . ' ' . $emailMessageSender->fromName;
            }
            $castedDownModel = self::castDownItem($emailMessageSender->personOrAccount);
            try
            {
                if (strval($castedDownModel) != null)
                            {
                                $params          = array('label' => strval($castedDownModel), 'wrapLabel' => false);
                                $moduleClassName = $castedDownModel->getModuleClassName();
                                $moduleId        = $moduleClassName::getDirectoryName();
                                $element         = new DetailsLinkActionElement('default', $moduleId,
                                                                                $castedDownModel->id, $params);
                                $existingModels[] = $element->render();
                            }
                return self::resolveStringValueModelsDataToStringContent($existingModels);
            }
            catch (AccessDeniedSecurityException $e)
            {
                return $emailMessageSender->fromAddress;
            }
        }

        public static function getRecipientsContent(RedBeanOneToManyRelatedModels $recipients, $type = null)
        {
            assert('$type == null || $type == EmailMessageRecipient::TYPE_TO ||
                    EmailMessageRecipient::TYPE_CC || EmailMessageRecipient::TYPE_BCC');
            $existingModels  = array();
            if ($recipients->count() == 0)
            {
                return;
            }
            foreach ($recipients as $recipient)
            {
                if ($type == null || $recipient->type == $type)
                {
                    if ($recipient->personOrAccount->id < 0)
                    {
                        $existingModels[] = $recipient->toAddress . ' ' . $recipient->toName;
                    }
                    else
                    {
                        $castedDownModel = self::castDownItem($recipient->personOrAccount);
                        try
                        {
                            if (strval($castedDownModel) != null)
                                        {
                                            $params          = array('label' => strval($castedDownModel), 'wrapLabel' => false);
                                            $moduleClassName = $castedDownModel->getModuleClassName();
                                            $moduleId        = $moduleClassName::getDirectoryName();
                                            $element         = new DetailsLinkActionElement('default', $moduleId,
                                                                                            $castedDownModel->id, $params);
                                            $existingModels[] = $element->render();
                                        }
                        }
                        catch (AccessDeniedSecurityException $e)
                        {
                            $existingModels[] = $recipient->toAddress . ' ' . $recipient->toName;
                        }
                    }
                }
            }
            return self::resolveStringValueModelsDataToStringContent($existingModels);
        }

        protected static function castDownItem(Item $item)
        {
            foreach (array('Contact', 'User', 'Account') as $modelClassName)
            {
                try
                {
                    $modelDerivationPathToItem = RuntimeUtil::getModelDerivationPathToItem($modelClassName);
                    return $item->castDown(array($modelDerivationPathToItem));
                }
                catch (NotFoundException $e)
                {
                }
            }
            throw new NotSupportedException();
        }
    }
?>