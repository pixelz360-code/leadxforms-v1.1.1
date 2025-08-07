<?php

function lxf_version() {
    if(defined(LXFORM_PLUGIN_VERSION)) {
        return LXFORM_PLUGIN_VERSION;
    }

    return '1.0.0';
}

function lxf_name() {
    if(defined(LXFORM_PLUGIN_NAME)) {
        return LXFORM_PLUGIN_NAME;
    }

    return 'LeadXForms';
}
function apiUrl(){
    $loaders = new LeadXForms_Loader();
    return $loaders->api_url();
}

function get_ip_address()
{
    $ip = getenv('REMOTE_ADDR');
    if (!empty(getenv('HTTP_CLIENT_IP'))) {
        $ip = getenv('HTTP_CLIENT_IP');
    } elseif (!empty(getenv('HTTP_X_FORWARDED_FOR'))) {
        $ip = getenv('HTTP_X_FORWARDED_FOR');
    }
    return $ip;
}

function lxf_id() {
    if(defined(LXFORM_PLUGIN_ID)) {
        return LXFORM_PLUGIN_ID;
    }

    return 'leadxforms';
}

function lxf_view($file_path, $data = []) {
    extract($data);
    include_once(plugin_dir_path(dirname(__FILE__)) . 'admin/partials/'. $file_path .'.php');
}

function dd($data) {
    echo '<pre>';
    print_r($data);
    echo '</pre>';
    wp_die();
}

function get_full_url() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
    $domain = $_SERVER['HTTP_HOST']; 
    return $protocol . $domain;
}

function lxf_get_domain() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
    $domain = $_SERVER['HTTP_HOST'];
    $url = $domain;
    return $url;
}

function my_strpos($haystack, $needle, $offset = 0) {
    $haystack_length = strlen($haystack);
    $needle_length = strlen($needle);

    for ($i = $offset; $i <= $haystack_length - $needle_length; $i++) {
        $substring = substr($haystack, $i, $needle_length);
        if ($substring === $needle) {
            return $i;
        }
    }

    return false; // If the needle is not found
}