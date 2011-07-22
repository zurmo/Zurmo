<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2011 Zurmo Inc.
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
     * A view that displays a list of currency models in the application.
     *
     */
    class CurrenciesCollectionView extends MetadataView
    {
        protected $controllerId;

        protected $moduleId;

        protected $currencies;

        public function __construct($controllerId, $moduleId, $currencies)
        {
            assert('is_string($controllerId)');
            assert('is_string($moduleId)');
            assert('is_array($currencies)');
            $this->controllerId           = $controllerId;
            $this->moduleId               = $moduleId;
            $this->currencies             = $currencies;
        }

        protected function renderContent()
        {
            $content  = '<div class="horizontal-line">';
            $content .= $this->renderActionElementBar(false);
            $content .= '</div>' . "\n";
            $content .= '<table>';
            $content .= '<colgroup>';
            $content .= '<col style="width:40%" /><col style="width:40%" /><col style="width:20%" />';
            $content .= '</colgroup>';
            $content .= '<tbody>';
            $content .= '<tr><th>' . Yii::t('Default', 'Code') . '</th>';
            $content .= '<th>' . Yii::t('Default', 'Rate to') . '&#160;' . Yii::app()->currencyHelper->getBaseCode(). '</th>';
            $content .= '<th>&#160;</th>';
            $content .= '</tr>';
            foreach ($this->currencies as $currency)
            {
                $route = $this->moduleId . '/' . $this->controllerId . '/delete/';
                $content .= '<tr>';
                $content .= '<td>' . $currency->code . '</td>';
                $content .= '<td>' . $currency->rateToBase . '</td>';
                $content .= '<td>';
                if (count($this->currencies) == 1 || CurrencyValue::isCurrencyInUseById($currency->id))
                {
                    $content .= '&#160;';
                }
                else
                {
                    $content .= CHtml::link(Yii::t('Default', 'Remove'), Yii::app()->createUrl($route,
                                            array('id' => $currency->id)));
                }
                $content .= '</td>';
                $content .= '</tr>';
            }
            $content .= '</tbody>';
            $content .= '</table>';
            return $content;
        }

        public static function getDefaultMetadata()
        {
            $metadata = array(
                'global' => array(
                ),
            );
            return $metadata;
        }

        public function isUniqueToAPage()
        {
            return true;
        }
    }
?>