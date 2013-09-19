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
     * A view that displays a list of currency models in the application.
     *
     */
    class MarketingListsManageSubscriptionsListView extends MetadataView
    {
        protected $controllerId;

        protected $moduleId;

        protected $marketingLists;

        protected $personId;

        protected $sourceMarketingListId;

        protected $modelId;

        protected $modelType;

        const TOGGLE_UNSUBSCRIPTION_LINK_CLASS = 'marketingListsManageSubscriptionListView-toggleUnsubscribed';

        public function __construct($controllerId, $moduleId, $marketingLists, $personId,
                                                                        $sourceMarketingListId, $modelId, $modelType)
        {
            assert('is_string($controllerId)');
            assert('is_string($moduleId)');
            assert('is_array($marketingLists)');
            $this->controllerId             = $controllerId;
            $this->moduleId                 = $moduleId;
            $this->marketingLists           = $marketingLists;
            $this->personId                 = $personId;
            $this->sourceMarketingListId    = $sourceMarketingListId;
            $this->modelId                  = $modelId;
            $this->modelType                = $modelType;
        }

        public function isUniqueToAPage()
        {
            return true;
        }

        public function getTitle()
        {
            $applicationName    = ZurmoConfigurationUtil::getByModuleName('ZurmoModule', 'applicationName');
            if ($applicationName != null)
            {
                $applicationName = ' - ' . $applicationName;
            }
            return Zurmo::t('MarketingListsModule', 'My Subscriptions') . $applicationName;
        }

        protected function renderContent()
        {
            $this->renderScripts();
            $titleContent       = $this->renderTitleContent();
            $bodyContent        = $this->renderMarketingListsList();
            $content            = $titleContent . ZurmoHtml::tag('div', array('class' => 'wide',
                                                                      'id' => 'marketingLists-manageSubscriptionsList'),
                                                                $bodyContent);
            $content            = ZurmoHtml::tag('div', array('class' => 'left-column full-width'), $content);
            $content            = ZurmoHtml::tag('div', array('class' => 'wrapper'), $content);
            return $content;
        }

        protected function renderMarketingListsList()
        {
            $colGroupContent    = ZurmoHtml::openTag('colgroup');
            $colGroupContent    .= ZurmoHtml::tag('col', array('style' => 'width:20%'));
            $colGroupContent    .= ZurmoHtml::tag('col', array('style' => 'width:80%'));
            $colGroupContent    .= ZurmoHtml::closeTag('colgroup');
            $rowsContentArray = array();
            foreach ($this->marketingLists as $marketingList)
            {
                $marketingListModel = $marketingList['model'];
                $subscribed         = $marketingList['subscribed'];
                $columnsContent     = ZurmoHtml::tag('td', array(), $this->renderToggleSubscriptionSwitch(
                                                                                                $marketingListModel->id,
                                                                                                $subscribed));
                $columnsContent     .= ZurmoHtml::tag('td', array(), strval($marketingListModel));
                $rowsContentArray[]    = ZurmoHtml::tag('tr', array(), $columnsContent);
            }
            $linkColumnsContent     = ZurmoHtml::tag('td', array(), $this->renderUnsubscribeAllLink());
            $linkColumnsContent     .= ZurmoHtml::tag('td');
            $rowsContentArray[]     = ZurmoHtml::tag('tr', array(), $linkColumnsContent);
            $rowsContent            = implode("\n", $rowsContentArray);
            $content                = $colGroupContent . $rowsContent;
            $tableContent           = ZurmoHtml::tag('table', array(), $content);
            return $tableContent;
        }

        protected function renderUnsubscribeAllLink()
        {
            $title      = Zurmo::t('MarketingListsModule', 'Unsubscribe All/OptOut');
            $hash       = Yii::app()->request->getQuery('hash');
            $url        = Yii::app()->createUrl('/marketingLists/external/optOut', array('hash' => $hash));
            $options    = array('class' => 'simple-link ' . static::TOGGLE_UNSUBSCRIPTION_LINK_CLASS);
            $link       = ZurmoHtml::link($title, $url, $options);
            return $link;
        }

        protected function renderToggleSubscriptionSwitch($marketingListId, $subscribed)
        {
            $template           = ZurmoHtml::tag('div', array('class' => 'switch-state clearfix'), '{input}{label}');
            $createNewActivity  = false;
            if ($marketingListId == $this->sourceMarketingListId)
            {
                $createNewActivity  = true;
            }
            $hash               = EmailMessageActivityUtil::resolveHashForUnsubscribeAndManageSubscriptionsUrls($this->personId, $marketingListId,
                                                                $this->modelId, $this->modelType, $createNewActivity);
            $subscribeUrl       = $this->getSubscribeUrlByHash($hash);
            $unsubscribeUrl     = $this->getUnsubscribeUrlByHash($hash);
            $checkedValue       = $subscribeUrl;
            if (!$subscribed)
            {
                $checkedValue   = $unsubscribeUrl;
            }
            $baseId             = static::TOGGLE_UNSUBSCRIPTION_LINK_CLASS . '_' . $marketingListId;
            $content = ZurmoHTML::radioButtonList(
                $baseId,
                $checkedValue,
                self::getDropDownArray($subscribeUrl, $unsubscribeUrl),
                array('separator' => '', 'template'  => $template));
            return ZurmoHtml::tag('div', array('class' => 'switch'), $content);
        }

        protected function getSubscribeUrlByHash($hash)
        {
            return $this->getSubscribeOrUnsubscribeUrlByHash($hash, 1);
        }

        protected function getUnsubscribeUrlByHash($hash)
        {
            return $this->getSubscribeOrUnsubscribeUrlByHash($hash, 0);
        }

        protected function getSubscribeOrUnsubscribeUrlByHash($hash, $subscribe = true)
        {
            $action = 'subscribe';
            if (!$subscribe)
            {
                $action = 'un' . $action;
            }
            return Yii::app()->createUrl('/marketingLists/external/' . $action, array('hash' => $hash));
        }

        public static function getDropDownArray($subscribeUrl, $unsubscribeUrl)
        {
            return array($subscribeUrl => Zurmo::t('Core', 'Subscribe'),
                        $unsubscribeUrl => Zurmo::t('Core', 'Unsubcribe'));
        }

        protected function renderScripts()
        {
            $this->renderToggleUnsubscribeScript();
        }

        protected function renderToggleUnsubscribeScript()
        {
            $scriptName = static::TOGGLE_UNSUBSCRIPTION_LINK_CLASS;
            if (Yii::app()->clientScript->isScriptRegistered($scriptName))
            {
                return;
            }
            else
            {
                Yii::app()->clientScript->registerScript($scriptName, '
                $("input:radio").unbind("change.toggleUnsubscribe")
                    .bind("change.toggleUnsubscribe", function (event)
                {
                    window.location.href = ($(this)).val();
                });');
            }
        }
    }
?>