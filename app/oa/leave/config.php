<?php
if(!isset($config->leave)) $config->leave = new stdclass();
$config->leave->require = new stdclass();
$config->leave->require->create = 'year,begin,end,type';
$config->leave->require->update = 'year,begin,end,type';
