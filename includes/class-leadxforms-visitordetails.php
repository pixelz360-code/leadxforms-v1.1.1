<?php

class LeadXForms_VisitorDetails {
    
    private $agent = "";
    private $browsers;
    private $devices;
    private $os;

    function __construct() {
        $this->agent = $_SERVER['HTTP_USER_AGENT'];
        $this->browsers = json_decode(file_get_contents(plugin_dir_path( dirname( __FILE__ ) ) . '/includes/json/browsers.json'));
        $this->devices = json_decode(file_get_contents(plugin_dir_path( dirname( __FILE__ ) ) . '/includes/json/devices.json'));
        $this->os = json_decode(file_get_contents(plugin_dir_path( dirname( __FILE__ ) ) . '/includes/json/os.json'));
    }

    public function get_browser() {
        $browser_name = "Unknown Browser";
        foreach ($this->browsers as $key => $val)
        {
            if (preg_match('|'.$key.'.|i', $this->agent, $match))
            {
                $browser_name = $val;
                break;
            }
        }
        return $browser_name;
    }

    public function get_os() {
        $devices = "Unknown Platform";
        foreach ($this->os as $key => $val)
        {
            if (preg_match('|'.preg_quote($key).'|i', $this->agent))
            {
                $devices = $val;
                break;
            }
        }
        return $devices;
    }

    public function get_devices() {
        $mobile = "unknown";
        foreach ($this->devices as $key => $val)
        {
            if (FALSE !== (stripos($this->agent, $key)))
            {
                $mobile = $val;
                break;
            }
        }
        return $mobile;
    }

    public  function get_country() {
        $output = array(
            "city"           => 'unknown',
            "state"          => '',
            "country"        => '',
            "country_code"   => '',
            "continent"      => '',
            "continent_code" => ''
        );
        $ip = $this->get_ip_address();
        $continents = array(
            "AF" => "Africa",
            "AN" => "Antarctica",
            "AS" => "Asia",
            "EU" => "Europe",
            "OC" => "Australia (Oceania)",
            "NA" => "North America",
            "SA" => "South America"
        );

        if (filter_var($ip, FILTER_VALIDATE_IP)) {

            $ipdat = @json_decode(file_get_contents("http://www.geoplugin.net/json.gp?ip=".$ip));

            if (@strlen(trim($ipdat->geoplugin_countryCode)) == 2) {

                $output = array(
                    "city"           => @$ipdat->geoplugin_city,
                    "state"          => @$ipdat->geoplugin_regionName,
                    "country"        => @$ipdat->geoplugin_countryName,
                    "country_code"   => @$ipdat->geoplugin_countryCode,
                    "continent"      => @$continents[strtoupper($ipdat->geoplugin_continentCode)],
                    "continent_code" => @$ipdat->geoplugin_continentCode
                );
            }
        }
        return $output;
    }

    public function get_device() {
        $tablet_browser = 0;
        $mobile_browser = 0;
        if (preg_match('/(tablet|ipad|playbook)|(android(?!.*(mobi|opera mini)))/i', strtolower($this->agent))) {
            $tablet_browser++;
        }
        if (preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone|android|iemobile)/i', strtolower($this->agent))) {
            $mobile_browser++;
        }
        if ((strpos(strtolower($_SERVER['HTTP_ACCEPT']),'application/vnd.wap.xhtml+xml') > 0) or ((isset($_SERVER['HTTP_X_WAP_PROFILE']) or isset($_SERVER['HTTP_PROFILE'])))) {
            $mobile_browser++;
        }
        $mobile_ua = strtolower(substr($this->agent, 0, 4));
        $mobile_agents = array(
            'w3c ','acs-','alav','alca','amoi','audi','avan','benq','bird','blac',
            'blaz','brew','cell','cldc','cmd-','dang','doco','eric','hipt','inno',
            'ipaq','java','jigs','kddi','keji','leno','lg-c','lg-d','lg-g','lge-',
            'maui','maxo','midp','mits','mmef','mobi','mot-','moto','mwbp','nec-',
            'newt','noki','palm','pana','pant','phil','play','port','prox',
            'qwap','sage','sams','sany','sch-','sec-','send','seri','sgh-','shar',
            'sie-','siem','smal','smar','sony','sph-','symb','t-mo','teli','tim-',
            'tosh','tsm-','upg1','upsi','vk-v','voda','wap-','wapa','wapi','wapp',
            'wapr','webc','winw','winw','xda ','xda-');
        if (in_array($mobile_ua,$mobile_agents)) {
            $mobile_browser++;
        }
        if (strpos(strtolower($this->agent),'opera mini') > 0) {
            $mobile_browser++;
                //Check for tablets on opera mini alternative headers
            $stock_ua = strtolower(isset($_SERVER['HTTP_X_OPERAMINI_PHONE_UA'])?$_SERVER['HTTP_X_OPERAMINI_PHONE_UA']:(isset($_SERVER['HTTP_DEVICE_STOCK_UA'])?$_SERVER['HTTP_DEVICE_STOCK_UA']:''));
            if (preg_match('/(tablet|ipad|playbook)|(android(?!.*mobile))/i', $stock_ua)) {
                $tablet_browser++;
            }
        }
        if ($tablet_browser > 0) {
            return 'Tablet';
        }
        else if ($mobile_browser > 0) {
            return 'Mobile';
        }
        else {
            return 'Desktop';
        }   
    }

    public function get_ip_address() {
        $ip = getenv('REMOTE_ADDR');
        if (!empty(getenv('HTTP_CLIENT_IP'))) {
            $ip = getenv('HTTP_CLIENT_IP');
        } elseif (!empty(getenv('HTTP_X_FORWARDED_FOR'))) {
            $ip = getenv('HTTP_X_FORWARDED_FOR');
        }
        
        return $ip;
    }

    public function get_ref_url() {
        return $_SERVER['HTTP_REFERER'];
    }
}