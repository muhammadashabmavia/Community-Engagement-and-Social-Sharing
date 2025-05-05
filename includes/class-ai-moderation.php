<?php
class AI_Moderation {
    
    private $openai_api_key;
    private $moderation_enabled;
    
    public function __construct() {
        $this->openai_api_key = get_option('ai_community_openai_key', '');
        $this->moderation_enabled = get_option('ai_community_moderation_enabled', true);
        
        add_action('admin_init', array($this, 'register_settings'));
    }
    
    public function moderate_content($content) {
        if (!$this->moderation_enabled || empty($this->openai_api_key)) {
            return array('approved' => true, 'message' => '');
        }
        
        $response = $this->call_openai_api($content);
        
        if (is_wp_error($response)) {
            return array('approved' => true, 'message' => 'Moderation unavailable. Content approved.');
        }
        
        $result = json_decode(wp_remote_retrieve_body($response), true);
        
        if (isset($result['flagged']) && $result['flagged']) {
            $message = __('Your content was flagged as inappropriate. Please revise your message.', 'ai-community');
            
            if (isset($result['categories']) && is_array($result['categories'])) {
                $flagged_categories = array_keys(array_filter($result['categories']));
                if (!empty($flagged_categories)) {
                    $message .= ' ' . sprintf(
                        __('Flagged categories: %s', 'ai-community'),
                        implode(', ', $flagged_categories)
                    );
                }
            }
            
            return array('approved' => false, 'message' => $message);
        }
        
        return array('approved' => true, 'message' => '');
    }
    
    private function call_openai_api($content) {
        $api_url = 'https://api.openai.com/v1/moderations';
        
        $headers = array(
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $this->openai_api_key
        );
        
        $body = array(
            'input' => $content,
            'model' => 'text-moderation-latest'
        );
        
        $args = array(
            'headers' => $headers,
            'body' => json_encode($body),
            'timeout' => 15
        );
        
        return wp_remote_post($api_url, $args);
    }
    
    public function generate_hashtags($content) {
        if (empty($this->openai_api_key)) {
            return array();
        }
        
        $prompt = "Generate 5 relevant hashtags for this content, focusing on metaphysical transformation, personal development, or spiritual growth:\n\n$content\n\nHashtags:";
        
        $api_url = 'https://api.openai.com/v1/completions';
        
        $headers = array(
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $this->openai_api_key
        );
        
        $body = array(
            'model' => 'text-davinci-003',
            'prompt' => $prompt,
            'max_tokens' => 60,
            'temperature' => 0.7,
            'top_p' => 1,
            'n' => 1
        );
        
        $response = wp_remote_post($api_url, array(
            'headers' => $headers,
            'body' => json_encode($body),
            'timeout' => 15
        ));
        
        if (is_wp_error($response)) {
            return array();
        }
        
        $result = json_decode(wp_remote_retrieve_body($response), true);
        
        if (isset($result['choices'][0]['text'])) {
            $hashtags_text = trim($result['choices'][0]['text']);
            $hashtags = array_map('trim', explode(',', $hashtags_text));
            $hashtags = array_map(function($tag) {
                $tag = preg_replace('/[^a-zA-Z0-9]/', '', $tag);
                return '#' . $tag;
            }, $hashtags);
            
            return array_slice($hashtags, 0, 5);
        }
        
        return array();
    }
    
    public function register_settings() {
        register_setting('ai_community_ai_settings', 'ai_community_openai_key');
        register_setting('ai_community_ai_settings', 'ai_community_moderation_enabled');
    }
    
    public function render_settings_page() {
        include AI_COMMUNITY_PATH . 'templates/ai-settings.php';
    }
}