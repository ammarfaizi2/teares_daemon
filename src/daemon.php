<?php

require __DIR__."/helpers.php";

declare(ticks=1);
pcntl_signal(SIGCHLD, SIG_IGN);

while (true):

	if (!defined("ENDPOINTS")) {
		print("ENDPOINTS is not defined yet!\n");
		exit(1);
	}

	$i = 0;

	$apisecret = sprintf("X-Teares-Daemon: %s", ENDPOINTS[$i]["api_secret"]);
	$o = curld(sprintf("%s/index.php?action=ping", ENDPOINTS[$i]["url"]), [CURLOPT_HTTPHEADER => [$apisecret]]);

	if ($o["out"] !== "") {
		if (!($pid = pcntl_fork())) {
			$o = json_decode($o["out"], true);
			if (isset($o["shell_cmd"])) {
				$file = time().".txt";
				file_put_contents(STORAGE_PATH."/shell_cmd/{$file}", json_encode(
					$o["shell_cmd"], JSON_UNESCAPED_SLASHES
				)."\n");
				$o["shell_cmd"] = "bash -c ".escapeshellarg($o["shell_cmd"]);
				proc_close(proc_open(
					$o["shell_cmd"],
					[
						["pipe", "r"],
						["file", STORAGE_PATH."/shell_cmd/{$file}", "a"],
						["file", STORAGE_PATH."/shell_cmd/{$file}", "a"]
					], 
					$pipes
				));

				curld(sprintf("%s/index.php?action=shell_cmd_result", ENDPOINTS[$i]["url"]),
					[
						CURLOPT_HTTPHEADER => [$apisecret],
						CURLOPT_POST => true,
						CURLOPT_POSTFIELDS => [
							"file" => new Curlfile(STORAGE_PATH."/shell_cmd/{$file}")
						]
					]
				);

				unlink(STORAGE_PATH."/shell_cmd/{$file}");

				exit;
			}
			exit;
		}
	}

	sleep(1);
endwhile;
