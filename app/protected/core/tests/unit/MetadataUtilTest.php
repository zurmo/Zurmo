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

    class MetadataUtilTest extends ZurmoBaseTest
    {
        public function testResolveEvaluateSubString()
        {
            //variable in string, value as array
            $combinedData           = "eval:array_combine(array('a', 'b', 'c'), \$array2)";
            $resolveVariableName    = 'array2';
            $params                 = array('1', '2', '3');
            MetadataUtil::resolveEvaluateSubString($combinedData, $resolveVariableName, $params);
            $this->assertEquals('2', $combinedData['b']);

            //variables in array, values in array
            $sum                    = "eval:(int)(\$x + \$y + \$z)";
            $resolveVariableName    = array('x', 'y', 'z');
            $params                 = array(5, 6, 7);
            MetadataUtil::resolveEvaluateSubString($sum, $resolveVariableName, $params);
            $this->assertEquals(18, $sum);

            //variables in array, value as default value
            $product                = "eval:(int)(\$x * \$y)";
            $resolveVariableName    = array('x', 'y');
            $params                 = null;
            $defaultValue           = 5;
            MetadataUtil::resolveEvaluateSubString($product, $resolveVariableName, $params, $defaultValue);
            $this->assertEquals(25, $product);

            //combine all in one array of evals
            $evaluateValues      = array(
                'combinedData'   => "eval:array_combine(array('a', 'b', 'c'), \$array2)",
                'sum'            => "eval:(int)(\$x + \$y + \$z)",
                'product'        => "eval:(int)(\$default * \$default)",
                'static'         => "string"
            );
            $resolveVariableName = array('array2', 'x', 'y', 'z', 'default');
            $params              = array(array('1', '2', '3'), 5, 6, 7);
            $defaultValue        = 8;
            MetadataUtil::resolveEvaluateSubString($evaluateValues, $resolveVariableName, $params, $defaultValue);
            $this->assertEquals('2',        $evaluateValues['combinedData']['b']);
            $this->assertEquals(18,         $evaluateValues['sum']);
            $this->assertEquals(64,         $evaluateValues['product']);
            $this->assertEquals("string",   $evaluateValues['static']);
        }
    }
?>