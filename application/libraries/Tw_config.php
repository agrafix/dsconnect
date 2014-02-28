<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Tw_config {
    
    /**
     *
     * @var CI_Controller
     */
    private $ci;
    
    /**
     *
     * @var string
     */
    private $w;
    
    private static $cfg_cache;
    
    public function __construct() {
        $this->ci = &get_instance();
    }
    
    public function set_world($world_identifier)
    {
        $this->w = $world_identifier;
    }
    
    public function get_all($cfg_type='config')
    {
        // load the file & do the caching
        $this->get('dummy_key', $cfg_type);
        
        return self::$cfg_cache[$this->w][$cfg_type];
    }
    
    public function get($cfg_key, $cfg_type='config')
    {
        $cacheFile = BASEPATH."../application/cache/".$this->w."_".$cfg_type.".xml";
        
        if (!file_exists($cacheFile))
        {
            $host = Tw_import::get_tw_host($this->w);
            $data = @file($host."/interface.php?func=get_".$cfg_type);
            
            if (!$data) 
            {
                die('Error: Could not read configs for '.$this->host.' type: '.$cfg_type);
            }
            
            $fp = @fopen($cacheFile, 'w+');
            if (!$fp)
            {
                die('Error: Could not write config cache file.');
            }
            fwrite($fp, implode('', $data));
            fclose($fp);
        }
        
        if (!isset(self::$cfg_cache[$this->w][$cfg_type]))
        {
            $xml = new SimpleXMLElement($cacheFile, NULL, TRUE);
            
            self::$cfg_cache[$this->w][$cfg_type] = (array)$xml;
            
            array_walk_recursive(self::$cfg_cache[$this->w][$cfg_type], function(&$val) {
                if (is_object($val)) {
                    $val = (array)$val;
                }
            });
            
            array_walk_recursive(self::$cfg_cache[$this->w][$cfg_type], function(&$val) {
                if (is_numeric($val)) {
                    $val = (float)$val;
                }
            });
        }
        
        return (isset(self::$cfg_cache[$this->w][$cfg_type][$cfg_key]) ? self::$cfg_cache[$this->w][$cfg_type][$cfg_key] : false);
    }
    
}
?>
