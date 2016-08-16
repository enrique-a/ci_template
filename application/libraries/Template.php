<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Template {
	const JS_INLINE_SEPARATOR = "\n";
	const SECTION_SEPARATOR = "\n";
	const VIEW_SEPARATOR = "\n";
	const TITLE_SEPARATOR = ' - ';
	const TEMPLATE_MAIN_VIEW = 'template.php';
	
	protected $ci;
	
	protected $css_files = [];
	protected $js_files = [];
	protected $js_inline = [];
	protected $sections = [];
	protected $views = []; // The main content
	
	protected $template_name = 'default';
	protected $template_views = 'templates';
	protected $template_media = 'media/templates';
	
	protected $title = '';
	protected $title_enabled = TRUE;
	protected $charset = 'utf-8';
	protected $language = 'en-us';
	protected $meta = ['keywords'=>'', 'description'=>''];
	protected $data = [];
	
	public $load;
	public $page;
	
	function __construct() {
		$this->ci =& get_instance();
		$this->load = new TemplateLoad($this);
		$this->page = new TemplatePage($this);
		$this->data['load'] = $this->load;
		$this->data['page'] = $this->page;
	}
	
	protected function add_file(string $path, string $file, string $type): string {
		$is_internal = ! preg_match('/^https?:\/\//', $file);
		
		if ($is_internal) {
			$file = $this->path($path . '/' . $file);
			if ( ! file_exists($file)) {
				show_error("Cannot locate {$type} file: {$file}.");
			}
			$file = base_url($file);
		}
		
		return $file;
	}
	
	public function add_css_file(string $file) {
		$file = $this->add_file('css', $file, 'stylesheet');
		
		if ( ! in_array($file, $this->css_files)) {
			$this->css_files[] = $file;
		}
	}
	
	public function get_css_files(): array {
		return $this->css_files;
	}
	
	public function add_js_file(string $file) {
		$file = $this->add_file('js', $file, 'javascript');
		
		if ( ! in_array($file, $this->js_files)) {
			$this->js_files[] = $file;
		}
	}
	
	public function get_js_files(): array {
		return $this->js_files;
	}
	
	public function start_js_inline() {
		ob_start();
	}
	
	public function end_js_inline() {
		$source = ob_get_clean();

		$source = preg_replace('/[\s ]*<\/?script[^>]*>[\s ]*/i', '', $source);
		$source = preg_replace('/\n+[\s ]*/', '', $source);
		
		$this->js_inline[] = $source;
	}
	
	public function get_js_inline(): string {
		return implode(self::JS_INLINE_SEPARATOR, $this->js_inline);
	}
	
	public function add_section($name, $view, $data=[]) {
		if ( ! array_key_exists($name, $this->sections)) {
			$this->sections[$name] = [];
		}
		
		$this->sections[$name][] = ['name'=>$view, 'data'=>$data];
		
		// $content = $this->ci->load->view($view, $data, true);
		// $this->sections[$name][] = $content;
	}
	
	public function display_section($name): string {
		if (isset($this->sections[$name])) {
			foreach($this->sections[$name] as $view) {
				$this->ci->load->view($view['name'], $view['data']);
				echo self::SECTION_SEPARATOR;
			}
		}
	}
	
	public function get_sections(): array {
		return $this->sections;
	}
	
	public function add_view(string $name, array $data=NULL) {
		if ( ! array_search($name, array_column($this->views, 'name')) ) {
			$this->views[] = ['name'=>$name, 'data'=>$data];
		}
	}
	
	public function set_template(string $template_name, string $template_views=NULL, string $template_media=NULL) {
		$this->template_name = $template_name;
		
		if($template_views != NULL) {
			$this->template_views = $template_views;
		}
		
		if($template_media != NULL) {
			$this->template_media = $template_media;
		}
	}
	
	/**
	 * File paths and URL related methods.
	 */
	
	public function path(string $url) {
		return $this->template_media . '/' . $this->template_name . '/' . $url;
	}
	
	public function url(string $url): string {
		return base_url($this->path($url));
	}
	
	public function url_img(string $file) {
		return $this->url('img/' . $file);
	}
	
	public function url_js(string $file) {
		return $this->url('js/' . $file);
	}
	
	public function url_css(string $file) {
		return $this->url('css/' . $file);
	}
	
	public function set_language(string $language) {
		$this->language = $language;
	}
	
	public function get_language(): string {
		return $this->language;
	}
	
	public function set_charset(string $charset) {
		$this->charset = $charset;
	}
	
	public function get_charset(): string {
		return $this->charset;
	}
	
	/**
	 * Set the title of the page.
	 *
	 * @param string $title
	 * @return void
	 */
	public function set_title(string $title) {
		$this->title = $title;
	}
	
	/**
	 * Append the given string at the end of the curent title.
	 *
	 * @param string $title
	 * @return void
	 */
	function append_title(string $title) {
		$this->title = $this->title . self::TITLE_SEPARATOR . $title;
	}
	
	/**
	 * Prepend the given string at the begining of the curent title.
	 *
	 * @param string $title
	 * @return void
	 */
	function prepend_title(string $title) {
		$this->title = $title . self::TITLE_SEPARATOR . $this->title;
	}
	
	public function get_title(): string {
		return $this->title;
	}
	
	public function set_title_enabled(boolean $enabled) {
		$this->title_enabled = $enabled;
	}
	
	public function is_title_enabled(): boolean {
		return $this->title_enabled;
	}
	
	/**
	 * Adds meta tags.
	 *
	 * @param string $name the name of the meta tag
	 * @param string $content the content of the meta tag
	 * @return void
	 */
	public function set_meta(string $name, string $content)
	{
		$this->meta[$name] = $content;
	}
	
	public function set_description(string $description) {
		$this->set_meta('description', $description);
	}
	
	public function set_keywords(string $keywords) {
		$this->set_meta('keywords', $keywords);
	}
	
	public function get_meta(): array {
		return $this->meta;
	}
	
	public function set_data(string $varname, $value) {
		$this->data[$varname] = $value;
	}
	
	public function append_data(array $data) {
		$this->data = array_merge($this->data, $data);
	}
	
	public function display_content(): string {
		// $content = '';
		foreach($this->views as $view) {
			$this->ci->load->view($view['name'], $view['data']);
			echo self::VIEW_SEPARATOR;
			// $content .= $this->ci->load->view($view['name'], $view['data'], true);
			// $content .= "\n";
		}
		// return $content;
	}
		
	public function display() {
		$view = $this->template_views . '/' . $this->template_name . '/' . self::TEMPLATE_MAIN_VIEW;
		$this->ci->load->view($view, $this->data);
	}
	
}

class TemplatePage {
	
	protected $template;
	
	function __construct(Template $template) {
		$this->template = $template;
	}
	
	public function __get($name) {
		switch($name) {
			case 'title':
				return $this->template->get_title();
			
			case 'title_enabled':
				return $this->template->is_title_enabled();
			
			case 'language':
				return $this->template->get_language();
			
			case 'charset':
				return $this->template->get_charset();
			
			case 'meta':
				return $this->template->get_meta();
			
			case 'css':
				return $this->template->get_css_files();
			
			case 'js':
				return $this->template->get_js_files();
			
			case 'js_inline':
				return $this->template->get_js_inline();
			
			case 'sections':
				return $this->template->get_sections();
			
			default:
				$trace = debug_backtrace();
				trigger_error(
					'Undefined property via __get(): ' . $name .
					' in ' . $trace[0]['file'] .
					' on line ' . $trace[0]['line'],
					E_USER_NOTICE);
		}
		
		return NULL;
	}
	
	public function url(string $url): string {
		return $this->template->url($url);
	}
	
	public function url_img(string $url): string {
		return $this->template->url_img($url);
	}
	
	public function url_js(string $url): string {
		return $this->template->url_js($url);
	}
	
	public function url_css(string $url): string {
		return $this->template->url_css($url);
	}
	
	public function display_section(string $name): string {
		return $this->template->get_section($name);
	}
	
	public function display_content(): string {
		return $this->template->get_content();
	}
}

class TemplateLoad {
	
	protected $template;
	
	function __construct(Template $template) {
		$this->template = $template;
	}
	
	public function css(string $file) {
		$this->template->add_css_file($file);
	}
	
	public function js(string $file) {
		$this->template->add_js_file($file);
	}
	
	public function section($name, $view, $data=[]) {
		$this->template->add_section($name, $view, $data);
	}
	
	public function view(string $name, $data=NULL) {
		$this->template->add_view($name, $data);
	}
	
	public function js_begin() {
		$this->template->start_js_inline();
	}
	
	public function js_end() {
		$this->template->end_js_inline();
	}
}
