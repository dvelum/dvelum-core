<?php
/**
 * DVelum project https://github.com/dvelum/dvelum-core , https://github.com/dvelum/dvelum
 *
 * MIT License
 *
 * Copyright (C) 2011-2020  Kirill Yegorov
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 *
 */

declare(strict_types=1);

namespace Dvelum;

/**
 * Page navigator
 */
class Paginator
{
    /**
     * Current page number
     * @var integer
     */
    public $curPage;
    /**
     * Pages count
     * @var integer
     */
    public $numPages;
    /**
     * Number of buttons
     * @var integer
     */
    public $numLinks;
    /**
     * URL template
     * @var string
     */
    public $pageLinkTpl;
    /**
     * String for replacing page number
     * @var string
     */
    public $tplId = '[page]';

    public function __toString()
    {
        if($this->numPages <= 1)
            return '';

        $digits = $this->findNearbyPages();

        $s = '<div class="pager" align="center">';
        $s.= $this->createNumBtns($digits);
        $s.= '</div>';

        return $s;
    }

    /**
     * @return array
     */
    public function findNearbyPages() : array
    {
        $digits = [];

        if($this->numLinks >= $this->numPages)
        {
            for($i = 1; $i <= $this->numPages; $i++)
                $digits[] = $i;

            return $digits;
        }

        if($this->curPage < $this->numLinks)
        {
            for($i = 1; $i <= $this->numLinks; $i++)
                $digits[] = $i;

            return $digits;
        }

        if($this->curPage > $this->numPages - $this->numLinks)
        {
            for($i = $this->numPages - $this->numLinks + 1; $i <= $this->numPages; $i++)
                $digits[] = $i;

            return $digits;
        }

        for($i = $this->curPage - intval($this->numLinks / 2), $j = 0; $j < $this->numLinks; $i++, $j++)
            $digits[] = $i;

        return $digits;
    }

    /**
     * @param array $digits
     * @return string
     */
    public function createNumBtns(array $digits) : string
    {
        $s = '';

        if($this->curPage > 1)
            $s .= '<a href="' . str_replace($this->tplId , (string) ($this->curPage - 1) , $this->pageLinkTpl) . '"><div class="pager_item">&laquo;</div></a>';


        for($i = 0, $sz = sizeof($digits); $i < $sz; $i++)
        {
            if($digits[$i] == $this->curPage)
                $s .= '<div class="pager_item_selected">' . $digits[$i] . '</div>';
            else
                $s .= '<a href="' . str_replace($this->tplId , $digits[$i] , $this->pageLinkTpl) . '"><div class="pager_item">' . $digits[$i] . '</div></a>';
        }

        if($this->curPage < $this->numPages)
            $s .= '<a href="' . str_replace($this->tplId , (string) ($this->curPage + 1) , $this->pageLinkTpl) . '"><div class="pager_item">&raquo;</div></a>';

        return $s;
    }

    /**
     * Set current page number
     * @param int $page
     * @return void
     */
    public function setCurrentPage(int $page) : void
    {
        $this->curPage = $page;
    }

    /**
     * Set number of pagination links
     * @param int $num
     * @return void
     */
    public function setLinksCount(int $num) : void
    {
        $this->numLinks = $num;
    }

    /**
     * Set count of list pages
     * @param int $count
     */
    public function setPagesCount(int $count) : void
    {
        $this->numPages = $count;
    }

    /**
     * Set pagination link template
     * @param string $template
     */
    public function setLinkUrlTemplate(string $template) : void
    {
        $this->pageLinkTpl = $template;
    }
}