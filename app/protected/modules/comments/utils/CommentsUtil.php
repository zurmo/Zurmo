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
     * Helper class for working with comments
     */
    class CommentsUtil
    {
        public static function sendNotificationOnNewComment(RedBeanModel $relatedModel, Comment $comment, $senderPerson, $peopleToSendNotification)
        {
            if (count($peopleToSendNotification) > 0)
            {
                $emailRecipients = array();
                foreach ($peopleToSendNotification as $people)
                {
                    if ($people->primaryEmail->emailAddress !== null &&
                    !UserConfigurationFormAdapter::resolveAndGetValue($people, 'turnOffEmailNotifications'))
                    {
                        $emailRecipients[] = $people;
                    }
                }
                $subject = self::getEmailSubject($relatedModel);
                $content = self::getEmailContent($relatedModel, $comment, $senderPerson);
                if ($emailRecipients > 0)
                {
                    EmailNotificationUtil::resolveAndSendEmail($senderPerson, $emailRecipients, $subject, $content);
                }
                else
                {
                    return;
                }
            }
            else
            {
                return;
            }
        }

        public static function getEmailContent(RedBeanModel $model, Comment $comment, User $user)
        {
            $emailContent  = new EmailMessageContent();
            $url           = static::getUrlToEmail($model);
            $textContent   = Zurmo::t('CommentsModule', "Hello, {lineBreak} {updaterName} added a new comment to the " .
                                             "{strongStartTag}{modelName}{strongEndTag}: {lineBreak}" .
                                             "\"{commentDescription}.\" {lineBreak}{lineBreak} {url} ",
                                    array('{lineBreak}'           => "\n",
                                          '{strongStartTag}'      => null,
                                          '{strongEndTag}'        => null,
                                          '{updaterName}'         => strval($user),
                                          '{modelName}'           => $model->getModelLabelByTypeAndLanguage(
                                                                     'SingularLowerCase'),
                                          '{commentDescription}'  => strval($comment),
                                          '{url}'                 => ZurmoHtml::link($url, $url)
                                        ));
            $emailContent->textContent  = EmailNotificationUtil::
                                                resolveNotificationTextTemplate($textContent);
            $htmlContent = Zurmo::t('CommentsModule', "Hello, {lineBreak} {updaterName} added a new comment to the " .
                                             "{strongStartTag}{url}{strongEndTag}: {lineBreak}" .
                                             "\"{commentDescription}.\"",
                               array('{lineBreak}'           => "<br/>",
                                     '{strongStartTag}'      => '<strong>',
                                     '{strongEndTag}'        => '</strong>',
                                     '{updaterName}'         => strval($user),
                                     '{commentDescription}'  => strval($comment),
                                     '{url}'                 => ZurmoHtml::link($model->getModelLabelByTypeAndLanguage(
                                                                'SingularLowerCase'), $url)
                                   ));
            $emailContent->htmlContent  = EmailNotificationUtil::resolveNotificationHtmlTemplate($htmlContent);
            return $emailContent;
        }

        public static function getEmailSubject($model)
        {
            if ($model instanceof Conversation || $model instanceof Mission)
            {
                return Zurmo::t('CommentsModule', 'New comment on {modelName}: {subject}',
                                    array('{subject}'   => strval($model),
                                          '{modelName}' => $model->getModelLabelByTypeAndLanguage('SingularLowerCase')));
            }
        }

        public static function getUrlToEmail($model)
        {
            if ($model instanceof Conversation)
            {
                return ConversationParticipantsUtil::getUrlToConversationDetailAndRelationsView($model->id);
            }
            elseif ($model instanceof Mission)
            {
                return Yii::app()->createAbsoluteUrl('missions/default/details/', array('id' => $model->id));
            }
        }
    }
?>