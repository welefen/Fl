<?php
$file = "cssparse.css";
$content = file_get_contents($file);
require 'class.csstidy.php';
$instance = new csstidy();
$instance->set_cfg("lowercase_s", true);
$instance->set_cfg("remove_last_", true);
$instance->set_cfg("sort_properties", true);
$instance->set_cfg("sort_selectors", true);
$instance->set_cfg("discard_invalid_properties", true);
$instance->set_cfg("preserve_css", true);
$instance->set_cfg("timestamp", true);
$result = $instance->parse($content);
echo $result;