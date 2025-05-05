<?php
/*
Plugin Name: Community Engagement and Social Sharing
Plugin URI: https://yourwebsite.com/ai-community
Description: AI-moderated community forum with social media challenges and reward system.
Version: 1.0.0
Author: Muhammad Ashab Mavia
Author URI: https://yourwebsite.com
License: GPLv2 or later
Text Domain: ai-community
*/

defined('ABSPATH') or die('Direct access not allowed');

// Define plugin constants
define('AI_COMMUNITY_VERSION', '1.0.0');
define('AI_COMMUNITY_PATH', plugin_dir_path(__FILE__));
define('AI_COMMUNITY_URL', plugin_dir_url(__FILE__));

// Include required files
require_once AI_COMMUNITY_PATH . 'includes/class-forum-manager.php';
require_once AI_COMMUNITY_PATH . 'includes/class-social-challenges.php';
require_once AI_COMMUNITY_PATH . 'includes/class-ai-moderation.php';
require_once AI_COMMUNITY_PATH . 'includes/class-reward-system.php';
require_once AI_COMMUNITY_PATH . 'includes/class-api-integration.php';

class Community_Engagement_and_Social_Sharing {
    
    private static $instance = null;
    public $forum_manager;
    public $social_challenges;
    public $ai_moderation;
    public $reward_system;
    public $api_integration;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->init_components();
        $this->register_hooks();
    }
    
    private function init_components() {
        $this->forum_manager = new Forum_Manager();
        $this->social_challenges = new Social_Challenges();
        $this->ai_moderation = new AI_Moderation();
        $this->reward_system = new Reward_System();
        $this->api_integration = new API_Integration();
    }
    
    private function register_hooks() {
        register_activation_hook(__FILE__, array($this, 'activate_plugin'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate_plugin'));
        
        add_action('plugins_loaded', array($this, 'load_textdomain'));
        add_action('admin_menu', array($this, 'create_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_public_assets'));
    }
    
    public function activate_plugin() {
        $this->forum_manager->create_forum_tables();
        $this->social_challenges->create_challenges_tables();
        $this->reward_system->create_rewards_tables();
        flush_rewrite_rules();
    }
    
    public function deactivate_plugin() {
        flush_rewrite_rules();
    }
    
    public function load_textdomain() {
        load_plugin_textdomain('ai-community', false, dirname(plugin_basename(__FILE__)) . '/languages/');
    }
    
    public function create_admin_menu() {
        add_menu_page(
            __('AI Community', 'ai-community'),
            __('AI Community', 'ai-community'),
            'manage_options',
            'ai-community',
            array($this, 'render_admin_dashboard'),
            'dashicons-groups',
            30
        );
        
        add_submenu_page(
            'ai-community',
            __('Forum Settings', 'ai-community'),
            __('Forum Settings', 'ai-community'),
            'manage_options',
            'ai-community-forum',
            array($this->forum_manager, 'render_settings_page')
        );
        
        add_submenu_page(
            'ai-community',
            __('Challenges', 'ai-community'),
            __('Challenges', 'ai-community'),
            'manage_options',
            'ai-community-challenges',
            array($this->social_challenges, 'render_settings_page')
        );
        
        add_submenu_page(
            'ai-community',
            __('Reward System', 'ai-community'),
            __('Reward System', 'ai-community'),
            'manage_options',
            'ai-community-rewards',
            array($this->reward_system, 'render_settings_page')
        );
        
        add_submenu_page(
            'ai-community',
            __('AI Settings', 'ai-community'),
            __('AI Settings', 'ai-community'),
            'manage_options',
            'ai-community-ai',
            array($this->api_integration, 'render_settings_page')
        );
    }
    
    public function render_admin_dashboard() {
        include AI_COMMUNITY_PATH . 'templates/admin-dashboard.php';
    }
    
    public function enqueue_admin_assets($hook) {
        if (strpos($hook, 'ai-community') !== false) {
            wp_enqueue_style('ai-community-admin', AI_COMMUNITY_URL . 'assets/css/admin.css', array(), AI_COMMUNITY_VERSION);
            wp_enqueue_script('ai-community-admin', AI_COMMUNITY_URL . 'assets/js/admin.js', array('jquery'), AI_COMMUNITY_VERSION, true);
        }
    }
    
    public function enqueue_public_assets() {
        if (is_page('community-forum') || is_page('social-challenges')) {
            wp_enqueue_style('ai-community-public', AI_COMMUNITY_URL . 'assets/css/public.css', array(), AI_COMMUNITY_VERSION);
            wp_enqueue_script('ai-community-public', AI_COMMUNITY_URL . 'assets/js/public.js', array('jquery'), AI_COMMUNITY_VERSION, true);
            
            wp_localize_script('ai-community-public', 'aiCommunity', array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('ai-community-nonce')
            ));
        }
    }
}

// Initialize the plugin
AI_Community_Engagement::get_instance();