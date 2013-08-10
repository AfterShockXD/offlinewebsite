<?php if ( defined('SYSINIT') ) return;

/********************************************************************
 * System Environment Configuration
 ********************************************************************/

    # The current working environment to be used with the system. All error
    # reporting and logging will be adapted and set appropriately. If auto
    # is specified then the system will attempt to automatically determine
    # the working environment
    #
    # Available values: auto, development, production
    $config['environment'] = 'development';
    $config['debugging'] = false;

    # ----------------------------------------------------------------
    # Development Environment Configuration
    # ----------------------------------------------------------------

    # Development environment configuration directives
    $config['development']['db']['driver']   = 'mysql';
    $config['development']['db']['hostname'] = 'localhost';
    $config['development']['db']['username'] = 'root';
    $config['development']['db']['password'] = '';
    $config['development']['db']['database'] = 'dbgcon';
    
    

    # Path directives
    $config['development']['host'] = 'http://localhost/offlinewebsite/';

    # ----------------------------------------------------------------
    # Production Environment Configuration
    # ----------------------------------------------------------------

    # Production environment configuration directives
    $config['production']['db']['driver']   = 'mysql';
    $config['production']['db']['hostname'] = '127.0.0.1';
    $config['production']['db']['username'] = 'webuser';
    $config['production']['db']['password'] = 'Web@2012';
    $config['production']['db']['database'] = 'dbjump';

    # Path directives
    $config['production']['host'] = 'http://www.jumpertrax.com/';

/********************************************************************
 * System Initialization
 ********************************************************************/

    # Automatically determine the system environment
    if ( strToLower($config['environment']) == 'auto' )
    {
        $config['environment'] = fnMatch('local*', $_SERVER['HTTP_HOST'])
            ? 'development' : 'production';
    }

    # Update the global database details references from the selected environment
    $config['db']   = $config[ $config['environment'] ]['db'];
    $config['host'] = $config[ $config['environment'] ]['host'];

    # For backward's compatibility, we intiate Eduan's arb db_data variable which is
    # used accross the system classes to initiate database connections within the
    # class instances
    $db_data = (object) array
    (
    	'host' => $config['db']['hostname'],
    	'user' => $config['db']['username'],
    	'pass' => $config['db']['password'],
    	'name' => $config['db']['database']
    );

    switch( $config['environment'] )
    {
        default:

            ini_set('display_errors', true);
            error_reporting(-1);

            break;

        case 'development':

            ini_set('display_errors', true);
            error_reporting(-1);

            break;
    }

   
    # ----------------------------------------------------------------
    # System Constants/Definitions
    # ----------------------------------------------------------------

	$config['root'] = dirname(dirname(__FILE__));

   
?>
