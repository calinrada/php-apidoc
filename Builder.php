<?php
/**
 * This file is part of the php-apidoc package.
 */
namespace Crada\Apidoc;

use Crada\Apidoc\Extractor;
use Crada\Apidoc\View;
use Crada\Apidoc\View\JsonView;
use Crada\Apidoc\Exception;

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
    const VERSION = '1.0.0';

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
        foreach ($this->_st_classes as $class) {
            $st_output[] = $extractor->getAllClassAnnotations($class);
        }

        return $st_output[1];
    }

    private function saveTemplate($data)
    {
        $template   = __DIR__.'/Resources/views/template/index.html';
        $outputDir  = __DIR__.'/Resources/views/docs';
        $oldContent = file_get_contents($template);

        $st_search = array(
            "##content##",
            "##date##",
            "##version##"
        );

        $st_replace = array(
            $data,
            date("Y-m-d, H:i:s"),
            static::VERSION
        );

        $newContent = str_replace($st_search, $st_replace, $oldContent);

        if (!is_dir($outputDir)) {
            if (!mkdir($outputDir)) {
                throw new Exception("I can't create directory");
            }
        }

        if (!file_put_contents($outputDir.'/index.html', $newContent)) {
            throw new Exception("I can't save the content to $outputDir");
        }
    }

    private function generateTemplate()
    {
        $st_annotations = $this->extractAnnotations();

        $template = '';
        $counter = 0;

        foreach ($st_annotations as $class => $methods) {
            foreach ($methods as $name => $docs) {
                if(count($docs) == 0) continue;
                $template .= '
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h4 class="panel-title">
                                <a data-toggle="collapse" data-parent="#accordion'.$counter.'"
                                    href="#collapseOne'.$counter.'">'.$this->generateBadgeForMethod($docs).' '.$name.' </a>
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
                                      <p>Soon...</p>
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

        $header = '<table class="table table-hover"><thead><tr><th>Name</th>
         <th>Type</th><th>Required</th><th>Description</th></tr></thead>
         <tbody>';

        $body = '';

        foreach ($st_params['ApiParams'] as $params) {
            $body .= '<tr><td>'.$params['name'].'</td>';
            $body .= '<td>'.$params['type'].'</td>';
            $body .= '<td>'.($params['nullable']=='1' ? 'No' : 'Yes').'</td>';
            $body .= '<td>'.$params['description'].'</td></tr>';
        }

        $footer = '</tbody></table>';

        return $header.$body.$footer;
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
