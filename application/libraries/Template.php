<?php (defined('BASEPATH')) OR exit('No direct script access allowed');

/**
 * Template Library
 * Handle masterview and views within masterview
 */

class Template {

    private $_ci;

    protected $brand_name = 'CodeIgniter Skeleton';
    protected $title_separator = ' - ';
    protected $ga_id = FALSE; // UA-XXXXX-X

    protected $layout = 'default';
    public $layoutPath = 'layout/default/';
    public $layoutPathPartial = 'layout/default/partial/';

    protected $title = FALSE;
    protected $description = FALSE;

    protected $metadata = array();
    private $stacksMetaData = array(); // just useful so var    

    protected $js = array();
    protected $css = array();

    function __construct()
    {
        $this->_ci =& get_instance();
    }

    /**
    * Set directories layout
    * @param string $layoutPath path in folder views/
    *
    * @return void
    */
    public function set_layoutPath($layoutPath)
    {
        $this->layoutPath = $layoutPath;

    }

    /**
     * Set page layout view (1 column, 2 column...)
     *
     * @access  public
     * @param   string  $layout
     * @return  void
     */
    public function set_layout($layout)
    {
        $this->layout = $layout;
    }

    /**
     * Set page title
     *
     * @access  public
     * @param   string  $title
     * @return  void
     */
    public function set_title($title)
    {
        $this->title = $title;
    }

    /**
     * Set page description
     *
     * @access  public
     * @param   string  $description
     * @return  void
     */
    public function set_description($description)
    {
        $this->description = $description;
    }

    /**
     * Add metadata
     *
     * @access  public
     * @param   string  $name
     * @param   string  $content
     * @return  void
     */
    public function add_metadata($name, $content, $position = 'ASC')
    {
        $name = htmlspecialchars(strip_tags($name));
        $content = htmlspecialchars(strip_tags($content));

        if ($name == 'description'
            || $name == 'keyworks' 
            || $name == 'og:title'
            || $name == 'og:description')
        {
            $this->metadata[$name] = '';
            $stacks = $this->stacksMetaData;
            $stacks[$name][] = $content;

            // first add -> first seen
            if ($position == 'ASC') {
                for ($i = 0; $i < count($stacks[$name]); $i++) {                    
                    $this->metadata[$name] .= empty($stacks[$name][$i]) ? '' : $stacks[$name][$i] .' ';
                }
            } else if ($position == 'DESC') { // fisrt add -> last seen
                for ($i = count($stacks[$name])-1; $i >= 0; $i--) {
                    $this->metadata[$name] .= empty($stacks[$name][$i]) ? '' : $stacks[$name][$i] . ' ';
                }
            }
            $this->metadata[$name] = trim($this->metadata[$name]);    
            $this->stacksMetaData = $stacks;

        } else {
            $this->metadata[$name] = $content;
        }
    }

    /**
     * Add js file path
     *
     * @access  public
     * @param   string  $js
     * @return  void
     */
    public function add_js($js)
    {
        $this->js[$js] = $js;
    }

    /**
     * Add css file path
     *
     * @access  public
     * @param   string  $css
     * @return  void
     */
    public function add_css($css)
    {
        $this->css[$css] = $css;
    }

    /**
     * Load view
     *
     * @access  public
     * @param   string  $view
     * @param   mixed   $data
     * @param   boolean $return
     * @return  void
     */
    public function load_view($view, $data = array(), $return = FALSE)
    {
        // Not include master view on ajax request
        if ($this->_ci->input->is_ajax_request())
        {
            $this->_ci->load->view($view, $data);
            return;
        }

        // Title
        if (empty($this->title))
        {
            $title = $this->brand_name;
        }
        else
        {
            $title = $this->title . $this->title_separator . $this->brand_name;
        }

        // Description
        $description = $this->description;

        // Metadata
        $metadata = array();
        foreach ($this->metadata as $name => $content)
        {
            if (strpos($name, 'og:') === 0)
            {
                $metadata[] = '<meta property="' . $name . '" content="' . $content . '">';
            }
            else
            {
                $metadata[] = '<meta name="' . $name . '" content="' . $content . '">';
            }
        }
        $metadata = implode('', $metadata);

        // Javascript
        $js = array();
        foreach ($this->js as $js_file)
        {
            $js[] = '<script src="' . assets_url($js_file) . '"></script>';
        }
        $js = implode('', $js);

        // CSS
        $css = array();
        foreach ($this->css as $css_file)
        {
            $css[] = '<link rel="stylesheet" href="' . assets_url($css_file) . '">';
        }
        $css = implode('', $css);
        
        $header = $this->_ci->load->view($this->layoutPathPartial . 'header', array(), TRUE);
        $footer = $this->_ci->load->view($this->layoutPathPartial . 'footer', array(), TRUE);
        $main_content = $this->_ci->load->view($view, $data, TRUE);

        $body = $this->_ci->load->view($this->layoutPath . $this->layout, array(
            'header' => $header,
            'footer' => $footer,
            'main_content' => $main_content,
        ), TRUE);

        return $this->_ci->load->view($this->layoutPathPartial . 'base_view', array(
            'title' => $title,
            'description' => $description,
            'metadata' => $metadata,
            'js' => $js,
            'css' => $css,
            'body' => $body,
            'ga_id' => $this->ga_id,
        ), $return);
    }
}

/* End of file Template.php */
/* Location: ./application/libraries/Template.php */