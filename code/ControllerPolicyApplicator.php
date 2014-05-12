<?php
/**
 * This extension will register the policy with the RequestProcessor filter system to be run at postRequest stage
 * of the control pipeline. This is done with the help of the ControllerPolicyRequestFilter.
 *
 * This will override any specific headers that have been set by the default HTTP::add_cache_headers, which is
 * actually what we want. The policies are applied in the order they are added, so if there are two added the
 * latter will override the former.
 */
class ControllerPolicyApplicator extends Extension {

	private $requestFilter;

	function setRequestFilter($filter) {
		$this->requestFilter = $filter;
	}

	/**
	 * $policy injected to $this->owner
	 */

	function setPolicies($policies) {
		if (!is_array($policies)) $policies = array($policies);

		$this->owner->policies = $policies;
	}

	function getPolicies() {
		if (isset($this->owner) && isset($this->owner->policies)) {
			return $this->owner->policies;
		}
	}

	/**
	 * Register the requested policies with the global request filter. This doesn't mean the policies will be
	 * executed at this point - it will rather be delayed until the RequestProcessor::postRequest runs.
	 */
	function onAfterInit() {
		// Flip the policy array, so the first element in the array is the one applying last.
		// This is needed so the policies on inheriting Controllers are in the intuitive order:
		// the more specific overrides the less specific.
		$policies = array_reverse($this->getPolicies());

		if ($policies) foreach($policies as $policy) {
			$this->requestFilter->addPolicy($policy);
		}

	}

}
