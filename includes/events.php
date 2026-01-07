<?php
if (!defined('ABSPATH')) exit;

class Beauty_Events {

    public static function trigger($event, $data = []) {
        do_action("beauty_event_{$event}", $data);
    }
}
