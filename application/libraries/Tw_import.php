<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Tw_import {

    /**
     * World
     *
     * @var string
     */
    public $world;
    /**
     * Save table sqls
     *
     * @var array
     */
    private $table_sqls = array();
    /**
     * Store CI-Instance
     *
     * @var CI_Controller
     */
    private $ci;
    /**
     * Tribalwars host
     *
     * @var string
     */
    private $tw_host = "";
    
    /**
     * Store tribalwars hosts
     */
    private static $tw_host_defs = array();
    
    private static $tw_base_hosts = array(
        'de' => 'http://www.die-staemme.de/',
        'ch' => 'http://www.staemme.ch/',
        'us' => 'http://www.tribalwars.us/',
        'uk' => 'http://www.tribalwars.co.uk/',
        'en' => 'http://www.tribalwars.net/'
    );

    public function __construct()
    {
        $this->ci = &get_instance();
        $this->ci->load->driver('cache');
    }

    /**
     * Start
     *
     * @param string $world
     * @return boolean
     */
    public function start_import($world)
    {
        $this->world = $world;

        $this->def_table_sqls();
        $this->def_tw_host();

        return true;
    }

    /**
     * Download a file
     *
     * @param string $file
     */
    public function cache_file($file)
    {
        $str = $this->tw_host."/map/".$file.".txt.gz";

        $gz = @gzfile($str);
        if (!$gz OR !is_array($gz))
        {
            die("Could not read $str");
        }

        $c = implode("", $gz);
        $c_t = trim($c);
        if (empty($c_t))
        {
            die("File was empty!");
        }

        $fp = @fopen(BASEPATH."../application/cache/".$this->world."-".$file.".txt", "w+");
        if (!$fp)
        {
            die("Could not write cache file! Filename: ".BASEPATH."../application/cache/".$this->world."-".$file.".txt");
        }

        @fwrite($fp, $c);
        @fclose($fp);
    }
    
    public function clear_cache($file)
    {
        unlink(BASEPATH."../application/cache/".$this->world."-".$file.".txt");
    }

    /**
     * Load a file into database
     *
     * @param string $file
     */
    public function load_into_db($file)
    {
        $this->ci->db->simple_query("TRUNCATE TABLE `".$this->world."_".$file."`");

        $sql = 'LOAD DATA LOCAL INFILE "'.BASEPATH.'../application/cache/'.$this->world.'-'.$file.'.txt"
INTO TABLE `'.$this->world."_".$file.'`
  FIELDS
  TERMINATED BY ","
  ENCLOSED BY ""
  LINES TERMINATED BY "\n";';

        $this->ci->db->simple_query($sql);
    }

    /**
     * Install a table
     *
     * @param string $tbl_name
     * @return boolean
     */
    public function install_table($tbl_name)
    {
        if (isset($this->table_sqls[$tbl_name]))
        {
            $this->ci->db->simple_query($this->table_sqls[$tbl_name]);
            return true;
        }
        else
        {
            die("No installation routine availible!");
        }
    }
    
    /**
     * Get URL to Homepage of specified world
     * 
     * @param string $worldId eg. de47
     * @return string
     */
    public static function get_tw_base_host($worldId) 
    {
        $id = substr($worldId, 0, 2);
        
        if (!isset(self::$tw_base_hosts[$id])) {
            die('World '.htmlspecialchars($worldId).' not supported!');
        }
        
        return self::$tw_base_hosts[$id];
    }
    
    /**
     * Get host of specified world
     * 
     * @param string $worldId eg. de47
     * @return string 
     */
    public static function get_tw_host($worldId) {
        $CI = &get_instance();
        $lang_id = substr($worldId, 0, 2);
        
        if (empty(self::$tw_host_defs[$lang_id])) {
            $url = self::get_tw_base_host($worldId).'/backend/get_servers.php';
            
            $cacheID = $lang_id."_get_servers.txt";
            
            if (!$file = $CI->cache->file->get($cacheID)) 
            {
                $file = @file_get_contents($url);

                if (!$file)
                {
                    die("Could not connect to ".self::get_tw_base_host($worldId));
                }
                
                $CI->cache->file->save($cacheID, $file, 3600 * 24);
            }
            
            self::$tw_host_defs[$lang_id] = unserialize($file);
        }
        
        return (isset(self::$tw_host_defs[$lang_id][$worldId]) ? self::$tw_host_defs[$lang_id][$worldId] : false);
    }

    /**
     * Define tw host
     *
     */
    private function def_tw_host()
    {
        $this->tw_host = self::get_tw_host($this->world);

        if (!$this->tw_host)
        {
            die("World $this->world not found!");
        }
    }

    /**
     * Define Table SQLs
     *
     */
    private function def_table_sqls()
    {
        $this->table_sqls["village"] = '
			CREATE TABLE IF NOT EXISTS `'.$this->world.'_village` (
				`id` INT( 11 ) UNSIGNED NOT NULL ,
				`name` VARCHAR( 255 ) NOT NULL ,
				`x` INT( 5 ) UNSIGNED NOT NULL ,
				`y` INT( 5 ) UNSIGNED NOT NULL ,
				`tribe` INT( 11 ) UNSIGNED NOT NULL ,
				`points` INT( 11 ) UNSIGNED NOT NULL ,
				`type` INT( 5 ) UNSIGNED NOT NULL ,
				PRIMARY KEY ( `id` )
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;';

        $this->table_sqls["player"] = '
			CREATE TABLE IF NOT EXISTS `'.$this->world.'_player` (
			  `id` int(11) unsigned NOT NULL,
			  `name` varchar(255) collate utf8_unicode_ci NOT NULL,
			  `ally` int(11) unsigned NOT NULL,
			  `villages` int(11) unsigned NOT NULL,
			  `points` int(11) unsigned NOT NULL,
			  `rank` int(11) unsigned NOT NULL,
			  PRIMARY KEY  (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;';

        $this->table_sqls["ally"] = '
			CREATE TABLE IF NOT EXISTS `'.$this->world.'_ally` (
			  `id` int(11) unsigned NOT NULL,
			  `name` varchar(255) collate utf8_unicode_ci NOT NULL,
			  `tag` varchar(255) collate utf8_unicode_ci NOT NULL,
			  `members` int(11) unsigned NOT NULL,
			  `villages` int(11) unsigned NOT NULL,
			  `points` int(11) unsigned NOT NULL,
			  `all_points` int(11) unsigned NOT NULL,
			  `rank` int(11) unsigned NOT NULL,
			  PRIMARY KEY  (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;';

        $this->table_sqls["conquer"] = '
			CREATE TABLE IF NOT EXISTS `'.$this->world.'_conquer` (
			  `village_id` int(11) unsigned NOT NULL,
			  `unix_timestamp` int(11) unsigned NOT NULL,
			  `new_owner` int(11) unsigned NOT NULL,
			  `old_owner` int(11) unsigned NOT NULL
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
		';
    }

}