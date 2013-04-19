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

    class MinimalDynamicLabelMbMenu extends MbMenu
    {
        // TODO: @Shoaibi: Low: Refactor this and MbMenu
        protected function renderMenuRecursive($items)
        {
            foreach ($items as $item)
            {
                $liClose    = null;
                $rendered   = false;
                if (!array_key_exists('renderHeader', $item) || $item['renderHeader'])
                {
                    $rendered   = true;
                    $liClose    = ZurmoHtml::closeTag('li') . "\n";
                    echo ZurmoHtml::openTag('li', isset($item['itemOptions']) ? $item['itemOptions'] : array());
                    if (isset($item['linkOptions']))
                    {
                         $htmlOptions = $item['linkOptions'];
                    }
                    else
                    {
                        $htmlOptions = array();
                    }
                    if (!empty($item['label']))
                    {
                        $resolvedLabelContent = $this->renderLabelPrefix() . ZurmoHtml::tag('span', array(), $item['label']);
                    }
                    else
                    {
                        $resolvedLabelContent = static::resolveAndGetSpanAndDynamicLabelContent($item);
                    }
                    if ((isset($item['ajaxLinkOptions'])))
                    {
                        echo ZurmoHtml::ajaxLink($resolvedLabelContent, $item['url'], $item['ajaxLinkOptions'], $htmlOptions);
                    }
                    elseif (isset($item['url']))
                    {
                        echo ZurmoHtml::link($this->renderLinkPrefix() . $resolvedLabelContent, $item['url'], $htmlOptions);
                    }
                    else
                    {
                        if (!empty($item['label']))
                        {
                            echo ZurmoHtml::link($resolvedLabelContent, "javascript:void(0);", $htmlOptions);
                        }
                        else
                        {
                            echo $resolvedLabelContent;
                        }
                    }
                }
                if (isset($item['items']) && count($item['items']))
                {
                    $nestedUlOpen   = null;
                    $nestedUlClose  = null;
                    if ($rendered)
                    {
                        $nestedUlOpen   = "\n" . ZurmoHtml::openTag('ul', $this->submenuHtmlOptions) . "\n";
                        $nestedUlClose  = ZurmoHtml::closeTag('ul') . "\n";
                    }
                    echo $nestedUlOpen;
                    $this->renderMenuRecursive($item['items']);
                    echo $nestedUlClose;
                }
                echo $liClose;
            }
        }

        protected static function resolveAndGetSpanAndDynamicLabelContent($item)
        {
            if (isset($item['dynamicLabelContent']))
            {
                return $item['dynamicLabelContent'];
            }
        }

        protected function resolveNavigationClass()
        {
            if (!Yii::app()->userInterface->isMobile())
            {
                parent::resolveNavigationClass();
            }
        }
    }
?>
