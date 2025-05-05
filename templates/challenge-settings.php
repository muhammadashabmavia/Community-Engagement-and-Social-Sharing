<div class="wrap">
    <h1><?php _e('Challenge Settings', 'ai-community'); ?></h1>
    
    <div class="challenge-form">
        <h2><?php _e('Create New Challenge', 'ai-community'); ?></h2>
        
        <form method="post" enctype="multipart/form-data">
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="title"><?php _e('Challenge Title', 'ai-community'); ?></label></th>
                    <td><input type="text" id="title" name="title" class="regular-text" required></td>
                </tr>
                
                <tr>
                    <th scope="row"><label for="description"><?php _e('Description', 'ai-community'); ?></label></th>
                    <td>
                        <?php 
                        wp_editor('', 'description', array(
                            'textarea_name' => 'description',
                            'media_buttons' => false,
                            'teeny' => true
                        )); 
                        ?>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><label for="duration"><?php _e('Duration (days)', 'ai-community'); ?></label></th>
                    <td><input type="number" id="duration" name="duration" min="1" max="30" value="7" required></td>
                </tr>
                
                <tr>
                    <th scope="row"><label for="hashtags"><?php _e('Hashtags', 'ai-community'); ?></label></th>
                    <td>
                        <input type="text" id="hashtags" name="hashtags" class="regular-text" 
                               placeholder="#7DayChallenge #Transformation" required>
                        <p class="description"><?php _e('Separate multiple hashtags with spaces', 'ai-community'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><label for="affiliate_products"><?php _e('Affiliate Products', 'ai-community'); ?></label></th>
                    <td>
                        <select id="affiliate_products" name="affiliate_products[]" multiple class="regular-text">
                            <?php 
                            $products = wc_get_products(array('limit' => -1));
                            foreach ($products as $product): ?>
                                <option value="<?php echo $product->get_id(); ?>">
                                    <?php echo esc_html($product->get_name()); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="description"><?php _e('Hold Ctrl/Cmd to select multiple products', 'ai-community'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><label for="badge_image"><?php _e('Badge Image', 'ai-community'); ?></label></th>
                    <td>
                        <input type="file" id="badge_image" name="badge_image" accept="image/*">
                    </td>
                </tr>
            </table>
            
            <?php submit_button(__('Create Challenge', 'ai-community'), 'primary', 'submit_challenge'); ?>
        </form>
    </div>
    
    <div class="existing-challenges">
        <h2><?php _e('Existing Challenges', 'ai-community'); ?></h2>
        
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php _e('Title', 'ai-community'); ?></th>
                    <th><?php _e('Duration', 'ai-community'); ?></th>
                    <th><?php _e('Participants', 'ai-community'); ?></th>
                    <th><?php _e('Actions', 'ai-community'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php 
                global $wpdb;
                $challenges = $wpdb->get_results(
                    "SELECT c.*, COUNT(p.participant_id) as participants 
                    FROM {$wpdb->prefix}ai_community_challenges c
                    LEFT JOIN {$wpdb->prefix}ai_community_challenge_participants p ON c.challenge_id = p.challenge_id
                    GROUP BY c.challenge_id
                    ORDER BY c.created_at DESC"
                );
                
                if ($challenges): 
                    foreach ($challenges as $challenge): ?>
                        <tr>
                            <td><?php echo esc_html($challenge->title); ?></td>
                            <td><?php printf(_n('%d day', '%d days', $challenge->duration, 'ai-community'), $challenge->duration); ?></td>
                            <td><?php echo $challenge->participants; ?></td>
                            <td>
                                <form method="post" style="display:inline;">
                                    <input type="hidden" name="reward_id" value="<?php echo $challenge->challenge_id; ?>">
                                    <button type="submit" name="delete_reward" class="button-link delete">
                                        <?php _e('Delete', 'ai-community'); ?>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; 
                else: ?>
                    <tr>
                        <td colspan="4"><?php _e('No challenges found.', 'ai-community'); ?></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>