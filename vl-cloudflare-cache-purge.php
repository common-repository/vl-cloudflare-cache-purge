<?php
/**
 * Plugin Name: VL Cloudflare Cache Purge
 * Description: This plugin will purge the Cloudflare Workers KV and Cloudflare purge when any post get updated.
 * Version: 1.0.2
 * Requires PHP: 7.2
 * Requires at least: 5.2
 * Author: Nitin Kumar Raghav <mails@nitinraghav.com>
 * Author URI: https://nitinraghav.com/
 * License: GPLv2 or later
 * Text Domain: vl-cloudflare-cache-purge
 * Tags: cloudflare, cache, static kv, cloudflare workers, workers, purge cache, purge workers
 */
include_once 'vl-flush.php';
if (class_exists('VLFLush')) {
    new VLFlush();
}