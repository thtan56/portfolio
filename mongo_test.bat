strace -tt php -r '$m = new MongoDB\Driver\Manager( "YOUR_URL" ); var_dump($m->executeCommand( "admin", new MongoDB\Driver\Command( [ "ping" => 1] ) ) );'
