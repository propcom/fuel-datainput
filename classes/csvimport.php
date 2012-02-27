<?php

namespace DataInput;

class FileNotFoundException extends \Exception {}

class CsvImport implements \Iterator {

	/* File Path */
	protected $_file;
	/* Head header? */
	protected $_has_header;
	/* CSV options */
	protected $_length;
	protected $_delimiter;
	protected $_enclosure;
	protected $_escape;
	/* CSV header */
	protected $_csv_header;
	/* File handle */
	protected $_csv_handle;
	/* Iterator counter */
	protected $_iterator_cnt = 0;

	protected function __construct($file, $has_header, $length, $delimiter, $enclosure, $escape) {
		if (!is_readable($file)) {
			throw new FileNotFoundException("File {$file} is not readable!");
		}

		$this->_file = $file;
		$this->_has_header = $has_header;
		$this->_length = $length;
		$this->_delimiter = $delimiter;
		$this->_enclosure = $enclosure;
		$this->_escape = $escape;

		$this->_csv_handle = fopen($file, 'r');

	}

	public static function forge($file = null, $has_header = null, $length = 0, $delimiter = ',', $enclosure='"', $escape='\\')
	{
		return new static($file, $has_header, $length, $delimiter, $enclosure, $escape);
	}

	/* Iterator interface */
	public function rewind() {
		rewind($this->_csv_handle);
		$this->_iterator_cnt = 0;
	}

	public function current() {
		return fgetcsv($this->_csv_handle, $this->_length, $this->_delimiter, $this->_enclosure, $this->_escape);
	}

	public function key() {
		return $this->_iterator_cnt;
	}

	public function next() {
		$this->_iterator_cnt++;
	}

	public function valid() {
		if(feof($this->_csv_handle)){
			return false;
		} else {
			return true;
		}
		//return feof($this->_csv_handle);
	}

}