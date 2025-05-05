<?php
class API_Integration {
    
    public function __construct() {
        add_action('admin_init', array($this, 'register_settings'));
    }
    
    public function register_settings() {
        register_setting('ai_community_api_settings', 'ai_community_openai_key');
        register_setting('ai_community_api_settings', 'ai_community_affiliate_api_key');
        register_setting('ai_community_api_settings', 'ai_community_moderation_enabled');
        register_setting('ai_community_api_settings', 'ai_community_auto_hashtags');
        
        add_settings_section(
            'ai_community_api_section',
            __('API Integration Settings', 'ai-community'),
            array($this, 'render_api_section'),
            'ai_community_api_settings'
        );
        
        add_settings_field(
            'ai_community_openai_key',
            __('OpenAI API Key', 'ai-community'),
            array($this, 'render_openai_key_field'),
            'ai_community_api_settings',
            'ai_community_api_section'
        );
        
        add_settings_field(
            'ai_community_affiliate_api_key',
            __('Affiliate API Key', 'ai-community'),
            array($this, 'render_affiliate_key_field'),
            'ai_community_api_settings',
            'ai_community_api_section'
        );
        
        add_settings_field(
            'ai_community_moderation_enabled',
            __('Enable AI Moderation', 'ai-community'),
            array($this, 'render_moderation_field'),
            'ai_community_api_settings',
            'ai_community_api_section'
        );
        
        add_settings_field(
            'ai_community_auto_hashtags',
            __('Enable Auto Hashtags', 'ai-community'),
            array($this, 'render_hashtags_field'),
            'ai_community_api_settings',
            'ai_community_api_section'
        );
    }
    
    public function render_api_section() {
        echo '<p>' . __('Configure API keys and integration settings for the AI Community plugin.', 'ai-community') . '</p>';
    }
    
    public function render_openai_key_field() {
        $value = get_option('ai_community_openai_key', '');
        echo '<input type="password" id="ai_community_openai_key" name="ai_community_openai_key" value="' . esc_attr($value) . '" class="regular-text">';
        echo '<p class="description">' . __('Enter your OpenAI API key for content moderation and hashtag generation.', 'ai-community') . '</p>';
    }
    
    public function render_affiliate_key_field() {
        $value = get_option('ai_community_affiliate_api_key', '');
        echo '<input type="password" id="ai_community_affiliate_api_key" name="ai_community_affiliate_api_key" value="' . esc_attr($value) . '" class="regular-text">';
        echo '<p class="description">' . __('Enter your affiliate API key if you want to track affiliate sales.', 'ai-community') . '</p>';
    }
    
    public function render_moderation_field() {
        $value = get_option('ai_community_moderation_enabled', true);
        echo '<label><input type="checkbox" id="ai_community_moderation_enabled" name="ai_community_moderation_enabled" value="1" ' . checked(1, $value, false) . '> ' . __('Enable AI content moderation', 'ai-community') . '</label>';
    }
    
    public function render_hashtags_field() {
        $value = get_option('ai_community_auto_hashtags', true);
        echo '<label><input type="checkbox" id="ai_community_auto_hashtags" name="ai_community_auto_hashtags" value="1" ' . checked(1, $value, false) . '> ' . __('Enable automatic hashtag generation', 'ai-community') . '</label>';
    }
    
    public function render_settings_page() {
        ?>
        <div class="wrap">
            <h1><?php _e('API Integration Settings', 'ai-community'); ?></h1>
            
            <form method="post" action="options.php">
                <?php
                settings_fields('ai_community_api_settings');
                do_settings_sections('ai_community_api_settings');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }
}