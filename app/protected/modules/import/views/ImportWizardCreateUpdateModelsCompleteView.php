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
     * View for displaying the import results after the sanitization and creation/update of models runs in a
     * sequential process.  This step occurs after the import mapping view is completed.
     * @see ImportCreateUpdateModelSequentialProcess
     */
    class ImportWizardCreateUpdateModelsCompleteView extends ImportWizardView
    {
        protected $modelsCreated    = 0;

        protected $modelsUpdated    = 0;

        protected $rowsWithErrors   = 0;

        public function __construct($controllerId, $moduleId, ImportWizardForm $model,
                                    $modelsCreated = 0, $modelsUpdated = 0, $rowsWithErrors = 0)
        {
            assert('is_int($modelsCreated)');
            assert('is_int($modelsUpdated)');
            assert('is_int($rowsWithErrors)');
            parent::__construct($controllerId, $moduleId, $model);
            $this->modelsCreated  = $modelsCreated;
            $this->modelsUpdated  = $modelsUpdated;
            $this->rowsWithErrors = $rowsWithErrors;
        }

        /**
         * Override to handle the form layout for this view.
         * @param $form If the layout is editable, then pass a $form otherwise it can
         * be null.
         * @return A string containing the element's content.
          */
        protected function renderFormLayout($form = null)
        {
            assert('$form instanceof ZurmoActiveForm');
            $content  = '<table>'      . "\n";
            $content .= '<tbody>'      . "\n";
            $content .= '<tr><td><h3>' . "\n";
            $content .= Yii::t('Default', 'Congratulations! Your import is complete.  Below is a summary of the results.');
            $content .= '</h3></br>'   . "\n";
            $content .= Yii::t('Default', 'Records created: {created}', array('{created}' => $this->modelsCreated))
                         . '</br>' . "\n";
            $content .= Yii::t('Default', 'Records updated: {updated}', array('{updated}' => $this->modelsUpdated))
                         . '</br>' . "\n";
            $content .= Yii::t('Default', 'Rows with errors: {errors}', array('{errors}' => $this->rowsWithErrors))
                         . '</br>' . "\n";
            $content .= '</td></tr>'   . "\n";
            $content .= '</tbody>'     . "\n";
            $content .= '</table>'     . "\n";
            $content .= $this->renderActionLinksContent();
            return $content;
        }

        /**
         * Override because there is no link to go to. This is the last step.
         */
        protected function renderNextPageLinkContent()
        {
            return null;
        }
    }
?>