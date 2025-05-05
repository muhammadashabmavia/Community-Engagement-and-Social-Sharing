<div class="wrap">
    <h1><?php _e('Reward System Settings', 'ai-community'); ?></h1>
    
    <div class="reward-form">
        <h2><?php _e('Create New Reward', 'ai-community'); ?></h2>
        
        <form method="post" enctype="multipart/form-data">
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="name"><?php _e('Reward Name', 'ai-community'); ?></label></th>
                    <td><input type="text" id="name" name="name" class="regular-text" required></td>
                </tr>
                
                <tr>
                    <th scope="row"><label for="description"><?php _e('Description', 'ai-community'); ?></label></th>
                    <td>
                        <textarea id="description" name="description" rows="3" class="regular-text"></textarea>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><label for="points_required"><?php _e('Points Required', 'ai-community'); ?></label></th>
                    <td><input type="number" id="points_required" name="points_required" min="1" required></td>
                </tr>
                
                <tr>
                    <th scope="row"><label for="badge_image"><?php _e('Badge Image', 'ai-community'); ?></label></th>
                    <td>
                        <input type="file" id="badge_image" name="badge_image" accept="image/*">
                    </td>
                </tr>
            </table>
            
            <?php submit_button(__('Create Reward', 'ai-community'), 'primary', 'submit_reward'); ?>
        </form>
    </div>
    
    <div class="existing-rewards">
        <h2><?php _e('Existing Rewards', 'ai-community'); ?></h2>
        
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php _e('Name', 'ai-community'); ?></th>
                    <th><?php _e('Description', 'ai-community'); ?></th>
                    <th><?php _e('Points', 'ai-community'); ?></th>
                    <th><?php _e('Redemptions', 'ai-community'); ?></th>
                    <th><?php _e('Actions', 'ai-community'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php 
                global $wpdb;
                $rewards = $wpdb->get_results(
                    "SELECT r.*, COUNT(rr.redemption_id) as redemptions 
                    FROM {$wpdb->prefix}ai_community_reward_items r
                    LEFT JOIN {$wpdb->prefix}ai_community_reward_redemptions rr ON r.reward_id = rr.reward_id
                    GROUP BY r.reward_id
                    ORDER BY r.points_required ASC"
                );
                
                if ($rewards): 
                    foreach ($rewards as $reward): ?>
                        <tr>
                            <td><?php echo esc_html($reward->name); ?></td>
                            <td><?php echo esc_html($reward->description); ?></td>
                            <td><?php echo $reward->points_required; ?></td>
                            <td><?php echo $reward->redemptions; ?></td>
                            <td>
                                <form method="post" style="display:inline;">
                                    <input type="hidden" name="reward_id" value="<?php echo $reward->reward_id; ?>">
                                    <button type="submit" name="delete_reward" class="button-link delete">
                                        <?php _e('Delete', 'ai-community'); ?>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; 
                else: ?>
                    <tr>
                        <td colspan="5"><?php _e('No rewards found.', 'ai-community'); ?></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>