<?php
/**
 * 郵便番号CSV
 *
 * @author	Jasmine
 * @link	https://github.com/jasbulilit/Zipcode
 * @package	ZipcodeCSV
 */
namespace ZipcodeCSV;

class CSVReader extends AbstractCSV implements \IteratorAggregate {

	/**
	 * @var string
	 */
	private $_iterator_class;

	/**
	 * @param string $csv_path		filepath
	 * @param resource $context		stream context resource
	 * @param string $class_name	CSVIterator class name
	 * @throws \InvalidArgumentException
	 */
	public function __construct($csv_path, $context = null, $class_name = '\ZipcodeCSV\CSVIterator') {
		$reflection = new \ReflectionClass($class_name);
		if (! $reflection->isSubclassOf('\ZipcodeCSV\CSVIterator')) {
			throw new \InvalidArgumentException('$class_name must implements Iterator.');
		}
		$this->_iterator_class = $class_name;

		parent::__construct($csv_path, $context);
	}

	/**
	 * @see \IteratorAggregate::getIterator()
	 * @return CSVIterator
	 */
	public function getIterator() {
		$options = array(
			'context' => $this->getContext()
		);

		return new $this->_iterator_class(
			$this->buildUri($this->getCSVPath(), parent::FILTER_CHAIN_READ),
			$this->delimiter,
			$this->enclosure,
			$this->escape,
			$options
		);
	}
}
