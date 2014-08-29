<?php

namespace SevenPercent\HTML;

abstract class Element {

	protected $attributes = [];
	protected $children = [];

	public function __construct($attributes = [], $children = []) {
		if (($num_args = func_num_args()) !== 0) {
			if (is_array($attributes)) {
				switch ($num_args) {
					case 1:
						if (preg_match('/^\d+$/', implode(array_keys($attributes))) === 1) {
							$this->children = $attributes;
						} else {
							$this->attributes = $attributes;
						}
						break;
					default:
						$this->attributes = $attributes;
						break;
				}
			} elseif (substr(trim($attributes), 0, 1) === '#') {
				$this->attributes = ['id' => ltrim($attributes, '#')];
			} elseif (substr(trim($attributes), 0, 1) === '.') {
				$this->attributes = ['class' => implode(' ', array_filter(array_map(function ($className) {
					return ltrim($className, '.');
				}, explode(' ', str_replace(["\t", "\n", "\r"], ' ', $attributes))), @strlen))];
			} else {
				$this->children = [$attributes];
			}
			if ($num_args === 2) {
				$this->children = is_callable($children) ? $children() : (is_array($children) ? $children : [$children]);
			} elseif ($num_args > 2) {
				$args = func_get_args();
				array_shift($args);
				array_shift($args);
				$this->children = is_callable($children) ? $children(...$args) : (is_array($children) ? $children : [$children]);
			}
		}
	}

	public function escape($content, $options) {
		return str_replace('&gt;', '>', htmlentities(mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8'), $options, 'UTF-8', FALSE));
	}

	public function __toString() {
		$that = $this;
		return '<' . ($tagName = substr(strrchr(get_called_class(), '\\'), 1)) . implode(array_map(function ($key, $value) use ($that) {
			if ($key === $value) {
				return " $key";
			} elseif ($value === '') {
				return " $key=\"\"";
			} else {
				$quotes = '';
				foreach ([' ', '"', '\'', '>', '='] as $test) {
					if (strpos($value, $test) !== FALSE) {
						$quotes = '"';
						break;
					}
				}
				return " $key=$quotes{$that->escape($value, ENT_COMPAT)}$quotes";
			}
		}, array_keys($this->attributes), $this->attributes)) . '>' . implode(array_map(function ($childNode) use ($that) {
			return $childNode instanceof Element ? (string)$childNode : $that->escape(print_r($childNode, TRUE), ENT_NOQUOTES);
		}, $this->children)) . "</$tagName>";
	}
}
