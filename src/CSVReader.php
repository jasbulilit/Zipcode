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

	const DEFAULT_ITERATOR_CLASS = '\ZipcodeCSV\CSVIterator';

	/**
	 * @var string
	 */
	private $_iterator_class;

	/**
	 * @param string $csv_path	filepath
	 * @param resource|null $context	stream context resource
	 * @param string $class_name	CSVIterator class name
	 * @throws \InvalidArgumentException
	 */
	public function __construct($csv_path, $context = null, $class_name = self::DEFAULT_ITERATOR_CLASS) {
		if ($class_name != self::DEFAULT_ITERATOR_CLASS
			&& ! is_subclass_of($class_name, self::DEFAULT_ITERATOR_CLASS)) {
			throw new \InvalidArgumentException('$class_name must extends ' . self::DEFAULT_ITERATOR_CLASS);
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
