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

    abstract class SubmitButtonActionElement extends ActionElement
    {
        protected $formRequiredToUse = true;

        public function render()
        {
            $htmlOptions = $this->getHtmlOptions();
            $request     = Yii::app()->getRequest();
            if ($request->enableCsrfValidation && isset($htmlOptions['csrf']) && $htmlOptions['csrf'])
            {
                $htmlOptions['params'][$request->csrfTokenName] = $request->getCsrfToken();
            }
            if (isset($htmlOptions['params']))
            {
                $params = CJavaScript::encode($htmlOptions['params']);
                unset($htmlOptions['params']);
            }
            else
            {
                $params = '{}';
            }
            if (isset($htmlOptions['class']))
            {
                $htmlOptions['class']  .= ' z-button';
            }
            else
            {
                $htmlOptions['class']   = 'z-button';
            }
            $cs = Yii::app()->getClientScript();
            $cs->registerCoreScript('jquery');
            $cs->registerCoreScript('yii');
            $handler                 = "jQuery.yii.submitForm(this, '', $params); return false;";
            if (isset($htmlOptions['onclick']))
            {
                $htmlOptions['onclick']  = $htmlOptions['onclick'] . $handler;
            }
            else
            {
                $htmlOptions['onclick']  = $handler;
            }
            $aContent                = ZurmoHtml::tag('span', array('class' => 'z-spinner'), null);
            $aContent               .= ZurmoHtml::tag('span', array('class' => 'z-icon'), null);
            $aContent               .= ZurmoHtml::tag('span', array('class' => 'z-label'), $this->getLabel());
            return ZurmoHtml::link($aContent, '#', $htmlOptions);
        }
    }
?>