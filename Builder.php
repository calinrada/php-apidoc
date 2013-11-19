<?php
/**
 * This file is part of the php-apidoc package.
 */
namespace Crada\Apidoc;

use Crada\Apidoc\Extractor;
use Crada\Apidoc\View;
use Crada\Apidoc\View\JsonView;

/**
 * Crada\Apidoc\Builder
 *
 * @license   http://opensource.org/licenses/bsd-license.php The BSD License
 * @author    Calin Rada <rada.calin@gmail.com>
 */
class Builder
{
    /**
     * Classes collection
     *
     * @var array
     */
    private $_st_classes;

    /**
     * Constructor
     *
     * @param array $st_classes
     */
    public function __construct(array $st_classes)
    {
        $this->_st_classes = $st_classes;
    }

    /**
     * Extract annotations
     *
     * @return array
     */
    private function extractAnnotations()
    {
        $extractor = new Extractor();
        foreach($this->_st_classes as $class) {
            $st_output[] = $extractor->getAllClassAnnotations($class);
        }

        return $st_output[1];
    }

    private function buildTemplate()
    {
        $st_annotations = $this->extractAnnotations();
    }

    /**
     * Output the annotations in json format
     *
     * @return json
     */
    public function renderJson()
    {
        $st_annotations = $this->extractAnnotations();

        $o_view = new JsonView();
        $o_view->set('annotations', $st_annotations);
        $o_view->render();
    }

    /**
     * Output the annotations in json format
     *
     * @return array
     */
    public function renderArray()
    {
        return $this->extractAnnotations();
    }

    /**
     * Build the docs
     */
    public function generate()
    {
        return $this->buildTemplate();
    }
}
