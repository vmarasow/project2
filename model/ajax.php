<?php 
	// query database and return in JSON
	function connect_to_database()
	{
		try {
			$dbh = new PDO('mysql:host=localhost;dbname=project2', 'jharvard', 'crimson');
			$dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
		}
		catch (PDOexception $e) {
			exit();
		}
		return $dbh;
	}

	function get_route($number)
	{
		//query database for stations joined with station info
		$dbh = connect_to_database();
		$sql = 'SELECT route'.$number.'.id, stations.lat, stations.lng 
		FROM route'. $number . ' JOIN stations ON stations.abbr=route' . $number . '.abbr';
		if (!$stmt = $dbh->prepare($sql)) {echo "error prepare"; exit();}
		$stmt->execute();
		$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
		function cmp ($a, $b)
		{
			if ($a["id"] === $b["id"])
			{
				return 0;
			}
			return ($a["id"] < $b["id"]) ? -1 : 1;
		}
		usort($result, "cmp");
		print_r(json_encode($result));
		$dbh = null;
	}
	//get_route(1);

	function get_names($number)
	{
		$dbh = connect_to_database();
		$sql = 'SELECT route'.$number.'.id, stations.name, stations.abbr
		FROM route'. $number . ' JOIN stations ON stations.abbr=route' . $number . '.abbr';
		if (!$stmt = $dbh->prepare($sql)) {echo "error prepare"; exit();}
		$stmt->execute();
		$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
		function cmp ($a, $b)
		{
			if ($a["id"] === $b["id"])
			{
				return 0;
			}
			return ($a["id"] < $b["id"]) ? -1 : 1;
		}
		usort($result, "cmp");
		print_r(json_encode($result));
		$dbh = null;

	}
	//get_names(1);

	function get_etd($station, $dest)
	{
		$xml = new SimpleXMLElement('http://api.bart.gov/api/etd.aspx?cmd=etd&orig=' . $station . '&key=MW9S-E7SL-26DU-VV8V', NULL, TRUE);
		$q = $xml->xpath("/root/station/etd");
		$q2 = null;
		foreach($q as $etd)
		{
			if ($etd->abbreviation[0].'' === $dest.'')
			{
				$q2 = $etd;
				break;
			}
		}
		print_r(json_encode($q2));
	}
	//get_etd('PITT', 'FRMT');

	function get_colors()
	{
		// connect to database and get all the colors
		$dbh = connect_to_database();
		$sql = 'SELECT `color`, `abbr`, `num` FROM routes';
		if (!$stmt = $dbh->prepare($sql)) {echo "error prepare"; exit();}
		$stmt->execute();
		$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
		print_r(json_encode($result));
		$dbh = null;
		// then pass them to whoever needs them in JSON
	}
	//get_colors();

	function get_color($line)
	{
		$dbh = connect_to_database();
		$sql = 'SELECT `color` FROM routes WHERE num=:route';
		if (!$stmt = $dbh->prepare($sql)) {echo "error prepare"; exit();}
		$stmt->bindValue(':route', $line);
		$stmt->execute();
		$result = $stmt->fetch(PDO::FETCH_ASSOC);
		$dbh = null;
		print_r(json_encode($result['color']));	
	}
	//get_color(11);