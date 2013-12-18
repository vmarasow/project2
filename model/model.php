<?php
	require('ajax.php');
	if (isset($_REQUEST['get_colors']))
	{
		get_colors();
	}
	elseif (isset($_REQUEST['get_route']))
	{
		get_route($_REQUEST['line']);
	}
	elseif (isset($_REQUEST['get_color']))
	{
		get_color($_REQUEST['line']);
	}
	elseif (isset($_REQUEST['get_names']))
	{
		get_names($_REQUEST['line']);
	}
	elseif (isset($_REQUEST['get_etd']))
	{
		get_etd($_REQUEST['station'], $_REQUEST['dest']);
	}

?>