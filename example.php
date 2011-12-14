<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'StatePaths.php';

$time_start = microtime(true);

// Finding optimal path to take through states
$sp = new StatePaths('climatedata');
$states = $sp->getStatesByRegion('W', 'M'); // fetch states from West -> Mountain region
$sp->setStartState('NM'); // find paths that start in NM
//$sp->setEndState('AZ'); // and end in AZ

$sp->getPathsByStates($states); // fetching possible paths 

$sp->setStartMonth(4); // start trip in April
$sp->setInterval('1 month'); // spending 1 month in each state
$sp->setTempRange(40, 68); // find paths that stay within this temp range

$sp->filterByTempRange(); // applying filter.

$sp->listPaths(true);

$time_end = microtime(true);
$time = $time_end - $time_start;
echo "Script completed in $time seconds\n";
