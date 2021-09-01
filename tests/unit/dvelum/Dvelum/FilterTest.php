<?php

namespace Dvelum;

use PHPUnit\Framework\TestCase;

class FilterTest extends TestCase
{
    public function testFilterValue(): void
    {
        $this->assertEquals(Filter::filterValue('integer', 123), 123);
        $this->assertEquals(Filter::filterValue('float', 12.2), 12.2);
        $this->assertEquals(Filter::filterValue('str', 333), '333');
        $this->assertEquals(
            Filter::filterValue('cleaned_string', " <a href='test'>Test</a>"),
            '&lt;a href=&#039;test&#039;&gt;Test&lt;/a&gt;'
        );
        $this->assertEquals(Filter::filterValue('email', 'cmd<03>@aa.ss'), 'cmd03@aa.ss');
        $this->assertEquals(Filter::filterValue('raw', 'saa'), 'saa');
        $this->assertEquals(Filter::filterValue('alphanum', 'pOl$1@_!;l'), 'pOl1_l');
        $this->assertEquals(Filter::filterValue('alpha', 'pOl$1@_!;4l'), 'pOll');
        $this->assertEquals(Filter::filterValue('somefilter', '11asdasd 2 d'), 11);
        $this->assertEquals(Filter::filterValue('alphanum', '~!@#$%^&*()234admin@mail.ru'), '234adminmailru');
        $this->assertEquals(Filter::filterValue('login', '~!@#$%^&*()admin@mail.ru\,\''), '@admin@mail.ru');
        $this->assertTrue(is_array(Filter::filterValue('array', 'asd')));
    }

    public function testFilterString(): void
    {
        $this->assertEquals(Filter::filterString('  <b><?php echo "biber"; ?></b>what? '), 'what?');
    }
}
