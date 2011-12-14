<?php
/**
 * This class provides the functionality to figure out possible paths
 * through the U.S. states using DFS http://en.wikipedia.org/wiki/Depth-first_search
 * This gets computational intensive as number of states increases ... aka don't 
 * run this against all 50 states. Instead you will want to add to list of states 
 * incrementally and if needed piece together smaller paths.
 * 
 * Climate Data: http://www.esrl.noaa.gov/psd/data/usclimate/tmp.state.19712000.climo
 * NOAA plans to have more current data in Feb 2012
 * 
 * Example problem this class solves:
 * 
 * You plan to take a road trip across the western united states in february.
 * You will spend 1 week in each state. You want to plan it so that you stay relatively warm
 * throughout the entire trip. Find the optimal path through the states to stay reasonably warm.
 * 
 * @link https://github.com/agentile/State-Paths
 * @author Anthony Gentile <asgentile@gmail.com>
 */
class StatePaths {
    /**
     * multidimensional array of states -> month -> temp
     */
    protected $states_month_temp = array();

    /**
     * numerical state month
     */
    public $start_month = 1;
    
    /**
     * date time object where we handle stay intervals for each state
     */
    public $start_date;
    
    /**
     * interval (time in state)
     */
    public $interval = '1 week';

    /**
     * define our comfort range (F) - Globally apply to all states
     */
    public $min_temp = 0;
    public $max_temp = 105;
    
    /**
     * define our comfort range (F) - State specific
     * 
     * e.g. array('NC' => array('min' => 40, 'max' => 70))
     */
    public $state_temp_ranges = array();

    /**
     * lets define the which states border each other
     */
    protected $state_borders = array(
        'AL' => array(
            'MS',
            'FL',
            'GA',
            'TN',
            'GA',
        ),
        'AZ' => array(
            'CA',
            'NV',
            'UT',
            'NM',
        ),
        'AR' => array(
            'MS',
            'OK',
            'TX',
            'LA',
            'TN',
            'MO',
        ),
        'CA' => array(
            'AZ',
            'NV',
            'OR',
        ),
        'CO' => array(
            'UT',
            'NM',
            'WY',
            'OK',
            'KS',
            'NE',
        ),
        'CT' => array(
            'NY',
            'RI',
            'MA',
        ),
        'DE' => array(
            'PA',
            'MD',
        ),
        'FL' => array(
            'AL',
            'GA',
        ),
        'GA' => array(
            'FL',
            'AL',
            'SC',
            'NC',
            'TN',
        ),
        'ID' => array(
            'WA',
            'OR',
            'MT',
            'WY',
            'NV',
            'UT',
        ),
        'IL' => array(
            'WI',
            'IA',
            'IN',
            'KY',
            'MO',
        ),
        'IN' => array(
            'IL',
            'MI',
            'OH',
            'KY',
        ),
        'IA' => array(
            'MN',
            'WI',
            'IL',
            'MO',
            'NE',
            'SD',
        ),
        'KS' => array(
            'NE',
            'MO',
            'CO',
            'OK',
        ),
        'KY' => array(
            'MO',
            'TN',
            'IL',
            'IN',
            'OH',
            'WV',
            'VA',
        ),
        'LA' => array(
            'TX',
            'AR',
            'MS',
        ),
        'ME' => array(
            'NH',
        ),
        'MD' => array(
            'PA',
            'DE',
            'VA',
            'WV',
        ),
        'MA' => array(
            'RI',
            'CT',
            'NH',
            'VT',
            'NY',
        ),
        'MI' => array(
            'IN',
            'OH',
        ),
        'MN' => array(
            'ND',
            'SD',
            'IA',
            'WI',
        ),
        'MS' => array(
            'LA',
            'AR',
            'TN',
            'AL',
        ),
        'MO' => array(
            'IA',
            'NE',
            'KS',
            'OK',
            'AR',
            'TN',
            'KY',
            'IL',
        ),
        'MT' => array(
            'ID',
            'WY',
            'SD',
            'ND',
        ),
        'NE' => array(
            'WY',
            'SD',
            'IA',
            'MO',
            'KS',
            'CO',
        ),
        'NV' => array(
            'CA',
            'OR',
            'ID',
            'UT',
            'AZ',
        ),
        'NH' => array(
            'ME',
            'VT',
            'MA',
        ),
        'NJ' => array(
            'PA',
            'NY',
        ),
        'NM' => array(
            'AZ',
            'TX',
            'OK',
            'CO',
        ),
        'NY' => array(
            'VT',
            'MA',
            'CT',
            'NJ',
            'PA',
        ),
        'NC' => array(
            'SC',
            'GA',
            'TN',
            'VA',
        ),
        'ND' => array(
            'MT',
            'SD',
            'MN',
        ),
        'OH' => array(
            'MI',
            'IN',
            'KY',
            'WV',
            'PA',
        ),
        'OK' => array(
            'CO',
            'KS',
            'MO',
            'AR',
            'TX',
            'NM',
        ),
        'OR' => array(
            'WA',
            'CA',
            'ID',
            'NV',
        ),
        'PA' => array(
            'OH',
            'WV',
            'MD',
            'NY',
            'NJ',
            'DE',
        ),
        'RI' => array(
            'MA',
            'CT',
        ),
        'SC' => array(
            'GA',
            'NC',
        ),
        'SD' => array(
            'ND',
            'NE',
            'MT',
            'WY',
            'IA',
            'MN',
        ),
        'TN' => array(
            'AR',
            'MO',
            'KY',
            'VA',
            'NC',
            'GA',
            'AL',
            'MS',
        ),
        'TX' => array(
            'NM',
            'OK',
            'AR',
            'LA',
        ),
        'UT' => array(
            'ID',
            'NV',
            'AZ',
            'CO',
            'WY',
        ),
        'VT' => array(
            'NH',
            'NY',
            'MA',
        ),
        'VA' => array(
            'KY',
            'WV',
            'TN',
            'NC',
            'MD',
        ),
        'WA' => array(
            'OR',
            'ID',
        ),
        'WV' => array(
            'PA',
            'OH',
            'KY',
            'VA',
            'MD',
        ),
        'WI' => array(
            'MN',
            'IA',
            'IL',
        ),
        'WY' => array(
            'MT',
            'ID',
            'UT',
            'CO',
            'NE',
            'SD',
        ),
    );
    
    /**
     * List of U.S. States, short to full names
     */
    public $us_states = array(
        'AL' => 'Alabama',
        'AK' => 'Alaska',
        'AS' => 'American Samoa',
        'AZ' => 'Arizona',
        'AR' => 'Arkansas',
        'CA' => 'California',
        'CO' => 'Colorado',
        'CT' => 'Connecticut',
        'DE' => 'Delaware',
        'DC' => 'District of Columbia',
        'FL' => 'Florida',
        'GA' => 'Georgia',
        'HI' => 'Hawaii',
        'ID' => 'Idaho',
        'IL' => 'Illinois',
        'IN' => 'Indiana',
        'IA' => 'Iowa',
        'KS' => 'Kansas',
        'KY' => 'Kentucky',
        'LA' => 'Louisiana',
        'ME' => 'Maine',
        'MD' => 'Maryland',
        'MA' => 'Massachusetts',
        'MI' => 'Michigan',
        'MN' => 'Minnesota',
        'MS' => 'Mississippi',
        'MO' => 'Missouri',
        'MT' => 'Montana',
        'NE' => 'Nebraska',
        'NV' => 'Nevada',
        'NH' => 'New Hampshire',
        'NJ' => 'New Jersey',
        'NM' => 'New Mexico',
        'NY' => 'New York',
        'NC' => 'North Carolina',
        'ND' => 'North Dakota',
        'OH' => 'Ohio',
        'OK' => 'Oklahoma',
        'OR' => 'Oregon',
        'PA' => 'Pennsylvania',
        'RI' => 'Rhode Island',
        'SC' => 'South Carolina',
        'SD' => 'South Dakota',
        'TN' => 'Tennessee',
        'TX' => 'Texas',
        'UT' => 'Utah',
        'VT' => 'Vermont',
        'VA' => 'Virginia',
        'WA' => 'Washington',
        'WI' => 'Wisconsin',
        'WV' => 'West Virginia',
        'WY' => 'Wyoming',
        'FM' => 'Federated States of Micronesia',
        'GU' => 'Guam',
        'MH' => 'Marshall Islands',
        'MP' => 'Northern Mariana Is.',
        'PW' => 'Palau Island',
        'PR' => 'Puerto Rico',
        'VI' => 'Virgin Islands',
    );
    
    /**
     * Top level regions
     */
    public $regions = array(
        'NE' => 'Northeast',
        'MW' => 'Midwest',
        'S'  => 'South',
        'W'  => 'West'
    );
    
    /**
     * Sub Regions
     */
    public $sub_regions = array(
        'WNC' => 'West North Central',
        'M' => 'Mountain',
        'P' => 'Pacific',
        'ENC' => 'East North Central',
        'WSC' => 'West South Central',
        'NE' => 'New England',
        'SA' => 'South Atlantic',
        'MA' => 'Middle Atlantic',
        'ESC' => 'East South Central'
    );
    
    /**
     * Parent child region mapping
     */
    public $regions_sub_regions = array(
        'NE' => array('NE', 'MA'),
        'MW' => array('ENC', 'WNC'),
        'S'  => array('SA', 'ESC', 'WSC'),
        'W'  => array('M', 'P')
    );
    
    /**
     * State with their region and subregions
     * 
     * Top level regions:
     *   'NE' => 'Northeast',
     *   'MW' => 'Midwest',
     *   'S'  => 'South',
     *   'W'  => 'West'
     *
     * Sub Regions:
     *   'WNC' => 'West North Central',
     *   'M' => 'Mountain',
     *   'P' => 'Pacific',
     *   'ENC' => 'East North Central',
     *   'WSC' => 'West South Central',
     *   'NE' => 'New England',
     *   'SA' => 'South Atlantic',
     *   'MA' => 'Middle Atlantic',
     *   'ESC' => 'East South Central'
     * 
     * Mapping:
     *   'NE' => array('NE', 'MA'),
     *   'MW' => array('ENC', 'WNC'),
     *   'S'  => array('SA', 'ESC', 'WSC'),
     *   'W'  => array('M', 'P')
     */
    public $states_by_region = array(
        'AS' => array('top' => null, 'sub' => null), 
        'FM' => array('top' => null, 'sub' => null),
        'GU' => array('top' => null, 'sub' => null),
        'MH' => array('top' => null, 'sub' => null),
        'MP' => array('top' => null, 'sub' => null),
        'PW' => array('top' => null, 'sub' => null),
        'PR' => array('top' => null, 'sub' => null),
        'VI' => array('top' => null, 'sub' => null),
        'CT' => array('top' => 'NE', 'sub' => 'NE'),
        'ME' => array('top' => 'NE', 'sub' => 'NE'),
        'MA' => array('top' => 'NE', 'sub' => 'NE'),
        'NH' => array('top' => 'NE', 'sub' => 'NE'),
        'RI' => array('top' => 'NE', 'sub' => 'NE'),
        'VT' => array('top' => 'NE', 'sub' => 'NE'),
        'NJ' => array('top' => 'NE', 'sub' => 'MA'),
        'NY' => array('top' => 'NE', 'sub' => 'MA'),
        'PA' => array('top' => 'NE', 'sub' => 'MA'),
        'IL' => array('top' => 'MW', 'sub' => 'ENC'),
        'IN' => array('top' => 'MW', 'sub' => 'ENC'),
        'MI' => array('top' => 'MW', 'sub' => 'ENC'),
        'OH' => array('top' => 'MW', 'sub' => 'ENC'),
        'WI' => array('top' => 'MW', 'sub' => 'ENC'),
        'IA' => array('top' => 'MW', 'sub' => 'WNC'),
        'KS' => array('top' => 'MW', 'sub' => 'WNC'),
        'MN' => array('top' => 'MW', 'sub' => 'WNC'),
        'MO' => array('top' => 'MW', 'sub' => 'WNC'),
        'NE' => array('top' => 'MW', 'sub' => 'WNC'),
        'ND' => array('top' => 'MW', 'sub' => 'WNC'),
        'SD' => array('top' => 'MW', 'sub' => 'WNC'),
        'DE' => array('top' => 'S', 'sub' => 'SA'),
        'DC' => array('top' => 'S', 'sub' => 'SA'),
        'FL' => array('top' => 'S', 'sub' => 'SA'),
        'GA' => array('top' => 'S', 'sub' => 'SA'),
        'MD' => array('top' => 'S', 'sub' => 'SA'),
        'NC' => array('top' => 'S', 'sub' => 'SA'),
        'SC' => array('top' => 'S', 'sub' => 'SA'),
        'VA' => array('top' => 'S', 'sub' => 'SA'),
        'WV' => array('top' => 'S', 'sub' => 'SA'),
        'AL' => array('top' => 'S', 'sub' => 'ESC'),
        'KY' => array('top' => 'S', 'sub' => 'ESC'),
        'MS' => array('top' => 'S', 'sub' => 'ESC'),
        'TN' => array('top' => 'S', 'sub' => 'ESC'),
        'AR' => array('top' => 'S', 'sub' => 'WSC'),
        'LA' => array('top' => 'S', 'sub' => 'WSC'),
        'OK' => array('top' => 'S', 'sub' => 'WSC'),
        'TX' => array('top' => 'S', 'sub' => 'WSC'),
        'AZ' => array('top' => 'W', 'sub' => 'M'),
        'CO' => array('top' => 'W', 'sub' => 'M'),
        'ID' => array('top' => 'W', 'sub' => 'M'),
        'MT' => array('top' => 'W', 'sub' => 'M'),
        'NV' => array('top' => 'W', 'sub' => 'M'),
        'NM' => array('top' => 'W', 'sub' => 'M'),
        'UT' => array('top' => 'W', 'sub' => 'M'),
        'WY' => array('top' => 'W', 'sub' => 'M'),
        'AK' => array('top' => 'W', 'sub' => 'P'),
        'CA' => array('top' => 'W', 'sub' => 'P'),
        'HI' => array('top' => 'W', 'sub' => 'P'),
        'OR' => array('top' => 'W', 'sub' => 'P'),
        'WA' => array('top' => 'W', 'sub' => 'P'),
    );
    
    /**
     * Depending on what states we are given we will build an array 
     * that mimicks $state_borders, but with only states and their borders 
     * from the list of states given.
     */
    protected $bounded_state_borders = array();
    
    /**
     * Specified start state for probable paths
     */
    public $start_state = null;
    
    /**
     * Specified end state for probable paths
     */
    public $end_state = null;

    /**
     * total states we are currently processing
     */
    public $total_states = 0; 
    
    /**
     * array to store paths
     */
    protected $paths = array();
    
    /**
     * array to store current path history so we don't visit a state twice
     */
    protected $path_history = array();
    
    /**
     * array to store temps mapping to path node
     */
    protected $temps = array();

    /**
     * __construct
     * 
     * Constructor!
     *
     * @param $climate_data_file
     * @param $start_month
     */
    public function __construct($climate_data_file = null, $start_month = 1, $interval = '1 week')
    {
        if ($climate_data_file) {
            $this->parseClimateData(realpath($climate_data_file));
        }
        $this->setStartMonth($start_month);
        $this->setInterval($interval);
    }
    
    /**
     * setStartMonth
     * 
     * Set the start month for intervals
     * 
     * @param $month integer month
     * @return void
     */
    public function setStartMonth($month)
    {
        $this->start_month = $month;
        $date = date('Y', time()) . '-' . $month . '-01';
        $this->start_date = new DateTime($date);
    }
    
    /**
     * setStartState
     * 
     * Set the start state for probable paths
     * 
     * @param $state
     * @return void
     */
    public function setStartState($state)
    {
        $this->start_state = strtoupper($state);
    }
    
    /**
     * setEndState
     * 
     * Set the end state for probable paths
     * 
     * @param $state
     * @return void
     */
    public function setEndState($state)
    {
        $this->end_state = strtoupper($state);
    }
    
    /**
     * setInterval
     * 
     * Set the time interval of stay per state.
     * 
     * @param $interval as allowed by strtotime
     * @return void
     */
    public function setInterval($interval)
    {
        $this->interval = $interval;
    }
    
    /**
     * setTempRange
     * 
     * @param $min float
     * @param $max float
     * 
     * @return void
     */
    public function setTempRange($min = null, $max = null)
    {
        if (!is_null($min)) {
            $this->min_temp = $min;
        }
        
        if (!is_null($max)) {
            $this->max_temp = $max;
        }
    }
    
    /**
     * setStateTempRange
     * 
     * @state #state string
     * @param $min float
     * @param $max float
     * 
     * @return void
     */
    public function setStateTempRange($state, $min, $max)
    {
        $this->state_temp_ranges[strtoupper($state)] = array('min' => $min, 'max' => $max);
    }
    
    /**
     * getStatesByRegion
     * 
     * @param $top top level region code
     * @param $sub sub level region code
     * 
     * @return array
     */
    public function getStatesByRegion($top, $sub = null)
    {
        $list = array();
        
        foreach ($this->states_by_region as $state => $regions) {
            if ($regions['top'] == $top && !is_null($sub) && $regions['sub'] == $sub) {
                $list[] = $state;
            } elseif ($regions['top'] == $top && is_null($sub)) {
                $list[] = $state;
            }
        }
        
        return $list;
    }

    /**
     * parseClimateData
     * 
     * lets parse our table of average temp per month per state in the 
     * following format: http://www.esrl.noaa.gov/psd/data/usclimate/tmp.state.19712000.climo
     *
     * @param $climatedata
     * @return void
     */
    public function parseClimateData($climatedata)
    {
        $lines = explode("\n", file_get_contents($climatedata));
        $states = array_flip($this->us_states);
        foreach ($lines as $line) {
            if (trim($line) == '') {
                continue;
            }
            
            $line = preg_split('/  +/', $line);
            $state_name = $line[0];
            $state = $states[$state_name];

            $this->states_month_temp[$state] = $line;
        }
    }
    
    /**
     * buildPaths
     * 
     * Do our recursive DFS through states/borders and store valid paths
     *
     * @param $state start state
     * 
     * @return void
     */
    public function buildPaths($state)
    {
        $this->path_history[] = $state;
        
        if (count($this->path_history) == $this->total_states) {
            $this->paths[] = $this->path_history;
            array_pop($this->path_history);
            return;
        }

        foreach ($this->bounded_state_borders[$state] as $next) {
            if (!in_array($next, $this->path_history)) {
                $this->buildPaths($next);
            }
        }

        array_pop($this->path_history);
    }
    
    /**
     * filterByTempRange
     *
     * Filter array of paths by temperature range.
     * @return void
     */
    public function filterByTempRange()
    {        
        foreach ($this->paths as $k => $path) {
            // reset the date time object to start month for each path we process
            $this->setStartMonth($this->start_month);
            $temp_history = array();
            foreach ($path as $state) {
                $month = (int) $this->start_date->format('n');
                $temp = $this->states_month_temp[$state][$month];
                // do we have specific state temp range?
                if (isset($this->state_temp_ranges[$state])) {
                    $min = $this->state_temp_ranges[$state]['min'];
                    $max = $this->state_temp_ranges[$state]['max'];
                } else {
                    $min = $this->min_temp;
                    $max = $this->max_temp;
                }
                if ($temp < $min || $temp > $max) {
                    unset($this->paths[$k]);
                    break;
                }
                $temp_history[] = $temp;
                $this->start_date->modify('+ ' . $this->interval);
            }
            if (count($temp_history) == count($path)) {
                $this->temps[] = $temp_history;
            }
        }
        $this->paths = array_values($this->paths);

        echo 'Found ' . count($this->paths) . " paths suitable for temp range.\n";
    }
    
    public function buildBoundedStateBorders($states)
    {
        foreach ($states as $state) {
            $this->bounded_state_borders[$state] = $this->state_borders[$state];
            foreach ($this->bounded_state_borders[$state] as $k => $border) {
                if (!in_array($border, $states)) {
                    unset($this->bounded_state_borders[$state][$k]);
                }
            }
        }
    }

    /**
     * getPathsByStates($states)
     *
     * @param $states array of states e.g. array('OR', 'WA', 'CA')
     *
     * @return void
     */
    public function getPathsByStates($states)
    {
        $states = array_map("strtoupper", $states);
        $this->total_states = count($states);
        $this->buildBoundedStateBorders($states);
        if (!is_null($this->start_state)) {
            if (!in_array($this->start_state, array_keys($this->state_borders))) {
                echo 'Error: ' . $this->start_state . " is not defined in our state borders.\n";
            } elseif (!in_array($this->start_state, $states)) {
                echo "Error: Start state must be one of the states provided.\n";
            } else {
                $this->buildPaths($this->start_state);
            }
        } else {
            foreach ($states as $state) {
                if (!in_array($state, array_keys($this->state_borders))) {
                    echo 'Error: ' . $state . " is not defined in our state borders.\n";
                    continue;
                }
                
                $this->buildPaths($state);
            }
        }
        
        if (!is_null($this->end_state)) {
            foreach ($this->paths as $k => $path) {
                if (end($path) != $this->end_state) {
                    unset($this->paths[$k]);
                }
            }
            $this->paths = array_values($this->paths);
        }
        
        echo 'Found ' . count($this->paths) . ' possible paths through ' . count($states) . " states\n";
    }

    /**
     * listPaths
     * 
     * Print out a paths
     * 
     * @param $with_temps
     * @param $full_names
     *
     * @return void
     */
    public function listPaths($with_temps = false, $full_names = false)
    {
        foreach ($this->paths as $k => $path)
        {
            $list = array();
            $count = count($path);
            foreach ($path as $i => $state) {
                if ($with_temps && $full_names) {
                    $list[] = "{$this->us_states[$state]}({$this->temps[$k][$i]})";
                } elseif ($with_temps && !$full_names) {
                    $list[] = "$state({$this->temps[$k][$i]})";
                } elseif (!$with_temps && $full_names) {
                    $list[] = $this->us_states[$state];
                } else {
                    $list[] = $state;
                }
            }
            echo implode(' => ', $list) . "\n\n";
        }
    }
}
