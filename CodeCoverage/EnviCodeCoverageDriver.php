<?php
/**
 *
 *
 *
 * @category   自動テスト
 * @package    UnitTest
 * @subpackage CodeCoverage
 * @author     Akito <akito-artisan@five-foxes.com>
 * @copyright  2011-2014 Artisan Project
 * @license    http://opensource.org/licenses/BSD-2-Clause The BSD 2-Clause License
 * @version    GIT: $Id$
 * @link       https://github.com/EnviMVC/EnviMVC3PHP
 * @see        http://www.enviphp.net/
 * @since      Class available since Release v3.3.3.5
 * @doc_ignore
 */

/**
 *
 *
 *
 * @category   自動テスト
 * @package    UnitTest
 * @subpackage CodeCoverage
 * @author     Akito <akito-artisan@five-foxes.com>
 * @copyright  2011-2014 Artisan Project
 * @license    http://opensource.org/licenses/BSD-2-Clause The BSD 2-Clause License
 * @version    Release: @package_version@
 * @link       https://github.com/EnviMVC/EnviMVC3PHP
 * @see        http://www.enviphp.net/
 * @since      Class available since Release v3.3.3.5
 */
class EnviCodeCoverageDriver
{
    private $filter;
    private $code_coverage;
    private static $driver_start = false;

    /**
     * @var string covered file
     */
    private $cover = array('method' => array(), 'class' => array());

    /**
     * +-- クラスのビルダ
     *
     * @access      public
     * @static
     * @param       EnviCodeCoverage $code_coverage
     * @return      EnviCodeCoverageDriver
     */
    public static function factory(EnviCodeCoverage $code_coverage)
    {
        $obj = new EnviCodeCoverageDriver;
        $obj->code_coverage = $code_coverage;
        return $obj;
    }
    /* ----------------------------------------- */


    public function setCover(array $cover)
    {
        foreach ($cover as $cover_method) {
            $tmp = explode('::', $cover_method, 2);
            if (count($tmp) === 2) {
                $this->cover['method'][$tmp[0]][$tmp[1]] = $cover_method;
            } else {
                $this->cover['class'][$cover_method] = $cover_method;
            }
        }
    }

    public function unSetCover()
    {
        $this->cover = array('method' => array(), 'class' => array());
    }


    /**
     * +-- code_coverage開始
     *
     * @access      public
     * @return      void
     */
    public function start()
    {
        if (self::$driver_start) {
            return;
        }
        //xdebug_start_code_coverage(XDEBUG_CC_UNUSED | XDEBUG_CC_DEAD_CODE);
        xdebug_start_code_coverage();
        self::$driver_start = true;
    }
    /* ----------------------------------------- */

    /**
     * +-- code_coverage終了
     *
     * @access      public
     * @return      array
     */
    public function finish()
    {
        if (!self::$driver_start) {
            return array();
        }
        $data = xdebug_get_code_coverage();
        xdebug_stop_code_coverage();
        self::$driver_start = false;
        $this->filter($data);
        return $data;
    }
    /* ----------------------------------------- */

    private function filter(array &$data)
    {
        foreach (array_keys($data) as $file_name) {
            if (!is_file($file_name) ||
                $this->code_coverage->filter()->isFiltered($file_name)) {
                unset($data[$file_name]);
                continue;
            }
            foreach ($this->code_coverage->parser()->getSkipLine($file_name, $this->cover['class'], $this->cover['method']) as $line) {
                unset($data[$file_name][$line]);
            }

            if (empty($data[$file_name])) {
                unset($data[$file_name]);
            }
        }
        return $data;
    }
}

