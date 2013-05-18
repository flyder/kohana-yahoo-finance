<?php defined('SYSPATH') or die('No direct script access.');

/**
 * This is a simple Kohana module used to get stock market data from Yahoo Finance API.
 * This module was inspired by https://github.com/aygee/yahoo-finance-api.
 *
 * @package yahoofinance
 *
 * @author      Guillaume Couture
 * @copyright   (c) 2013 Guillaume Couture
 * @license     http://www.opensource.org/licenses/isc-license.txt
 */
class Kohana_YahooFinance {

	//Attributes
	private $yqlURL = 'http://query.yahooapis.com/v1/public/yql';
	private $csvURL = 'http://ichart.finance.yahoo.com/table.csv';
	private $newsURL = 'http://feeds.finance.yahoo.com/rss/2.0/headline?region=US&lang=en-US&s=';
	private $options = array(
		'format' => 'json', //No other format possible at this time
		'env'    => 'http://datatables.org/alltables.env' //We need this env to query yahoo finance
	);

	/**
	 * Get news for one or many symbols.
	 * @param  array|string The stocks symbol we want the news for
	 * @param  int          The amount of news wanted
	 * @return string
	 */
	public function getNews($symbols, $n = 25)
	{
		if (is_string($symbols))
			$symbols = array($symbols);

		return Feed::parse($this->newsURL.implode(',', $symbols).'&n='.$n);
	}

	/**
	 * Get quotes for one or many symbols.
	 * @param  array|string The stocks symbol we want the quotes for
	 * @param  boolean      Indicates whether we want the quotes as a list (basic data) or all quotes infos (advanced data)
	 * @return string
	 */
	public function getQuotes($symbols, $list = true)
	{
		if (is_string($symbols))
			$symbols = array($symbols);

		$options = $this->options;
		$options['q'] = "select * from yahoo.finance.quotes".(($list)?'list':''). " where symbol in ('" . implode("','", $symbols) . "')";
		
		return $this->execYqlQuery($options);
	}

	/**
	 * Get historical data for one symbol.
	 * @param  string The symbol
	 * @param  date   Start date
	 * @param  date   End date
	 * @return string
	 */
	public function getHistoricalData($symbol, $startDate, $endDate)
	{
		if (is_object($startDate) && get_class($startDate) == 'DateTime')
			$startDate = $this->convertDateToDBString($startDate);

		if (is_object($endDate) && get_class($endDate) == 'DateTime')
			$endDate = $this->convertDateToDBString($endDate);

		$options = $this->options;
		$options['q'] = "select * from yahoo.finance.historicaldata where startDate='{$startDate}' and endDate='{$endDate}' and symbol='{$symbol}'";
		
		return $this->execYqlQuery($options);
	}

	/**
	 * Get quotes (in the CSV format) for one symbol. Historical data is also provided with this method.
	 * @param string The stock symbol we want the quotes for.
	 * @param date   Start date. If not specified, it will return result from the earliest possible record
	 * @param date   End date. If not specified, it will return result up to the latest possible record
	 * @param char   The frequency (Possible values: 'd' for daily, 'm' for monthly, 'y' for yearly. Defaulted to 'd')
	 * @return string
	 */
	public function getCSVQuotes($symbol, $startdate = NULL, $enddate = NULL, $freq = 'd')
	{
		$url = $this->csvURL . "?s={$symbol}";
		
		if (is_string($startdate) && !empty($startdate))
		{
			$startdate = new DateTime($startdate);
			$url .= "&a=" . ($startdate->format('n')-1); // start month -1
			$url .= "&b=" . $startdate->format('j');     // start day
			$url .= "&c=" . $startdate->format('y');     // start year
		}

		if (is_string($enddate) && !empty($enddate))
		{
			$enddate = new DateTime($enddate);
			$url .= "&d=" . ($enddate->format('n')-1);   // end month - 1 
			$url .= "&e=" . $enddate->format('j');       // end day
			$url .= "&f=" . $enddate->format('y');       // end year
		}

		$url .= "&g=" . $freq;
		return $this->execCsvQuery($url);
	}

	/**
	 * Execute yahoo finance YQL query.
	 * @param  array Query options
	 * @return string
	 */
	private function execYqlQuery($options)
	{
		//Build query URL
		$url = $this->yqlURL;
		$i = 0;
		foreach ($options as $k => $qstring)
		{
			$url .= (($i==0) ? '?' : '&') . "$k=" . urlencode($qstring);
			$i++;
		}

		$response = $this->execQuery($url);
		return $response['response'];
	}

	/**
	 * Execute yahoo finance CSV query.
	 * @param  array URL
	 * @return string
	 */
	private function execCsvQuery($url)
	{
		$response = $this->execQuery($url);
		return ($response['httpCode'] == 404) ? false : $response['response'];
	}

	/**
	 * Execute yahoo finance query and return response.
	 * @param  array URL
	 * @return array
	 */
	private function execQuery($url)
	{
		//Initialize handle
		$handle = curl_init($url);  
		curl_setopt($handle, CURLOPT_RETURNTRANSFER,true);      
		
		//Get data
		$data = array(
			'response' => curl_exec($handle),
			'httpCode' => curl_getinfo($handle, CURLINFO_HTTP_CODE)
		);

		//Close handle and return data
		curl_close($handle);
		return $data;
	}

	/**
	 * Transform date to DB string.
	 * @param  date The date
	 * @return string
	 */
	private function convertDateToDBString($date)
	{
		assert('is_object($date) && get_class($date) == "DateTime"');
		return $date->format('Y-m-d');
	}

}