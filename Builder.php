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
 * Crada\Apidoc\Builder
 *
 * @license   http://opensource.org/licenses/bsd-license.php The BSD License
 * @author    Calin Rada <rada.calin@gmail.com>
 */
class Builder
{
    /**
     * Version number
     *
     * @var string
     */
    const VERSION = '1.3.0';

    /**
     * Classes collection
     *
     * @var array
     */
    private $_st_classes;

    /**
     * Output directory for documentation
     *
     * @var string
     */
    private $_output_dir;

    /**
     * Constructor
     *
     * @param array $st_classes
     */
    public function __construct(array $st_classes, $s_output_dir)
    {
        $this->_st_classes = $st_classes;
        $this->_output_dir = $s_output_dir;
    }

    /**
     * Extract annotations
     *
     * @return array
     */
    private function extractAnnotations()
    {
        foreach ($this->_st_classes as $class) {
            $st_output[] = Extractor::getAllClassAnnotations($class);
        }

        return end($st_output);
    }

    private function saveTemplate($data)
    {
        $template   = __DIR__.'/Resources/views/template/index.html';
        $oldContent = file_get_contents($template);

        $st_search = array(
            '##content##',
            '##date##',
            '##version##'
        );

        $st_replace = array(
            $data,
            date('Y-m-d, H:i:s'),
            static::VERSION
        );

        $newContent = str_replace($st_search, $st_replace, $oldContent);

        if (!is_dir($this->_output_dir)) {
            if (!mkdir($this->_output_dir)) {
                throw new Exception('I can\'t create directory');
            }
        }

        if (!file_put_contents($this->_output_dir.'/index.html', $newContent)) {
            throw new Exception('I can\'t save the content to $this->_output_dir');
        }
    }

    /**
     * Generate the content of the documentation
     *
     * @return boolean
     */
    private function generateTemplate()
    {
        $st_annotations = $this->extractAnnotations();

        $template = '';
        $counter = 0;
        $section = null;

        foreach ($st_annotations as $class => $methods) {
            foreach ($methods as $name => $docs) {
                if(isset($docs['ApiDescription'][0]['section']) && $docs['ApiDescription'][0]['section'] !== $section) {
                    $section = $docs['ApiDescription'][0]['section'];
                    $template .= '<h2>'.$section.'</h2>';
                }
                if(0 === count($docs)) {
                    continue;
                }
                $template .= '
<div class="panel panel-default">
    <div class="panel-heading">
        <h4 class="panel-title">
            '.$this->generateBadgeForMethod($docs).' <a data-toggle="collapse" data-parent="#accordion'.$counter.'" href="#collapseOne'.$counter.'"> '.$docs['ApiRoute'][0]['name'].' </a>
        </h4>
    </div>
    <div id="collapseOne'.$counter.'" class="panel-collapse collapse">
        <div class="panel-body">
            <!-- Nav tabs -->
            <ul class="nav nav-tabs" id="php-apidoctab'.$counter.'">
              <li class="active"><a href="#info'.$counter.'" data-toggle="tab">Info</a></li>
              <li><a href="#sandbox'.$counter.'" data-toggle="tab">Sandbox</a></li>
            </ul>
            <!-- Tab panes -->
            <div class="tab-content">
              <div class="tab-pane active" id="info'.$counter.'">
                   '.$docs['ApiDescription'][0]['description'].'
                    <hr>
                    '.$this->generateParamsTemplate($docs).'
              </div>
              <div class="tab-pane" id="sandbox'.$counter.'">
                  <div class="row">
                      <div class="col-md-6">
                          Parameters
                          <hr>
                          '.$this->generateRouteParametersForm($docs, $counter).'
                      </div>
                      <div class="col-md-6">
                          Headers
                          <hr>
                          Soon...
                      </div>
                  </div>
              </div>
            </div>
        </div>
    </div>
</div>';
                $counter++;
            }
        }
        $this->saveTemplate($template);

        return true;
    }

    /**
     * Generates the template for parameters
     *
     * @param  array       $st_params
     * @return void|string
     */
    private function generateParamsTemplate($st_params)
    {
        if (!isset($st_params['ApiParams'])) {
            return;
        }
        $tpl = '
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
        {{ body }}
    </tbody>
</table>';
        $body = array();
        foreach ($st_params['ApiParams'] as $params) {
            $body[] = '<tr>';
            $body[] = '<td>'.$params['name'].'</td>';
            $body[] = '<td>'.$params['type'].'</td>';
            $body[] = '<td>'.($params['nullable'] == '1' ? 'No' : 'Yes').'</td>';
            $body[] = '<td>'.$params['description'].'</td>';
            $body[] = '</tr>';
        }

        return str_replace('{{ body }}', implode(PHP_EOL, $body), $tpl);
    }

    /**
     * Generate route paramteres form
     *
     * @param array $st_params
     * @param integer $counter
     * @return void|mixed
     */
    private function generateRouteParametersForm($st_params, $counter)
    {
        if (!isset($st_params['ApiParams'])) {
            return;
        }

        $tpl = '<form enctype="application/x-www-form-urlencoded" role="form" action="'.$st_params['ApiRoute'][0]['name'].'" method="'.$st_params['ApiMethod'][0]['type'].'" name="form'.$counter.'" id="form'.$counter.'">{{ body }}';

        $body = array();
        foreach ($st_params['ApiParams'] as $params) {
            $body[] = '<div class="form-group">';
            $body[] = '<input type="text" class="form-control input-sm" id="'.$params['name'].'" placeholder="'.$params['name'].'" name="'.$params['name'].'">';
            $body[] = '</div>';
        }

        $body[] = '<button type="submit" class="btn btn-success send">Send</button>';
        $body[] = '</form>';

        return str_replace('{{ body }}', implode(PHP_EOL, $body), $tpl);
    }

    /**
     * Generates a badge for method
     *
     * @param  array  $data
     * @return string
     */
    private function generateBadgeForMethod($data)
    {
        $method = strtoupper($data['ApiMethod'][0]['type']);

        $st_labels = array(
            'POST' => 'label-primary',
            'GET' => 'label-success',
            'PUT' => 'label-warning',
            'DELETE' => 'label-danger'
        );

        $template = '<span class="label '.$st_labels[$method].'">'.$method.'</span>';

        return $template;
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
