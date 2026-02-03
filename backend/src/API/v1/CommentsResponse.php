<?php

namespace Karolina\API\v1;

use Asika\Autolink\Autolink;
use Karolina\Discussion\Comment;

class CommentsResponse
{

    private $comments;
    private $imgStorageUrl;

    public function __construct(array $comments, $imgStorageUrl)
    {

        $this->comments = $comments;
        $this->imgStorageUrl = $imgStorageUrl;

    }

    public function get()
    {
        $response = [];

        foreach ($this->comments as $comment) {

            $commentResponse = $this->getSingleComment($comment);

            $commentResponse['comment_replies'] = [];
            foreach ($comment->getReplies() as $reply) {
                $commentResponse['comment_replies'][] = $this->getSingleComment($reply);
            }

            $response[] = $commentResponse;
        }

        return $response;

    }

    private function getSingleComment (Comment $comment) {


        /* @var $comment \Karolina\Discussion\Comment */
        $commentResponse = [];

        $options = array(
            'strip_scheme' => false,
            'auto_title' => false,
            'escape' => true,
            'auto_title' => true,
            'link_no_scheme' => true
        );

        $autolink = new Autolink($options);
        $autolink->linkNoScheme('https');
        $autolink->textLimit(function($url)
        {
            return $url;
            return \Asika\Autolink\LinkHelper::shorten($url, 15, 6);
        });

        $commentResponse['format'] = $comment->getFormat();
        $commentResponse['urls_in_comment'] = [];

        if ($comment->getFormat() == 'plaintext') {

            $strippedValue = strip_tags ($comment->getSanitizedValue());
            $commentResponse['value'] = $autolink->convert($strippedValue);
            $commentResponse['value'] = $strippedValue;
            preg_match_all('#\bhttps?://[^,\s()<>]+(?:\([\w\d]+\)|([^,[:punct:]\s]|/))#', $strippedValue, $matched);
            $commentResponse['urls_in_comment'] = $matched[0];    

        } else if ($comment->getFormat() == 'tiptap-json') {

            $commentResponse['value'] = $comment->getSanitizedValue();
            $commentResponse['html_value'] = $comment->getHTMLValue();
        }

        $commentResponse['type'] = $comment->getType();


        $commentResponse['posted_time'] = date('c', $comment->getPostedTime());
        $commentResponse['comment_id'] = $comment->getId();
        $commentResponse['upvotes_count'] = $comment->countUpvotes();

        $commentResponse['topic'] = $comment->getTopicId();

        $commentResponse['upvoters'] = $comment->getUpvoters();
        $commentResponse['subject'] = $comment->getSubject();

        $commentResponse['images'] = $comment->getImages();
        $commentResponse['attachments'] = $comment->getAttachments();
        $commentResponse['submissions'] = $comment->getSubmissions();        

        $commentResponse['reactions'] = $comment->getReactionsByType();

        $commentResponse['commenter']['first_name'] = 'Depricated first';
        $commentResponse['commenter']['last_name'] = 'Depricated last';
        $commentResponse['commenter']['user_id'] = $comment->getUserId();

        $commentResponse['commenter']['image'] = 'https://s3-eu-west-1.amazonaws.com/idea-village-image-host/sandbox/1d2952f3-2041-4f24-bd19-441bf4c4882c.jpg';
        $commentResponse['commenter']['profile_pic'] = 'https://s3-eu-west-1.amazonaws.com/idea-village-image-host/sandbox/1d2952f3-2041-4f24-bd19-441bf4c4882c.jpg';

        return $commentResponse;


    }

    private function auto_link_text($text) {
        $pattern  = '#\b(([\w-]+://?|www[.])[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/)))#';
        return preg_replace_callback($pattern, array($this, 'auto_link_text_callback'), $text);
    }

    function auto_link_text_callback($matches) {
        $max_url_length = 50;
        $max_depth_if_over_length = 2;
        $ellipsis = '&hellip;';

        $url_full = $matches[0];
        $url_short = '';

        if (strlen($url_full) > $max_url_length) {
            $parts = parse_url($url_full);
            $url_short = $parts['scheme'] . '://' . preg_replace('/^www\./', '', $parts['host']) . '/';

            $path_components = explode('/', trim($parts['path'], '/'));
            foreach ($path_components as $dir) {
                $url_string_components[] = $dir . '/';
            }

            if (!empty($parts['query'])) {
                $url_string_components[] = '?' . $parts['query'];
            }

            if (!empty($parts['fragment'])) {
                $url_string_components[] = '#' . $parts['fragment'];
            }

            for ($k = 0; $k < count($url_string_components); $k++) {
                $curr_component = $url_string_components[$k];
                if ($k >= $max_depth_if_over_length || strlen($url_short) + strlen($curr_component) > $max_url_length) {
                    if ($k == 0 && strlen($url_short) < $max_url_length) {
                        // Always show a portion of first directory
                        $url_short .= substr($curr_component, 0, $max_url_length - strlen($url_short));
                    }
                    $url_short .= $ellipsis;
                    break;
                }
                $url_short .= $curr_component;
            }

        } else {
            $url_short = $url_full;
        }

        return "<a rel=\"nofollow\" href=\"$url_full\">$url_short</a>";
    }


}