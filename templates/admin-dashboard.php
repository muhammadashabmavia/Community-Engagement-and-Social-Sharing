<div class="wrap ai-community-dashboard">
    <h1><?php _e('AI Community Dashboard', 'ai-community'); ?></h1>
    
    <div class="dashboard-stats">
        <div class="stat-box">
            <h3><?php _e('Total Forum Topics', 'ai-community'); ?></h3>
            <?php
            global $wpdb;
            $count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}ai_community_topics");
            ?>
            <div class="stat-value"><?php echo $count; ?></div>
        </div>
        
        <div class="stat-box">
            <h3><?php _e('Active Challenges', 'ai-community'); ?></h3>
            <?php
            $count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}ai_community_challenges WHERE is_active = 1");
            ?>
            <div class="stat-value"><?php echo $count; ?></div>
        </div>
        
        <div class="stat-box">
            <h3><?php _e('Total Participants', 'ai-community'); ?></h3>
            <?php
            $count = $wpdb->get_var("SELECT COUNT(DISTINCT user_id) FROM {$wpdb->prefix}ai_community_challenge_participants");
            ?>
            <div class="stat-value"><?php echo $count; ?></div>
        </div>
    </div>
    
    <div class="recent-activity">
        <h2><?php _e('Recent Activity', 'ai-community'); ?></h2>
        
        <div class="activity-tabs">
            <ul>
                <li class="active"><a href="#recent-topics"><?php _e('Forum Topics', 'ai-community'); ?></a></li>
                <li><a href="#recent-challenges"><?php _e('Challenge Completions', 'ai-community'); ?></a></li>
                <li><a href="#recent-rewards"><?php _e('Reward Redemptions', 'ai-community'); ?></a></li>
            </ul>
            
            <div id="recent-topics" class="tab-content active">
                <?php
                $topics = $wpdb->get_results(
                    "SELECT * FROM {$wpdb->prefix}ai_community_topics 
                    ORDER BY created_at DESC LIMIT 5"
                );
                
                if ($topics): ?>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th><?php _e('Title', 'ai-community'); ?></th>
                                <th><?php _e('Author', 'ai-community'); ?></th>
                                <th><?php _e('Date', 'ai-community'); ?></th>
                                <th><?php _e('Status', 'ai-community'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($topics as $topic): ?>
                                <tr>
                                    <td><?php echo esc_html($topic->title); ?></td>
                                    <td><?php echo get_the_author_meta('display_name', $topic->user_id); ?></td>
                                    <td><?php echo date_i18n(get_option('date_format'), strtotime($topic->created_at)); ?></td>
                                    <td><?php echo ucfirst($topic->status); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p><?php _e('No forum topics yet.', 'ai-community'); ?></p>
                <?php endif; ?>
            </div>
            
            <div id="recent-challenges" class="tab-content">
                <?php
                $completions = $wpdb->get_results(
                    "SELECT cp.*, c.title 
                    FROM {$wpdb->prefix}ai_community_challenge_participants cp
                    JOIN {$wpdb->prefix}ai_community_challenges c ON cp.challenge_id = c.challenge_id
                    WHERE cp.is_completed = 1
                    ORDER BY cp.created_at DESC LIMIT 5"
                );
                
                if ($completions): ?>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th><?php _e('Challenge', 'ai-community'); ?></th>
                                <th><?php _e('User', 'ai-community'); ?></th>
                                <th><?php _e('Completed', 'ai-community'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($completions as $completion): ?>
                                <tr>
                                    <td><?php echo esc_html($completion->title); ?></td>
                                    <td><?php echo get_the_author_meta('display_name', $completion->user_id); ?></td>
                                    <td><?php echo date_i18n(get_option('date_format'), strtotime($completion->created_at)); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p><?php _e('No challenge completions yet.', 'ai-community'); ?></p>
                <?php endif; ?>
            </div>
            
            <div id="recent-rewards" class="tab-content">
                <?php
                $redemptions = $wpdb->get_results(
                    "SELECT rr.*, r.name as reward_name, u.display_name 
                    FROM {$wpdb->prefix}ai_community_reward_redemptions rr
                    JOIN {$wpdb->prefix}ai_community_reward_items r ON rr.reward_id = r.reward_id
                    JOIN {$wpdb->users} u ON rr.user_id = u.ID
                    ORDER BY rr.created_at DESC LIMIT 5"
                );
                
                if ($redemptions): ?>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th><?php _e('Reward', 'ai-community'); ?></th>
                                <th><?php _e('User', 'ai-community'); ?></th>
                                <th><?php _e('Points', 'ai-community'); ?></th>
                                <th><?php _e('Date', 'ai-community'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($redemptions as $redemption): ?>
                                <tr>
                                    <td><?php echo esc_html($redemption->reward_name); ?></td>
                                    <td><?php echo esc_html($redemption->display_name); ?></td>
                                    <td><?php echo $redemption->points_used; ?></td>
                                    <td><?php echo date_i18n(get_option('date_format'), strtotime($redemption->created_at)); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p><?php _e('No reward redemptions yet.', 'ai-community'); ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>