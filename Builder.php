<?php
/**
 * This file is part of the php-apidoc package.
 */
namespace Crada\Apidoc;

use Crada\Apidoc\Extractor,
    Crada\Apidoc\View,
    Crada\Apidoc\View\JsonView,
    Crada\Apidoc\Exception;

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
    const VERSION = '1.3.4';

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
    protected $template_path   = null;

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

        if (!$template_path) {
             $template_path = __DIR__.'/Resources/views/template/index.html';
        }
        $this->template_path = $template_path;
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
        $oldContent = file_get_contents($this->template_path);

        $tr = array(
            '{{ content }}' => $data,
            '{{ title }}' => $this->_title,
            '{{ date }}'    => date('Y-m-d, H:i:s'),
            '{{ version }}' => static::VERSION,
        );
        $newContent = strtr($oldContent, $tr);

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
                $template[$section][] = strtr(static::$mainTpl, $tr);
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
          $ret = array();
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
                  $ret[] = strtr(static::$sampleReponseTpl, $tr);
              }
          }

          $responseBody = implode(PHP_EOL, $ret);
        }

        if(!isset($st_params['ApiReturnHeaders'])) {
          $responseHeaders = '';
        } else {
          $ret = array();
          foreach ($st_params['ApiReturnHeaders'] as $headers) {
            if(isset($headers['sample'])) {
              $tr = array(
                '{{ elt_id }}'      => $counter,
                '{{ response }}'    => $headers['sample'],
                '{{ description }}' => ''
              );

              $ret[] = strtr(static::$sampleReponseHeaderTpl, $tr);
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

        $body = array();
        foreach ($st_params['ApiHeaders'] as $params) {
            $tr = array(
                '{{ name }}'        => $params['name'],
                '{{ type }}'        => $params['type'],
                '{{ nullable }}'    => @$params['nullable'] == '1' ? 'No' : 'Yes',
                '{{ description }}' => @$params['description'],
            );
            $body[] = strtr(static::$paramContentTpl, $tr);
        }

        return strtr(static::$paramTableTpl, array('{{ tbody }}' => implode(PHP_EOL, $body)));

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
        
        $body = array();
        foreach ($st_params['ApiParams'] as $params) {
            $tr = array(
                '{{ name }}'        => $params['name'],
                '{{ type }}'        => $params['type'],
                '{{ nullable }}'    => @$params['nullable'] == '1' ? 'No' : 'Yes',
                '{{ description }}' => @$params['description'],
            );
            if (isset($params['sample'])) {
                $tr['{{ type }}'].= ' '.strtr(static::$paramSampleBtnTpl, array('{{ sample }}' => $params['sample']));
            }
            $body[] = strtr(static::$paramContentTpl, $tr);
        }

        return strtr(static::$paramTableTpl, array('{{ tbody }}' => implode(PHP_EOL, $body)));
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

      return strtr(static::$samplePostBodyTpl, array(
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
        $headers = array();
        $params = array();

        if (isset($st_params['ApiParams']) && is_array($st_params['ApiParams']))
        {
            foreach ($st_params['ApiParams'] as $param)
            {
                $params[] = strtr(static::$sandboxFormInputTpl, array('{{ name }}' => $param['name'], '{{ description }}' => $param['description'], '{{ sample }}' => $param['sample']));
            }
        }

        if (isset($st_params['ApiHeaders']) && is_array($st_params['ApiHeaders']))
        {
            foreach ($st_params['ApiHeaders'] as $header)
            {
                $headers[] = strtr(static::$sandboxFormInputTpl, array('{{ name }}' => $header['name']));
            }
        }

        $tr = array(
            '{{ elt_id }}' => $counter,
            '{{ method }}' => $st_params['ApiMethod'][0]['type'],
            '{{ route }}'  => $st_params['ApiRoute'][0]['name'],
            '{{ headers }}' => implode(PHP_EOL, $headers),
            '{{ params }}'   => implode(PHP_EOL, $params),
        );

        return strtr(static::$sandboxFormTpl, $tr);
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
        $st_labels = array(
            'POST'   => 'label-primary',
            'GET'    => 'label-success',
            'PUT'    => 'label-warning',
            'DELETE' => 'label-danger',
            'OPTIONS'=> 'label-info'
        );

        return '<span class="label '.$st_labels[$method].'">'.$method.'</span>';
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

    public static $mainTpl = '
<div class="panel panel-default">
    <div class="panel-heading">
        <h4 class="panel-title">
            {{ method }} <a data-toggle="collapse" data-parent="#accordion{{ elt_id }}" href="#collapseOne{{ elt_id }}"> {{ route }}</a>
        </h4>
    </div>
    <div id="collapseOne{{ elt_id }}" class="panel-collapse collapse">
        <div class="panel-body">

            <!-- Nav tabs -->
            <ul class="nav nav-tabs" id="php-apidoctab{{ elt_id }}">
                <li class="active"><a href="#info{{ elt_id }}" data-toggle="tab">Info</a></li>
                <li><a href="#sandbox{{ elt_id }}" data-toggle="tab">Sandbox</a></li>
                <li><a href="#sample{{ elt_id }}" data-toggle="tab">Sample output</a></li>
            </ul>

            <!-- Tab panes -->
            <div class="tab-content">

                <div class="tab-pane active" id="info{{ elt_id }}">
                    <div class="well">
                    {{ description }}
                    </div>
                    <div class="panel panel-default">
                      <div class="panel-heading"><strong>Headers</strong></div>
                      <div class="panel-body">
                        {{ headers }}
                      </div>
                    </div>
                    <div class="panel panel-default">
                      <div class="panel-heading"><strong>Parameters</strong></div>
                      <div class="panel-body">
                        {{ parameters }}
                      </div>
                    </div>
                    <div class="panel panel-default">
                      <div class="panel-heading"><strong>Body</strong></div>
                      <div class="panel-body">
                        {{ body }}
                      </div>
                    </div>
                </div><!-- #info -->

                <div class="tab-pane" id="sandbox{{ elt_id }}">
                    <div class="row">
                        <div class="col-md-12">
                        {{ sandbox_form }}
                        </div>
                        <div class="col-md-12">
                            Response
                            <hr>
                            <div class="col-md-12" style="overflow-x:auto">
                                <pre id="response_headers{{ elt_id }}"></pre>
                                <pre id="response{{ elt_id }}"></pre>
                            </div>
                        </div>
                    </div>
                </div><!-- #sandbox -->

                <div class="tab-pane" id="sample{{ elt_id }}">
                    <div class="row">
                        <div class="col-md-12">
                            {{ sample_response_headers }}
                            {{ sample_response_body }}
                        </div>
                    </div>
                </div><!-- #sample -->

            </div><!-- .tab-content -->
        </div>
    </div>
</div>';

static $samplePostBodyTpl = '<pre id="sample_post_body{{ elt_id }}">{{ body }}</pre>';

        static $sampleReponseTpl = '
{{ description }}
<hr>
<pre id="sample_response{{ elt_id }}">{{ response }}</pre>';
  
        static $sampleReponseHeaderTpl = '
<pre id="sample_resp_header{{ elt_id }}">{{ response }}</pre>';

        static $paramTableTpl = '
<table class="table table-hover">
    <thead>
        <tr>
            <th>Name</th>
            <th>Type</th>
            <th>Required</th>
            <th>Description</th>
        </tr>
    </thead>
    <tbody>
        {{ tbody }}
    </tbody>
</table>';

        static $paramContentTpl = '
<tr>
    <td>{{ name }}</td>
    <td>{{ type }}</td>
    <td>{{ nullable }}</td>
    <td>{{ description }}</td>
</tr>';

        static $paramSampleBtnTpl = '
<a href="javascript:void(0);" data-toggle="popover" data-trigger="focus" data-placement="bottom" title="Sample" data-content="{{ sample }}">
    <i class="btn glyphicon glyphicon-exclamation-sign"></i>
</a>';

        static $sandboxFormTpl = '
        <div class="col-md-6">
    Headers
    <hr/>
    <div class="headers">
    {{ headers }}
    </div>
    </div>
    <div class="col-md-6">
<form enctype="application/x-www-form-urlencoded" role="form" action="{{ route }}" method="{{ method }}" name="form{{ elt_id }}" id="form{{ elt_id }}">
    
    Parameters
    <hr/>
    {{ params }}
    <button type="submit" class="btn btn-success send" rel="{{ elt_id }}">Send</button>
</form></div>';

        static $sandboxFormInputTpl = '
<div class="form-group">
    <label class="control-label" for="{{ name }}">{{ name }}</label>
    <input type="text" class="form-control input-sm" id="{{ name }}" placeholder="{{ description }} - Ex: {{ sample }}" name="{{ name }}">
</div>';
}
