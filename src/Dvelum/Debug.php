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

class Debug
{
    /**
     * Script startup time
     * @var float
     */
    protected static $scriptStartTime;
    /**
     * Database profiler
     * @var array $dbProfilers
     */
    static protected $dbProfilers = [];
    /**
     * @var array
     */
    static protected $timers = [];
    /**
     * @var array
     */
    static protected $loadedClasses = [];
    /**
     * @var array
     */
    static protected $loadedConfigs = [];
    /**
     * @var array
     */
    static protected $cacheCores = [];

    /**
     * @param array $data
     */
    static public function setCacheCores(array $data) : void
    {
        self::$cacheCores = $data;
    }

    /**
     * @param int $time
     */
    static public function setScriptStartTime(int $time) : void
    {
        self::$scriptStartTime = $time;
    }

    /**
     * @param \Zend\Db\Adapter\Profiler\ProfilerInterface $profiler
     */
    static public function addDbProfiler(\Zend\Db\Adapter\Profiler\ProfilerInterface $profiler) : void
    {
        self::$dbProfilers[] = $profiler;
    }

    /**
     * @param array $data
     */
    static public function setLoadedClasses(array $data) : void
    {
        self::$loadedClasses = $data;
    }

    /**
     * @param array $data
     */
    static public function setLoadedConfigs(array $data) : void
    {
        self::$loadedConfigs = $data;
    }

    /**
     * Get debug information
     * @param array $options
     * @return string  - html formated results
     */
    static public function getStats(array $options) : string
    {
        $options = array_merge(
            array(
                'cache' => true,
                'sql' => false,
                'autoloader' => false,
                'configs' => false,
                'includes' => false
            ),
            $options
        );

        $str = '';

        if (self::$scriptStartTime) {
            $str .= '<b>Time:</b> ' . number_format((microtime(true) - self::$scriptStartTime), 5) . "sec.<br>\n";
        }

        $str .= '<b>Memory:</b> ' . number_format((memory_get_usage() / (1024 * 1024)), 3) . "mb<br>\n"
            . '<b>Memory peak:</b> ' . number_format((memory_get_peak_usage() / (1024 * 1024)), 3) . "mb<br>\n"
            . '<b>Includes:</b> ' . count(get_included_files()) . "<br>\n"
            . '<b>Autoloaded:</b> ' . count(self::$loadedClasses) . "<br>\n"
            . '<b>Config files:</b> ' . count(self::$loadedConfigs) . "<br>\n";

        if (!empty(self::$dbProfilers)) {
            $str .= self::getQueryProfiles($options);
        }


        if ($options['configs']) {
            $str .= "<b>Configs (" . count(self::$loadedConfigs) . "):</b>\n<br> " . implode("\n\t <br>",
                    self::$loadedConfigs) . '<br>';
        }

        if ($options['autoloader']) {
            $str .= "<b>Autoloaded (" . count(self::$loadedClasses) . "):</b>\n<br> " . implode("\n\t <br>",
                    self::$loadedClasses) . '<br>';
        }

        if ($options['includes']) {
            $str .= "<b>Includes (" . count(get_included_files()) . "):</b>\n<br> " . implode("\n\t <br>",
                    get_included_files());
        }


        if (!empty(self::$cacheCores) && $options['cache']) {
            $body = '';
            $globalCount = array('load' => 0, 'save' => 0, 'remove' => 0, 'total' => 0);
            $globalTotal = 0;

            foreach (self::$cacheCores as $name => $cacheCore) {
                if (!$cacheCore) {
                    continue;
                }

                $count = $cacheCore->getOperationsStat();

                $count['total'] = $count['load'] + $count['save'] + $count['remove'];

                $globalCount['load'] += $count['load'];
                $globalCount['save'] += $count['save'];
                $globalCount['remove'] += $count['remove'];
                $globalCount['total'] += $count['total'];

                $body .= '
                    <tr align="right">
                        <td align="left" >' . $name . '</td>
                        <td>' . $count['load'] . '</td>
                        <td>' . $count['save'] . '</td>
                        <td>' . $count['remove'] . '</td>
                        <td style="border-left:2px solid #000000;">' . $count['total'] . '</td>
                    </tr>';

            }

            $body .= '
                    <tr align="right" style="border-top:2px solid #000000;">
                        <td align="left" >Total</td>
                        <td>' . $globalCount['load'] . '</td>
                        <td>' . $globalCount['save'] . '</td>
                        <td>' . $globalCount['remove'] . '</td>
                        <td style="border-left:2px solid #000000;">' . $globalCount['total'] . '</td>
                    </tr>';

            $str .= '<div style=" padding:1px;"> <center><b>Cache</b></center>
                <table cellpadding="2" cellspacing="2" border="1" style="font-size:10px;">
                    <tr style="background-color:#cccccc;font-weight:bold;">
                        <td>Name</td>
                        <td>Load</td>
                        <td>Save</td>
                        <td>Remove</td>
                        <td style="border-left:2px solid #000000;">Total</td>
                    </tr>
                    ' . $body . '
                </table>
             </div>';
        }


        return '<div id="debugPanel" style="position:fixed;font-size:12px;left:10px;bottom:10px;overflow:auto;max-height:300px;padding:5px;background-color:#ffffff;z-index:1000;border:1px solid #cccccc;">' . $str . ' <center><a href="javascript:void(0)" onClick="document.getElementById(\'debugPanel\').style.display = \'none\'">close</a></center></div>';
    }

    /**
     * Start timer
     * @param string $name
     * @return void
     */
    static public function startTimer(string $name) : void
    {
        self::$timers[$name] = array(
            'start' => microtime(true),
            'stop' => 0
        );
    }

    /**
     * Stop timer
     * @param string $name
     * @return float - time elapsed
     */
    static public function stopTimer($name) : float
    {
        if (!isset(self::$timers[$name])) {
            return 0;
        }

        self::$timers[$name]['stop'] = microtime(true);
        return self::$timers[$name]['stop'] - self::$timers[$name]['start'];
    }

    /**
     * Get time
     * @param string $timer
     * @return float time elapsed
     */
    static public function getTimerTime($timer)
    {
        if (!isset(self::$timers[$timer])) {
            return 0;
        }

        if (!self::$timers[$timer]['stop']) {
            return self::stopTimer($timer);
        }

        self::$timers[$timer]['stop'] = microtime(true);
        return self::$timers[$timer]['stop'] - self::$timers[$timer]['start'];
    }

    /**
     * @param array $options
     * @return string
     */
    static protected function getQueryProfiles(array $options) : string
    {
        $str = '';

        $totalCount = 0;
        $totalTime = 0;
        $profiles = [];

        foreach (self::$dbProfilers as $prof) {
            $totalCount += count($prof->getProfiles());
            //$totalTime += $prof->getTotalElapsedSecs();
            $prof = $prof->getProfiles();
            if (!empty($prof)) {
                foreach ($prof as $item) {
                    $profiles[] = $item;
                    $totalTime += $item['elapse'];
                }
            }
        }


        $str .= '<b>Queries:</b> ' . $totalCount . '<br>' . '<b>Queries time:</b> ' . number_format($totalTime,
                5) . 'sec.<br>';
        if ($options['sql']) {
            if (!empty($profiles)) {
                foreach ($profiles as $queryProfile) {
                    $str .= '<span style="color:blue;font-size: 11px;">' . number_format($queryProfile['elapse'],
                            5) . 's. </span><span style="font-size: 11px;color:green;">' . $queryProfile['sql'] . "</span><br>\n";
                }
            }
        }
        $str .= "<br>\n";

        return $str;
    }

}