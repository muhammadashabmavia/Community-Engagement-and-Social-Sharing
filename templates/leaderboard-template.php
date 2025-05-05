<div class="ai-community-leaderboard">
    <div class="leaderboard-header">
        <h2><?php _e('Community Leaderboard', 'ai-community'); ?></h2>
        <p><?php _e('Top contributors based on points earned', 'ai-community'); ?></p>
    </div>
    
    <div class="leaderboard-list">
        <?php 
        $leaderboard = $reward_system->get_leaderboard(20);
        if ($leaderboard): ?>
            <table>
                <thead>
                    <tr>
                        <th><?php _e('Rank', 'ai-community'); ?></th>
                        <th><?php _e('Member', 'ai-community'); ?></th>
                        <th><?php _e('Points', 'ai-community'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($leaderboard as $index => $user): 
                        $user_data = get_userdata($user->user_id);
                        $avatar = get_avatar($user->user_id, 40);
                        ?>
                        <tr>
                            <td class="rank"><?php echo $index + 1; ?></td>
                            <td class="user">
                                <?php echo $avatar; ?>
                                <span class="name"><?php echo esc_html($user_data->display_name); ?></span>
                            </td>
                            <td class="points"><?php echo $user->total_points; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p><?php _e('No leaderboard data available yet.', 'ai-community'); ?></p>
        <?php endif; ?>
    </div>
    
    <?php if (is_user_logged_in()): ?>
        <div class="user-position">
            <h3><?php _e('Your Position', 'ai-community'); ?></h3>
            <?php
            global $wpdb;
            $user_id = get_current_user_id();
            $position = $wpdb->get_var($wpdb->prepare(
                "SELECT position FROM (
                    SELECT user_id, RANK() OVER (ORDER BY SUM(points) DESC) as position 
                    FROM {$wpdb->prefix}ai_community_points 
                    GROUP BY user_id
                ) as ranks 
                WHERE user_id = %d",
                $user_id
            ));
            
            $points = $reward_system->get_user_points($user_id);
            ?>
            <p>
                <?php printf(__('You are ranked #%d with %d points.', 'ai-community'), 
                    $position ?: 0, 
                    $points
                ); ?>
            </p>
        </div>
    <?php else: ?>
        <p class="login-notice"><?php _e('Log in to see your position on the leaderboard.', 'ai-community'); ?></p>
    <?php endif; ?>
</div>