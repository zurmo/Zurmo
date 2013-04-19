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

    class WebUser extends CWebUser
    {
        protected $userModel = null;

        public function __get($attributeName)
        {
            assert('is_string($attributeName)');
            assert('$attributeName != ""');
            try
            {
                if ($attributeName != 'isGuest' && $this->userModel === null)
                {
                    $username = parent::__get('username');
                    $this->userModel = User::getByUsername($username);
                }
                if ($attributeName == 'userModel')
                {
                    return $this->userModel;
                }
                elseif ($this->userModel !== null && $this->userModel->isAttribute($attributeName))
                {
                    return $this->userModel->$attributeName;
                }
                else
                {
                    return parent::__get($attributeName);
                }
            }
            catch (NotFoundException $e)
            {
                if (Yii::app()->isApplicationInstalled())
                {
                    //Perhaps the username has changed, clear session and logout user.
                    Yii::app()->getSession()->destroy();
                    Yii::app()->request->redirect(Yii::app()->homeUrl);
                }
            }
            catch (CException $e)
            {
            }
        }

        public function __set($attributeName, $value)
        {
            if ($attributeName == 'userModel')
            {
                $this->userModel = $value;
            }
            else
            {
                parent::__set($attributeName, $value);
            }
        }

        public function hasUserModel()
        {
            return $this->userModel !== null;
        }

        protected function afterLogin($fromCookie)
        {
            //Only run afterLogin when not coming from a cookie otherwise Yii::app()->user is not available.
            if (!$fromCookie)
            {
                AuditEvent::logAuditEvent('UsersModule', UsersModule::AUDIT_EVENT_USER_LOGGED_IN);
                if ($this->hasEventHandler('onAfterLogin'))
                {
                    $this->onAfterLogin(new CEvent($this));
                }
            }
        }

        /**
         * Raised right AFTER the login is completed for a WebUser
         * @param CEvent $event the event parameter
         */
        public function onAfterLogin($event)
        {
            $this->raiseEvent('onAfterLogin', $event);
        }

        protected function beforeLogout()
        {
            AuditEvent::logAuditEvent('UsersModule', UsersModule::AUDIT_EVENT_USER_LOGGED_OUT);
            return true;
        }

        /**
        * Initializes the application component.
        * This method overrides the parent implementation by starting session,
        * performing cookie-based authentication if enabled, and updating the flash variables.
        */
        public function init()
        {
            CApplicationComponent::init();

            if (ApiRequest::isApiRequest())
            {
                if ($sessionId = Yii::app()->apiRequest->getSessionId())
                {
                    Yii::app()->session->setSessionID($sessionId);
                    Yii::app()->session->open();
                    $session = Yii::app()->getSession();
                    if (Yii::app()->apiRequest->isSessionTokenRequired())
                    {
                        if ($session['token'] != Yii::app()->apiRequest->getSessionToken() || $session['token'] == '')
                        {
                            Yii::app()->session->clear();
                            Yii::app()->session->destroy();
                        }
                    }
                }
                else
                {
                    Yii::app()->session->open();
                    $sessionId = Yii::app()->session->getSessionID();
                    $userPassword = Yii::app()->apiRequest->getPassword();
                    $token = ZurmoSession::createSessionToken($sessionId, $userPassword);
                    $session = Yii::app()->getSession();
                    $session['token'] = $token;
                }
            }
            else
            {
                Yii::app()->getSession()->open();
                if ($this->getIsGuest() && $this->allowAutoLogin)
                {
                    $this->restoreFromCookie();
                }
                elseif ($this->autoRenewCookie && $this->allowAutoLogin)
                {
                    $this->renewCookie();
                }
            }

            if ($this->autoUpdateFlash)
            {
                $this->updateFlash();
            }
            $this->updateAuthStatus();
        }
    }
?>