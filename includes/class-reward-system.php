<?php
class Reward_System {
    
    public function __construct() {
        add_action('init', array($this, 'register_reward_post_type'));
        add_shortcode('ai_reward_leaderboard', array($this, 'render_leaderboard_shortcode'));
        add_action('wp_ajax_redeem_reward', array($this, 'redeem_reward'));
        add_action('wp_ajax_nopriv_redeem_reward', array($this, 'redeem_reward'));
    }
    
    public function create_rewards_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql_points = "CREATE TABLE {$wpdb->prefix}ai_community_points (
            point_id BIGINT(20) NOT NULL AUTO_INCREMENT,
            user_id BIGINT(20) NOT NULL,
            points INT(11) NOT NULL,
            activity_type VARCHAR(50) NOT NULL,
            reference_id BIGINT(20),
            created_at DATETIME NOT NULL,
            PRIMARY KEY (point_id),
            KEY user_id (user_id)
        ) $charset_collate;";
        
        $sql_rewards = "CREATE TABLE {$wpdb->prefix}ai_community_reward_items (
            reward_id BIGINT(20) NOT NULL AUTO_INCREMENT,
            name VARCHAR(255) NOT NULL,
            description TEXT,
            points_required INT(11) NOT NULL,
            badge_image VARCHAR(255),
            is_active TINYINT(1) DEFAULT 1,
            created_at DATETIME NOT NULL,
            PRIMARY KEY (reward_id))
        ) $charset_collate;";
        
        $sql_redemptions = "CREATE TABLE {$wpdb->prefix}ai_community_reward_redemptions (
            redemption_id BIGINT(20) NOT NULL AUTO_INCREMENT,
            user_id BIGINT(20) NOT NULL,
            reward_id BIGINT(20) NOT NULL,
            points_used INT(11) NOT NULL,
            status VARCHAR(20) DEFAULT 'pending',
            created_at DATETIME NOT NULL,
            PRIMARY KEY (redemption_id),
            KEY user_id (user_id),
            KEY reward_id (reward_id))
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_points);
        dbDelta($sql_rewards);
        dbDelta($sql_redemptions);
        
        // Insert default rewards if table is empty
        if ($wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}ai_community_reward_items") == 0) {
            $this->create_default_rewards();
        }
    }
    
    private function create_default_rewards() {
        global $wpdb;
        
        $default_rewards = array(
            array(
                'name' => __('Bronze Badge', 'ai-community'),
                'description' => __('Awarded for active participation', 'ai-community'),
                'points_required' => 100,
                'badge_image' => 'bronze-badge.png'
            ),
            array(
                'name' => __('Silver Badge', 'ai-community'),
                'description' => __('Awarded for consistent contributions', 'ai-community'),
                'points_required' => 300,
                'badge_image' => 'silver-badge.png'
            ),
            array(
                'name' => __('Gold Badge', 'ai-community'),
                'description' => __('Awarded for exceptional engagement', 'ai-community'),
                'points_required' => 500,
                'badge_image' => 'gold-badge.png'
            ),
            array(
                'name' => __('Affiliate Upgrade', 'ai-community'),
                'description' => __('Higher commission rates for affiliate sales', 'ai-community'),
                'points_required' => 200,
                'badge_image' => 'affiliate-upgrade.png'
            )
        );
        
        foreach ($default_rewards as $reward) {
            $wpdb->insert(
                $wpdb->prefix . 'ai_community_reward_items',
                array(
                    'name' => $reward['name'],
                    'description' => $reward['description'],
                    'points_required' => $reward['points_required'],
                    'badge_image' => $reward['badge_image'],
                    'created_at' => current_time('mysql')
                ),
                array('%s', '%s', '%d', '%s', '%s')
            );
        }
    }
    
    public function register_reward_post_type() {
        $args = array(
            'public' => true,
            'label'  => __('Rewards', 'ai-community'),
            'show_in_menu' => false,
            'supports' => array('title', 'editor', 'thumbnail'),
            'rewrite' => array('slug' => 'community-rewards'),
        );
        register_post_type('ai_community_reward', $args);
    }
    
    public function award_points($user_id, $activity_type, $reference_id = null) {
        $points = $this->get_points_for_activity($activity_type);
        
        if ($points <= 0) {
            return false;
        }
        
        global $wpdb;
        
        return $wpdb->insert(
            $wpdb->prefix . 'ai_community_points',
            array(
                'user_id' => $user_id,
                'points' => $points,
                'activity_type' => $activity_type,
                'reference_id' => $reference_id,
                'created_at' => current_time('mysql')
            ),
            array('%d', '%d', '%s', '%d', '%s')
        );
    }
    
    private function get_points_for_activity($activity_type) {
        $point_values = array(
            'forum_post' => 10,
            'forum_reply' => 5,
            'challenge_join' => 20,
            'challenge_day' => 15,
            'challenge_complete' => 50,
            'social_share' => 5,
            'affiliate_sale' => 30
        );
        
        return isset($point_values[$activity_type]) ? $point_values[$activity_type] : 0;
    }
    
    public function get_user_points($user_id) {
        global $wpdb;
        
        $total = $wpdb->get_var($wpdb->prepare(
            "SELECT SUM(points) FROM {$wpdb->prefix}ai_community_points WHERE user_id = %d",
            $user_id
        ));
        
        $redeemed = $wpdb->get_var($wpdb->prepare(
            "SELECT SUM(points_used) FROM {$wpdb->prefix}ai_community_reward_redemptions 
            WHERE user_id = %d AND status = 'approved'",
            $user_id
        ));
        
        $total = $total ? intval($total) : 0;
        $redeemed = $redeemed ? intval($redeemed) : 0;
        
        return $total - $redeemed;
    }
    
    public function get_leaderboard($limit = 10) {
        global $wpdb;
        
        $query = $wpdb->prepare(
            "SELECT user_id, SUM(points) as total_points 
            FROM {$wpdb->prefix}ai_community_points 
            GROUP BY user_id 
            ORDER BY total_points DESC 
            LIMIT %d",
            $limit
        );
        
        return $wpdb->get_results($query);
    }
    
    public function get_available_rewards() {
        global $wpdb;
        
        return $wpdb->get_results(
            "SELECT * FROM {$wpdb->prefix}ai_community_reward_items 
            WHERE is_active = 1 
            ORDER BY points_required ASC"
        );
    }
    
    public function redeem_reward($user_id, $reward_id) {
        global $wpdb;
        
        // Get reward details
        $reward = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}ai_community_reward_items WHERE reward_id = %d",
            $reward_id
        ));
        
        if (!$reward) {
            return new WP_Error('invalid_reward', __('Invalid reward selected.', 'ai-community'));
        }
        
        // Check user points
        $user_points = $this->get_user_points($user_id);
        
        if ($user_points < $reward->points_required) {
            return new WP_Error('insufficient_points', __('You do not have enough points for this reward.', 'ai-community'));
        }
        
        // Create redemption record
        $result = $wpdb->insert(
            $wpdb->prefix . 'ai_community_reward_redemptions',
            array(
                'user_id' => $user_id,
                'reward_id' => $reward_id,
                'points_used' => $reward->points_required,
                'created_at' => current_time('mysql')
            ),
            array('%d', '%d', '%d', '%s')
        );
        
        if ($result) {
            // Process reward based on type
            $this->process_reward($user_id, $reward);
            
            return true;
        }
        
        return new WP_Error('redemption_failed', __('Error processing redemption. Please try again.', 'ai-community'));
    }
    
    public function process_reward($user_id, $reward) {
        // Handle different reward types
        if (strpos($reward->name, 'Affiliate Upgrade') !== false) {
            // Upgrade user's affiliate status
            update_user_meta($user_id, 'ai_community_affiliate_level', 'premium');
        }
        
        // In a real implementation, you might send an email, unlock features, etc.
        do_action('ai_community_reward_processed', $user_id, $reward);
    }
    
    public function get_user_rewards($user_id) {
        global $wpdb;
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT r.*, rr.created_at as redeemed_date 
            FROM {$wpdb->prefix}ai_community_reward_redemptions rr
            JOIN {$wpdb->prefix}ai_community_reward_items r ON rr.reward_id = r.reward_id
            WHERE rr.user_id = %d AND rr.status = 'approved'
            ORDER BY rr.created_at DESC",
            $user_id
        ));
    }
    
    public function render_leaderboard_shortcode() {
        ob_start();
        include AI_COMMUNITY_PATH . 'templates/leaderboard-template.php';
        return ob_get_clean();
    }
    
    public function render_settings_page() {
        if (isset($_POST['submit_reward'])) {
            $this->save_reward();
        }
        
        if (isset($_POST['delete_reward'])) {
            $this->delete_reward(intval($_POST['reward_id']));
        }
        
        include AI_COMMUNITY_PATH . 'templates/reward-settings.php';
    }
    
    private function save_reward() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        $name = sanitize_text_field($_POST['name']);
        $description = wp_kses_post($_POST['description']);
        $points_required = intval($_POST['points_required']);
        
        global $wpdb;
        
        $data = array(
            'name' => $name,
            'description' => $description,
            'points_required' => $points_required,
            'created_at' => current_time('mysql')
        );
        
        $format = array('%s', '%s', '%d', '%s');
        
        // Handle image upload
        if (!empty($_FILES['badge_image']['name'])) {
            $upload = wp_upload_bits($_FILES['badge_image']['name'], null, file_get_contents($_FILES['badge_image']['tmp_name']));
            
            if (!$upload['error']) {
                $data['badge_image'] = $upload['url'];
                $format[] = '%s';
            }
        }
        
        $wpdb->insert(
            $wpdb->prefix . 'ai_community_reward_items',
            $data,
            $format
        );
        
        add_action('admin_notices', function() {
            echo '<div class="notice notice-success"><p>' . __('Reward created successfully!', 'ai-community') . '</p></div>';
        });
    }
    
    private function delete_reward($reward_id) {
        global $wpdb;
        
        $wpdb->delete(
            $wpdb->prefix . 'ai_community_reward_items',
            array('reward_id' => $reward_id),
            array('%d')
        );
        
        add_action('admin_notices', function() {
            echo '<div class="notice notice-success"><p>' . __('Reward deleted successfully!', 'ai-community') . '</p></div>';
        });
    }
}