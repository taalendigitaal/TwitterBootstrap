<?php

App::uses('FormHelper', 'View/Helper');
App::uses('Set', 'Utility');

class BootstrapFormHelper extends FormHelper
{
    public $settings = array(
        'form_search' => 'form-search',
        'form_inline' => 'form-inline',
        'form_horizontal' => 'form-horizontal',
        'class_group' => 'form-group',
        'class_labels' => 'control-label col-md-2',
        'class_inputs' => 'col-md-4',
        'class_actions' => 'col-md-4 col-md-offset-2',
        'class_action' => 'form-actions',
        'class_button' => 'btn btn-default',
        'class_error' => 'has-error',
        'ajaxSettings' => array(
            'class_labels' => 'col-md-4',
            'class_inputs' => 'col-md-8',
            'class_actions' => 'col-md-8 col-md-offset-4'
        )
    );

    public $helpers = array('Html' => array('className' => 'TwitterBootstrap.BootstrapHtml'));
    protected $_isHorizontal = false;
    protected $_Opts = array();

    public function create($model = null, $options = array())
    {
        $this->settings = array_merge($this->settings, $options);
        if ($this->request->isAjax && ! empty($this->settings['ajaxSettings'])) {
            $this->settings = array_merge($this->settings, $this->settings['ajaxSettings']);
        }
        if ($this->request->isAjax && ! empty($options['ajaxSettings'])) {
            $this->settings = array_merge($this->settings, $options['ajaxSettings']);
        }

        $class = explode(' ', $this->_extractOption('class', $options));
        $inputDefaults = $this->_extractOption('inputDefaults', $options, array());

        if (in_array($this->settings['form_horizontal'], $class)) {
            $this->_isHorizontal = true;
        }

        if (in_array($this->settings['form_search'], $class) || in_array($this->settings['form_inline'], $class)) {
            $options['inputDefaults'] = Set::merge($inputDefaults, array('div' => false, 'label' => false));
        } else {
            $options['inputDefaults'] = Set::merge($inputDefaults, array('div' => $this->settings['class_group']));
        }

        return parent::create($model, $options);
    }

    public function checkbox($fieldName, $options = array())
    {
        $label = $this->_extractOption('label', $this->_Opts[$fieldName]);
        if (!is_array($label)) {
            $label = array('text' => $label);
        }
        $after = $this->_extractOption('after', $this->_Opts[$fieldName]);

        if ($this->_isHorizontal) {
            $label['text'] = $after;
            $label['class'] = null;
        }

        $label = $this->addClass($label, 'checkbox');
        $text = $label['text'];
        unset($label['text']);
        $out = parent::checkbox($fieldName, $options) . $text;
        if (isset($options['checkboxLabel']) && $options['checkboxLabel'] === false) {
            return $out;
        }
        return $this->label($fieldName, $out, $label);
    }

    public function radio($fieldName, $radioOptions = array(), $options = array())
    {
        $options['legend'] = false;
        $options['separator'] = "\n";

        $inline = $this->_extractOption('inline', $options);
        unset($options['inline']);

        $out = parent::radio($fieldName, $radioOptions, $options);

        // normal radio buttons
        if (! $inline) {
            return $this->_restructureLabel($out, array('div' => 'radio'));
        }

        // inline radio buttons
        if ($inline === true) {
            return $this->_restructureLabel($out, array('class' => 'radio-inline'));
        }

        // radio buttons with button style
        $out = $this->_restructureLabel($out, array('class' => 'radio-inline btn btn-default', 'checkedClass' => 'active', 'disabledClass' => 'disabled'));
        return $this->Html->div('btn-group', $out, array('data-toggle' => 'buttons'));
    }

    protected function _restructureLabel($out, $options = array())
    {
        $out = preg_replace("/\n/", "", $out);
        $out = preg_replace("/\<input/", "\n<input", $out);
        $out = preg_replace("/\n/", "", $out, 1);
        $out = explode("\n", $out);

        $div = $this->_extractOption('div', $options);
        unset($options['div']);

        $checkedClass = $this->_extractOption('checkedClass', $options);
        unset($options['checkedClass']);

        $disabledClass = $this->_extractOption('disabledClass', $options);
        unset($options['disabledClass']);

        foreach ($out as &$_out) {
            $regex = "@";
            $regex .= ".*";
            $regex .= "(\<input[^>]+\>)";
            $regex .= ".*";
            $regex .= "\<label[^>]+\>";
            $regex .= "(.*)";
            $regex .= "\</label\>";
            $regex .= ".*";
            $regex .= "@";
            $input = preg_replace($regex, "$1$2", $_out);

            if (! $input) {
                continue;
            }

            $opt = $options;
            if ($checkedClass && preg_match('/\<input[^>]*checked="checked"[^>]*\>/', $_out)) {
                $opt['class'] = isset($opt['class']) ? implode(' ', array_merge(explode(' ', $opt['class']), explode(' ', $checkedClass))) : $checkedClass;
            }
            if ($disabledClass && preg_match('/\<input[^>]*disabled="disabled"[^>]*\>/', $_out)) {
                $opt['class'] = isset($opt['class']) ? implode(' ', array_merge(explode(' ', $opt['class']), explode(' ', $disabledClass))) : $disabledClass;
            }

            $_out = $this->Html->tag('label', $input, $opt);

            if ($div) {
                $_out = $this->Html->div($div, $_out);
            }
        }

        return implode("\n", $out);
    }

    public function select($fieldName, $options = array(), $attributes = array())
    {
        $multiple = $this->_extractOption('multiple', $attributes);
        $inline = $this->_extractOption('inline', $attributes);
        unset($attributes['inline']);

        $out = parent::select($fieldName, $options, $attributes);

        // select
        if ('checkbox' !== $multiple) {
            return $out;
        }

        // normal checkboxes
        if (! $inline) {
            return $this->_restructureLabel($out, array('div' => 'checkbox'));
        }

        // inline checkboxes
        if ($inline === true) {
            return $this->_restructureLabel($out, array('class' => 'checkbox-inline'));
        }

        // checkboxes with button style
        $out = $this->_restructureLabel($out, array('class' => 'checkbox-inline btn btn-default', 'checkedClass' => 'active', 'disabledClass' => 'disabled'));
        return $this->Html->div('btn-group', $out, array('data-toggle' => 'buttons'));
    }

    public function submit($caption = null, $options = array())
    {
        $default = array(
            'type' => 'submit',
            'class' => $this->settings['class_button'],
            'div' => $this->settings['class_action'],
            'icon' => null,
        );
        $options = array_merge($default, $this->_inputDefaults, $options);
        if ($options['div'] !== false && $this->_isHorizontal) {
            $options['div'] = $this->settings['class_action'];
        }
        if ($options['icon']) {
            $caption = $this->Html->icon($options['icon']) . ' ' . $caption;
            unset($options['icon']);
        }
        $div = $this->_extractOption('div', $options);
        unset($options['div']);
        $out = $this->button($caption, $options);
        return (false === $div) ? $out : $this->Html->div($div, $out);
    }

    public function input($fieldName, $options = array())
    {
        $options = array_merge(array('format' => array('before', 'label', 'between', 'input', 'error', 'after')), $this->_inputDefaults, $options);
        $this->_Opts[$fieldName] = $options;

        $type = $this->_extractOption('type', $options);
        $options = $this->_getType($fieldName, $options);

        $hidden = null;
        if ('hidden' === $options['type']) {
            $options['div'] = false;
            $options['label'] = false;
        } else {
            $options = $this->uneditable($fieldName, $options, true);
            $options = $this->addon($fieldName, $options, true);
            $options = $this->_setOptions($fieldName, $options);
            $options = $this->_controlGroupStates($fieldName, $options);
            $options = $this->_buildAfter($options);

            $hidden = $this->_hidden($fieldName, $options);
            if ($hidden) {
                $options['hiddenField'] = false;
            }
        }

        if (is_null($type) && empty($this->_Opts[$fieldName]['type'])) {
            unset($options['type']);
        }

        $disabled = $this->_extractOption('disabled', $options, false);
        if ($disabled) {
            $options = $this->addClass($options, 'disabled');
        }

        $div = $this->_extractOption('div', $options);
        $options['div'] = false;

        $before = $this->_extractOption('before', $options);
        $options['before'] = null;

        $modelKey = $this->model();
        $fieldKey = $this->field();
        $required = isset($options['required']) ? $options['required'] : $this->_introspectModel($modelKey, 'validates', $fieldKey);
        if ($div !== false && $required && $type !== 'hidden' && $type !== 'checkbox') {
            $div .= ' required';
        }

        $label = $this->_extractOption('label', $options);
        if (false !== $label) {
            if (!is_array($label)) {
                $label = array('text' => $label);
            }
            if (false !== $div) {
                $class = $this->_extractOption('class', $label, $this->settings['class_labels']);
                $label = $this->addClass($label, $class);
            }

            if ($required && is_array($label)) {
                $class = 'required';
                $label = $this->addClass($label, $class);
            }

            $text = $label['text'];
            unset($label['text']);
            $label = $this->label($fieldName, $text, $label);
        }
        $options['label'] = false;

        $between = $this->_extractOption('between', $options);
        $options['between'] = null;

        $divControls = $this->_extractOption('divControls', $options, $this->settings['class_inputs']);
        $options['divControls'] = null;

        $input = parent::input($fieldName, $options);

        $input = $hidden . ((false === $div) ? $input : $this->Html->div($divControls, $input));

        $out = $before . $label . $between . $input;
        return (false === $div) ? $out : $this->Html->div($div, $out);
    }

    protected function _getType($fieldName, $options)
    {
        if (!isset($options['type'])) {
            $this->setEntity($fieldName);
            $modelKey = $this->model();
            $fieldKey = $this->field();

            $options['type'] = 'text';
            if (isset($options['options'])) {
                $options['type'] = 'select';
            } elseif (in_array($fieldKey, array('psword', 'passwd', 'password'))) {
                $options['type'] = 'password';
            } elseif (isset($options['checked'])) {
                $options['type'] = 'checkbox';
            } elseif ($fieldDef = $this->_introspectModel($modelKey, 'fields', $fieldKey)) {
                $type = $fieldDef['type'];
                $primaryKey = $this->fieldset[$modelKey]['key'];
            }

            if (isset($type)) {
                $map = array(
                    'string' => 'text', 'datetime' => 'datetime',
                    'boolean' => 'checkbox', 'timestamp' => 'datetime',
                    'text' => 'textarea', 'time' => 'time',
                    'date' => 'date', 'float' => 'number',
                    'integer' => 'number'
                );

                if (isset($this->map[$type])) {
                    $options['type'] = $this->map[$type];
                } elseif (isset($map[$type])) {
                    $options['type'] = $map[$type];
                }
                if ($fieldKey == $primaryKey) {
                    $options['type'] = 'hidden';
                }
            }
            if (preg_match('/_id$/', $fieldKey) && $options['type'] !== 'hidden') {
                $options['type'] = 'select';
            }

            if ($modelKey === $fieldKey) {
                $options['type'] = 'select';
            }
        }
        return $options;
    }

    public function uneditable($fieldName, $options = array(), $before = false)
    {
        if ($before) {
            $class = explode(' ', $this->_extractOption('class', $options));
            if (in_array('uneditable-input', $class)) {
                $this->_Opts[$fieldName] = $options;
                $options['type'] = 'uneditable';
            }
            return $options;
        } else {
            return $this->Html->tag('span', $options['value'], $options['class']);
        }
    }

    public function addon($fieldName, $options = array(), $before = false)
    {
        if ($before) {
            $prepend = $this->_extractOption('prepend', $options);
            $append = $this->_extractOption('append', $options);
            if ($prepend || $append) {
                $this->_Opts[$fieldName] = $options;
                $options['type'] = 'addon';
            }
            return $options;
        } else {
            $type = $this->_extractOption('type', $this->_Opts[$fieldName]);

            $default = array('wrap' => 'span', 'class' => 'input-group-addon');
            $divOptions = array();
            foreach (array('prepend', 'append') as $addon) {
                $$addon = null;
                $option = (array) $this->_extractOption($addon, $options);
                if ($option) {
                    foreach ($option as $_option) {
                        if (!is_array($_option)) {
                            $_option = array($_option);
                        }
                        array_push($_option, array());
                        list($text, $addonOptions) = $_option;
                        $addonOptions += $default;

                        $wrap = $addonOptions['wrap'];
                        unset($addonOptions['wrap']);

                        $$addon .= $this->Html->tag($wrap, $text, $addonOptions);
                    }

                    unset($options[$addon]);
                    $divOptions = $this->addClass($divOptions, 'input-group');
                }
            }
            $out = $prepend . $this->{$type}($fieldName, $options) . $append;
            return $this->Html->tag('div', $out, $divOptions);
        }
    }

    protected function _setOptions($fieldName, $options)
    {
        if ('textarea' === $options['type']) {
            $options += array('cols' => false, 'rows' => '3');
        }
        if ('checkbox' === $options['type']) {
            if ($this->_isHorizontal) {
                $options['after'] = null;
            } else {
                $options['label'] = false;
            }
        }

        if ('checkbox' !== $options['type'] && 'radio' !== $options['type']) {
            if (!isset($options['class']) || !$options['class']) {
                $options['class'] = 'form-control';
            } else {
                $options['class'] .= ' form-control';
            }
        }

        return $options;
    }

    protected function _controlGroupStates($fieldName, $options)
    {
        $div = $this->_extractOption('div', $options);
        if (false !== $div) {
            $inlines = (array) $this->_extractOption('helpInline', $options, array());
            foreach ($options as $key => $value) {
                if (in_array($key, array('warning', 'success'))) {
                    unset($options[$key]);
                    array_unshift($inlines, $value);
                    $options = $this->addClass($options, $key, 'div');
                }
            }
            if ($inlines) {
                $options['helpInline'] = $inlines;
            }
        }
        if ($this->error($fieldName)) {
            $error = $this->_extractOption('error', $options, array());
            if (false !== $error) {
                $options['error'] = array_merge($error, array(
                    'attributes' => array(
                        'wrap' => 'span',
                        'class' => 'help-block',
                    ),
                ));
            }
            if (false !== $div) {
                $options = $this->addClass($options, $this->settings['class_error'], 'div');
            }
        }
        return $options;
    }

    protected function _buildAfter($options)
    {
        $outInline = array();
        $inlines = (array) $this->_extractOption('helpInline', $options, array());
        if ($inlines) {
            unset($options['helpInline']);
        }
        foreach ($inlines as $inline) {
            $outInline[] = $this->help($inline, array('type' => 'inline'));
        }
        $outInline = implode(' ', $outInline);

        $outBlock = array();
        $blocks = (array) $this->_extractOption('helpBlock', $options, array());
        if ($blocks) {
            unset($options['helpBlock']);
        }
        foreach ($blocks as $block) {
            $outBlock[] = $this->help($block, array('type' => 'block'));
        }
        $outBlock = implode('', $outBlock);

        $options['after'] = $outInline . $outBlock . $this->_extractOption('after', $options);
        return $options;
    }

    public function help($text, $options = array())
    {
        $classMap = array(
            'inline' => array('wrap' => 'span', 'class' => 'help-inline'),
            'block' => array('wrap' => 'p', 'class' => 'help-block'),
        );
        $options += array('type' => 'inline');
        $options += $this->_extractOption($options['type'], $classMap, array());
        unset($options['type']);
        $wrap = $options['wrap'];
        unset($options['wrap']);
        return $this->Html->tag($wrap, $text, $options);
    }

    protected function _hidden($fieldName, $options)
    {
        $type = $options['type'];
        if (!in_array($type, array('checkbox', 'radio', 'select'))) {
            return null;
        }
        $multiple = $this->_extractOption('multiple', $options);
        $multiple = current(explode(' ', $multiple));
        if ('select' === $type && !$multiple) {
            return null;
        }
        $hiddenField = $this->_extractOption('hiddenField', $options, true);
        if (!$hiddenField) {
            return null;
        }

        $out = null;
        if ('checkbox' === $type || !isset($options['value']) || $options['value'] === '') {
            $options['secure'] = false;
            $options = $this->_initInputField($fieldName, $options);

            $style = ('select' === $type && 'checkbox' !== $multiple) ? null : '_';
            $hiddenOptions = array(
                'id' => $options['id'] . $style,
                'name' => $options['name'],
                'value' => '',
            );

            if ('checkbox' === $type) {
                $hiddenOptions['value'] = ($hiddenField !== true ? $hiddenField : '0');
                $hiddenOptions['secure'] = false;
            }
            if (isset($options['disabled']) && $options['disabled'] == true) {
                $hiddenOptions['disabled'] = 'disabled';
            }
            $out = $this->hidden($fieldName, $hiddenOptions);
        }
        return $out;
    }

    public function actionsCreate($options = array())
    {
        $class = $this->_extractOption('class', $options, $this->settings['class_actions']); // @todo configurable class

        return $this->Html->useTag('tagstart', 'div', array('class' => $this->settings['class_group'])) . $this->Html->useTag('tagstart', 'div', array('class' => $class));
    }

    public function actionsEnd()
    {
        return $this->Html->useTag('tagend', 'div') . $this->Html->useTag('tagend', 'div');
    }

    public function inputCreate($fieldName, $options = array())
    {
        $options = array_merge(array('format' => array('between', 'error')), $this->_inputDefaults, $options);
        $this->_Opts[$fieldName] = $options;

        $modelKey = $this->model();
        $fieldKey = $this->field();
        $required = $this->_introspectModel($modelKey, 'validates', $fieldKey);

        $label = $this->_extractOption('label', $options);
        if (false !== $label) {
            if (! is_array($label)) {
                $label = array('text' => $label);
            }

            $class = $this->_extractOption('class', $label, $this->settings['class_labels']);

            $label = $this->addClass($label, $class);

            if ($required && is_array($label)) {
                $class = 'required';
                $label = $this->addClass($label, $class);
            }

            $text = $label['text'];
            unset($label['text']);
            $label = $this->label($fieldName, $text, $label);
        }

        return $this->Html->useTag('tagstart', 'div', array('class' => $this->settings['class_group'])) . $label . $this->Html->useTag('tagstart', 'div', array('class' => $this->settings['class_inputs']));
    }

    public function inputEnd()
    {
        return $this->Html->useTag('tagend', 'div') . $this->Html->useTag('tagend', 'div');
    }

}