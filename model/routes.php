<?php
	// get the routes from the BART API
	/*
	 *	This script is meant to be ran from the command line,
	 *	and fills up the database with the data from BART for the cache.
	 */
	$line = 0;
	// connect to database
	try {
		$dbh = new PDO('mysql:host=localhost;dbname=project2', 'jharvard', 'crimson');
		$dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
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
		$number = $route->number;
		// each route is an object
		$xml = new SimpleXMLElement('http://api.bart.gov/api/route.aspx?cmd=routeinfo&route=' 
			. $number . '&key=UPKK-ZGH9-6SQ5-4LR3', NULL, TRUE);
		$stations = $xml->xpath('/root/routes/route/config');

		// store the route colors and numbers in route_info table 
		$stmt = $dbh->prepare('INSERT INTO routes (`color`, `num`, `name`, `abbr`) 
								VALUES (:color, :num, :name, :abbr) ON DUPLICATE KEY UPDATE color=color');
		$stmt->bindValue(':color', (string)$route->color);
		$stmt->bindValue(':num', (int)$number);
		$stmt->bindValue(':name', (string)$route->name);
		$stmt->bindValue(':abbr', (string)$route->abbr);
		$stmt->execute();

		// get all details for the stations
		foreach ($stations[0]->station as $station)
		{
			$xml = new SimpleXMLElement('http://api.bart.gov/api/stn.aspx?cmd=stninfo&orig=' 
				. $station . '&key=UPKK-ZGH9-6SQ5-4LR3', NULL, TRUE);
			$station_info = $xml->xpath('/root/stations/station');
			$name = $station_info[0]->name; $abbr = $station_info[0]->abbr; $lat = $station_info[0]->gtfs_latitude;
			$lng = $station_info[0]->gtfs_longitude; $address = $station_info[0]->address;
			 
			   // Uncomment this line to see the script do its work in the console
			 	echo "\t".$name."\t".$abbr."\t".$lat."\t".$lng."\t".$address."\n";
			 
			
			// fills all the route tables with the stop abbreviations
			$stmt = $dbh->prepare('INSERT INTO route' . (int)$number . ' (`abbr`) VALUES (:abbr) ON DUPLICATE KEY UPDATE abbr=abbr');
			echo $number;
			$stmt->bindValue(':abbr', $abbr);
			$stmt->execute();
			// fills the stations table with all the station data
			$sql = "INSERT INTO stations (name, abbr, lat, lng, address) VALUES (:name, :abbr, :lat, :lng, :address)
					ON DUPLICATE KEY UPDATE name=name";
			if (!$stmt = $dbh->prepare($sql)) {echo "error prepare"; exit();}

			$stmt->bindValue(':name', $name);
			$stmt->bindValue(':abbr', $abbr);
			$stmt->bindValue(':lat', $lat);
			$stmt->bindValue(':lng', $lng);
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