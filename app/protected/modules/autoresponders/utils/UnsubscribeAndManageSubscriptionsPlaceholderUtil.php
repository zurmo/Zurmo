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

    class UnsubscribeAndManageSubscriptionsPlaceholderUtil
    {
        const CONFIG_KEY_PLAIN                      = 'AutoresponderOrCampaignFooterPlainText';

        const CONFIG_KEY_RICH_TEXT                  = 'AutoresponderOrCampaignFooterRichText';

        const CONFIG_MODULE_NAME                    = 'AutorespondersModule';

        const UNSUBSCRIBE_URL_PLACEHOLDER           = '{{UNSUBSCRIBE_URL}}';

        const MANAGE_SUBSCRIPTIONS_URL_PLACEHOLDER  = '{{MANAGE_SUBSCRIPTIONS_URL}}';

        /**
         * @param $isHtmlContent
         * @param bool $returnDefault
         * @return configuration|string
         */
        public static function getContentByType($isHtmlContent, $returnDefault = true)
        {
            assert('is_bool($isHtmlContent)');
            $key        = static::resolveConfigKeyByContentType((bool) $isHtmlContent);
            $content    = ZurmoConfigurationUtil::getByModuleName(static::CONFIG_MODULE_NAME, $key);
            if (empty($content) && $returnDefault)
            {
                $content = static::resolveDefaultValue($isHtmlContent);
            }
            return $content;
        }

        /**
         * @param string $content
         * @param bool $isHtmlContent
         */
        public static function setContentByType($content, $isHtmlContent)
        {
            assert('is_string($content)');
            assert('is_bool($isHtmlContent)');
            $key        = static::resolveConfigKeyByContentType((bool) $isHtmlContent);
            ZurmoConfigurationUtil::setByModuleName(static::CONFIG_MODULE_NAME, $key, $content);
        }

        protected static function resolveConfigKeyByContentType($isHtmlContent)
        {
            assert('is_bool($isHtmlContent)');
            if ($isHtmlContent)
            {
                return static::CONFIG_KEY_RICH_TEXT;
            }
            else
            {
                return static::CONFIG_KEY_PLAIN;
            }
        }

        protected static function resolveDefaultValue($isHtmlContent)
        {
            $unsubscribeUrlPlaceHolder          = static::UNSUBSCRIBE_URL_PLACEHOLDER;
            $manageSubscriptionsUrlPlaceHolder  = static::MANAGE_SUBSCRIPTIONS_URL_PLACEHOLDER;
            StringUtil::prependNewLine($unsubscribeUrlPlaceHolder, $isHtmlContent);
            StringUtil::prependNewLine($manageSubscriptionsUrlPlaceHolder, $isHtmlContent);
            $content     = $unsubscribeUrlPlaceHolder . $manageSubscriptionsUrlPlaceHolder;
            return $content;
        }
    }
?>