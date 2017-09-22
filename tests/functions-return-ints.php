<?php

/**
 * Anonymous functions aren't supported in PHP 5.2.x which WordPress does still support,
 * so here's a bunch of helper functions for making filters return certain numbers. Ugh.
 */

function __return_int_1() {
	return 1;
}

function __return_int_100() {
	return 100;
}

function __return_int_150() {
	return 150;
}

function __return_int_300() {
	return 300;
}

function __return_int_350() {
	return 350;
}

function __return_int_500() {
	return 500;
}

function __return_int_768() {
	return 768;
}

function __return_int_1024() {
	return 1024;
}

function __return_int_1500() {
	return 1500;
}
