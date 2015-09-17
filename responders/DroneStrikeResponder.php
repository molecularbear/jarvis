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
        'just {dir} of',
        'on the outskirts of',
        'in a shantytown {dir} of',
        'an abandoned mine near',
        'downtown',
        'hocking pirated DVDs on the streets of',
        'sailing in a hot air balloon over'
    );
    
    private static $locs = array(
        array('name' => 'Da Nang, Vietnam', 'latitude' => 16.0466742, 'longitude' => 108.206706),
        array('name' => 'Delhi, India', 'latitude' => 28.6454414, 'longitude' => 77.0907573),
        array('name' => 'Ho Chi Minh City, Vietnam', 'latitude' => 10.768451, 'longitude' => 106.6943626),
        array('name' => 'Mexico City, Mexico', 'latitude' => 19.3907336, 'longitude' => -99.1436127),
        array('name' => 'Nashville, TN', 'latitude' => 36.1866405, 'longitude' => -86.7852455),
        array('name' => 'Omaha, NE', 'latitude' => 41.2918589, 'longitude' => -96.0812485),
        array('name' => 'Shanghai, China', 'latitude' => 31.2243489, 'longitude' => 121.4767528),
        array('name' => 'Tokyo, Japan', 'latitude' => 35.673343, 'longitude' => 139.710388)
    );
    
    public function respond($redirect = false) {
        if (!$this->requireConfig(array('forecast.io_key'))) {
            return 'forecast.io_key is required.';
        }
        
        // TODO: add local time?
        // TODO: figure out $this->communication?
        // TODO: auto-parse the matches?
        // TODO: add more locations
        // TODO: add collateral dmg (by the way, ...)
        
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
            $booms = str_repeat(':boom:', 5);
            $mission = ":airplane: ... :three: ... :two: ... :one: ... $booms\n" .
                ":smiley: We have visual on *$target's* smoldering corpse - mission accomplished!";
        }
        else {
            $mission = ':disappointed: Understood, sir. Mission aborted.';
        }
        
        return ":neutral_face: Sir, agents report a fix on high-value enemy combatant *$target*:\n" .
            "```\n" .
            "LOCATION: $subloc {$loc['name']}\n" .
            "WEATHER: $forecast\n" .
            "```\n" .
            ":angry: Do we have permission to neutralize the target?\n" .
            ":guardsman: _{$permission}._\n$mission";
    }
}