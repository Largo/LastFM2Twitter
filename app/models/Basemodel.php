<?php

class Basemodel extends Model {
	public function reload() {
		$object = self::factory(get_called_class())->find_one($this->id);
		$this->hydrate($object->as_array());
	}

	/**
	 * @return $this
     */
    public static function create() {
        return Model::factory(get_called_class())->create();
    }

    /**
     * @return array|IdiormResultSet
     */
    public static function find_many() {
        return Model::factory(get_called_class())->find_many();
    }

    /**
     * @param null $id
     * @return $this
     */
    public static function find_one($id = null) {
        return Model::factory(get_called_class())->find_one((int) $id);
    }

    /**
     * @param $id
     * @return $this
     */
    public static function findId($id) {
        return Model::factory(get_called_class())->find_one((int) $id);
    }

		/**
	     * @return ORM|ORMWrapper
	     */
	    public static function factory($class_name = '', $connection_name = null) {
	        return Model::factory(get_called_class());
	    }

    /**
     * Check if there is another Row with the same value for a field
     * @param $fieldName
     * @param $fieldValue
     * @return if there is one return true
     */
    public function uniqueIndexCheckFail($fieldName, $fieldValue) {
        $item = Model::factory(get_class($this))
                ->where($fieldName, $fieldValue)
                ->where_not_equal('id', (int) $this->id)
                ->find_one();

        return ($item != null);
    }

    protected function getEmptyMessageFor($name) {
        return 'Field ' . $name . ' cannot be empty.';
    }

    protected function getUniqueIndexFailMessageFor($name) {
        return 'There is already an entry with the same value at the field ' . $name;
    }

    protected function getNotNummericMessageFor($name) {
        return 'Field '  . $name . ' is not a number, but should be a number.';
    }

    public function validate() {
        return false;
    }

    public function save() {
        if ($this->id == null) {
            $this->created_on = date('c');
        }

        $validation = $this->validate();
        if ($validation != null) {
            return false;
        }

        $this->updated_on = date('c');
        return parent::save();
    }
}
