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

    /**
     * View that renders a a security component in the form of a
     * tree or noded list view.
     */
    abstract class SecurityTreeListView extends MetadataView
    {
        protected $controllerId;

        protected $moduleId;

        protected $items;

        public function __construct($controllerId, $moduleId, $items)
        {
            assert('$controllerId != null');
            assert('$moduleId != null');
            assert('is_array($items)');
            $this->controllerId           = $controllerId;
            $this->moduleId               = $moduleId;
            $this->items                  = $items;
        }

        /**
         * Render Tree Menu.
         * @param nodeRelationName - parent relation name such as 'role' or 'group'
         * @param nodesRelationName - children relation name such as 'roles' or 'groups'
         * @return string tree content
         */
        protected function renderTreeMenu($nodeRelationName, $nodesRelationName, $title)
        {
            $parentNode = array('text' => $title);
            $itemNodes  = array();
            foreach ($this->items as $item)
            {
                if (empty($item->{$nodeRelationName}->id) || $item->{$nodeRelationName}->id < 1)
                {
                    if ($this->resolveIsNodeLinkableById($item->id, strval($item)))
                    {
                        $text  = $this->makeTreeMenuNodeLink(
                                         strval($item),
                                        'details',
                                        $item->id);
                        $route = $this->makeTreeMenuNodeRoute('details', $item->id);
                    }
                    else
                    {
                        $text  = strval($item);
                        $route = null;
                    }

                    $userCount        = $this->resolveUserCountForItem($item);
                    $node             = array('link'      => $text,
                                              'userCount' => $userCount,
                                              'route'     => $route);
                    $node['children'] = $this->makeChildrenNodes($this->items, $item, $nodeRelationName);
                    $itemNodes[]      = $node;
                }
            }
            $dataTree               = $itemNodes;
            return $this->renderTreeListView($dataTree);
        }

        protected function getModelRelationNameForUserCount()
        {
            return 'users';
        }

        protected function renderTreeListView($data)
        {
            throw new NotImplementedException();
        }

        protected static function renderTreeListViewNode(& $content, $data, $indent)
        {
            assert('is_string($content)');
            assert('is_array($data)');
            foreach ($data as $node)
            {
                $content .= '<tr>';
                $content .= '<td class="level-' . $indent . '">';
                $content .= $node['link'];
                $content .= '</td>';
                $content .= '<td>';
                $content .= $node['userCount'];
                $content .= '</td>';
                $content .= '<td>';
                if(isset($node['route']) && $node['route'] != null)
                {
                    $content .= CHtml::link(CHtml::tag('span', array(), Yii::t('Default', 'Configure') ),
                                            $node['route']);
                }
                $content .= '</td>';
                $content .= '</tr>';
                if(isset($node['children']))
                {
                    static::renderTreeListViewNode($content, $node['children'], $indent + 1);
                }
            }
        }

        /**
         * @param $isLink - Currently if this gets set to true in this function
         * then this is propogated downstream because makeChildrenNodes
         * is used recursively.  @see renderTreeMenu
         * @return CTreeView ready nodes array
         */
        protected function makeChildrenNodes($items, $parentItem, $nodeRelationName, $isLink = true)
        {
            assert('is_string($nodeRelationName)');
            assert('is_bool($isLink)');
            $itemNodes = array();
            foreach ($items as $item)
            {
                if (isset($item->{$nodeRelationName}) &&
                $item->{$nodeRelationName}->id == $parentItem->id &&
                $parentItem->id > 0)
                {
                    if ($isLink && !$this->resolveIsNodeLinkableById($item->id, strval($item)))
                    {
                        $isLink = false;
                    }
                    if ($isLink)
                    {
                        $text = $this->makeTreeMenuNodeLink(
                                        strval($item),
                                        'details',
                                        $item->id);
                       $route = $this->makeTreeMenuNodeRoute('details', $item->id);
                    }
                    else
                    {
                        $text  = strval($item);
                        $route = null;
                    }
                    $userCount        = $this->resolveUserCountForItem($item);
                    $node             = array('link'      => $text,
                                              'userCount' => $userCount,
                                              'route'     => $route);
                    $node['children'] = $this->makeChildrenNodes($items, $item, $nodeRelationName, $isLink);
                    $itemNodes[]      = $node;
                }
            }
            return $this->resolveNodeWithChildrenItems($itemNodes, $parentItem);
        }

        /**
         * Override if you need to add additional items into the tree for each
         * node.  An example is roles, that show the list of users for each role.
         */
        protected function resolveNodeWithChildrenItems(array $itemNodes, $parentItem)
        {
            assert('$parentItem instanceof Item');
            return $itemNodes;
        }

        protected function makeTreeMenuNodeLink($label, $action, $id)
        {
            return CHtml::Link($label, $this->makeTreeMenuNodeRoute($action, $id));
        }

        protected function makeTreeMenuNodeRoute($action, $id)
        {
            return Yii::app()->createUrl($this->moduleId . '/' . $this->controllerId . '/' . $action . '/', array('id' => $id));
        }

        public static function getDefaultMetadata()
        {
            $metadata = array(
                'global' => array(),
            );
            return $metadata;
        }

        /**
         * Override if special logic is needed to control
         * when a node is displayed as a link or not
         */
        protected function resolveIsNodeLinkableById($id, $name)
        {
            return true;
        }

        protected function resolveUserCountForItem(Item $item)
        {
            return $item->{$this->getModelRelationNameForUserCount()}->count();
        }
    }
?>
