<?php

namespace maybeworks\libs;

interface SearchInterface {

	const EVENT_PROCCESS_FILTER = 'proccessFilter';

	public function search($params = [], $formName = false, $options = []);

	public static function forSearch($params = [], $formName = false, $options = []);

}