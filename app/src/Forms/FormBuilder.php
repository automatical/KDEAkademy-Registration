<?php

namespace App\Forms;

class FormBuilder {

	private $view;

	public function __construct($view) {
		$this->view = $view;
	}

	public function buildForm($formdata, $formvalues, $organisation = [99]) {
		$formvalues = $this->sanitizeFormValues($formvalues);

		$formCollections = [];

		foreach($organisation as $collection) {
			$inputs = [];
			for($i = 0; $i < $collection; $i++) {
				$inputs[] = array_shift($formdata->forms);
			}
			$formCollections[] = $inputs;
		}

		$formCollections = array_map(function($forms) use ($formvalues) {
			return implode("\r\n", array_map(function($form) use ($formvalues) {
				if(!$form) {
					return;
				}
				return $this->{"get".ucfirst($form->type)}($form, $formvalues);
			}, $forms));
		}, $formCollections);

		$formCollections[] = $this->getSubmitButton($formdata->submitlabel, $formvalues);

		return $formCollections;
	}

	private function getText($form, $formvalues) {
		return $this->view->fetch("formbuilder/text.twig", ['form' => (array)$form, 'values' => $formvalues]);
	}

	private function getDropdown($form, $formvalues) {
		if(is_string($form->data)) {
			$values = json_decode(file_get_contents(implode("", [dirname(__FILE__),"/../../configuration/data/", strtolower($form->data), ".json"])))->data;

			$form->data = new \stdClass();
			$form->data->type = "list";
			$form->data->values = $values;
		}

		return $this->view->fetch("formbuilder/dropdown.twig", ['form' => (array)$form, 'values' => $formvalues]);
	}

	private function getCheckboxes($form, $formvalues) {
		return $this->view->fetch("formbuilder/checkboxes.twig", ['form' => (array)$form, 'values' => $formvalues]);
	}

	private function getTextarea($form, $formvalues) {
		return $this->view->fetch("formbuilder/textarea.twig", ['form' => (array)$form, 'values' => $formvalues]);
	}

	private function getInformation($form, $formvalues) {
		return $this->view->fetch("formbuilder/information.twig", ['form' => (array)$form, 'values' => $formvalues]);
	}

	private function getDate($form, $formvalues) {
		return $this->view->fetch("formbuilder/date.twig", ['form' => (array)$form, 'values' => $formvalues]);
	}

	private function getSubmitButton($label, $formvalues) {
		return $this->view->fetch("formbuilder/submit.twig", ["label" => $label]);
	}

	private function sanitizeFormValues($formvalues) {
		$formvalues = (array)$formvalues;
		foreach($formvalues as $key => $val) {
			if(!is_string($val)) {
				$val = (array)$val;
				foreach($val as $k => $v) {
					$val[$k] = $v;
				}
			}
			$formvalues[str_replace('_', ' ', $key)] = $val;
		}

		return $formvalues;
	}

}