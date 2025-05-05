jQuery(document).ready(function($) {
    // Forum topic creation
    $('#ai-forum-topic-form').on('submit', function(e) {
        e.preventDefault();
        
        var form = $(this);
        var formData = form.serialize();
        
        $.ajax({
            url: aiCommunity.ajaxurl,
            type: 'POST',
            data: formData + '&action=create_forum_topic&nonce=' + aiCommunity.nonce,
            beforeSend: function() {
                form.find('button[type="submit"]').text('Posting...').prop('disabled', true);
            },
            success: function(response) {
                if (response.success) {
                    alert('Topic created successfully!');
                    location.reload();
                } else {
                    alert(response.data);
                    form.find('button[type="submit"]').text('Submit').prop('disabled', false);
                }
            },
            error: function() {
                alert('An error occurred. Please try again.');
                form.find('button[type="submit"]').text('Submit').prop('disabled', false);
            }
        });
    });
    
    // Challenge joining
    $('.join-challenge').on('click', function() {
        var challengeId = $(this).closest('.challenge').data('challenge-id');
        var button = $(this);
        
        $.ajax({
            url: aiCommunity.ajaxurl,
            type: 'POST',
            data: {
                action: 'join_challenge',
                challenge_id: challengeId,
                nonce: aiCommunity.nonce
            },
            beforeSend: function() {
                button.text('Joining...').prop('disabled', true);
            },
            success: function(response) {
                if (response.success) {
                    alert('Challenge joined successfully!');
                    location.reload();
                } else {
                    alert(response.data);
                    button.text('Join Challenge').prop('disabled', false);
                }
            },
            error: function() {
                alert('An error occurred. Please try again.');
                button.text('Join Challenge').prop('disabled', false);
            }
        });
    });
    
    // Forum category filtering
    $('.forum-categories li a').on('click', function(e) {
        e.preventDefault();
        
        var category = $(this).data('category');
        var topicsContainer = $('.topics-list');
        
        $.ajax({
            url: aiCommunity.ajaxurl,
            type: 'POST',
            data: {
                action: 'filter_forum_topics',
                category: category,
                nonce: aiCommunity.nonce
            },
            beforeSend: function() {
                topicsContainer.html('<div class="loading">Loading topics...</div>');
            },
            success: function(response) {
                if (response.success) {
                    topicsContainer.html(response.data);
                } else {
                    topicsContainer.html('<p>Error loading topics. Please try again.</p>');
                }
            },
            error: function() {
                topicsContainer.html('<p>Error loading topics. Please try again.</p>');
            }
        });
    });
    
    // Toggle topic form
    $('.create-topic button').on('click', function() {
        $(this).next('.topic-form').toggle();
    });
});