<?php
class Social_Challenges {
    
    public function __construct() {
        add_action('init', array($this, 'register_challenge_post_type'));
        add_shortcode('ai_social_challenges', array($this, 'render_challenges_shortcode'));
        add_action('wp_ajax_join_challenge', array($this, 'join_challenge'));
        add_action('wp_ajax_nopriv_join_challenge', array($this, 'join_challenge'));
        add_action('wp_ajax_complete_challenge_day', array($this, 'complete_challenge_day'));
        add_action('wp_ajax_nopriv_complete_challenge_day', array($this, 'complete_challenge_day'));
    }
    
    public function create_challenges_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql_challenges = "CREATE TABLE {$wpdb->prefix}ai_community_challenges (
            challenge_id BIGINT(20) NOT NULL AUTO_INCREMENT,
            title VARCHAR(255) NOT NULL,
            description LONGTEXT NOT NULL,
            duration INT(11) NOT NULL,
            hashtags TEXT NOT NULL,
            affiliate_products TEXT,
            is_active TINYINT(1) DEFAULT 1,
            created_at DATETIME NOT NULL,
            PRIMARY KEY (challenge_id)
        ) $charset_collate;";
        
        $sql_participants = "CREATE TABLE {$wpdb->prefix}ai_community_challenge_participants (
            participant_id BIGINT(20) NOT NULL AUTO_INCREMENT,
            challenge_id BIGINT(20) NOT NULL,
            user_id BIGINT(20) NOT NULL,
            start_date DATETIME NOT NULL,
            completed_days TEXT,
            is_completed TINYINT(1) DEFAULT 0,
            social_shares TEXT,
            PRIMARY KEY (participant_id),
            KEY challenge_id (challenge_id),
            KEY user_id (user_id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_challenges);
        dbDelta($sql_participants);
    }
    
    public function register_challenge_post_type() {
        $args = array(
            'public' => true,
            'label'  => __('Social Challenges', 'ai-community'),
            'show_in_menu' => false,
            'supports' => array('title', 'editor', 'thumbnail'),
            'rewrite' => array('slug' => 'social-challenges'),
        );
        register_post_type('ai_social_challenge', $args);
    }
    
    public function render_challenges_shortcode() {
        ob_start();
        include AI_COMMUNITY_PATH . 'templates/challenge-template.php';
        return ob_get_clean();
    }
    
    public function join_challenge() {
        check_ajax_referer('ai-community-nonce', 'nonce');
        
        $user_id = get_current_user_id();
        $challenge_id = intval($_POST['challenge_id']);
        
        if (!$user_id) {
            wp_send_json_error(__('You must be logged in to join a challenge.', 'ai-community'));
        }
        
        global $wpdb;
        
        // Check if user already joined
        $existing = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}ai_community_challenge_participants 
            WHERE user_id = %d AND challenge_id = %d",
            $user_id, $challenge_id
        ));
        
        if ($existing) {
            wp_send_json_error(__('You have already joined this challenge.', 'ai-community'));
        }
        
        $result = $wpdb->insert(
            $wpdb->prefix . 'ai_community_challenge_participants',
            array(
                'challenge_id' => $challenge_id,
                'user_id' => $user_id,
                'start_date' => current_time('mysql'),
                'completed_days' => serialize(array()),
                'social_shares' => serialize(array())
            ),
            array('%d', '%d', '%s', '%s', '%s')
        );
        
        if ($result) {
            $reward_system = new Reward_System();
            $reward_system->award_points($user_id, 'challenge_join', $challenge_id);
            
            wp_send_json_success(__('Challenge joined successfully!', 'ai-community'));
        } else {
            wp_send_json_error(__('Error joining challenge. Please try again.', 'ai-community'));
        }
    }
    
    public function complete_challenge_day() {
        check_ajax_referer('ai-community-nonce', 'nonce');
        
        $user_id = get_current_user_id();
        $challenge_id = intval($_POST['challenge_id']);
        $day = intval($_POST['day']);
        $social_url = esc_url_raw($_POST['social_url']);
        
        if (!$user_id) {
            wp_send_json_error(__('You must be logged in to complete challenge days.', 'ai-community'));
        }
        
        global $wpdb;
        
        // Get participant record
        $participant = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}ai_community_challenge_participants 
            WHERE user_id = %d AND challenge_id = %d",
            $user_id, $challenge_id
        ));
        
        if (!$participant) {
            wp_send_json_error(__('You have not joined this challenge.', 'ai-community'));
        }
        
        $completed_days = unserialize($participant->completed_days);
        $social_shares = unserialize($participant->social_shares);
        
        if (in_array($day, $completed_days)) {
            wp_send_json_error(__('You have already completed this day.', 'ai-community'));
        }
        
        $completed_days[] = $day;
        $social_shares[$day] = $social_url;
        
        $result = $wpdb->update(
            $wpdb->prefix . 'ai_community_challenge_participants',
            array(
                'completed_days' => serialize($completed_days),
                'social_shares' => serialize($social_shares)
            ),
            array(
                'user_id' => $user_id,
                'challenge_id' => $challenge_id
            ),
            array('%s', '%s'),
            array('%d', '%d')
        );
        
        if ($result !== false) {
            $reward_system = new Reward_System();
            $reward_system->award_points($user_id, 'challenge_day', $challenge_id);
            
            // Check if challenge is fully completed
            $challenge = $this->get_challenge($challenge_id);
            if (count($completed_days) >= $challenge->duration) {
                $wpdb->update(
                    $wpdb->prefix . 'ai_community_challenge_participants',
                    array('is_completed' => 1),
                    array(
                        'user_id' => $user_id,
                        'challenge_id' => $challenge_id
                    ),
                    array('%d'),
                    array('%d', '%d')
                );
                
                $reward_system->award_points($user_id, 'challenge_complete', $challenge_id);
            }
            
            wp_send_json_success(__('Day completed successfully!', 'ai-community'));
        } else {
            wp_send_json_error(__('Error completing day. Please try again.', 'ai-community'));
        }
    }
    
    public function get_challenges($active_only = true, $limit = 10, $offset = 0) {
        global $wpdb;
        
        $where = $active_only ? 'WHERE is_active = 1' : '';
        
        $query = $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}ai_community_challenges $where 
            ORDER BY created_at DESC LIMIT %d OFFSET %d",
            $limit, $offset
        );
        
        return $wpdb->get_results($query);
    }
    
    public function get_challenge($challenge_id) {
        global $wpdb;
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}ai_community_challenges WHERE challenge_id = %d",
            $challenge_id
        ));
    }
    
    public function get_user_participation($user_id, $challenge_id) {
        global $wpdb;
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}ai_community_challenge_participants 
            WHERE user_id = %d AND challenge_id = %d",
            $user_id, $challenge_id
        ));
    }
    
    public function render_settings_page() {
        if (isset($_POST['submit_challenge'])) {
            $this->save_challenge();
        }
        
        include AI_COMMUNITY_PATH . 'templates/challenge-settings.php';
    }
    
    private function save_challenge() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        $title = sanitize_text_field($_POST['title']);
        $description = wp_kses_post($_POST['description']);
        $duration = intval($_POST['duration']);
        $hashtags = sanitize_text_field($_POST['hashtags']);
        $products = isset($_POST['affiliate_products']) ? array_map('intval', $_POST['affiliate_products']) : array();
        
        global $wpdb;
        
        $wpdb->insert(
            $wpdb->prefix . 'ai_community_challenges',
            array(
                'title' => $title,
                'description' => $description,
                'duration' => $duration,
                'hashtags' => $hashtags,
                'affiliate_products' => serialize($products),
                'created_at' => current_time('mysql')
            ),
            array('%s', '%s', '%d', '%s', '%s', '%s')
        );
        
        add_action('admin_notices', function() {
            echo '<div class="notice notice-success"><p>' . __('Challenge created successfully!', 'ai-community') . '</p></div>';
        });
    }
}