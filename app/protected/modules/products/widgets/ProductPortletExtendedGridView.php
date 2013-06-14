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
     * Extends the ExtendedGridView to provide additional functionality.
     * @see ExtendedGridView class
     */
    class ProductPortletExtendedGridView extends ExtendedGridView
    {
        public $params;

        /**
         * Render totals in a product portlet view
         */
        protected function renderTotalBarDetails()
        {
            $persistantProductConfigItemValue = ProductsPortletPersistentConfigUtil::getForCurrentUserByPortletIdAndKey(
                                                                                                $this->params['portletId'],
                                                                                                'filteredByStage');
            $relationModelClassName = get_class($this->params["relationModel"]);
            $relationModelId        = $this->params["relationModel"]->id;
            $relationModel          = $relationModelClassName::getById($relationModelId);
            $models                 = $relationModel->products;
            $oneTimeTotal           = 0;
            $monthlyTotal           = 0;
            $annualTotal            = 0;
            foreach ($models as $model)
            {
                if ($persistantProductConfigItemValue === null)
                {
                    $persistantProductConfigItemValue = ProductsConfigurationForm::FILTERED_BY_ALL_STAGES;
                }
                if ($persistantProductConfigItemValue != ProductsConfigurationForm::FILTERED_BY_ALL_STAGES)
                {
                    if ($model->stage->value != $persistantProductConfigItemValue)
                    {
                        continue;
                    }
                }

                if ($model->priceFrequency == ProductTemplate::PRICE_FREQUENCY_ONE_TIME)
                {
                    $oneTimeTotal += $this->getAdjustedTotalByCurrency($model);
                }

                if ($model->priceFrequency == ProductTemplate::PRICE_FREQUENCY_MONTHLY)
                {
                    $monthlyTotal += $this->getAdjustedTotalByCurrency($model);
                }

                if ($model->priceFrequency == ProductTemplate::PRICE_FREQUENCY_ANNUALLY)
                {
                    $annualTotal  += $this->getAdjustedTotalByCurrency($model);
                }
            }

            $content            = Zurmo::t("Core", "Total: ");
            $contentArray        = array();

            if ($oneTimeTotal > 0)
            {
                $contentArray[] = Yii::app()->numberFormatter->formatCurrency($oneTimeTotal,
                                                                    Yii::app()->currencyHelper->getCodeForCurrentUserForDisplay())
                                                                     . Zurmo::t("Core", " One Time");
            }
            if ($monthlyTotal > 0)
            {
                $contentArray[] = Yii::app()->numberFormatter->formatCurrency($monthlyTotal,
                                                                    Yii::app()->currencyHelper->getCodeForCurrentUserForDisplay())
                                                                     . Zurmo::t("Core", " Monthly");
            }
            if ($annualTotal > 0)
            {
                $contentArray[] = Yii::app()->numberFormatter->formatCurrency($annualTotal,
                                                                    Yii::app()->currencyHelper->getCodeForCurrentUserForDisplay())
                                                                     . Zurmo::t("Core", " Annually");
            }

            if (empty ($contentArray))
            {
                $content = '';
            }
            else
            {
                $content .= implode(', ', $contentArray);
            }
            echo $content;
        }

        /**
         * Gets the adjusted value of the price by comparing the currency to the
         * base currency
         * @param RedBeanModel $model
         * @return float
         */
        protected function getAdjustedTotalByCurrency($model)
        {
            $price = 0;
            $currentUserCurrency    = Yii::app()->currencyHelper->getActiveCurrencyForCurrentUser();
            $currency = $model->sellPrice->currency;
            if ($currency->rateToBase == $currentUserCurrency->rateToBase )
            {
                $price = $model->sellPrice->value * $model->quantity;
            }
            else
            {
                $price = (($model->sellPrice->value * $currency->rateToBase)
                                / ($currentUserCurrency->rateToBase)) * $model->quantity;
            }
            return $price;
        }
    }
?>
