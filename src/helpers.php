<?php

if (!function_exists("curld")) {
	/**
	 * @param string $url
	 * @param array  $opt
	 * @return array
	 */
	function curld(string $url, array $opt = []): array
	{
		$ch = curl_init($url);
		$optf = [
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_SSL_VERIFYHOST => false,
			CURLOPT_USERAGENT => "tearesd"
		];

		foreach ($opt as $key => &$value) {
			$optf[$key] = $value;
		}

		unset($key, $value, $opt);
		curl_setopt_array($ch, $optf);
		$out = curl_exec($ch);
		$err = curl_error($ch);
		$ern = curl_errno($ch);
		$info = curl_getinfo($ch);
		curl_close($ch);

		return [
			"out" => $out,
			"err" => $err,
			"ern" => $ern,
			"info" => $info
		];
	}
}
