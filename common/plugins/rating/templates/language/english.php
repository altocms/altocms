<?php
/**
 * russian.php
 * Файл локализации плагина Rating
 *
 * @author      Андрей Воронов <andreyv@gladcode.ru>
 *              Является частью плагина Rating
 * @version     0.0.1 от 30.01.2015 15:45
 */
return array(

    'admin_title'                    => 'Rating system (standard)',
    'save'                           => 'Save',
    'cancel'                         => 'Cancel',

    'rating_enabled'                 => 'Enable rating system',

    'acl_vote_user_rating_notice'    => 'Vote for the user',
    'acl_vote_blog_rating_notice'    => 'vote for the blog',
    'acl_vote_topic_rating_notice'   => 'Vote for the topic',
    'acl_vote_comment_rating_notice' => 'Vote for your comment',
    'acl_vote'                       => 'The lower threshold rating to vote (users with lower ratings will not be able to vote)',
    'acl_notice'                     => 'Common settings',

    'user_config'                    => 'User rating calculation parameters from voting for him',
    'user_vote'                      => 'Use voting for user',
    'user_dislike'                   => 'Use dislike for user',
    'user_min_change'                => 'The minimum possible changes',
    'user_max_change'                => 'The value of the maximum possible change',
    'user_max_rating'                => 'The maximum rating of the vote',
    'user_right_border'              => 'Right boundaries rating votes',
    'user_left_border'               => 'The left boundary of the rating votes',
    'user_left_divider'              => 'Left divider range',
    'user_mid_divider'               => 'Divider average range',
    'user_right_divider'             => 'Divider right range',

    'blog_config'                    => 'The parameters for calculating the rating of the blog when voting for him',
    'blog_vote'                      => 'Use voting for blog',
    'blog_dislike'                   => 'Use dislike for blog',
    'blog_min_change'                => 'The minimum possible changes',
    'blog_max_change'                => 'The value of the maximum possible change',
    'blog_max_rating'                => 'The maximum rating of the vote',
    'blog_right_border'              => 'Right boundaries rating votes',
    'blog_left_border'               => 'The left boundary of the rating votes',
    'blog_left_divider'              => 'Left divider range',
    'blog_mid_divider'               => 'Divider average range',
    'blog_right_divider'             => 'Divider right range',

    'comment_config'                 => 'Parameters for calculating power by voting for his comment',
    'comment_vote'                   => 'Use voting for comment',
    'comment_dislike'                => 'Use dislike for comment',
    'comment_min_change'             => 'The minimum possible changes',
    'comment_max_change'             => 'The value of the maximum possible change',
    'comment_max_rating'             => 'The maximum rating of the vote',
    'comment_right_border'           => 'Right boundaries rating votes',
    'comment_left_border'            => 'The left boundary of the rating votes',
    'comment_left_divider'           => 'Left divider range',
    'comment_mid_divider'            => 'Divider average range',
    'comment_right_divider'          => 'Divider right range',

    'topic_config'                   => 'The parameters for calculating the strength and power user voting for his topic',
    'topic_vote'                     => 'Use voting for topic',
    'topic_dislike'                  => 'Use dislike for topic',
    'topic_min_change'               => 'The minimum possible changes',
    'topic_max_change'               => 'The value of the maximum possible change',
    'topic_max_rating'               => 'The maximum rating of the vote',
    'topic_right_border'             => 'Right boundaries rating votes',
    'topic_left_border'              => 'The left boundary of the rating votes',
    'topic_left_divider'             => 'Left divider range',
    'topic_mid_divider'              => 'Divider average range',
    'topic_right_divider'            => 'Divider right range',
    'topic_auth_coef'                => 'Coefficient of authors',

    'rating_config'                  => 'The parameters for calculating the rating of topic voting for him',
    'rating_vote'                    => 'Use voting for author',
    'rating_topic_border_1'          => 'The first border rankings topic',
    'rating_topic_border_2'          => 'The second border rankings topic',
    'rating_topic_border_3'          => 'The third border rankings topic',
    'rating_topic_k1'                => 'Modifier first range',
    'rating_topic_k2'                => 'Modifier second range',
    'rating_topic_k3'                => 'Third factor range',
    'rating_topic_k4'                => 'Fourth factor range',

    'personal_recalc'                => 'The coefficients for the conversion of ratings personal blogs',
    'topic_rating_sum'               => 'Coefficient for the sum of all ratings topics blog',
    'count_topic'                    => 'The coefficient for the number of topics',

);