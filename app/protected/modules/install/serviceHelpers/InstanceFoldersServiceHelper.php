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
     * Checks if instance folders (assets, data, and runtime are present and writable.)
     */
    class InstanceFoldersServiceHelper extends ServiceHelper
    {
        /**
         * Check if the required runtime folders are present and writable i.e. (assets , runtime and data)
         * @returns true, if the required folders are present, or false if not installed. If returning
         * false, populates the $message property accordingly.
         */
        protected function checkService()
        {
            $passed      = true;
            $pathsToTest = array(
                INSTANCE_ROOT . '/assets',
                INSTANCE_ROOT . '/protected/data',
                INSTANCE_ROOT . '/protected/runtime'
            );
            foreach ($pathsToTest as $pathToTest)
            {
                if (!file_exists($pathToTest))
                {
                    if ($this->message != null)
                    {
                        $this->message .= "\n";
                    }
                    $this->message .= Yii::t('Default', '{folderPath} is missing.', array('{folderPath}' => $pathToTest));
                    $passed = false;
                }
                if (!is_writable($pathToTest))
                {
                    if ($this->message != null)
                    {
                        $this->message .= "\n";
                    }
                    $this->message .= Yii::t('Default', '{folderPath} is not writable.',
                                             array('{folderPath}' => $pathToTest));
                    $passed = false;
                }
            }
            if ($passed)
            {
                    $this->message .= Yii::t('Default', 'The instance folders are present and writable.');
            }
            return $passed;
        }
    }
?>
