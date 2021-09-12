<?php

/*
 *
 * DVelum project https://github.com/dvelum/
 *
 * MIT License
 *
 *  Copyright (C) 2011-2021  Kirill Yegorov https://github.com/dvelum/dvelum-core
 *
 *  Permission is hereby granted, free of charge, to any person obtaining a copy
 *  of this software and associated documentation files (the "Software"), to deal
 *  in the Software without restriction, including without limitation the rights
 *  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *  copies of the Software, and to permit persons to whom the Software is
 *  furnished to do so, subject to the following conditions:
 *
 *  The above copyright notice and this permission notice shall be included in all
 *  copies or substantial portions of the Software.
 *
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *  FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 *  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 *  SOFTWARE.
 *
 */

declare(strict_types=1);

namespace Dvelum\Tree;

use Exception as Exception;

/**
 * Class optimized for fast work with tree structures.
 * Easily handles up to 25000-30000 sets of elements (less than 1 second to fill out)
 * Copyright (C) 2011-2018  Kirill Yegorov
 * @package Dvelum
 */
class Tree
{
    /**
     * @var Item[]
     */
    protected $items = [];
    /**
     * @var array<int|string,mixed>
     */
    protected $children = [];

    /**
     * Set elements sorting order by ID
     * @param mixed $id — element identifier
     * @param integer $order — sorting order
     * @return bool
     */
    public function setItemOrder($id, int $order): bool
    {
        if (!$this->itemExists($id)) {
            return false;
        }

        $this->items[$id]->setOrder($order);
        return true;
    }

    /**
     * Sort child elements
     * @param mixed $parentId — nor required;  a parent identifier -
     * is the root node by default, which sorts all other nodes
     */
    public function sortItems($parentId = false): void
    {
        if ($parentId) {
            $this->sortChildren($parentId);
        } else {
            foreach ($this->children as $k => $v) {
                $this->sortChildren($k);
            }
        }
    }

    /**
     * Check if the node exists by its identifier
     * @param mixed $id
     * @return bool
     */
    public function itemExists($id): bool
    {
        return isset($this->items[$id]);
    }

    /**
     * Get the number of elements in a tree
     * @return int
     */
    public function getItemsCount(): int
    {
        return count($this->items);
    }

    /**
     * Add a node to the tree
     * @param mixed $id — unique identifier cannot be 0
     * @param mixed $parent — parent node identifier
     * @param mixed $data — node data
     * @param null|integer $order - sorting order, not required
     * @return bool —  successfully invoked
     */
    public function addItem($id, $parent, $data, ?int $order = null): bool
    {
        if ((string)$id === '0' || isset($this->items[$id])) {
            return false;
        }

        $item = new Item($id, $parent, $data, $order);
        $this->items[$id] = $item;

        if (!isset($this->children[$parent])) {
            $this->children[$parent] = [];
        }

        $this->children[$parent][] = $id;
        return true;
    }

    /**
     * Update the node data
     * @param mixed $id — node identifier
     * @param array<int|string,mixed> $data — node data
     * @return bool — successfully invoked
     */
    public function updateItem($id, array $data): bool
    {
        if ((string)$id === '0' || !isset($this->items[$id])) {
            return false;
        }

        $this->items[$id]->setData($data);
        return true;
    }

    /**
     * Get node structure by ID
     * @param mixed $id
     * @return Item - an array with keys ('id','parent','order','data')
     * @throws Exception
     */
    public function getItem($id): Item
    {
        if ($this->itemExists($id)) {
            return $this->items[$id];
        } else {
            throw new Exception('Item "' . $id . '" is not found');
        }
    }

    /**
     * Get node data by ID
     * @param mixed $id
     * @return mixed
     * @throws \Exception
     */
    public function getItemData($id)
    {
        return $this->getItem($id)->getData();
    }

    /**
     * Check if the node has child elements
     * @param mixed $id — node identifier
     * @return boolean
     */
    public function hasChildren($id): bool
    {
        if (isset($this->children[$id]) && !empty($this->children[$id])) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get data on all child elements (recursively)
     * @param mixed $id - parent node identifier
     * @phpstan-return array<int|string,mixed> - an array with keys ('id','parent','order','data')
     * @return array{id:int|string,parent:int|string,order:int,data:array|mixed}
     */
    public function getChildrenRecursive($id): array
    {
        $data = [];
        if ($this->hasChildren($id)) {
            $elements = $this->getChildren($id);
            foreach ($elements as $v) {
                $id = $v->getId();
                $data[] = $id;
                $subElements = $this->getChildrenRecursive($id);
                if (!empty($subElements)) {
                    $data = array_merge($data, $subElements);
                }
            }
        }
        return $data;
    }

    /**
     * @param string|int $id
     */
    protected function sortChildren($id): void
    {
        if (!isset($this->children[$id]) || empty($this->children[$id])) {
            return;
        }

        $tmp = [];
        $chCount = 0;
        foreach ($this->children[$id] as $itemId) {
            $order = $this->items[$itemId]->getOrder();
            if (is_null($order)) {
                $order = $chCount;
            }
            $tmp[$itemId] = $order;
            $chCount++;
        }

        $this->children[$id] = [];
        asort($tmp);

        $sort = 0;
        foreach ($tmp as $key => $order) {
            $this->items[$key]->setOrder($sort);
            $this->children[$id][] = $key;
            $sort++;
        }
    }

    /**
     * Get child nodes’ structures
     * @param int|string $id
     * @return array<int|string,mixed>
     */
    public function getChildren($id): array
    {
        if (!$this->hasChildren($id)) {
            return [];
        }

        $data = [];
        foreach ($this->children[$id] as $itemId) {
            $data[] = $this->items[$itemId];
        }

        return $data;
    }

    /**
     * Recursively removing
     * @param mixed $id
     * @return void
     */
    protected function remove($id): void
    {
        $children = $this->getChildren($id);

        if (!empty($children)) {
            foreach ($children as $k => $v) {
                $this->remove($v->getId());
            }
        }

        if (isset($this->children[$id])) {
            unset($this->children[$id]);
        }

        $parent = $this->items[$id]->getParent();

        if (!empty($this->children[$parent])) {
            foreach ($this->children[$parent] as $index => $item) {
                if ($item === $id) {
                    unset($this->children[$parent][$index]);
                }
            }
        }
        unset($this->items[$id]);
    }

    /**
     * Get the parent node identifier by the child node identifier
     * @param mixed $id — child node identifier
     * @return int|string|null
     */
    public function getParentId($id)
    {
        if (!isset($this->items[$id])) {
            return null;
        }

        return $this->items[$id]->getParent();
    }

    /**
     * Change the parent node for the node
     * @param mixed $id — node identifier
     * @param mixed $newParent — new parent node identifier
     * @return bool
     */
    public function changeParent($id, $newParent): bool
    {
        if (!isset($this->items[$id]) || !isset($this->items[$newParent]) || (string)$id == (string)$newParent) {
            return false;
        }

        $oldParent = $this->items[$id]->getParent();
        $this->items[$id]->setParent($newParent);

        if (!empty($this->children[$oldParent])) {
            foreach ($this->children[$oldParent] as $index => $item) {
                if ($item === $id) {
                    unset($this->children[$oldParent][$index]);
                }
            }
        }

        $this->children[$newParent][] = $id;
        return true;
    }

    /**
     * Delete node
     * @param mixed $id
     * @return void
     */
    public function removeItem($id): void
    {
        if ($this->itemExists($id)) {
            $this->remove($id);
        }
    }

    /**
     * Get structures of the tree elements (nodes)
     * @return Item[] - an array with Item
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * Get list of parent nodes
     * @param mixed $id
     * @return array<int,int|string>
     */
    public function getParentsList($id): array
    {
        $parents = [];
        if (!isset($this->items[$id])) {
            return [];
        }

        $parentId = $id;
        while ($parentId) {
            $p = $this->items[$parentId]->getParent();
            if (isset($this->items[$p])) {
                $parentId = $p;
                $parents[] = $p;
            } else {
                $parentId = false;
            }
        }

        if (!empty($parents)) {
            $parents = array_reverse($parents);
        }
        return $parents;
    }
}
