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

    abstract class MergeTagsValidator extends CValidator
    {
        public $modelClassName;

        public $type;

        public $language;

        abstract protected function resolveMergeTagValidatorProperties($object);

        /**
         * Validates the attribute of the model.
         * If there is any error, the error message is added to the model.
         * @param RedBeanModel $model the model being validated
         * @param string $attribute the attribute being validated
         */
        protected function validateAttribute($object, $attribute)
        {
            $this->resolveMergeTagValidatorProperties($object);
            $passedValidation = true;
            if (!empty($object->$attribute) && @class_exists($this->modelClassName))
            {
                $model              = new $this->modelClassName(false);
                $mergeTagsUtil      = MergeTagsUtilFactory::make($this->type, $this->language, $object->$attribute);
                $invalidTags        = array();
                $mergeTagCount      = $mergeTagsUtil->extractMergeTagsPlaceHolders();
                if ($mergeTagCount && !$mergeTagsUtil->resolveMergeTagsArrayToAttributes($model, $invalidTags, null))
                {
                    $passedValidation = false;
                    if (!empty($invalidTags))
                    {
                        foreach ($invalidTags as $tag)
                        {
                            $errorMessage = EmailTemplateHtmlAndTextContentElement::renderModelAttributeLabel($attribute) .
                                                                                ': Invalid MergeTag({mergeTag}) used.';
                            $this->addError($object, $attribute, Zurmo::t('EmailTemplatesModule', $errorMessage,
                                                                                            array('{mergeTag}' => $tag)));
                        }
                    }
                    else
                    {
                        $this->addError($object, $attribute, Zurmo::t('EmailTemplatesModule', 'Provided content contains few invalid merge tags.'));
                    }
                }
            }
            return $passedValidation;
        }
    }
?>