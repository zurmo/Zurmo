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

    class AutoresponderSubjectListViewColumnAdapter extends TextListViewColumnAdapter
    {
        public function renderGridViewData()
        {
            $className  = get_class($this);
            $value      = $className . '::resolveSubjectAndMetricsSummary($data, "' . $this->view->redirectUrl . '")';
            return array(
                'name'  => 'Name',
                'value' => $value,
                'type'  => 'raw',
            );
        }

        /**
         * @param Autoresponder $autoresponder
         * @param string $redirectUrl
         * @return string $content
         */
        public static function resolveSubjectAndMetricsSummary(Autoresponder $autoresponder, $redirectUrl)
        {
            $content  = static::resolveSubjectWithRedirectURl($autoresponder->subject, $autoresponder->id, $redirectUrl);
            $content .= static::renderExtraInfoContent($autoresponder);
            $content = ZurmoHtml::tag('div', array('class' => 'autoresponder-extra-info'), $content);
            $content .= static::renderMetricsContent($autoresponder);
            return $content;
        }

        public static function resolveSubjectWithRedirectURl($subject, $id, $redirectUrl)
        {
            $url = Yii::app()->createUrl('/autoresponders/default/edit',
                                                                array('id' => $id, 'redirectUrl' => $redirectUrl));
            return ZurmoHtml::link($subject, $url, array('class' => 'edit-autoresponder-link'));
        }

        protected static function renderExtraInfoContent(Autoresponder $autoresponder)
        {
            $operationValuesAndLabels   = Autoresponder::getOperationTypeDropDownArray();
            $durationTypeValueAndLabels = TimeDurationUtil::getValueAndLabels();
            if (!isset($operationValuesAndLabels[$autoresponder->operationType]))
            {
                return;
            }
            if (!isset($durationTypeValueAndLabels[$autoresponder->fromOperationDurationType]))
            {
                return;
            }
            if ($autoresponder->fromOperationDurationInterval == 0)
            {
                return Zurmo::t('AutorespondersModule', 'Send immediately after {operation}',
                                array('{operation}' => $operationValuesAndLabels[$autoresponder->operationType]));
            }
            else
            {
                $content = Zurmo::t('AutorespondersModule', 'Send {interval} {type} after {operation}',
                                    array('{interval}'  => $autoresponder->fromOperationDurationInterval,
                                          '{type}'      => $durationTypeValueAndLabels[$autoresponder->fromOperationDurationType],
                                          '{operation}' => $operationValuesAndLabels[$autoresponder->operationType]));
                return $content;
            }
        }

        protected static function renderMetricsContent(Autoresponder $autoresponder)
        {
            $dataProvider = new AutoresponderGroupedChartDataProvider($autoresponder);
            $data = $dataProvider->getChartData();
            $sentQuantity         = Yii::app()->numberFormatter->formatDecimal((int)$data[MarketingChartDataProvider::SENT]);
            $openQuantity         = Yii::app()->numberFormatter->formatDecimal((int)$data[MarketingChartDataProvider::UNIQUE_OPENS]);
            $openRate             = round(NumberUtil::divisionForZero($openQuantity, $sentQuantity) * 100, 2);
            $clickQuantity        = Yii::app()->numberFormatter->formatDecimal((int)$data[MarketingChartDataProvider::UNIQUE_CLICKS]);
            $clickRate            = round(NumberUtil::divisionForZero($clickQuantity, $sentQuantity) * 100, 2);
            $unsubscribedQuantity = Yii::app()->numberFormatter->formatDecimal((int)$data[MarketingChartDataProvider::UNSUBSCRIBED]);
            $unsubscribedRate     = round(NumberUtil::divisionForZero($unsubscribedQuantity, $sentQuantity) * 100, 2);
            $bouncedQuantity      = Yii::app()->numberFormatter->formatDecimal((int)$data[MarketingChartDataProvider::BOUNCED]);
            $bouncedRate          = round(NumberUtil::divisionForZero($bouncedQuantity, $sentQuantity) * 100, 2);

            $content = null;
            $content .= ZurmoHtml::tag('div', array('class' => 'autoresponder-stats'),
                                        Zurmo::t('MarketingModule', '{quantity} sent',
                                        array('{quantity}' => ZurmoHtml::tag('strong', array(), $sentQuantity))));
            $content .= ZurmoHtml::tag('div', array('class' => 'autoresponder-stats'),
                                        Zurmo::t('MarketingModule', '{quantity} opens ({openRate}%)',
                                        array('{quantity}' => ZurmoHtml::tag('strong', array(), $openQuantity),
                                              '{openRate}' => ZurmoHtml::tag('span', array(), $openRate))));
            $content .= ZurmoHtml::tag('div', array('class' => 'autoresponder-stats'),
                                        Zurmo::t('MarketingModule', '{quantity} unique clicks ({clickRate}%)',
                                        array('{quantity}' => ZurmoHtml::tag('strong', array(), $clickQuantity),
                                              '{clickRate}' => ZurmoHtml::tag('span', array(), $clickRate))));
            $content .= ZurmoHtml::tag('div', array('class' => 'autoresponder-stats'),
                                        Zurmo::t('MarketingModule', '{quantity} Unsubscribed ({unsubscribedRate}%)',
                                        array('{quantity}' => ZurmoHtml::tag('strong', array(), $unsubscribedQuantity),
                                              '{unsubscribedRate}' => ZurmoHtml::tag('span', array(), $unsubscribedRate))));
            $content .= ZurmoHtml::tag('div', array('class' => 'autoresponder-stats'),
                                        Zurmo::t('MarketingModule', '{quantity} Bounces ({bouncedRate}%)',
                                        array('{quantity}' => ZurmoHtml::tag('strong', array(), $bouncedQuantity),
                                              '{bouncedRate}' => ZurmoHtml::tag('span', array(), $bouncedRate))));
            return $content;
        }
    }
?>