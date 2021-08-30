<?php

/**
 *  DVelum project https://github.com/dvelum/dvelum
 *  Copyright (C) 2011-2018  Kirill Yegorov
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Dvelum\Tree;

use PHPUnit\Framework\TestCase;
use Dvelum\Tree\ArrayTree as Tree;
use stdClass;
use Exception;

class ArrayTreeTest extends TestCase
{
    public function testSetItemOrder() : void
    {
        $tree = new Tree();
        $tree->addItem(1, 0, 'item1');
        $tree->setItemOrder(1, 2);

        $item = $tree->getItem(1);
        $this->assertEquals($item['order'], 2);
    }

    public function testItemExists() : void
    {
        $tree = new Tree();

        $tree->addItem(1, 0, 'item1');

        $this->assertTrue($tree->itemExists(1));
        $tree->addItem('asd', 0, 'item1');
        $this->assertFalse($tree->itemExists(2));
    }


    public function testGetItemsCount() : void
    {
        $tree = new Tree();

        $tree->addItem(1, 0, 'item1');
        $this->assertEquals($tree->getItemsCount(), 1);
        $tree->addItem(2, 1, 100);
        $this->assertEquals($tree->getItemsCount(), 2);
    }


    public function testAddItem() : void
    {
        $tree = new Tree();
        $item = new stdClass();
        $item->id = 1;

        $this->assertTrue($tree->addItem($item->id, 0, $item));
        $this->assertEquals(1, $tree->getItemsCount());

        $item = new stdClass();
        $item->id = 0;
        $this->assertFalse($tree->addItem($item->id, 0, $item));
        $this->assertEquals(1, $tree->getItemsCount());

        $item->id = 2;
        $this->assertTrue($tree->addItem($item->id, 1, $item));
        $this->assertEquals(2, $tree->getItemsCount());
    }


    public function testUpdateItem() : void
    {
        $tree = new Tree();
        $item = new stdClass();
        $item->id = 1;
        $tree->addItem($item->id, 0, $item);

        $item2 = array('id' => 2, 'text' => 'text');

        $this->assertTrue($tree->updateItem($item->id, $item2));
        $this->assertFalse($tree->updateItem(4, $item2));

        $this->assertEquals($tree->getItemData($item->id), $item2);
    }

    public function testGetItem() : void
    {
        $tree = new Tree();
        $item = new stdClass();
        $item->id = 1;
        $tree->addItem($item->id, 0, $item);

        $itemResult = $tree->getItem($item->id);
        $this->assertTrue(!empty($itemResult));
        $this->assertTrue(is_array($itemResult));
        $this->assertEquals($itemResult['id'], $item->id);
        $this->assertEquals($itemResult['parent'], 0);
        $this->assertEquals($itemResult['data'], $item);
        $this->assertEquals($itemResult['order'], 0);

        $exception = false;
        try {
            $tree->getItem(8);
        } catch (Exception $e) {
            $exception = true;
        }

        $this->assertTrue($exception);
    }

    /**
     * @depends testGetItem
     */
    public function testGetItemData() : void
    {
        $tree = new Tree();
        $item = new stdClass();
        $item->id = 1;
        $tree->addItem($item->id, 0, $item);
        $this->assertEquals($tree->getItemData($item->id), $item);
    }

    public function testHasChilds() : void
    {
        $tree = new Tree();
        $item = new stdClass();
        $item->id = 1;
        $tree->addItem($item->id, 0, $item);

        $item2 = new stdClass();
        $item2->id = 2;
        $tree->addItem($item2->id, 1, $item2);

        $this->assertTrue($tree->hasChildren(0));
        $this->assertTrue($tree->hasChildren(1));
        $this->assertFalse($tree->hasChildren(2));
    }

    public function testGetChildrenRecursive() : void
    {
        $tree = new Tree();
        $tree->addItem(1, 0, 100);
        $tree->addItem(2, 1, 200);
        $tree->addItem(3, 2, 300);
        $tree->addItem(4, 3, 400);
        $tree->addItem(5, 1, 500);

        $childs = $tree->getChildrenRecursive(1);

        $this->assertContains(2, $childs);
        $this->assertContains(3, $childs);
        $this->assertContains(4, $childs);
        $this->assertContains(5, $childs);
        $this->assertEquals(count($childs), 4);
    }

    public function testSortChildren() : void
    {
        $tree = new Tree();
        $tree->addItem('a', 0, 50, 1);
        $tree->addItem(1, 'a', 100, 1);
        $tree->addItem(2, 'a', 200, 2);
        $tree->addItem(3, 'a', 300, 3);
        $tree->setItemOrder(2, 4);
        $tree->sortItems('a');

        $items = $tree->getChildren('a');
        $newOrder = [];
        foreach ($items as $v) {
            $newOrder[] = $v['data'];
        }

        $this->assertEquals($newOrder, [100, 300, 200]);

        $tree = new Tree();

        $tree->addItem(1, 0, 100, 1);
        $tree->addItem(2, 0, 200, 2);
        $tree->addItem(3, 0, 300, 3);
        $tree->setItemOrder(2, 4);
        $tree->sortItems();

        $items = $tree->getChildren(0);
        $newOrder = [];
        foreach ($items as $v) {
            $newOrder[] = $v['data'];
        }

        $this->assertEquals($newOrder, [100, 300, 200]);
    }


    public function testGetChildren() : void
    {
        $tree = new Tree();

        $tree->addItem(1, 0, 'item1');
        $tree->addItem(2, 1, 100);
        $tree->addItem(3, 1, 200);
        $tree->addItem(4, 3, 'item3-1');

        $childs = $tree->getChildren(1);
        $this->assertEquals(count($childs), 2);
        $this->assertEquals($childs[2]['data'], 100);
        $this->assertEquals($childs[3]['data'], 200);
    }

    public function testRemove() : void
    {
        $tree = new Tree();
        $tree->addItem(1, 0, 'item1');
        $tree->addItem(2, 1, 'item2');
        $tree->addItem(3, 2, 'item2');

        $tree->removeItem(2);
        $this->assertFalse($tree->itemExists(2));
        $this->assertFalse($tree->hasChildren(1));
        $this->assertFalse($tree->itemExists(3));
    }

    public function testGetParentId() : void
    {
        $tree = new Tree();
        $tree->addItem(1, 0, 'item1');
        $tree->addItem(2, 1, 'item2');

        $this->assertEquals($tree->getParentId(1), 0);
        $this->assertEquals($tree->getParentId(2), 1);
    }


    public function testChangeParent() : void
    {
        $tree = new Tree();

        $tree->addItem(1, 0, 'item1');
        $tree->addItem(2, 1, 100);
        $tree->addItem(3, 1, 200);
        $tree->addItem(4, 3, 'item3-1');

        $tree->changeParent(4, 2);
        $this->assertEquals($tree->getParentId(4), 2);
    }

    public function testRemoveItem() : void
    {
        $tree = new Tree();
        $item = new stdClass();
        $item->id = 1;
        $tree->addItem($item->id, 0, $item);

        $item2 = new stdClass();
        $item2->id = 2;
        $tree->addItem($item2->id, 1, $item2);

        $tree->removeItem(2);
        $this->assertEquals($tree->getItemsCount(), 1);
        $this->assertFalse($tree->itemExists(2));
        $this->assertFalse($tree->hasChildren(1));
    }

    public function testGetItems() : void
    {
        $tree = new Tree();
        $item = new stdClass();
        $item->id = 1;
        $tree->addItem($item->id, 0, $item);

        $item2 = new stdClass();
        $item2->id = 2;
        $tree->addItem($item2->id, 1, $item2);

        $data = $tree->getItems();
        $this->assertTrue(is_array($data));
        $this->assertEquals(count($data), 2);
        $this->assertEquals($data[1]['data'], $item);
        $this->assertEquals($data[2]['data'], $item2);
    }

    public function testGetParentsList() : void
    {
        $tree = new Tree();
        $tree->addItem(1, 0, 100);
        $tree->addItem(2, 0, 200);
        $tree->addItem(3, 2, 300);
        $tree->addItem(4, 3, 400);
        $tree->addItem(5, 3, 500);

        $this->assertEquals($tree->getParentsList(5), [2, 3]);
    }
}
