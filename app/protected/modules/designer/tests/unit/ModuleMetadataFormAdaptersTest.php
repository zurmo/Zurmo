<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2012 Zurmo Inc.
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

    class ModuleMetadataFormAdaptersTest extends BaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            Yii::app()->languageHelper->load();
            Yii::app()->languageHelper->setActiveLanguages(array('es', 'fr', 'it', 'de'));
        }

        public static function tearDownAfterClass()
        {
            Yii::app()->languageHelper->setActiveLanguages(array());
            parent::tearDownAfterClass();
        }

        public function testModuleMetadataToFormAdapter()
        {
            $module = new TestModule(null, null);
            $metadata = $module::getMetadata();
            $this->assertEquals(1, $metadata['global']['a']);
            $this->assertEquals(2, $metadata['global']['b']);
            $this->assertEquals(3, $metadata['global']['c']);
            $adapter = new ModuleMetadataToFormAdapter($metadata['global'] , get_class($module));
            $moduleForm = $adapter->getModuleForm();
            $this->assertEquals(1, $moduleForm->a);
            $this->assertEquals(2, $moduleForm->b);
            $this->assertEquals(3, $moduleForm->c);
            $singularCompareData = array(
                'en' => 'tes',
                'es' => 'tes',
                'it' => 'tes',
                'fr' => 'tes',
                'de' => 'tes',
            );
            $this->assertEquals($singularCompareData, $moduleForm->singularModuleLabels);
            $pluralCompareData = array(
                'en' => 'test',
                'es' => 'test',
                'it' => 'test',
                'fr' => 'test',
                'de' => 'test',
            );
            $this->assertEquals($pluralCompareData, $moduleForm->pluralModuleLabels);
        }

        public function testModuleFormToMetadataAdapter()
        {
            $metadata = TestModule::getMetadata();
            $this->assertEquals(1, $metadata['global']['a']);
            $this->assertEquals(2, $metadata['global']['b']);
            $this->assertEquals(3, $metadata['global']['c']);
            $module = new TestModule(null, null);
            $moduleForm = new TestModuleForm();
            $moduleForm->a = 5;
            $moduleForm->b = 6;
            $moduleForm->c = 7;
            $moduleForm->singularModuleLabels = array(
                'en' => 'texs',
                'es' => 'texs',
                'it' => 'texs',
                'fr' => 'texs',
                'de' => 'texs',
            );
            $moduleForm->pluralModuleLabels = array(
                'en' => 'texst',
                'es' => 'texst',
                'it' => 'texst',
                'fr' => 'texst',
                'de' => 'texst',
            );
            $adapter = new ModuleFormToMetadataAdapter($module, $moduleForm);
            $adapter->setMetadata();
            $metadata = $module::getMetadata();
            $this->assertEquals(5, $metadata['global']['a']);
            $this->assertEquals(6, $metadata['global']['b']);
            $this->assertEquals(7, $metadata['global']['c']);
            $singularCompareData = array(
                'en' => 'texs',
                'es' => 'texs',
                'it' => 'texs',
                'fr' => 'texs',
                'de' => 'texs',
            );
            $this->assertEquals($singularCompareData, $metadata['global']['singularModuleLabels']);
            $pluralCompareData = array(
                'en' => 'texst',
                'es' => 'texst',
                'it' => 'texst',
                'fr' => 'texst',
                'de' => 'texst',
            );
            $this->assertEquals($pluralCompareData, $metadata['global']['pluralModuleLabels']);
        }

        /**
         * @depends testModuleFormToMetadataAdapter
         */
        public function testAttributeLabelArrayMergeIsWorking()
        {
            $module = new TestModule(null, null);
            $moduleForm = new TestModuleForm();
            $moduleForm->a = 5;
            $moduleForm->b = 6;
            $moduleForm->c = 7;
            $moduleForm->singularModuleLabels = array(
                'it' => 'git',
                'fr' => 'frit',
                'de' => 'dit',
            );
            $moduleForm->pluralModuleLabels = array(
                'it' => 'gits',
                'fr' => 'frits',
                'de' => 'dits',
            );
            $adapter = new ModuleFormToMetadataAdapter($module, $moduleForm);
            $adapter->setMetadata();
            $metadata = $module::getMetadata();
            $this->assertEquals(5, $metadata['global']['a']);
            $this->assertEquals(6, $metadata['global']['b']);
            $this->assertEquals(7, $metadata['global']['c']);
            $singularCompareData = array(
                'en' => 'texs',
                'es' => 'texs',
                'it' => 'git',
                'fr' => 'frit',
                'de' => 'dit',
            );
            $this->assertEquals($singularCompareData, $metadata['global']['singularModuleLabels']);
            $pluralCompareData = array(
                'en' => 'texst',
                'es' => 'texst',
                'it' => 'gits',
                'fr' => 'frits',
                'de' => 'dits',
            );
            $this->assertEquals($pluralCompareData, $metadata['global']['pluralModuleLabels']);
        }
    }
?>
