<?php

namespace Dvelum\Db;

use Dvelum\Orm\Model;
use PHPUnit\Framework\TestCase;

class SelectTest extends TestCase
{
    protected function getSelect() : Select
    {
        $select = new Select();
        //$select->setDbAdapter(Model::factory('User')->getDbConnection());
        return $select;
    }

    public function testSelectSimple() : void
    {
        $sql = $this->getSelect();

        $sql->from('table');
        $str = 'SELECT `table`.* FROM `table`;';
        $this->assertEquals($sql->assemble(), $str);
    }

    public function testSelectSimpleFromString() : void
    {
        $sql = $this->getSelect();
        $sql->from('table', 'id,name, date');
        $str = 'SELECT `table`.`id`, `table`.`name`, `table`.`date` FROM `table`;';
        $this->assertEquals($sql->assemble(), $str);
    }

    public function testSelectFromArray() : void
    {
        $sql = $this->getSelect();
        $sql->from('table', array('id', 'title', 'name'));
        $str = 'SELECT `table`.`id`, `table`.`title`, `table`.`name` FROM `table`;';
        $this->assertEquals($sql->assemble(), $str);
    }

    public function testSelectFromArrayAlias() : void
    {
        $sql = $this->getSelect();
        $sql->from('table', array('count' => 'COUNT(*)', 'field_name' => 'name', 'order'));
        $str = 'SELECT COUNT(*) AS `count`, `table`.`name` AS `field_name`, `table`.`order` FROM `table`;';
        $this->assertEquals($sql->assemble(), $str);
    }

    public function testSelectFromArrayAliasTableAlias() : void
    {
        $sql = $this->getSelect();
        $sql->from(array('t' => 'some_table'), array('count' => 'COUNT(*)', 'field_name' => 'name', 'order'));
        $str = 'SELECT COUNT(*) AS `count`, `t`.`name` AS `field_name`, `t`.`order` FROM `some_table` AS `t`;';
        $this->assertEquals($sql->assemble(), $str);
    }

    public function testSelectDistinct() : void
    {
        $sql = $this->getSelect();
        $sql->from('table')->distinct();
        $str = 'SELECT DISTINCT `table`.* FROM `table`;';
        $this->assertEquals($sql->assemble(), $str);
    }

    public function testSelectLimit() : void
    {
        $sql = $this->getSelect();
        $sql->from('table')->limit(10, 20);
        $str = 'SELECT `table`.* FROM `table` LIMIT 20,10;';
        $this->assertEquals($sql->assemble(), $str);

        $sql = $this->getSelect();
        $sql->from('table')->limit(10);
        $str = 'SELECT `table`.* FROM `table` LIMIT 10;';
        $this->assertEquals($sql->assemble(), $str);
    }

    public function testSelectLimitPage() : void
    {
        $sql = $this->getSelect();
        $sql->from('table')->limitPage(4, 10);
        $str = 'SELECT `table`.* FROM `table` LIMIT 30,10;';
        $this->assertEquals($sql->assemble(), $str);
    }

    public function testSelectGroup() : void
    {
        $sql = $this->getSelect();
        $sql->from('table')->group(array('type', 'cat'));
        $str = 'SELECT `table`.* FROM `table` GROUP BY `type`,`cat`;';
        $this->assertEquals($sql->assemble(), $str);

        $sql = $this->getSelect();
        $sql->from('table')->group('type');
        $str = 'SELECT `table`.* FROM `table` GROUP BY `type`;';
        $this->assertEquals($sql->assemble(), $str);

        $sql = $this->getSelect();
        $sql->from('table')->group('type,cat');
        $str = 'SELECT `table`.* FROM `table` GROUP BY `type`,`cat`;';
        $this->assertEquals($sql->assemble(), $str);
    }


    public function testSelectOrder() : void
    {
        $sql = $this->getSelect();
        $sql->from('table')->order(array('name' => 'DESC', 'group' => 'ASC'));
        $str = 'SELECT `table`.* FROM `table` ORDER BY `name` DESC,`group` ASC;';
        $this->assertEquals($sql->assemble(), $str);

        $sql = $this->getSelect();
        $sql->from('table')->order(array('name', 'group'));
        $str = 'SELECT `table`.* FROM `table` ORDER BY `name`,`group`;';
        $this->assertEquals($sql->assemble(), $str);


        $sql = $this->getSelect();
        $sql->from('table')->order(array('name ASC', 'group DESC'));
        $str = 'SELECT `table`.* FROM `table` ORDER BY name ASC,group DESC;';
        $this->assertEquals($sql->assemble(), $str);

        $sql = $this->getSelect();
        $sql->from('table')->order('name DESC, group ASC');
        $str = 'SELECT `table`.* FROM `table` ORDER BY `name` DESC,`group` ASC;';
        $this->assertEquals($sql->assemble(), $str);


        $sql = $this->getSelect();
        $sql->from('table')->order('name DESC');
        $str = 'SELECT `table`.* FROM `table` ORDER BY `name` DESC;';
        $this->assertEquals($sql->assemble(), $str);
    }

    public function testWhere() : void
    {
        $sql = $this->getSelect();
        $sql->from('table')->where('`id` =?', 7)->order('name DESC');
        $str = 'SELECT `table`.* FROM `table` WHERE (`id` =\'7\') ORDER BY `name` DESC;';
        $this->assertEquals($sql->assemble(), $str);

        $sql = $this->getSelect();
        $sql->from('table')->where('`id` =?', 0.6)->order('name DESC');
        $str = 'SELECT `table`.* FROM `table` WHERE (`id` =\'0.6\') ORDER BY `name` DESC;';
        $this->assertEquals($sql->assemble(), $str);

        $sql = $this->getSelect();
        $sql->from('table')->where('`code` =?', 'code')->order('name DESC');
        $str = 'SELECT `table`.* FROM `table` WHERE (`code` =\'code\') ORDER BY `name` DESC;';
        $this->assertEquals($sql->assemble(), $str);

        $sql = $this->getSelect();
        $sql->from('table')->where('`code` IN(?)', array('first', 'second'))->order('name DESC');
        $str = 'SELECT `table`.* FROM `table` WHERE (`code` IN(\'first\',\'second\')) ORDER BY `name` DESC;';
        $this->assertEquals($sql->assemble(), $str);

        $sql = $this->getSelect();
        $sql->from('table')->where('`id` IN(?)', array(7, 8, 9))->order('name DESC');
        $str = 'SELECT `table`.* FROM `table` WHERE (`id` IN(7,8,9)) ORDER BY `name` DESC;';
        $this->assertEquals($sql->assemble(), $str);
    }

    public function testHaving() : void
    {
        $sql = $this->getSelect();
        $sql->from('sb_content', array('c_code' => 'CONCAT(code,"i")'))
            ->having('`c_code` =?', "indexi");
        $str = 'SELECT CONCAT(code,"i") AS `c_code` FROM `sb_content` HAVING (`c_code` =\'indexi\');';
        $this->assertEquals($sql->assemble(), $str);
    }

    public function testOrHaving() : void
    {
        $sql = $this->getSelect();
        $sql->from('sb_content', array('c_code' => 'CONCAT(code,"i")'))
            ->having('`c_code` =?', "indexi")
            ->orHaving('`c_code` =?', "articlesi");
        $str = 'SELECT CONCAT(code,"i") AS `c_code` FROM `sb_content` HAVING (`c_code` =\'indexi\')' .
            ' OR (`c_code` =\'articlesi\');';
        $this->assertEquals(str_replace("\n", '', $sql->assemble()), $str);
    }


    public function testOrWhere() : void
    {
        $sql = $this->getSelect();
        $sql->from('table')
            ->where('`id` =?', 7)
            ->where('`code` =?', "code")
            ->orWhere('`id` =?', 8)
            ->orWhere('`id` =?', 9);
        $str = 'SELECT `table`.* FROM `table` WHERE (`id` =\'7\' AND `code` =\'code\') OR (`id` =\'8\' )' .
            ' OR ( `id` =\'9\');';
        $this->assertEquals(str_replace("\n", '', $sql->assemble()), $str);
    }

    public function testJoinLeft() : void
    {
        $sql = $this->getSelect();
        $sql->from(array('a' => 'table'))->joinLeft(
            array('b' => 'table'),
            'a.code = b.id',
            array('title', 'time')
        );
        $str = 'SELECT `a`.*, `b`.`title`, `b`.`time` FROM `table` AS `a` LEFT JOIN `table` AS `b` ON a.code = b.id;';

        $this->assertEquals($sql->assemble(), $str);


        $sql = $this->getSelect();
        $sql->from(array('a' => 'table'))
            ->joinLeft(
                'table',
                'a.code = table.id',
                'title,time'
            )
            ->joinLeft(
                'table',
                'a.code_2 = table.id',
                'title,time'
            );

        $str = 'SELECT `a`.*, `table`.`title`, `table`.`time`, `table_1`.`title`, `table_1`.`time` '.
        'FROM `table` AS `a` '.
        'LEFT JOIN `table` AS `table` ON a.code = table.id '.
        'LEFT JOIN `table` AS `table_1` ON a.code_2 = table.id;';
        $this->assertEquals($str, $sql->assemble());


        $sql = $this->getSelect();
        $sql->from(array('a' => 'table'))->joinLeft(
            array('table'),
            'a.code = table.id',
            '*'
        );
        $str = 'SELECT `a`.*, `table`.* FROM `table` AS `a` LEFT JOIN `table` AS `table` ON a.code = table.id;';
        $this->assertEquals( $str, $sql->assemble());
    }

    public function testJoinRight() : void
    {
        $sql = $this->getSelect();
        $sql->from(array('a' => 'table'))->joinRight(
            array('b' => 'table2'),
            'a.code = b.id',
            array('title', 'time')
        );
        $str = 'SELECT `a`.*, `b`.`title`, `b`.`time` FROM `table` AS `a` RIGHT JOIN `table2` AS `b` ON a.code = b.id;';
        $this->assertEquals(str_replace("\n", '', $str), $sql->assemble());
    }

    public function testJoinIner() : void
    {
        $sql = $this->getSelect();
        $sql->from(array('a' => 'table'))
            ->joinInner(
                array('b' => 'table2'),
                'a.code = b.id',
                array('title', 'time')
            );
        $str = 'SELECT `a`.*, `b`.`title`, `b`.`time` FROM `table` AS `a` INNER JOIN `table2` AS `b` ON a.code = b.id;';
        $this->assertEquals($sql->assemble(), $str);


        $sql = $this->getSelect();
        $sql->from(array('a' => 'table'))
            ->joinInner(
                array('b' => 'table2'),
                'a.code = b.id',
                array('title', 'time')
            )
            ->joinInner(
                array('c' => 'table3'),
                'b.code = c.id',
                array('ctitle' => 'title', 'ctime' => 'time')
            );
        $str = 'SELECT `a`.*, `b`.`title`, `b`.`time`, `c`.`title` AS `ctitle`, `c`.`time` AS `ctime` '.
        'FROM `table` AS `a` '.
        'INNER JOIN `table2` AS `b` ON a.code = b.id '.
        'INNER JOIN `table3` AS `c` ON b.code = c.id;';
        $this->assertEquals($str, $sql->assemble());
    }

    public function testJoin() : void
    {
        $sql = $this->getSelect();
        $sql->from(array('a' => 'table'))
            ->join(
                array('b' => 'table2'),
                'a.code = b.id',
                array('title', 'time')
            );
        $str = 'SELECT `a`.*, `b`.`title`, `b`.`time` FROM `table` AS `a` INNER JOIN `table2` AS `b` ON a.code = b.id;';
        $this->assertEquals(str_replace("\n", '', $str), $sql->assemble());
    }

    public function testForUpdate() : void
    {
        $sql = $this->getSelect();
        $sql->from('table');
        $sql->forUpdate();
        $str = 'SELECT `table`.* FROM `table` FOR UPDATE;';
        $this->assertEquals($sql->assemble(), $str);
    }
}
