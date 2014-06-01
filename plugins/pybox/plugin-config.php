<?php

  // this file contains things you might need to personalize but probably do not.

define('CSCIRCLES_ASST_ID_DE', 11351); // hacky; will be fixed later

// optional reporting and exporting.
// if you want logging to work, you must define one of the two
// PYBOXLOG constants
define ('ON_CEMC_SERVER', UWPHOME == 'http://cscircles.cemc.uwaterloo.ca'
        || UWPHOME == 'http://cscircles.cemc.uwaterloo.ca/dev');
if (ON_CEMC_SERVER) {
  // if you want some of these, remove them from the 'if' block
  define('PYBOXLOG_EMAIL', 'daveagp@gmail.com');        // e-mail notifications for logging
  define('PPYBOXLOG', ABSPATH . '../../pybox_log.txt'); // file, writeable by apache, for logging
  define('PEXPORT', ABSPATH . '../../export/');         // export directory
}

// messages sent by CS Circles will have this return address:
//modified by Marija Djokic
define('CSCIRCLES_BOUNCE_EMAIL', 'm.djokic@kg.ac.rs'); 
//
/* this means they will bounce to the cemc.uwaterloo.ca site and not yours,
   and the links generated might not be correct.

   If you want to change this, you must also change the two constants at the top
   of send_email.py, you must configure your server as described at the top
   of bounce_email.py, and you must ensure /usr/bin/python3 works. */

// you probably don't need to change these
//maximum size 'POST' that submit.php will accept
define('POSTLIMIT', 20000); 
// if the cpu limit for a problem is X, walltime limit is FACTOR*X + BUFFER
define('WALLFACTOR', 2); 
define('WALLBUFFER', 4); 

// wordpress "turns on" magic quotes even if they are off,
// so no matter what is your server setting, this probably should be true
// http://wordpress.org/support/topic/sql-injection-escaping-and-magic-quotes
define('MAGIC_QUOTES_USED', true);

// you probably don't need to change these if you install our python3jail and safeexec repositories
define('PPYTHON3MODJAIL', '/bin/python3');
define('PSCRATCHDIRMODJAIL', 'scratch/');

