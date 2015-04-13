<?php
/**
 * 郵便番号CSV
 *
 * @author	Jasmine
 * @link	https://github.com/jasbulilit/Zipcode
 * @package	ZipcodeCSV
 */
namespace ZipcodeCSV;

class CSVReader implements \IteratorAggregate {

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
	 * @var string
	 */
	private $_iterator_class;

	/**
	 * @var array
	 */
	private $_filters = array();

	/**
	 * @param string $csv_path		filepath/url(when allow_url_fopen enabled)
	 * @param resource $context		stream context resource
	 * @param string $class_name	CSVIterator class name
	 * @throws \InvalidArgumentException
	 */
	public function __construct($csv_path, $context = null, $class_name = '\ZipcodeCSV\CSVIterator') {
		$reflection = new \ReflectionClass($class_name);
		if (! $reflection->isSubclassOf('\ZipcodeCSV\CSVIterator')) {
			throw new \InvalidArgumentException('$class_name must implements Iterator.');
		}
		$this->_csv_path = $csv_path;
		$this->_context = $context;
		$this->_iterator_class = $class_name;
	}

	/**
	 * @see \IteratorAggregate::getIterator()
	 * @return CSVIterator
	 */
	public function getIterator() {
		$options = null;
		if ($this->_context !== null) {
			$options['context'] = $this->_context;
		}

		return new $this->_iterator_class(
			$this->_buildUri($this->_csv_path),
			$this->delimiter,
			$this->enclosure,
			$this->escape,
			$options
		);
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
	 * @access	private
	 * @param	string	$file_path
	 * @return	string
	 */
	private function _buildUri($file_path) {
		if (count($this->_filters) > 0) {
			return sprintf('php://filter/read=%s/resource=%s',
				urlencode(implode('|', array_keys($this->_filters))),
				$file_path);
		} else {
			return $file_path;
		}
	}
}
