<?php
	// get the routes from the BART API
	/*
	 *	This script is meant to be ran from the command line,
	 *	and fills up the database with the data from BART for the cache.
	 */

	// connect to database
	try {
		$dbh = new PDO('mysql:host=localhost;dbname=project2', 'jharvard', 'crimson');
		$dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
		echo "connected to database.";
	}
	catch(PDOexception $e)
	{
		echo 'error conneting to database' . $e->getMessage;
	}
	// start getting the data from BART API
	$xml = new SimpleXMLElement('http://api.bart.gov/api/route.aspx?cmd=routes&key=UPKK-ZGH9-6SQ5-4LR3', NULL, TRUE);
	$routes = $xml->xpath('/root/routes/route');
	foreach ($routes as $route)
	{
		// each route is an object
		$xml = new SimpleXMLElement('http://api.bart.gov/api/route.aspx?cmd=routeinfo&route=' 
			. $route->number . '&key=UPKK-ZGH9-6SQ5-4LR3', NULL, TRUE);
		$stations = $xml->xpath('/root/routes/route/config');
		echo $route->number;
		// get all details for the stations
		foreach ($stations[0]->station as $station)
		{
			$xml = new SimpleXMLElement('http://api.bart.gov/api/stn.aspx?cmd=stninfo&orig=' 
				. $station . '&key=UPKK-ZGH9-6SQ5-4LR3', NULL, TRUE);
			$station_info = $xml->xpath('/root/stations/station');
			$name = $station_info[0]->name; $abbr = $station_info[0]->abbr; $lat = $station_info[0]->gtfs_latitude;
			$long = $station_info[0]->gtfs_longitude; $address = $station_info[0]->address;
			echo "\t".$name."\t".$abbr."\t".$lat."\t".$long."\t".$address."\n";
			// fills all the route tables with the stop abbreviations
			$sql = 'INSERT INTO route' . $route->number . ' (`abbr`) VALUES (:abbr) ON DUPLICATE KEY UPDATE abbr=abbr';
			
			try {
				$stmt = $dbh->prepare($sql);
			}
			catch (PDOexception $e)
			{
				echo "Error prepare" . $e->getMessage();
				exit();
			}
			$stmt->bindValue(':abbr', $abbr);
			$stmt->execute();
			// fills the stations table with all the station data
			$sql = "INSERT INTO stations (name, abbr, lat, `long`, address) VALUES (:name, :abbr, :lat, :long, :address)
					ON DUPLICATE KEY UPDATE name=name";
			if (!$stmt = $dbh->prepare($sql)) {echo "error prepare"; exit();}

			$stmt->bindValue(':name', $name);
			$stmt->bindValue(':abbr', $abbr);
			$stmt->bindValue(':lat', $lat);
			$stmt->bindValue(':long', $long);
			$stmt->bindValue(':address', $address);

			try {
				$stmt->execute();
			}
			catch (PDOexception $e)
			{
				echo "Error exec" . $e->getMessage();
				exit();
			}		
		}
	}