<?php
/**
 * This file is part of the php-apidoc package.
 */
namespace Crada\Apidoc\View;

/**
 * Crada\Apidoc\View\JsonView
 *
 * @license   http://opensource.org/licenses/bsd-license.php The BSD License
 * @author    Calin Rada <rada.calin@gmail.com>
 */
class JsonView extends BaseView
{
    /**
     * (non-PHPdoc)
     * @see \Crada\Apidoc\View\BaseView::render()
     */
    public function render()
    {
        $data     = json_encode($this->st_data, JSON_FORCE_OBJECT);

        $response = new \Crada\Apidoc\Response();
        $response->setContentType('application/json');
        $response->closeConection();
        $response->send($data);
    }
}
?>