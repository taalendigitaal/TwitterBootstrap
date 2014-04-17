<?php
App::uses('PaginatorHelper', 'View/Helper');

class BootstrapPaginatorHelper extends PaginatorHelper {

	public function pagination($options = array()) {
		$default = array(
			'div' => 'pagination pagination-centered'
		);

		$model = (empty($options['model'])) ? $this->defaultModel() : $options['model'];

		$pagingParams = $this->request->params['paging'][$model];
		$pageCount = $pagingParams['pageCount'];

		if ($pageCount < 2) {
			// Don't display pagination if there is only one page
			return '';
		} else if ($pageCount == 2) {
			// If only two pages, don't show duplicate prev/next buttons
			$default['units'] = array('prev', 'numbers', 'next');
		} else {
			$default['units'] = array('first', 'prev', 'numbers', 'next', 'last');
		}

		$options += $default;

		$units = $options['units'];
		unset($options['units']);
		$class = $options['div'];
		unset($options['div']);

		$out = array();
		foreach ($units as $unit) {
			if ($unit === 'numbers') {
				$out[] = $this->{$unit}($options);
			} else {
				$out[] = $this->{$unit}(null, $options);
			}
		}
		return $this->Html->div($class, $this->Html->tag('ul', implode("\n", $out)));
	}

	public function pager($options = array()) {
		$default = array(
			'ul' => 'pager',
			'prev' => 'Previous',
			'next' => 'Next',
			'disabled' => 'hide',
		);
		$options += $default;

		$class = $options['ul'];
		unset($options['ul']);
		$prev = $options['prev'];
		unset($options['prev']);
		$next = $options['next'];
		unset($options['next']);

		$out = array();
		$out[] = $this->prev($prev, array_merge($options, array('class' => 'previous')));
		$out[] = $this->next($next, array_merge($options, array('class' => 'next')));

		return $this->Html->tag('ul', implode("\n", $out), compact('class'));
	}

	public function prev($title = null, $options = array(), $disabledTitle = null, $disabledOptions = array()) {
		$default = array(
			'title' => '<',
			'tag' => 'li',
			'model' => $this->defaultModel(),
			'class' => null,
			'disabled' => 'disabled',
		);
		$options += $default;
		if (empty($title)) {
			$title = $options['title'];
		}
		unset($options['title']);

		$disabled = $options['disabled'];
		$params = (array)$this->params($options['model']);
		if ($disabled === 'hide' && !$params['prevPage']) {
			return null;
		} elseif (!$params['prevPage']) {
                    $disabledTitle = $this->Html->tag('span', $title, array('escape' => false));
                } else {
                    $disabledTitle = $this->Html->link($title, array(), array('escape' => false));
                }
		unset($options['disabled']);

		return parent::prev($title, $options, $disabledTitle, array_merge($options, array(
			'escape' => false,
			'class' => $disabled,
		)));
	}

	public function next($title = null, $options = array(), $disabledTitle = null, $disabledOptions = array()) {
		$default = array(
			'title' => '>',
			'tag' => 'li',
			'model' => $this->defaultModel(),
			'class' => null,
			'disabled' => 'disabled',
		);
		$options += $default;
		if (empty($title)) {
			$title = $options['title'];
		}
		unset($options['title']);

		$disabled = $options['disabled'];
		$params = (array)$this->params($options['model']);
		if ($disabled === 'hide' && !$params['nextPage']) {
			return null;
		} elseif (!$params['nextPage']) {
                    $disabledTitle = $this->Html->tag('span', $title, array('escape' => false));
                } else {
                    $disabledTitle = $this->Html->link($title, array(), array('escape' => false));
                }
		unset($options['disabled']);

		return parent::next($title, $options, $disabledTitle, array_merge($options, array(
			'escape' => false,
			'class' => $disabled,
		)));
	}

	public function numbers($options = array()) {
            if ($options === true) {
			$options = array(
				'before' => ' | ', 'after' => ' | ', 'first' => 'first', 'last' => 'last'
			);
		}

		$defaults = array(
			'tag' => 'li', 'before' => null, 'after' => null, 'model' => $this->defaultModel(), 'class' => null,
			'modulus' => '11', 'separator' => false, 'first' => null, 'last' => null, 'ellipsis' => '<li class="disabled"><a href="#">…</a></li>',
			'currentClass' => 'active', 'currentTag' => 'a'
		);
		$options += $defaults;

		$params = (array)$this->params($options['model']) + array('page' => 1);
		unset($options['model']);

		if ($params['pageCount'] <= 1) {
			return false;
		}

		extract($options);
		unset($options['tag'], $options['before'], $options['after'], $options['model'],
			$options['modulus'], $options['separator'], $options['first'], $options['last'],
			$options['ellipsis'], $options['class'], $options['currentClass'], $options['currentTag']
		);

		$out = '';

		if ($modulus && $params['pageCount'] > $modulus) {
			$half = intval($modulus / 2);
			$end = $params['page'] + $half;

			if ($end > $params['pageCount']) {
				$end = $params['pageCount'];
			}
			$start = $params['page'] - ($modulus - ($end - $params['page']));
			if ($start <= 1) {
				$start = 1;
				$end = $params['page'] + ($modulus - $params['page']) + 1;
			}

			if ($first && $start > 1) {
				$offset = ($start <= (int)$first) ? $start - 1 : $first;
				if ($offset < $start - 1) {
					$out .= $this->first($offset, compact('tag', 'separator', 'ellipsis', 'class'));
				} else {
					$out .= $this->first($offset, compact('tag', 'separator', 'class', 'ellipsis') + array('after' => $separator));
				}
			}

			$out .= $before;

			for ($i = $start; $i < $params['page']; $i++) {
				$out .= $this->Html->tag($tag, $this->link($i, array('page' => $i), $options), compact('class')) . $separator;
			}

			if ($class) {
				$currentClass .= ' ' . $class;
			}

                        if ($currentTag && $currentTag === 'a') {
                            $out .= $this->Html->tag($tag, $this->link($params['page'], array('page' => $params['page']), $options), array('class' => $currentClass));
                        } elseif ($currentTag) {
				$out .= $this->Html->tag($tag, $this->Html->tag($currentTag, $params['page']), array('class' => $currentClass));
			} else {
				$out .= $this->Html->tag($tag, $params['page'], array('class' => $currentClass));
			}
			if ($i != $params['pageCount']) {
				$out .= $separator;
			}

			$start = $params['page'] + 1;
			for ($i = $start; $i < $end; $i++) {
				$out .= $this->Html->tag($tag, $this->link($i, array('page' => $i), $options), compact('class')) . $separator;
			}

			if ($end != $params['page']) {
				$out .= $this->Html->tag($tag, $this->link($i, array('page' => $end), $options), compact('class'));
			}

			$out .= $after;

			if ($last && $end < $params['pageCount']) {
				$offset = ($params['pageCount'] < $end + (int)$last) ? $params['pageCount'] - $end : $last;
				if ($offset <= $last && $params['pageCount'] - $end > $offset) {
					$out .= $this->last($offset, compact('tag', 'separator', 'ellipsis', 'class'));
				} else {
					$out .= $this->last($offset, compact('tag', 'separator', 'class', 'ellipsis') + array('before' => $separator));
				}
			}

		} else {
			$out .= $before;

			for ($i = 1; $i <= $params['pageCount']; $i++) {
				if ($i == $params['page']) {
					if ($class) {
						$currentClass .= ' ' . $class;
					}
					if ($currentTag) {
						$out .= $this->Html->tag($tag, $this->Html->tag($currentTag, $i), array('class' => $currentClass));
					} else {
						$out .= $this->Html->tag($tag, $i, array('class' => $currentClass));
					}
				} else {
					$out .= $this->Html->tag($tag, $this->link($i, array('page' => $i), $options), compact('class'));
				}
				if ($i != $params['pageCount']) {
					$out .= $separator;
				}
			}

			$out .= $after;
		}

		return $out;
	}

	public function first($title = null, $options = array()) {
		$default = array(
			'title' => '<<',
			'tag' => 'li',
			'after' => null,
			'model' => $this->defaultModel(),
			'separator' => null,
			'ellipsis' => null,
			'class' => null,
		);
		$options += $default;
		if (empty($title)) {
			$title = $options['title'];
		}
		unset($options['title']);

		return (parent::first($title, $options)) ? (parent::first($title, $options)) : $this->Html->tag(
			$options['tag'],
			$this->link($title, array(), $options),
			array('class' => 'disabled')
		);
	}

	public function last($title = null, $options = array()) {
		$default = array(
			'title' => '>>',
			'tag' => 'li',
			'after' => null,
			'model' => $this->defaultModel(),
			'separator' => null,
			'ellipsis' => null,
			'class' => null,
		);
		$options += $default;
		if (empty($title)) {
			$title = $options['title'];
		}
		unset($options['title']);

		$params = (array)$this->params($options['model']);

		return (parent::last($title, $options)) ? (parent::last($title, $options)) : $this->Html->tag(
			$options['tag'],
			$this->link($title, array(), $options),
			array('class' => 'disabled')
		);
	}

}
