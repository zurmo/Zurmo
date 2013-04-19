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
     * Element used to display text and html areas on EmailTemplateEditAndDetailsView
     */
    class EmailTemplateHtmlAndTextContentElement extends Element implements DerivedElementInterface
    {
        const HTML_CONTENT_INPUT_NAME = 'htmlContent';

        const TEXT_CONTENT_INPUT_NAME = 'textContent';

        public static function getModelAttributeNames()
        {
            return array(
                static::HTML_CONTENT_INPUT_NAME,
                static::TEXT_CONTENT_INPUT_NAME,
            );
        }

        public static function renderModelAttributeLabel($name)
        {
            $labels = static::renderLabels();
            return $labels[$name];
        }

        protected static function renderLabels()
        {
            $labels = array(Zurmo::t('EmailTemplatesModule', 'Html Content'),
                            Zurmo::t('EmailTemplatesModule', 'Text Content'));
            return array_combine(static::getModelAttributeNames(), $labels);
        }

        protected function renderHtmlContentAreaLabel()
        {
            return static::renderModelAttributeLabel(static::HTML_CONTENT_INPUT_NAME);
        }

        protected function renderTextContentAreaLabel()
        {
            return static::renderModelAttributeLabel(static::TEXT_CONTENT_INPUT_NAME);
        }

        protected function resolveTabbedContent($plainTextContent, $htmlContent)
        {
            $this->registerTabbedContentScripts();
            $textTabHyperLink   = ZurmoHtml::link($this->renderTextContentAreaLabel(), '#tab1', array('class' => 'active-tab'));
            $htmlTabHyperLink   = ZurmoHtml::link($this->renderHtmlContentAreaLabel(), '#tab2');
            $tagsGuideLink      = null;
            if ($this->form)
            {
                $controllerId           = Yii::app()->getController()->getId();
                $moduleId               = Yii::app()->getController()->getModule()->getId();
                $modelId                = $this->model->id;
                $params                 = array('htmlOptions' => array('id' => 'mergetag-guide', 'class' => 'simple-link'));
                $tagsGuideLinkElement   = new MergeTagGuideAjaxLinkActionElement($controllerId, $moduleId, $modelId, $params);
                $tagsGuideLink          = $tagsGuideLinkElement->render();
            }
            $tabContent         = ZurmoHtml::tag('div', array('class' => 'tabs-nav'), $textTabHyperLink . $htmlTabHyperLink . $tagsGuideLink);

            $plainTextDiv       = ZurmoHtml::tag('div',
                                                array('id' => 'tab1',
                                                      'class' => 'active-tab tab email-template-' . static::TEXT_CONTENT_INPUT_NAME),
                                                $plainTextContent);
            $htmlContentDiv     = ZurmoHtml::tag('div',
                                                array('id' => 'tab2',
                                                      'class' => 'tab email-template-' . static::HTML_CONTENT_INPUT_NAME),
                                                $htmlContent);
            return ZurmoHtml::tag('div', array('class' => 'email-template-content'), $tabContent . $plainTextDiv . $htmlContentDiv);
        }

        protected function registerTabbedContentScripts()
        {
            Yii::app()->clientScript->registerScript('email-templates-tab-switch-handler', "
                    $('.tabs-nav a:not(.simple-link)').click( function()
                    {
                        //the menu items
                        $('.active-tab', $(this).parent()).removeClass('active-tab');
                        $(this).addClass('active-tab');
                        //the sections
                        var _old = $('.tab.active-tab'); //maybe add context here for tab-container
                        _old.fadeToggle();
                        var _new = $( $(this).attr('href') );
                        _new.fadeToggle(150, 'linear', function()
                        {
                                _old.removeClass('active-tab');
                                _new.addClass('active-tab');
                        });
                        return false;
                    });
                ");
        }

        protected function renderControlNonEditable()
        {
            assert('$this->attribute == null');
            return $this->resolveTabbedContent($this->model->textContent, $this->model->htmlContent);
        }

        protected function renderControlEditable()
        {
            return $this->resolveTabbedContent($this->renderTextContentArea(), $this->renderHtmlContentArea());
        }

        // REVIEW : @Shoaibi Create a HTML element out of it.
        protected function renderHtmlContentArea()
        {
            $id                      = $this->getEditableInputId(static::HTML_CONTENT_INPUT_NAME);
            $htmlOptions             = array();
            $htmlOptions['id']       = $id;
            $htmlOptions['name']     = $this->getEditableInputName(static::HTML_CONTENT_INPUT_NAME);
            $cClipWidget             = new CClipWidget();
            $cClipWidget->beginClip("Redactor");
            $cClipWidget->widget('application.core.widgets.Redactor', array(
                                        'htmlOptions' => $htmlOptions,
                                        'content'     => $this->model->htmlContent,
                                ));
            $cClipWidget->endClip();
            $content                 = ZurmoHtml::label($this->renderHtmlContentAreaLabel(), $id);
            $content                .= $cClipWidget->getController()->clips['Redactor'];
            $content                .= $this->renderHtmlContentAreaError();
            return $content;
        }

         protected function renderTextContentArea()
         {
            $textContentElement                         = new TextAreaElement($this->model, static::TEXT_CONTENT_INPUT_NAME, $this->form);
            $textContentElement->editableTemplate       = $this->editableTemplate;
            return $textContentElement->render();
         }

        protected function renderHtmlContentAreaError()
        {
            if (strpos($this->editableTemplate, '{error}') !== false)
            {
                return $this->form->error($this->model, static::HTML_CONTENT_INPUT_NAME);
            }
            else
            {
                return null;
            }
        }

        protected function renderLabel()
        {
            return null;
        }

        protected function renderError()
        {
            return null;
        }
     }
?>
