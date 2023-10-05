<?php

function testSuite($testCases) {
	$useHtml = isset($_SERVER['HTTP_USER_AGENT']);
	// php_sapi_name() === 'cli'
	$date = date('d.m.Y H:i:s');
	$title = $date . PHP_EOL . PHP_EOL;

	if ($useHtml) {
		$redColor = "";  
		$greenColor = ""; 
		$resetColor = ""; 
		echo '<div style="background: black; color: floralwhite;
		padding: 8px 14px;
		line-height: 1.25;">';
		echo nl2br("<code>{$title}</code>");
	} else {
		$redColor = "\e[31m";  
		$greenColor = "\e[32m"; 
		$resetColor = "\e[0m"; 
		echo PHP_EOL;
		echo $title;
	}
	foreach ($testCases as $testCase) {
		$testData = testEngine($testCase);

		$assertionResult = ($testData['assert'] ? $greenColor : $redColor) . '<span style="color: ' . ($testData['assert'] ? 'lawngreen;">pass' : 'orangered;">fail') . '</span>' . $resetColor;
		$testName = $testCase['testName'] ?? '';
		$output = "<code>{$testName}: {$testData['testSubject']} (<b style='color: darkseagreen;'>{$testData['args']}</b>) - {$assertionResult}</code>" . PHP_EOL;
		if ($useHtml) {
			$output = nl2br($output);
		} else {
			$output = strip_tags($output);
		}
		echo $output;
	}
	if ($useHtml) {
		echo '</div>';
	} else {
		echo PHP_EOL;
	}
}

function testEngine($testCase) {
	if ($testCase['assertion'] == 'visitSee') {
		$result = @file_get_contents($testCase['file']);
		$assert = str_contains(($result ?? ''), $testCase['testValue']);
		$args = $testCase['testValue'];
		$testSubject = $testCase['file'];
	} else {
		if (method_exists($testCase['class'], $testCase['methodName'])) {
			if (isset($testCase['args'])) {
				$result = call_user_func_array([$testCase['class'], $testCase['methodName']], $testCase['args']);
			} else {
				$result = call_user_func([$testCase['class'], $testCase['methodName']]);
			}
		
			if ($testCase['assertion'] == 'assertEquals') {
				$assert = $testCase['testValue'] == $result;
			} else if ($testCase['assertion'] == 'assertVariableType') {
				$assert = $testCase['testValue'] == gettype($result);
			} else if ($testCase['assertion'] == 'assertInstanceOf') {
				$assert = $result instanceof $testCase['testValue'];
			}
		} else {
			$assert = false;
		}
		$args = implode(', ', (array_keys($testCase['args'] ?? [])));
		$testSubject = $testCase['methodName'];
	}

	$testData = [
		'testSubject' => $testSubject,
		'args' => $args,
		'result' => $result,
		'assert' => $assert,
	];

	return $testData;
}

function testClassMethod($class, $methodName, $assertion, $testValue, $testName, $args = []) {
	$testCase = [
		'testName' => $testName,
		'class' => $class,
		'methodName' => $methodName,
		'assertion' => $assertion,
		'testValue' => $testValue,
		'args' => $args,
	];

	return $testCase;
}

function testFile($file, $assertion, $testValue, $testName) {
	$testCase = [
		'testName' => $testName,
		'assertion' => $assertion,
		'file' => $file,
		'testValue' => $testValue,
	];

	return $testCase;
}

?>