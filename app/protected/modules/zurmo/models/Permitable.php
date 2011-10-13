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

    class Permitable extends Item
    {
        public function contains(Permitable $permitable)
        {
            return $this->isSame($permitable);
        }

        public function getEffectiveRight($moduleName, $rightName)
        {
            assert('is_string($moduleName)');
            assert('is_string($rightName)');
            assert('$moduleName != ""');
            assert('$rightName  != ""');
            return $this->getActualRight($moduleName, $rightName) == Right::ALLOW ? Right::ALLOW : Right::DENY;
        }

        public function getActualRight($moduleName, $rightName)
        {
            assert('is_string($moduleName)');
            assert('is_string($rightName)');
            assert('$moduleName != ""');
            assert('$rightName  != ""');
            if (!SECURITY_OPTIMIZED)
            {
                // The slow way will remain here as documentation
                // for what the optimized way is doing.
                $combinedRight = $this->getExplicitActualRight       ($moduleName, $rightName) |
                                 $this->getInheritedActualRight      ($moduleName, $rightName) |
                                 $this->getPropagatedActualAllowRight($moduleName, $rightName);
                if (($combinedRight & Right::DENY) == Right::DENY)
                {
                    return Right::DENY;
                }
                assert('in_array($combinedRight, array(Right::NONE, Right::ALLOW))');
                return $combinedRight;
            }
            else
            {
                // You must cast a Permitable down to a User or Group.
                // This is a limitation of the RedBeanModel's mapping to
                // the database.
                throw new NotSupportedException();
            }
        }

        public function getExplicitActualRight($moduleName, $rightName)
        {
            assert('is_string($moduleName)');
            assert('is_string($rightName)');
            assert('$moduleName != ""');
            assert('$rightName  != ""');
            if (!SECURITY_OPTIMIZED)
            {
                // The slow way will remain here as documentation
                // for what the optimized way is doing.
                $combinedRight = Right::NONE;
                foreach ($this->rights as $right)
                {
                    if ($right->moduleName == $moduleName &&
                        $right->name       == $rightName)
                    {
                        $combinedRight |= $right->type;
                        if ($right->type == Right::DENY)
                        {
                            break; // Shortcircuit.
                        }
                    }
                }
                if (($combinedRight & Right::DENY) == Right::DENY)
                {
                    return Right::DENY;
                }
                assert('in_array($combinedRight, array(Right::NONE, Right::ALLOW))');
                return $combinedRight;
            }
            else
            {
                $permitableId = $this->getClassId('Permitable');
                return intval(ZurmoDatabaseCompatibilityUtil::
                                callFunction("get_permitable_explicit_actual_right($permitableId, '$moduleName', '$rightName')"));
            }
        }

        public function getPropagatedActualAllowRight($moduleName, $rightName)
        {
            // You must cast a Permitable down to a User or Group.
            // This is a limitation of the RedBeanModel's mapping to
            // the database.
            throw new NotSupportedException();
        }

        public function getInheritedActualRight($moduleName, $rightName)
        {
            assert('is_string($moduleName)');
            assert('is_string($rightName)');
            assert('$moduleName != ""');
            assert('$rightName  != ""');
            if (!SECURITY_OPTIMIZED)
            {
                // The slow way will remain here as documentation
                // for what the optimized way is doing.
                $combinedRight = $this->getInheritedActualRightIgnoringEveryone                      ($moduleName, $rightName) |
                                 Group::getByName(Group::EVERYONE_GROUP_NAME)->getExplicitActualRight($moduleName, $rightName);
                if (($combinedRight & Right::DENY) == Right::DENY)
                {
                    return Right::DENY;
                }
                assert('in_array($combinedRight, array(Right::NONE, Right::ALLOW))');
                return $combinedRight;
            }
            else
            {
                // You must cast a Permitable down to a User or Group.
                // This is a limitation of the RedBeanModel's mapping to
                // the database.
                throw new NotSupportedException();
            }
        }

        protected function getInheritedActualRightIgnoringEveryone($moduleName, $rightName)
        {
            // You must cast a Permitable down to a User or Group.
            // This is a limitation of the RedBeanModel's mapping to
            // the database.
            throw new NotSupportedException();
        }

        public function setRight($moduleName, $rightName, $type = Right::ALLOW)
        {
            assert('is_string($moduleName)');
            assert('is_string($rightName)');
            assert('$moduleName != ""');
            assert('$rightName  != ""');
            assert('in_array($type, array(Right::ALLOW, Right::DENY))');
            $found = false;
            foreach ($this->rights as $right)
            {
                if ($right->moduleName == $moduleName &&
                    $right->name       == $rightName)
                {
                    $right->type = $type;
                    $found = true;
                    break;
                }
            }
            if (!$found)
            {
                $right = new Right();
                $right->moduleName  = $moduleName;
                $right->name        = $rightName;
                $right->type        = $type;
                $this->rights->add($right);
            }
        }

        public function removeRight($moduleName, $rightName, $type = Right::ALLOW)
        {
            assert('is_string($moduleName)');
            assert('is_string($rightName)');
            assert('$moduleName != ""');
            assert('$rightName  != ""');
            assert('in_array($type, array(Right::ALLOW, Right::DENY))');
            foreach ($this->rights as $right)
            {
                if ($right->moduleName == $moduleName &&
                    $right->name       == $rightName)
                {
                    $this->rights->remove($right);
                }
            }
        }

        public function removeAllRights()
        {
            $this->rights->removeAll();
        }

        public function getEffectivePolicy($moduleName, $policyName)
        {
            assert('is_string($moduleName)');
            assert('is_string($policyName)');
            assert('$moduleName != ""');
            assert('$policyName != ""');
            // A permitable gets the default policy until it is saved.
            if ($this->id > 0)
            {
                $value = $this->getActualPolicy($moduleName, $policyName);
                if ($value !== null)
                {
                    return $value;
                }
            }
            return $moduleName::getPolicyDefault($policyName);
        }

        public function getActualPolicy($moduleName, $policyName)
        {
            assert('is_string($moduleName)');
            assert('is_string($policyName)');
            assert('$moduleName != ""');
            assert('$policyName  != ""');
            $value = $this->getExplicitActualPolicy($moduleName, $policyName);
            if ($value !== null)
            {
                return $value;
            }
            $value = $this->getInheritedActualPolicy($moduleName, $policyName);
            if ($value !== null)
            {
                return $value;
            }
            if (!SECURITY_OPTIMIZED)
            {
                return Group::getByName(Group::EVERYONE_GROUP_NAME)->getExplicitActualPolicy($moduleName, $policyName);
            }
            else
            {
                $permitableName = 'Everyone';
                try
                {
                    return RightsCache::getEntry($permitableName . $moduleName . $policyName .  'ActualPolicy');
                }
                catch (NotFoundException $e)
                {
                    $actualPolicy = ZurmoDatabaseCompatibilityUtil::
                                    callFunction("get_named_group_explicit_actual_policy(
                                                 'Everyone', '$moduleName', '$policyName')");
                }
                RightsCache::
                cacheEntry($permitableName . $moduleName . $policyName .  'ActualPolicy', $actualPolicy);
                return $actualPolicy;
            }
        }

        public function getExplicitActualPolicy($moduleName, $policyName)
        {
            assert('is_string($moduleName)');
            assert('is_string($policyName)');
            assert('$moduleName != ""');
            assert('$policyName != ""');
            if (!SECURITY_OPTIMIZED)
            {
                // The slow way will remain here as documentation
                // for what the optimized way is doing.
                foreach ($this->policies as $policy)
                {
                    if ($policy->moduleName == $moduleName &&
                        $policy->name       == $policyName)
                    {
                        return $policy->value;
                    }
                }
                return null;
            }
            else
            {
                $permitableId = $this->getClassId('Permitable');
                try
                {
                    return RightsCache::getEntry($permitableId . $moduleName . $policyName .  'ExplicitActualPolicy');
                }
                catch (NotFoundException $e)
                {

                    $explictActualPolicy =  ZurmoDatabaseCompatibilityUtil::
                                            callFunction("get_permitable_explicit_actual_policy(
                                                         $permitableId, '$moduleName', '$policyName')");
                }
                RightsCache::
                cacheEntry($permitableId . $moduleName . $policyName .  'ExplicitActualPolicy', $explictActualPolicy);
                return $explictActualPolicy;
            }
        }

        public function getInheritedActualPolicy($moduleName, $policyName)
        {
            assert('is_string($moduleName)');
            assert('is_string($policyName)');
            assert('$moduleName != ""');
            assert('$policyName  != ""');
            $value = $this->getInheritedActualPolicyIgnoringEveryone($moduleName, $policyName);
            if ($value !== null)
            {
                return $value;
            }
            return Group::getByName(Group::EVERYONE_GROUP_NAME)->getExplicitActualPolicy($moduleName, $policyName);
        }

        public function setPolicy($moduleName, $policyName, $value)
        {
            assert('is_string($moduleName)');
            assert('is_string($policyName)');
            assert('$moduleName != ""');
            assert('$policyName != ""');
            assert('!is_bool($value)'); // Remember booleans must be specified as 1 & 0 for RedBeanModel.
            assert('$value !== null');
            $found = false;
            foreach ($this->policies as $policy)
            {
                if ($policy->moduleName == $moduleName &&
                    $policy->name       == $policyName)
                {
                    $policy->value = $value;
                    $found = true;
                    break;
                }
            }
            if (!$found)
            {
                $policy = new Policy();
                $policy->moduleName = $moduleName;
                $policy->name       = $policyName;
                $policy->value      = $value;
                $this->policies->add($policy);
            }
        }

        public function removePolicy($moduleName, $policyName)
        {
            assert('is_string($moduleName)');
            assert('is_string($policyName)');
            assert('$moduleName != ""');
            assert('$policyName != ""');
            foreach ($this->policies as $policy)
            {
                if ($policy->moduleName == $moduleName &&
                    $policy->name       == $policyName)
                {
                    $this->policies->remove($policy);
                }
            }
        }

        public function removeAllPolicies()
        {
            $this->policies->removeAll();
        }

        public static function getDefaultMetadata()
        {
            $metadata = parent::getDefaultMetadata();
            $metadata[__CLASS__] = array(
                'relations' => array(
                    'rights'      => array(RedBeanModel::HAS_MANY, 'Right',  RedBeanModel::OWNED),
                    'policies'    => array(RedBeanModel::HAS_MANY, 'Policy', RedBeanModel::OWNED),
                ),
                'foreignRelations' => array(
                    'Permission',
                ),
            );
            return $metadata;
        }

        public static function isTypeDeletable()
        {
            return false;
        }
    }
?>
