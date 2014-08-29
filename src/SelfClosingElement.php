<?php

namespace SevenPercent\HTML;

abstract class SelfClosingElement extends Element {

	public function __toString() {
		return '<' . ($tagName = substr(strrchr(get_called_class(), '\\'), 1)) . implode(array_map(function ($key, $value) {
			if ($key === $value) {
				return " $key";
			} else {
				$quotes = '';
				foreach ([' ', '"', '\'', '>', '='] as $test) {
					if (strpos($value, $test) !== FALSE) {
						$quotes = '"';
						break;
					}
				}
				return " $key=$quotes{$this->escape($value, ENT_COMPAT)}$quotes";
			}
		}, array_keys($this->attributes), $this->attributes)) . '>';
	}
}
