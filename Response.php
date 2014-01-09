<?php
/**
 * This file is part of the php-apidoc package.
 */
namespace Crada\Apidoc;

/**
 * @license http://opensource.org/licenses/bsd-license.php The BSD License
 * @author  Calin Rada <rada.calin@gmail.com>
 */
class Response
{
    /**
     * Set header
     *
     * @param string  $key
     * @param string  $value
     * @param integer $http_response_code Optional
     * @example $response->setHeader('Content-type','application/json')
     */
    public function setHeader($key, $value, $http_response_code = null)
    {
        header($key.': '.$value, null, $http_response_code);
    }

    /**
     * Set content type for the output
     *
     * @param string $s_contentType
     * @see http://www.freeformatter.com/mime-types-list.html
     */
    public function setContentType($s_contentType)
    {
        header('Content-type: '.$s_contentType);
    }

    /**
     * Sends connection: close header
     */
    public function closeConection()
    {
        header('Connection: close');
    }

    /**
     * Wraper for echo
     *
     * @param $data
     */
    public function send($data)
    {
        echo $data;
        exit(0);
    }
}
