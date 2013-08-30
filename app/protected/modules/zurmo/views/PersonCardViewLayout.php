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
     * Layout for the business card view for a person.
     */
    class PersonCardViewLayout
    {
        protected $model;

        public function __construct($model)
        {
            assert('$model instanceof User || $model instanceof Person');
            $this->model = $model;
        }

        public function renderContent()
        {
            $content  = $this->renderFrontOfCardContent();
            $content .= $this->renderBackOfCardContent();
            return $content;
        }

        protected function renderFrontOfCardContent()
        {
            $content  = $this->resolveAvatarContent();
            $content .= $this->resolveNameContent();
            $content .= $this->resolveBackOfCardLinkContent();
            $content .= $this->resolveJobTitleContent();
            $content .= $this->resolveDepartmentAndAccountContent();
            $content .= $this->resolveGenderAndAgeContent();
            $content .= $this->resolveSocialConnectorsContent();
            $content .= $this->resolvePhoneAndEmailContent();
            $content .= $this->resolveAddressContent();
            return $content;
        }

        protected function resolveAvatarContent()
        {
            $content = Yii::app()->dataEnhancer->getPersonAvatar($this->model);
            if ($content == null)
            {
                $htmlOptions = array('class' => 'gravatar', 'width' => '100', 'height' => '100');
                $url         = Yii::app()->theme->baseUrl . '/images/offline_user.png';
                $content     = ZurmoHtml::image($url, strval($this->model), $htmlOptions);
            }
            return $content;
        }

        protected function resolveNameContent()
        {
            $element                       = new DropDownElement($this->model, 'title', null);
            $element->nonEditableTemplate  = '{content}';
            if (StarredUtil::modelHasStarredInterface($this->model))
            {
                $starLink = StarredUtil::getToggleStarStatusLink($this->model, null);
            }
            else
            {
                $starLink = null;
            }
            $salutation                    = $element->render();
            if ($salutation != null)
            {
                $spanContent = ZurmoHtml::tag('span', array('class' => 'salutation'), $element->render());
            }
            else
            {
                $spanContent = null;
            }
            return ZurmoHtml::tag('h2', array(), $spanContent . strval($this->model) . $starLink);
        }

        protected function resolveBackOfCardLinkContent()
        {
            if (Yii::app()->dataEnhancer->personHasBackOfCard($this->model))
            {
                static::registerBackOfCardScript();
                $spanContent  = ZurmoHtml::tag('span', array('class' => 'show'), Yii::app()->dataEnhancer->getPersonBackOfCardLabel());
                $spanContent .= ZurmoHtml::tag('span', array(), Yii::app()->dataEnhancer->getPersonBackOfCardCloseLabel());
                $content      = ZurmoHtml::link($spanContent, '#', array('class' => 'toggle-back-of-card-link mini-button clearfix'));
                return $content;
            }
        }

        protected function resolveJobTitleContent()
        {
            if ($this->model->jobTitle != null)
            {
                $content  = ZurmoHtml::tag('h3', array('class' => 'position'), $this->model->jobTitle);
                return $content;
            }
        }

        protected function resolveDepartmentAndAccountContent()
        {
            $departmentAndAccountContent = null;
            if ($this->model->department != null)
            {
                $departmentAndAccountContent = $this->model->department;
            }
            if ($this->model instanceof Contact && $this->model->account->id > 0)
            {
                if ($departmentAndAccountContent != null)
                {
                    $departmentAndAccountContent .=  ' / ';
                }
                $departmentAndAccountContent .= static:: resolveAccountContentByUser($this->model->account, Yii::app()->user->userModel);
            }
            elseif ($this->model instanceof Contact)
            {
                if ($departmentAndAccountContent != null && $this->model->companyName != null)
                {
                    $departmentAndAccountContent .=  ' / ' . $this->model->companyName;
                }
                elseif ($departmentAndAccountContent == null && $this->model->companyName != null)
                {
                    $departmentAndAccountContent = $this->model->companyName;
                }
            }
            if ($departmentAndAccountContent != null)
            {
                return ZurmoHtml::tag('h4', array('class' => 'position'), $departmentAndAccountContent);
            }
        }

        protected function resolveGenderAndAgeContent()
        {
            $demographicContent = Yii::app()->dataEnhancer->getPersonDemographicViewContent($this->model);
            if ($demographicContent != null)
            {
                return ZurmoHtml::tag('div', array('class' => 'demographic-details'), $demographicContent);
            }
        }

        protected function resolveSocialConnectorsContent()
        {
            $socialContent = Yii::app()->dataEnhancer->getPersonSocialNetworksViewContent($this->model);
            if ($socialContent != null)
            {
                return ZurmoHtml::tag('div', array('class' => 'social-details'), $socialContent);
            }
        }

        protected function resolvePhoneAndEmailContent()
        {
            $content = null;
            if ($this->model->officePhone != null)
            {
                $content .= ZurmoHtml::tag('span', array('class' => 'icon-office-phone'), $this->model->officePhone);
            }
            if ($this->model->mobilePhone != null)
            {
                $content .= ZurmoHtml::tag('span', array('class' => 'icon-mobile-phone'), $this->model->mobilePhone);
            }
            if ($this->model->primaryEmail->emailAddress != null)
            {
                $emailContent = EmailMessageUtil::renderEmailAddressAsMailToOrModalLinkStringContent(
                                    $this->model->primaryEmail->emailAddress, $this->model);
                $content .= ZurmoHtml::tag('span', array('class' => 'icon-email'), $emailContent);
            }
            if ($content != null)
            {
                return ZurmoHtml::tag('div', array('class' => 'contact-details'), $content);
            }
        }

        protected function resolveAddressContent()
        {
            $element                       = new AddressElement($this->model, 'primaryAddress', null);
            $element->breakLines           = false;
            $element->nonEditableTemplate  = '{content}';
            return ZurmoHtml::tag('div', array('class' => 'address'), $element->render());
        }

        protected function renderBackOfCardContent()
        {
            $backOfCardContent = Yii::app()->dataEnhancer->getPersonBackOfCardViewContent($this->model);
            if ($backOfCardContent != null)
            {
                return ZurmoHtml::tag('div', array('class' => 'back-of-card clearfix'), $backOfCardContent);
            }
        }

        protected static function registerBackOfCardScript()
        {
            $script = "
            $('.toggle-back-of-card-link').click(function()
            {
                $('span', this).slideToggle();
                $('.back-of-card').slideToggle();
                return false;
            });";
            Yii::app()->getClientScript()->registerScript('backOfCardScript', $script);
        }

        protected static function resolveAccountContentByUser(Account $account, User $user)
        {
            $userCanAccess   = RightsUtil::canUserAccessModule('AccountsModule', $user);
            $userCanReadItem = ActionSecurityUtil::canUserPerformAction('Details', $account, $user);
            if ($userCanAccess && $userCanReadItem)
            {
                return ZurmoHtml::link(Yii::app()->format->text($account), Yii::app()->createUrl('accounts/default/details/',
                                                                           array('id' => $account->id)));
            }
            elseif (!$userCanAccess && $userCanReadItem)
            {
                return strval($account);
            }
            else
            {
                return;
            }
        }
    }
?>
