<?php

namespace maybeworks\libs;

interface SearchInterface {

	const EVENT_PROCESS_FILTER = 'processFilter';

	public function search($params = [], $formName = false, $options = []);

	public static function forSearch($params = [], $formName = false, $options = []);

}
