<?php

namespace App\Helpers;

class MentionHelper
{
    /**
     * Convert mentions in text to clickable profile links
     * @param string $text The post content text
     * @param array $taggedUsers Array of tagged user objects with id and name
     * @return string HTML with clickable mention links
     */
    public static function convertMentionsToLinks($text, $taggedUsers)
    {
        if (empty($taggedUsers) || empty($text)) {
            return htmlspecialchars($text ?? '');
        }

        $result = htmlspecialchars($text);

        foreach ($taggedUsers as $user) {
            $mention = '@' . $user->name;
            $link = '<a href="/' . $user->username . '" class="text-[#38157a] font-semibold hover:underline">' . htmlspecialchars($user->name) . '</a>';
            $result = str_replace(htmlspecialchars($mention), $link, $result);
        }

        return $result;
    }
}
