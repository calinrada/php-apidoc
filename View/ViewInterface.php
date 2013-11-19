<?php
/**
 * This file is part of the php-apidoc package.
 */
namespace Crada\Apidoc\View;

/**
 * Crada\Apidoc\View\ViewInterface
 *
 * @license   http://opensource.org/licenses/bsd-license.php The BSD License
 * @author    Calin Rada <rada.calin@gmail.com>
 */
interface ViewInterface
{
    /**
     * Act as constructor
     */
    public function __init();

    /**
     * Set template file
     * @param string $file Full path to file
     */
    public function setTemplate($file);

    /**
     * Get current template
     */
    public function getTemplate();

    /**
     * Set parameters to render
     * @param string $key
     * @param mixed  $value Value - can be string / array
     */
    public function set($key, $value);

    /**
     * Get a value by key
     * @param string $key Key name
     */
    public function get($key);

    /**
     * Get all values
     */
    public function getAll();

    /**
     * Render template
     */
    public function render();
}
