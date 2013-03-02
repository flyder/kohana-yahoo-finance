# Kohana-Yahoo-Finance

This is a simple Kohana module used to get stock market data from Yahoo Finance API.
This module was inspired by https://github.com/aygee/yahoo-finance-api

## Version

Written for Kohana 3.3

## Installation

Step 1: Download the module into your modules subdirectory under modules/yahoofinance/.

Step 2: Enable the module in your bootstrap file:

	Kohana::modules(array(
		// 'auth'       => MODPATH.'auth',       // Basic authentication
		// 'codebench'  => MODPATH.'codebench',  // Benchmarking tool
		// 'database'   => MODPATH.'database',   // Database access
		// 'image'      => MODPATH.'image',      // Image manipulation
		// 'orm'        => MODPATH.'orm',        // Object Relationship Mapping
		// 'pagination' => MODPATH.'pagination', // Paging of results
		// 'userguide'  => MODPATH.'userguide',  // User guide and API documentation
		'yahoofinance'  => MODPATH.'yahoofinance'
	));

## Usage

This is very simple:

	$obj = new Kohana_YahooFinance();
	$data = $obj->getQuotes('AAPL');
	$datas = $obj->getQuotes(array('AAPL', 'MSFT'));