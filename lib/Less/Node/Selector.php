<?php

//less.js : lib/less/tree/selector.js

namespace Less\Node;

class Selector {

	public $type = 'Selector';
	public $elements;
	public $extend;
	private $_css;

	public function __construct($elements, $extend = null) {
		$this->elements = $elements;
		$this->extend = $extend;
	}

	function accept($visitor) {
		$this->elements = $visitor->visit($this->elements);
	}

	public function match($other) {
		$len   = count($this->elements);

		$oelements = array_slice( $other->elements, (count($other->elements) && $other->elements[0]->value === "&") ? 1 : 0);
		$olen = count($oelements);

		$max = min($len, $olen);
		if ($len < $olen) {
			return false;
		} else {
			for ($i = 0; $i < $max; $i ++) {
				if ($this->elements[$i]->value !== $oelements[$i]->value) {
					return false;
				}
			}
		}
		return true;
	}

	public function toCSS ($env){
		if ($this->_css) {
			return $this->_css;
		}

		if (is_array($this->elements) && isset($this->elements[0]) &&
			$this->elements[0]->combinator instanceof \Less\Node\Combinator &&
			$this->elements[0]->combinator->value === '') {
				$this->_css = ' ';
		}else{
			$this->_css = '';
		}

		$temp = array_map(function ($e) use ($env) {
			if (is_string($e)) {
				return ' ' . trim($e);
			} else {
				return $e->toCSS($env);
			}
		}, $this->elements);
		$this->_css .= implode('', $temp);

		return $this->_css;
	}

	public function compile($env) {

		$elements = array();
		foreach($this->elements as $e){
			$elements[] = $e->compile($env);
		}
		return new \Less\Node\Selector($elements, $this->extend);
	}
}
