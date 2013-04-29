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
     * Controller Class for managing Ldap Authentication.
     *
     */
    class ZurmoLdapController extends ZurmoModuleController
    {
        const LDAP_CONFIGURATION_FILTER_PATH =
              'application.modules.zurmo.controllers.filters.LdapExtensionCheckControllerFilter';
              
        public function filters()
        {
            return array(
                array(self::LDAP_CONFIGURATION_FILTER_PATH,
                     'controller' => $this,
                )
            );
        }
        
        public function actionConfigurationEditLdap()
        {
            $configurationForm = LdapConfigurationFormAdapter::makeFormFromGlobalConfiguration();
            $postVariableName   = get_class($configurationForm);
            if (isset($_POST[$postVariableName]))
            {
                $configurationForm->setAttributes($_POST[$postVariableName]);
                if ($configurationForm->validate())
                {
                    LdapConfigurationFormAdapter::setConfigurationFromForm($configurationForm);
                    Yii::app()->user->setFlash('notification',
                        Zurmo::t('ZurmoModule', 'LDAP Configuration saved successfully.')
                    );
                    $this->redirect(Yii::app()->createUrl('configuration/default/index'));
                }
            }
            $editView = new LdapConfigurationEditAndDetailsView(
                                    'Edit',
                                    $this->getId(),
                                    $this->getModule()->getId(),
                                    $configurationForm);
            $editView->setCssClasses( array('AdministrativeArea') );
            $view = new ZurmoConfigurationPageView(ZurmoDefaultAdminViewUtil::
                                         makeStandardViewForCurrentUser($this, $editView));
            echo $view->render();
        }

        public function actionTestConnection()
        {
            $configurationForm = LdapConfigurationFormAdapter::makeFormFromGlobalConfiguration();
            $postVariableName  = get_class($configurationForm);
            if (isset($_POST[$postVariableName]) || (isset($_POST['LdapConfigurationForm'])))
            {
                if (isset($_POST[$postVariableName]))
                {
                    $configurationForm->setAttributes($_POST[$postVariableName]);
                }
                else
                {
                    $configurationForm->serverType            = $_POST['LdapConfigurationForm']['serverType'];
                    $configurationForm->host                  = $_POST['LdapConfigurationForm']['host'];
                    $configurationForm->port                  = $_POST['LdapConfigurationForm']['port'];
                    $configurationForm->bindRegisteredDomain  = $_POST['LdapConfigurationForm']['bindRegisteredDomain'];
                    $configurationForm->bindPassword          = $_POST['LdapConfigurationForm']['bindPassword'];
                    $configurationForm->baseDomain            = $_POST['LdapConfigurationForm']['baseDomain'];
                    $configurationForm->enabled               = $_POST['LdapConfigurationForm']['enabled'];
                }
                if ($configurationForm->host != null && $configurationForm->port != null &&
                    $configurationForm->bindRegisteredDomain != null && $configurationForm->bindPassword != null &&
                    $configurationForm->baseDomain != null && $configurationForm->serverType != null)
                {
                    $authenticationHelper = new ZurmoAuthenticationHelper;
                    $authenticationHelper->ldapServerType           = $configurationForm->serverType;
                    $authenticationHelper->ldapHost                 = $configurationForm->host;
                    $authenticationHelper->ldapPort                 = $configurationForm->port;
                    $authenticationHelper->ldapBindRegisteredDomain = $configurationForm->bindRegisteredDomain;
                    $authenticationHelper->ldapBindPassword         = $configurationForm->bindPassword;
                    $authenticationHelper->ldapBaseDomain           = $configurationForm->baseDomain;
                    $authenticationHelper->ldapEnabled              = $configurationForm->enabled;

                    $serverType                = $configurationForm->serverType;
                    $host                      = $configurationForm->host;
                    $port                      = $configurationForm->port;
                    $bindRegisteredDomain      = $configurationForm->bindRegisteredDomain;
                    $bindPassword              = $configurationForm->bindPassword;
                    $baseDomain                = $configurationForm->baseDomain;
                    $testConnectionResults     = LdapUtil::establishConnection($serverType, $host, $port, $bindRegisteredDomain,
                                                                               $bindPassword, $baseDomain);
                    if ($testConnectionResults)
                    {
                       $messageContent = Zurmo::t('ZurmoModule', 'Successfully Connected to Ldap Server') . "\n";
                    }
                    else
                    {
                       $messageContent = Zurmo::t('ZurmoModule', 'Unable to connect to Ldap server') . "\n";
                    }
                }
                else
                {
                    $messageContent = Zurmo::t('ZurmoModule', 'All fields are required') . "\n";
                }
                Yii::app()->getClientScript()->setToAjaxMode();
                $messageView = new TestLdapConnectionView($messageContent);
                $view = new ModalView($this, $messageView);
                echo $view->render();
            }
            else
            {
                throw new NotSupportedException();
            }
        }
    }
?>