<?php
class Forum_Manager {
    
    public function __construct() {
        add_action('init', array($this, 'register_forum_post_type'));
        add_shortcode('ai_community_forum', array($this, 'render_forum_shortcode'));
        add_action('wp_ajax_create_forum_topic', array($this, 'create_forum_topic'));
        add_action('wp_ajax_nopriv_create_forum_topic', array($this, 'create_forum_topic'));
    }
    
    public function create_forum_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql_topics = "CREATE TABLE {$wpdb->prefix}ai_community_topics (
            topic_id BIGINT(20) NOT NULL AUTO_INCREMENT,
            user_id BIGINT(20) NOT NULL,
            title VARCHAR(255) NOT NULL,
            content LONGTEXT NOT NULL,
            category VARCHAR(100) NOT NULL,
            status VARCHAR(20) DEFAULT 'pending',
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY (topic_id)
        ) $charset_collate;";
        
        $sql_replies = "CREATE TABLE {$wpdb->prefix}ai_community_replies (
            reply_id BIGINT(20) NOT NULL AUTO_INCREMENT,
            topic_id BIGINT(20) NOT NULL,
            user_id BIGINT(20) NOT NULL,
            content LONGTEXT NOT NULL,
            status VARCHAR(20) DEFAULT 'pending',
            created_at DATETIME NOT NULL,
            PRIMARY KEY (reply_id),
            KEY topic_id (topic_id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_topics);
        dbDelta($sql_replies);
    }
    
    public function register_forum_post_type() {
        $args = array(
            'public' => true,
            'label'  => __('Community Forum', 'ai-community'),
            'show_in_menu' => false,
            'supports' => array('title', 'editor', 'comments'),
            'rewrite' => array('slug' => 'community-forum'),
        );
        register_post_type('ai_community_forum', $args);
    }
    
    public function render_forum_shortcode() {
        ob_start();
        include AI_COMMUNITY_PATH . 'templates/forum-template.php';
        return ob_get_clean();
    }
    
    public function create_forum_topic() {
        check_ajax_referer('ai-community-nonce', 'nonce');
        
        $user_id = get_current_user_id();
        $title = sanitize_text_field($_POST['title']);
        $content = wp_kses_post($_POST['content']);
        $category = sanitize_text_field($_POST['category']);
        
        if (empty($title) || empty($content) || empty($category)) {
            wp_send_json_error(__('All fields are required.', 'ai-community'));
        }
        
        global $wpdb;
        
        $result = $wpdb->insert(
            $wpdb->prefix . 'ai_community_topics',
            array(
                'user_id' => $user_id,
                'title' => $title,
                'content' => $content,
                'category' => $category,
                'created_at' => current_time('mysql'),
                'updated_at' => current_time('mysql')
            ),
            array('%d', '%s', '%s', '%s', '%s', '%s')
        );
        
        if ($result) {
            $topic_id = $wpdb->insert_id;
            
            // Send to AI moderation
            $ai_moderation = new AI_Moderation();
            $moderation_result = $ai_moderation->moderate_content($content);
            
            if ($moderation_result['approved']) {
                $wpdb->update(
                    $wpdb->prefix . 'ai_community_topics',
                    array('status' => 'approved'),
                    array('topic_id' => $topic_id),
                    array('%s'),
                    array('%d')
                );
                
                // Award points for contribution
                $reward_system = new Reward_System();
                $reward_system->award_points($user_id, 'forum_post', $topic_id);
                
                wp_send_json_success(__('Topic created successfully!', 'ai-community'));
            } else {
                wp_send_json_error($moderation_result['message']);
            }
        } else {
            wp_send_json_error(__('Error creating topic. Please try again.', 'ai-community'));
        }
    }
    
    public function render_settings_page() {
        include AI_COMMUNITY_PATH . 'templates/forum-settings.php';
    }
    
    public function get_categories() {
        return array(
            'financial-freedom' => __('Financial Freedom', 'ai-community'),
            'personal-development' => __('Personal Development', 'ai-community'),
            'spiritual-healing' => __('Spiritual Healing', 'ai-community'),
            'relationships' => __('Relationships', 'ai-community'),
            'health-wellness' => __('Health & Wellness', 'ai-community')
        );
    }
    
    public function get_topics($category = '', $status = 'approved', $limit = 10, $offset = 0) {
        global $wpdb;
        
        $where = array();
        $prepare_values = array();
        
        if (!empty($category)) {
            $where[] = 'category = %s';
            $prepare_values[] = $category;
        }
        
        if (!empty($status)) {
            $where[] = 'status = %s';
            $prepare_values[] = $status;
        }
        
        $where_clause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        
        $query = $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}ai_community_topics $where_clause ORDER BY created_at DESC LIMIT %d OFFSET %d",
            array_merge($prepare_values, array($limit, $offset))
        );
        
        return $wpdb->get_results($query);
    }
}