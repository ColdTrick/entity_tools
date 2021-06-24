<?php

// user selection depends on support from the discussions plugin
$vars['add_users'] = (bool) elgg_get_plugin_setting('enable_global_discussions', 'discussions');

echo elgg_view('input/entity_tools_container', $vars);
