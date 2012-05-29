<?php

	/*found at http://snipplr.com/view.php?codeview&id=4622*/
	class Collection implements IteratorAggregate {
		protected $items;
		protected $attributes;

        /**
         * (PHP 5 &gt;= 5.0.0)<br/>
         * Retrieve an external iterator
         * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
         * @return Traversable An instance of an object implementing <b>Iterator</b> or
         * <b>Traversable</b>
         */
        public function getIterator()
        {
            return $this->items;
        }

        public function __construct() {
			$this->items = array();
			$this->attributes = array();
			$this->attributes['Count'] = 0;
			$this->attributes['IsFixedSize'] = false;
			$this->attributes['FixedSize'] = 0;
			$this->attributes['IsReadOnly'] = false;
		}
		
		public function __get($var) {
			if(key_exists($var, (array)$this->attributes)) {
				return $this->attributes[$var];
			}
			else {
				throw new Exception("The property {$var} does not exist", 0);
			}
		}
		
		public function __set($var, $value) {
			if(key_exists($var, (array)$this->attributes)) {
				$this->attributes[$var] = $value;
			}
			else {
				throw new Exception("The property {$var} cannot be set as it does not exist", 0);
			}
		}
		
		public function add($item) {
			if($this->attributes['IsFixedSize']) {
				if($this->attributes['Count'] < $this->attributes['FixedSize']) {
					$this->items[] = $item;
					$this->attributes['Count'] += 1;
				}
				else {
					throw new Exception("Cannot not add more items to collection. Max size is {$this->attributes['FixedSize']}", 0);
				}
			}
			else if($this->attributes['IsReadOnly']) {
				throw new Exception("Cannot add item to a read only collection", 0);
			}
			else {
				$this->items[] = $item;
				$this->attributes['Count'] += 1;
			}
		}
		
		public function addRange(array $items) {
			foreach($items as $item) {
				self::add($item);
			}
		}
		
		public function contains($item) {
			foreach($this->items as $i) {
				if($i == $item) {
					return true;
				}
			}
			return false;
		}
		
		public function get($index) {
			if(key_exsits($index, $this->items)) {
				return $this->items[$index];
			}
			return false;
		}
		
		public function getCollectionAsArray() {
			return $this->items;
		}

		
		public function indexOf($item, $startIndex = 0) {
			for($i = $startIndex; $i < $this->attributes['Count']; $i++) {
				if($this->items[$i] == $item) {
					return $i;
					break;
				}
			}
			return -1;
		}
		
		public function lastIndexOf($item) {
			$lastIndex = -1;
			
			for($i = 0; $i < $this->attributes['Count']; $i++) {
				if($this->items[$i] == $item) {
					$lastIndex = $i;
				}
			}
			return $lastIndex;
		}
		
		public function insert($index, $item) {
			if($this->attributes['IsFixedSize']) {
				if($index < $this->attributes['FixedSize']) {
					$this->items[$index] = $item;
					$this->attributes['Count'] += 1;
				}
				else {
					throw new Exception("Cannot insert item at {$index}. Max size is {$this->attributes['FixedSize']}", 0);
				}
			}
			else if($this->attributes['IsReadOnly']) {
				throw new Exception("Cannot insert an item into a read only collection", 0);
			}
			else {
				$this->items[$index] = $item;
				$this->attributes['Count'] += 1;
			}
		}
		
		public function remove($item) {
			$index = self::indexOf($item);
			self::removeAt($index);
		}
		
		public function removeAt($index) {
			if(!$this->attributes['IsReadOnly']) {
				if(key_exists($index, $this->items)) {
					unset($this->items[$index]);
					$this->attributes['Count'] -= 1;
				}
				else {
					throw new Exception("Index out of range. The index {$index} is out of range of the collection", 0);
				}
			}
			else {
				throw new Exception("Cannot remove item from read only collection", 0);
			}
		}
		
		public function removeRange($startIndex, $endIndex) {
			for($i = $startIndex; $i < $endIndex; $i++) {
				self::removeAt($i);
			}
		}
		
		public function sort() {
			sort($this->items, SORT_STRING);
		}

	}
?>
