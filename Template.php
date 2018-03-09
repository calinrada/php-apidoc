<?php
/**
 * This file is part of the php-apidoc package.
 */
namespace Crada\Apidoc;

/**
* Class imported from https://github.com/isRuslan/php-template
* @author  Ruslan Ismagilov <ruslan.ismagilov.ufa@gmail.com>
*
* @license http://opensource.org/licenses/bsd-license.php The BSD License
* @author  Calin Rada <rada.calin@gmail.com>
*/
class Template {
    /**
     * Content variables
     * @access private
     * @var array
     */
    private $vars = [];

    /**
     * Content delimiters
     * @access private
     * @var string
     */
    private $l_delim = '{{',
            $r_delim = '}}';

    /**
     * Set template property in template file
     * @access public
     * @param string $key property name
     * @param string $value property value
     */
    public function assign( $key, $value )
    {
        $this->vars[$key] = $value;
    }

    /**
     * Parse template file
     * @access public
     * @param string $template_file
     */
    public function parse( $template_file )
    {
        if ( file_exists( $template_file ) ) {
            $content = file_get_contents($template_file);

            foreach ( $this->vars as $key => $value ) {
                if ( is_array( $value ) ) {
                    $content = $this->parsePair($key, $value, $content);
                } else {
                    $content = $this->parseSingle($key, (string) $value, $content);
                }
            }

            return $content;
        } else {
            exit( '<h1>Template error</h1>' );
        }
    }

    /**
     * Parsing content for single varliable
     * @access private
     * @param string $key property name
     * @param string $value property value
     * @param string $string content to replace
     * @param integer $index index of loop item
     * @return string replaced content
     */
    private function parseSingle( $key, $value, $string, $index = null )
    {
        if ( isset( $index ) ) {
            $string = str_replace( $this->l_delim . '%index%' . $this->r_delim, $index, $string );
        }
        return str_replace( $this->l_delim . $key . $this->r_delim, $value, $string );
    }

    /**
     * Parsing content for loop varliable
     * @access private
     * @param string $variable loop name
     * @param string $value loop data
     * @param string $string content to replace
     * @return string replaced content
     */
    private function parsePair( $variable, $data, $string )
    {
        $match = $this->matchPair($string, $variable);
        if( $match == false ) return $string;

        $str = '';
        foreach ( $data as $k_row => $row ) {
            $temp = $match['1'];
            foreach( $row as $key => $val ) {
                if( !is_array( $val ) ) {
                    $index = array_search( $k_row, array_keys( $data ) );
                    $temp = $this->parseSingle( $key, $val, $temp, $index );
                } else {
                    $temp = $this->parsePair( $key, $val, $temp );
                }
            }
            $str .= $temp;
        }

        return str_replace( $match['0'], $str, $string );
    }

    /**
     * Match loop pair
     * @access private
     * @param string $string content with loop
     * @param string $variable loop name
     * @return string matched content
     */
    private function matchPair( $string, $variable )
    {
        if ( !preg_match("|" . preg_quote($this->l_delim) . 'loop ' . $variable . preg_quote($this->r_delim) . "(.+?)". preg_quote($this->l_delim) . 'end loop' . preg_quote($this->r_delim) . "|s", $string, $match ) ) {
            return false;
        }

        return $match;
    }
}
