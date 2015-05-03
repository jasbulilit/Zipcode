<?php
/**
 * 郵便番号CSV
 *
 * @author	Jasmine
 * @link	https://github.com/jasbulilit/Zipcode
 * @package	ZipcodeCSV
 */
namespace ZipcodeCSV;

abstract class AbstractCSV {

	const FILTER_CHAIN_READ		= 'read';
	const FILTER_CHAIN_WRITE	= 'write';

	/**
	 * The field delimiter
	 *
	 * @var string
	 */
	protected $delimiter = ',';

	/**
	 * The field enclosure character
	 *
	 * @var string
	 */
	protected $enclosure = '"';

	/**
	 * The field escape character
	 *
	 * @var string
	 */
	protected $escape = '\\';

	/**
	 * @var string
	 */
	private $_csv_path;

	/**
	 * @var resource
	 */
	private $_context;

	/**
	 * @var array
	 */
	private $_filters = array();

	/**
	 * @param string $csv_path		filepath
	 * @param resource $context		stream context resource
	 * @throws \InvalidArgumentException
	 */
	public function __construct($csv_path, $context = null) {
		$this->_csv_path = $csv_path;
		$this->setContext($context);
	}

	/**
	 * @return string
	 */
	public function getDelimiter() {
		return $this->delimiter;
	}

	/**
	 * @return string
	 */
	public function getEnclosure() {
		return $this->enclosure;
	}

	/**
	 * @return string
	 */
	public function getEscape() {
		return $this->escape;
	}

	/**
	 * @return string
	 */
	public function getCSVPath() {
		return $this->_csv_path;
	}

	/**
	 * @return resource
	 */
	public function getContext() {
		return $this->_context;
	}

	/**
	 * @return array
	 */
	public function getFilters() {
		return $this->_filters;
	}

	/**
	 * @param string $delimiter
	 * @return void
	 */
	public function setDelimiter($delimiter) {
		$this->delimiter = $delimiter;
	}

	/**
	 * @param string $enclosure
	 * @return void
	 */
	public function setEnclosure($enclosure) {
		$this->enclosure = $enclosure;
	}

	/**
	 * @param string $escape
	 * @return void
	 */
	public function setEscape($escape) {
		$this->escape = $escape;
	}

	/**
	 * @param resource
	 * @return void
	 */
	public function setContext($context) {
		$this->_context = $context;
	}

	/**
	 * @param string $filter_name
	 * @return boolean
	 */
	public function hasFilter($filter_name) {
		return isset($this->_filters[$filter_name]);
	}

	/**
	 * @param string $filter_name
	 * @param string $filter_class	set null if use build-in filter
	 * @return void
	 * @throws \RuntimeException
	 */
	public function addFilter($filter_name, $filter_class = null) {
		if ($this->hasFilter($filter_name)) {
			throw new \RuntimeException("Filter '{$filter_name}' already exists.");
		}
		if ($filter_class !== null) {
			if (! stream_filter_register($filter_name, $filter_class)) {
				throw new \RuntimeException("Failed to register stream filter {$filter_class} as {$filter_name}");
			}
		} else {
			// build-in filter
			$filter_class = $filter_name;
		}

		$this->_filters[$filter_name] = $filter_class;
	}

	/**
	 * @param string $filter_name
	 * @return void
	 */
	public function removeFilter($filter_name) {
		if (! $this->hasFilter($filter_name)) {
			throw new \RuntimeException("Filter '{$filter_name}' not exists.");
		}
		unset($this->_filters[$filter_name]);
	}

	/**
	 * @access	protected
	 * @param	string	$file_path
	 * @param	string	$chain
	 * @return	string
	 */
	protected function buildUri($file_path, $chain) {
		if (count($this->_filters) > 0) {
			return sprintf('php://filter/%s=%s/resource=%s',
				$chain,
				urlencode(implode('|', array_keys($this->_filters))),
				$file_path);
		} else {
			return $file_path;
		}
	}
}
