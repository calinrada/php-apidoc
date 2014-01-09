<?php
/**
 * This file is part of the php-apidoc package.
 */
namespace Crada\Apidoc\View;

use Crada\Apidoc\Exception;

/**
 * @license http://opensource.org/licenses/bsd-license.php The BSD License
 * @author  Calin Rada <rada.calin@gmail.com>
 */
class BaseView implements ViewInterface
{
    /**
     * Template name
     *
     * @var string
     */
    protected $s_file;

    /**
     * Data array that has to be rendered
     *
     * @var array
     */
    protected $st_data = array();

    /**
     * Array with config parameters for the View
     *
     * @var array
     */
    protected $st_viewConfig;

    /**
     * View object
     *
     * @var object
     */
    protected $o_view;

    /**
     * {@inheritdoc}
     */
    public function __init() {}

    /**
     * Set configuration params from the config file.
     *
     * @param array $st_config
     */
    public function setConfig($st_config)
    {
        $this->st_viewConfig = $st_config;
    }

    /**
     * Get config param(s)
     *
     * @param  string               $s_key
     * @return string|array|boolean
     */
    public function getConfig($s_key = null)
    {
        if ($s_key) {
            if (isset($this->st_viewConfig[$s_key])) {
                return $this->st_viewConfig[$s_key];
            }

            return false;
        }

        return $this->st_viewConfig;
    }

    /**
     * Set template file
     * @param string $file Full path to file
     */
    public function setTemplate($file)
    {
        $this->s_file = $file;
    }

    /**
     * {@inheritdoc}
     */
    public function getTemplate()
    {
        return $this->s_file;
    }

    /**
     * Set parameters to render
     * @param string  $key
     * @param mixed   $value     Value - can be string / array
     * @param boolean $b_skipKey If true, the value will be assign directly to $this->st_data
     */
    public function set($key, $value, $b_skipKey = false)
    {
        if (true === $b_skipKey) {
            $this->st_data = $value;
        } else {
            $this->st_data[$key] = $value;
        }
    }

    /**
     * Get a value by key
     * @param string $key Key name
     */
    public function get($key)
    {
        return $this->st_data[$key];
    }

    /**
     * {@inheritdoc}
     */
    public function getAll()
    {
        return $this->st_data;
    }

    /**
     * {@inheritdoc}
     */
    public function render()
    {
        $file = str_replace('\\','/',$this->getTemplate()).'.php';
        if (!file_exists($file)) {
            throw new Exception('Template ' . $file . ' does not exist.');
        }
        extract($this->st_data);
        ob_start();
        include($file);
        $output = ob_get_contents();
        ob_end_clean();
        echo $output;
    }
}
