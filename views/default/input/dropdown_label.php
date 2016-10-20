<?php
/**
 * Elgg dropdown input
 * Displays a dropdown (select) input field
 *
 * @warning Default values of FALSE or NULL will match "" (empty string) but not 0.
 *
 * @package Elgg
 * @subpackage Core
 *
 * @uses $vars["value"]          The current value, if any
 * @uses $vars["options"]        An array of strings representing the options for the dropdown field
 * @uses $vars["options_values"] An associative array of "value" => "option"
 *                               where "value" is the name and "option" is
 * 								 the value displayed on the button. Replaces
 *                               $vars["options"] when defined.
 * @uses $vars["class"]          Additional CSS class
 */

$class = (array) elgg_extract('class', $vars, []);
$class[] = 'elgg-input-dropdown';

$vars['class'] = $class;
$vars['disabled'] = elgg_extract('disabled', $vars, false);

$options_values = elgg_extract('options_values', $vars);
unset($vars['options_values']);

$options = elgg_extract('options', $vars);
unset($vars['options']);

$value = elgg_extract('value', $vars);
unset($vars['value']);

$list_options = '';
if (!empty($options_values) && is_array($options_values)) {
	foreach ($options_values as $opt_value => $option) {
		if (is_array($option)) {
			$group_list_options = '';
			foreach ($option as $some_value => $some_label) {
				$option_attrs = [
					'value' => $some_value,
					'selected' => (string) $some_value == (string) $value,
				];
				$group_list_options .= elgg_format_element('option', $option_attrs, $some_label);
			}
			
			$list_options .= elgg_format_element('optgroup', ['label' => $opt_value], $group_list_options);
		} else {
			$option_attrs = [
				'value' => $opt_value,
				'selected' => (string) $option == (string) $value,
			];
			$list_options .= elgg_format_element('option', $option_attrs, $option);
		}
	}
} elseif (!empty($options) && is_array($options)) {
	foreach ($options as $option) {
		$option_attrs = [
			'selected' => (string) $option == (string) $value,
		];
		$list_options .= elgg_format_element('option', $option_attrs, $option);
	}
}

echo elgg_format_element('select', $vars, $list_options);
