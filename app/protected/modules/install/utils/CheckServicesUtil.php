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
     * Helper class for organizing and checking services during an installation.
     */
    class CheckServicesUtil
    {
        const CHECK_PASSED = 1;

        const CHECK_FAILED = 2;

        /**
         * Utilize if a check does not pass or fail but is able to ascertain partial information, thus resulting in
         * a warning.
         * @var integer
         */
        const CHECK_WARNING = 3;

        private static function getServicesToCheck()
        {
            return array('WebServer',
                         'Php',
                         'PhpTimeZone',
                         'PhpMemoryBytes',
                         'PhpFileUploads',
                         'PhpUploadSize',
                         'PhpPostSize',
                         'ServerVariable',
                         'PCRE',
                         'SPL',
                         'Ctype',
                         'FilePermissions',
                         'InstanceFolders',
                         'APC',
                         'Soap',
                         'Curl',
                         'Yii',
                         'RedBean',
                         'MbString',
                         'Memcache',
                         'SetIncludePath',
                         'IMAP',
                         'Pdo',
                         'PdoMysql',
                         'Ldap'
            );
        }

        private static function getAdditionalServicesToCheck()
        {
            return array('Database',
                         'DatabaseCheckSafeMode',
                         'DatabaseMaxAllowedPacketSize',
                         'DatabaseMaxSpRecursionDepth',
                         'DatabaseThreadStack',
                         'DatabaseDefaultCollation',
                         'DatabaseLogBin',
                         'DatabaseOptimizerSearchDepth'
            );
        }

        /**
         * Check all services and return the resulting data in an array. The resulting data is organized first by
         * whether a service passed or not, and then by if it is a required or optional service.
         */
        public static function checkServicesAndGetResultsDataForDisplay($checkAdditionalServices = false, $form = null)
        {
            if (!$checkAdditionalServices)
            {
                $servicesToCheck                                                  = self::getServicesToCheck();
            }
            else
            {
                $servicesToCheck                                                  = self::getAdditionalServicesToCheck();
            }

            $resultsData                                                       = array();
            $resultsData[self::CHECK_PASSED]                                   = array();
            $resultsData[self::CHECK_FAILED]                                   = array();
            $resultsData[self::CHECK_FAILED] [ServiceHelper::REQUIRED_SERVICE] = array();
            $resultsData[self::CHECK_FAILED] [ServiceHelper::OPTIONAL_SERVICE] = array();
            $resultsData[self::CHECK_WARNING]                                  = array();

            foreach ($servicesToCheck as $service)
            {
                $serviceHelperClassName = $service . 'ServiceHelper';

                if ($form && property_exists($serviceHelperClassName, 'form'))
                {
                    $serviceHelper = new $serviceHelperClassName($form);
                }
                else
                {
                    $serviceHelper = new $serviceHelperClassName();
                }
                if ($serviceHelper->runCheckAndGetIfSuccessful())
                {
                    $resultsData[self::CHECK_PASSED][] = array('service' => $service,
                                                                      'message' => $serviceHelper->getMessage());
                }
                elseif ($serviceHelper->didCheckProduceWarningStatus())
                {
                    $resultsData[self::CHECK_WARNING][]  = array('service' => $service,
                                                                      'message' => $serviceHelper->getMessage());
                }
                else
                {
                    $serviceType  = $serviceHelper->getServiceType();
                    $resultsData[self::CHECK_FAILED][$serviceType][] = array('service' => $service,
                                                                      'message' => $serviceHelper->getMessage());
                }
            }
            return $resultsData;
        }
    }
?>