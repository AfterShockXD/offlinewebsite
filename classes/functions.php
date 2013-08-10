<?php

    /**
     * Load the database
     */
    function &load_database()
    {
        global $config, $db;

        # Attempt to initiate a database connection
        $db = new MySQL
        (
            $config['db']['hostname'],
            $config['db']['username'],
            $config['db']['password'],
            $config['db']['database']
        );

        return $db;
    }

    /**
     * Load the template
     */
    function &load_template( $tpl = 'default' )
    {
        global $template, $tags;

        # Append the .php extension if it doesn't exist
        $tpl  = rtrim(strtolower($tpl), '.php') .'.php';
        $tags = isset($tags) ? $tags : array();

        # Attempt to load the template class
        $template = new Template($tpl, null, true, $tags);

        return $template;
    }

    /**
     * Retrieve the file contents and return it
     */
    function load_file( $__file_path )
    {
        if ( !file_exists($__file_path) ) return false;

        ob_start();
        extract($GLOBALS);
        include($__file_path);
        $contents = ob_get_contents();
        ob_end_clean();

        return $contents;
    }

    /**
     * Deters AJAX Requests
     */
    function is_ajax()
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
            and (strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
    }

    /**
     * Redirect
     **/
    function redirect( $url )
    {
        ob_end_clean();
        header("Location: {$url}");
        die;
    }

    /**
     * URL
     */
    function url( $url = '' )
    {
        global $config;
        return rtrim($config['host'], '/') .'/'. ltrim($url, '/');
    }
    
    
    function calculate_distance($lat1, $lon1, $lat2, $lon2, $unit='K')
    {
    	$theta = $lon1 - $lon2;
    	$dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
    	$dist = acos($dist);
    	$dist = rad2deg($dist);
    	$miles = $dist * 60 * 1.1515;
    	$unit = strtoupper($unit);
    
    	if ($unit == "K") {
    		return (number_format($miles * 1.609344,3));
    	} else if ($unit == "N") {
    		return ($miles * 0.8684);
    	} else {
    		return $miles;
    	}
    }
    
    function add_date($orgDate,$mth) {
    	$cd = strtotime($orgDate);
    	$retDAY = date('Y-m-d', mktime(0,0,0,date('m',$cd)+$mth,date('d',$cd),date('Y',$cd)));
    	return $retDAY;
    }
    
?>