<?php
namespace jt2k\Jarvis;

class DroneStrikeResponder extends Responder
{
    public static $pattern = '^drone\s*strike\s+(.+)$';
    
    private static $dirs = array(
        'north',
        'northeast',
        'east',
        'southeast',
        'south',
        'southwest',
        'west',
        'northwest'
    );
    
    private static $sublocs = array(
        '{fives} clicks {dir} of',
        'right in the heart of',
        'just {dir} of',
        'on the outskirts of',
        'in a shantytown {dir} of',
        'in an abandoned mine near',
        'in downtown'
    );
    
    private static $locs = array(
        array('name' => 'Tokyo, Japan', 'latitude' => 35.673343, 'longitude' => 139.710388),
        array('name' => 'Shanghai, China', 'latitude' => 31.2243489, 'longitude' => 121.4767528),
        array('name' => 'Mexico City, Mexico', 'latitude' => 19.3907336, 'longitude' => -99.1436127),
        array('name' => 'Delhi, India', 'latitude' => 28.6454414, 'longitude' => 77.0907573),
        array('name' => 'Omaha, NE', 'latitude' => 41.2918589, 'longitude' => -96.0812485),
        array('name' => 'Nashville, TN', 'latitude' => 36.1866405, 'longitude' => -86.7852455),
    );
    
    public function respond($redirect = false) {
        if (!$this->requireConfig(array('forecast.io_key'))) {
            return 'forecast.io_key is required.';
        }
        
        // TODO: add local time?
        // TODO: figure out $this->communication?
        // TODO: auto-parse the matches?
        /// TODO: add collateral dmg (by the way, ...)
        
        $target = $this->matches[1];
        $subloc = self::$sublocs[rand(0, count(self::$sublocs) - 1)];
        $subloc = str_replace('{fives}', rand(1, 4) * 5, $subloc);
        $subloc = str_replace('{dir}', self::$dirs[rand(0, count(self::$dirs) - 1)], $subloc);
        $loc = self::$locs[rand(0, count(self::$locs) - 1)];
        $weatherConfig = array(
            'forecast.io_key' => $this->config['forecast.io_key'],
            'location' => array($loc['latitude'], $loc['longitude'])
        );
        $weather = new WeatherResponder($weatherConfig, $this->communication, array('weather brief', 'weather', 'brief'), null);
        $forecast = $weather->respond();
        
        $ball = new EightBallResponder($this->config, $this->communication, array('8ball', array('8ball')), null);
        $permission = $ball->respond();
        if (EightBallResponder::isPositive($permission)) {
            $mission = ":airplane: ... :three: ... :two: ... :one: ... :boom::boom::boom::boom::boom:\n" .
                "*$target* was completely vaporized. Misson accomplished!";
        }
        else {
            $mission = 'Understood, sir. Mission aborted.';
        }
        
        return "Agents have located *$target* $subloc {$loc['name']}.\n" .
            "Current weather conditions: $forecast\n" .
            "Do we have permission to neutralize the target?\n" .
            ":guardsman: _{$permission}._\n$mission";
    }
}