<?php
namespace DataInput;

class FileNotFoundException extends \Exception {}
class ModelNameRequiredException extends \Exception {}

class Csv {

	/* File Path */
	protected $_file;
	/* Model Info */
	protected $_model_name;
	protected $_model;
	protected $_ignored_fields = array('updated_at','created_at','id');
	protected $_special_fields = array(
		array(
			'search' => array('latitude','longitude'),
			'callback' => 'add_lonlat_field'
		),
		array(
			'search' => array('lat','lon'),
			'callback' => 'add_lonlat_field'
		),
		array(
			'search' => array('lat','lng'),
			'callback' => 'add_lonlat_field'
		),// Damn you google.
	);
	/* Has header? */
	protected $_has_header;
	/* CSV options */
	protected $_length;
	protected $_delimiter;
	protected $_enclosure;
	protected $_escape;
	/* CSV header */
	protected $_csv_header;
	/* CSV Fields */
	protected $_csv_columns = array();
	/* File handle */
	protected $_csv_handle;
	protected $_fieldset;

	public function __construct($file, $model, $has_header,  $length, $delimiter, $enclosure, $escape) {
		if (!is_readable($file)) {
			throw new FileNotFoundException("File {$file} is not readable!");
		}

		if($model == null) {
			throw new ModelNameRequiredException("You must supply a model name to ".__CLASS__);	
		}

		$this->_model = $model::forge();
		$this->_model_name = $model;

		$this->_file = $file;
		$this->_has_header = $has_header;
		$this->_length = $length;
		$this->_delimiter = $delimiter;
		$this->_enclosure = $enclosure;
		$this->_escape = $escape;

		$this->_csv_handle = fopen($file, 'r');

		if ($this->_has_header) {
			$this->_csv_header = fgetcsv($this->_csv_handle);
			$this->_csv_columns = $this->_csv_header;
		} else {
			$columns = count(fgetcsv($this->_csv_handle));
			for($i = 0;$i < $columns; $i++){
				$column_num = $i + 1;
				$this->_csv_columns[] = "Column ".$column_num;
			}
		}

		/* check for blanks */
		$tidy_columns = array();
		foreach($this->_csv_columns as $key => $column){
			if($column == ''){
				$tidy_columns[] = 'Column '.($key + 1);
			} else {
				$tidy_columns[] = $column;
			}
		}

		$this->_csv_columns = $tidy_columns;

	}

	public static function forge($file = null, $model_name = null, $has_header = null, $length = 0, $delimiter = ',', $enclosure='"', $escape='\\')
	{
		return new static($file, $model_name, $has_header, $length, $delimiter, $enclosure, $escape);
	}

	public function generate_fieldset()
	{

		$this->_fieldset = \Fieldset::forge();

		// Get the properties of the supplied model. Get a keyed array for checks
		$fields = $this->_model->properties();
		$model_fields = array_keys($fields);
		$options = array('class' => 'import-hide');

		/* Run through all of our special fields and check for any matches.
		 * If array intersect returns results - sort them to index the keys correctly.
		 * If arrays match then we have a special field match and remove those fields
		 * from the main mapping. Call the items callback method.
		 */
		foreach($this->_special_fields as $spec_field){
			if(is_array($spec_field['search'])){
				sort($spec_field['search']);
				$result = array_intersect($model_fields,$spec_field['search']);
				sort($result);
				if($result == $spec_field['search']){
					$this->_ignored_fields = array_merge($this->_ignored_fields,$spec_field['search']);
					$this->$spec_field['callback']();
				}
			} else {
				//TODO: Add support for string special fields.
			}
		}

		foreach($fields as $name => $properties){
			if(!in_array($name,$this->_ignored_fields)){
				if(isset($properties['validation'])){
					$this->_fieldset->add($name,$properties['label'],$options,array($properties['validation']));
				} else {
					$this->_fieldset->add($name,$properties['label'],$options);
				}
			}
		}

	}

	protected function add_lonlat_field()
	{
		$options = array(
			'type' => 'select',
			'options' => $this->_csv_columns,
		);
		$this->_fieldset->add('geocoded','Generate latitude and longitude from:',$options);
	}

	public function get_csv_headers()
	{
		return $this->_csv_columns;
	}

	public function render_mapping()
	{
		$this->generate_fieldset();
		$view = \View::forge('mapper');
		$view->set('fieldset',$this->_fieldset,false);
		$view->set('columns',$this->get_csv_headers(),false);
		return $view;
	}

}