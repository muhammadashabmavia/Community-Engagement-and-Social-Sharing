<div class="ai-community-forum">
    <div class="forum-header">
        <h2><?php _e('Metaphysical Transformation Corner', 'ai-community'); ?></h2>
        <p><?php _e('Join discussions on financial freedom, personal development, and spiritual healing.', 'ai-community'); ?></p>
    </div>
    
    <div class="forum-categories">
        <h3><?php _e('Categories', 'ai-community'); ?></h3>
        <ul>
            <?php foreach ($forum_manager->get_categories() as $slug => $name): ?>
                <li><a href="#" data-category="<?php echo esc_attr($slug); ?>"><?php echo esc_html($name); ?></a></li>
            <?php endforeach; ?>
        </ul>
    </div>
    
    <div class="forum-topics">
        <h3><?php _e('Recent Topics', 'ai-community'); ?></h3>
        
        <?php if (is_user_logged_in()): ?>
            <div class="create-topic">
                <button class="button"><?php _e('Create New Topic', 'ai-community'); ?></button>
                <div class="topic-form" style="display:none;">
                    <form id="ai-forum-topic-form">
                        <input type="text" name="title" placeholder="<?php _e('Topic title', 'ai-community'); ?>" required>
                        <select name="category" required>
                            <option value=""><?php _e('Select category', 'ai-community'); ?></option>
                            <?php foreach ($forum_manager->get_categories() as $slug => $name): ?>
                                <option value="<?php echo esc_attr($slug); ?>"><?php echo esc_html($name); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <textarea name="content" placeholder="<?php _e('Your message...', 'ai-community'); ?>" required></textarea>
                        <button type="submit" class="button"><?php _e('Submit', 'ai-community'); ?></button>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <p><?php _e('Please log in to create topics.', 'ai-community'); ?></p>
        <?php endif; ?>
        
        <div class="topics-list">
            <?php 
            $topics = $forum_manager->get_topics();
            if ($topics): 
                foreach ($topics as $topic): 
                    $author = get_user_by('id', $topic->user_id);
                    ?>
                    <div class="topic" data-topic-id="<?php echo $topic->topic_id; ?>">
                        <div class="topic-header">
                            <h4><?php echo esc_html($topic->title); ?></h4>
                            <span class="category"><?php echo esc_html($forum_manager->get_categories()[$topic->category]); ?></span>
                        </div>
                        <div class="topic-content"><?php echo wp_kses_post($topic->content); ?></div>
                        <div class="topic-meta">
                            <span class="author"><?php echo esc_html($author->display_name); ?></span>
                            <span class="date"><?php echo date_i18n(get_option('date_format'), strtotime($topic->created_at)); ?></span>
                        </div>
                    </div>
                <?php endforeach; 
            else: ?>
                <p><?php _e('No topics found. Be the first to start a discussion!', 'ai-community'); ?></p>
            <?php endif; ?>
        </div>
    </div>
</div>