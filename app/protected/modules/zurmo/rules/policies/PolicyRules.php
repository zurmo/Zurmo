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
     * Policy Rules define parameters for managing
     * the administrative user interface for policies
     */
    abstract class PolicyRules
    {
        protected $moduleName;

        protected $policy;

        protected $explicit;

        protected $inherited;

        /**
         * Construct a policy rule
         * @param $moduleName - module class name
         * @param $policy - the policy name
         * @param $explicit - explicit policy value if it exists
         * @param $inherited - inherited policy value if it exists
         */
        public function __construct($moduleName, $policy, $explicit, $inherited)
        {
            assert('is_string($moduleName)');
            assert('is_string($moduleName)');
            assert('is_numeric($explicit) || $explicit == null');
            assert('is_numeric($inherited) || $inherited == null');
            $this->moduleName = $moduleName;
            $this->policy     = $policy;
            $this->explicit   = $explicit;
            $this->inherited  = $inherited;
        }

        /**
         * @return boolean - true if this policy should show in the administrative
         * user interface of rights.
         */
        public function showInView()
        {
            return true;
        }

        /**
         * Returns the string element name for the administrative user interface
         * of rights.
         * @return string
         */
        abstract function getElementAttributeType();

        /**
         * Returns the string element name for the effective user interface
         * of rights.  This is a readonly element.
         * @return string
         */
        abstract function getEffectiveElementAttributeType();

        /**
         * @return boolean - if the element type is derived or not.
         */
        public function isElementTypeDerived()
        {
            return false;
        }

        /**
         * @return array of validation rules to be used by the rights form for
         * the administrative interface of rights.
         * @return array;
         */
        abstract public function getFormRules();
    }
?>