<?php

require_once 'test_suite.php';

$exampleTestCases = [
	testFile('https://google.com', 'visitSee', 'ugauga', 'Test google'),
	testFile('https://google.com', 'visitSee', 'google', 'Test google'),
];

testSuite($exampleTestCases);

?>