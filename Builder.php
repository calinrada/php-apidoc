<?php
/**
 * This file is part of the php-apidoc package.
 */
namespace Crada\Apidoc;

use Crada\Apidoc\Extractor;
use Crada\Apidoc\View\JsonView;
use Crada\Apidoc\Template;
use Crada\Apidoc\Exception;

/**
 * @license http://opensource.org/licenses/bsd-license.php The BSD License
 * @author  Calin Rada <rada.calin@gmail.com>
 */
class Builder
{
    /**
     * Version number
     *
     * @var string
     */
    const VERSION = '1.3.8';

    /**
     * Classes collection
     *
     * @var array
     */
    protected $_st_classes;

    /**
     * Output directory for documentation
     *
     * @var string
     */
    protected $_output_dir;

    /**
     * Title to be displayed
     * @var string
     */
    protected $_title;

    /**
     * Output filename for documentation
     *
     * @var string
     */
    protected $_output_file;

    /**
     * Template file path
     * @var string
     **/
    protected $template_path   = __DIR__.'/Resources/views/default';

    /**
     * Template object
     * @var Template
     */
    protected $o_template;

    /**
     * Constructor
     *
     * @param array $st_classes
     */
    public function __construct(array $st_classes, $s_output_dir, $title = 'php-apidoc', $s_output_file = 'index.html', $template_path = null)
    {
        $this->_st_classes = $st_classes;
        $this->_output_dir = $s_output_dir;
        $this->_title = $title;
        $this->_output_file = $s_output_file;

        if ($template_path) {
            $this->template_path = $template_path;
        }

        $this->o_template = new Template();
    }

    /**
     * Extract annotations
     *
     * @return array
     */
    protected function extractAnnotations()
    {
        foreach ($this->_st_classes as $class) {
            $st_output[] = Extractor::getAllClassAnnotations($class);
        }

        return end($st_output);
    }

    protected function saveTemplate($data, $file)
    {
        $this->o_template->assign('content', $data);
        $this->o_template->assign('title', $this->_title);
        $this->o_template->assign('date', date('Y-m-d, H:i:s'));
        $this->o_template->assign('version', static::VERSION);

        $newContent = $this->o_template->parse($this->template_path.'/index.html');

        if (!is_dir($this->_output_dir)) {
            if (!mkdir($this->_output_dir)) {
                throw new Exception('Cannot create directory');
            }
        }
        if (!file_put_contents($this->_output_dir.'/'.$file, $newContent)) {
            throw new Exception('Cannot save the content to '.$this->_output_dir);
        }
    }

    /**
     * Generate the content of the documentation
     *
     * @return boolean
     */
    protected function generateTemplate()
    {
        $st_annotations = $this->extractAnnotations();

        $template = array();
        $counter = 0;
        $section = null;
        $partial_template = $this->loadPartialTemplate('main');

        foreach ($st_annotations as $class => $methods) {
            foreach ($methods as $name => $docs) {
                if (isset($docs['ApiDescription'][0]['section'])) {
                  $section = $docs['ApiDescription'][0]['section'];
                }elseif(isset($docs['ApiSector'][0]['name'])){
                    $section = $docs['ApiSector'][0]['name'];
                }else{
                    $section = $class;
                }
                if (0 === count($docs)) {
                    continue;
                }

                $sampleOutput = $this->generateSampleOutput($docs, $counter);

                $tr = array(
                    '{{ elt_id }}'                  => $counter,
                    '{{ method }}'                  => $this->generateBadgeForMethod($docs),
                    '{{ route }}'                   => $docs['ApiRoute'][0]['name'],
                    '{{ description }}'             => $docs['ApiDescription'][0]['description'],
                    '{{ headers }}'                 => $this->generateHeadersTemplate($counter, $docs),
                    '{{ parameters }}'              => $this->generateParamsTemplate($counter, $docs),
                    '{{ body }}'                    => $this->generateBodyTemplate($counter, $docs),
                    '{{ sandbox_form }}'            => $this->generateSandboxForm($docs, $counter),
                    '{{ sample_response_headers }}' => $sampleOutput[0],
                    '{{ sample_response_body }}'    => $sampleOutput[1]
                );

                $template[$section][] = strtr($partial_template, $tr);
                $counter++;
            }
        }

        $output = '';

        foreach ($template as $key => $value) {
          array_unshift($value, '<h2>' . $key . '</h2>');
          $output .= implode(PHP_EOL, $value);
        }

        $this->saveTemplate($output, $this->_output_file);

        return true;
    }

    /**
     * Generate the sample output
     *
     * @param  array   $st_params
     * @param  integer $counter
     * @return string
     */
    protected function generateSampleOutput($st_params, $counter)
    {
        if (!isset($st_params['ApiReturn'])) {
            $responseBody = '';
        } else {
          $ret = [];
          $partial_template = $this->loadPartialTemplate('sampleReponseTpl');
          foreach ($st_params['ApiReturn'] as $params) {
              if (in_array($params['type'], array('object', 'array(object) ', 'array', 'string', 'boolean', 'integer', 'number')) && isset($params['sample'])) {
                  $tr = array(
                      '{{ elt_id }}'      => $counter,
                      '{{ response }}'    => $params['sample'],
                      '{{ description }}' => '',
                  );
                  if (isset($params['description'])) {
                      $tr['{{ description }}'] = $params['description'];
                  }
                  $ret[] = strtr($partial_template, $tr);
              }
          }

          $responseBody = implode(PHP_EOL, $ret);
        }

        if(!isset($st_params['ApiReturnHeaders'])) {
          $responseHeaders = '';
        } else {
          $ret = [];
          $partial_template = $this->loadPartialTemplate('sampleReponseHeaderTpl');

          foreach ($st_params['ApiReturnHeaders'] as $headers) {
            if(isset($headers['sample'])) {
              $tr = array(
                '{{ elt_id }}'      => $counter,
                '{{ response }}'    => $headers['sample'],
                '{{ description }}' => ''
              );

              $ret[] = strtr($partial_template, $tr);
            }
          }

          $responseHeaders = implode(PHP_EOL, $ret);
        }

        return array($responseHeaders, $responseBody);
    }

    /**
     * Generates the template for headers
     * @param  int          $id
     * @param  array        $st_params
     * @return void|string
     */
    protected function generateHeadersTemplate($id, $st_params)
    {
        if (!isset($st_params['ApiHeaders']))
        {
             return;
        }

        $body = [];
        $partial_template = $this->loadPartialTemplate('paramContentTpl');

        foreach ($st_params['ApiHeaders'] as $params) {
            $tr = array(
                '{{ name }}'        => $params['name'],
                '{{ type }}'        => $params['type'],
                '{{ nullable }}'    => @$params['nullable'] == '1' ? 'No' : 'Yes',
                '{{ description }}' => @$params['description'],
            );
            $body[] = strtr($partial_template, $tr);
        }

        return strtr($this->loadPartialTemplate('paramTableTpl'), array('{{ tbody }}' => implode(PHP_EOL, $body)));

    }

    /**
     * Generates the template for parameters
     *
     * @param  int         $id
     * @param  array       $st_params
     * @return void|string
     */
    protected function generateParamsTemplate($id, $st_params)
    {
        if (!isset($st_params['ApiParams']))
        {
             return;
        }

        $body = [];
        $paramSampleBtnTpl = $this->loadPartialTemplate('paramSampleBtnTpl');
        $paramContentTpl = $this->loadPartialTemplate('paramContentTpl');

        foreach ($st_params['ApiParams'] as $params) {
            $tr = array(
                '{{ name }}'        => $params['name'],
                '{{ type }}'        => $params['type'],
                '{{ nullable }}'    => @$params['nullable'] == '1' ? 'No' : 'Yes',
                '{{ description }}' => @$params['description'],
            );

            if (isset($params['sample'])) {
                $tr['{{ type }}'].= ' '.strtr($paramSampleBtnTpl, array('{{ sample }}' => $params['sample']));
            }

            $body[] = strtr($paramContentTpl, $tr);
        }

        return strtr($this->loadPartialTemplate('paramTableTpl'), array('{{ tbody }}' => implode(PHP_EOL, $body)));
    }

    /**
     * Generate POST body template
     *
     * @param  int      $id
     * @param  array    $body
     * @return void|string
     */
    private function generateBodyTemplate($id, $docs)
    {
      if (!isset($docs['ApiBody']))
      {
        return;
      }

      $body = $docs['ApiBody'][0];

      return strtr($this->loadPartialTemplate('samplePostBodyTpl'), array(
        '{{ elt_id }}' => $id,
        '{{ body }}' => $body['sample']
      ));

    }

    /**
     * Generate route paramteres form
     *
     * @param  array      $st_params
     * @param  integer    $counter
     * @return void|mixed
     */
    protected function generateSandboxForm($st_params, $counter)
    {
        $headers = [];
        $params = [];

        $sandboxFormInputTpl = $this->loadPartialTemplate('sandboxFormInputTpl');

        if (isset($st_params['ApiParams']) && is_array($st_params['ApiParams']))
        {
            foreach ($st_params['ApiParams'] as $param)
            {
                $params[] = strtr($sandboxFormInputTpl, [
                    '{{ type }}' => $param['type'],
                    '{{ name }}' => $param['name'],
                    '{{ description }}' => $param['description'],
                    '{{ sample }}' => $param['sample']
                ]);
            }
        }

        if (isset($st_params['ApiHeaders']) && is_array($st_params['ApiHeaders']))
        {
            foreach ($st_params['ApiHeaders'] as $header)
            {
                $headers[] = strtr($sandboxFormInputTpl, [
                    '{{ type }}' => 'text',
                    '{{ name }}' => $header['name'],
                    '{{ description }}' => $header['description'],
                    '{{ sample }}' => $header['sample']
                ]);
            }
        }

        $tr = array(
            '{{ elt_id }}' => $counter,
            '{{ method }}' => $st_params['ApiMethod'][0]['type'],
            '{{ route }}'  => $st_params['ApiRoute'][0]['name'],
            '{{ headers }}' => implode(PHP_EOL, $headers),
            '{{ params }}'   => implode(PHP_EOL, $params),
        );

        return strtr($this->loadPartialTemplate('sandboxFormTpl'), $tr);
    }

    /**
     * Generates a badge for method
     *
     * @param  array  $data
     * @return string
     */
    protected function generateBadgeForMethod($data)
    {
        $method = strtoupper($data['ApiMethod'][0]['type']);

        $st_labels = [
            'POST'   => 'label-primary',
            'GET'    => 'label-success',
            'PUT'    => 'label-warning',
            'DELETE' => 'label-danger',
            'PATCH'  => 'label-default',
            'OPTIONS'=> 'label-info'
        ];

        return '<span class="label '.$st_labels[$method].'">'.$method.'</span>';
    }

    /**
     * Load required template
     *
     * @param string $s_name Template name
     * @return string
     */
    public function loadPartialTemplate($s_name)
    {
        $content = file_get_contents($this->template_path."/partial/".$s_name.".html");

        return $content;
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
        return $this->generateTemplate();
    }
}
