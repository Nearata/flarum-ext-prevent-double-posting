<?php

namespace Nearata\PreventDoublePosting\Discussion\Access;

use Flarum\Discussion\Discussion;
use Flarum\Post\Post;
use Flarum\Settings\SettingsRepositoryInterface;
use Flarum\User\Access\AbstractPolicy;
use Flarum\User\User;

class DiscussionPolicy extends AbstractPolicy
{
    protected $settings;

    public function __construct(SettingsRepositoryInterface $settings)
    {
        $this->settings = $settings;
    }

    protected function doublePost(User $user, Discussion $discussion)
    {
        return $this->canDoublePost($user, $discussion);
    }

    private function canDoublePost(User $actor, Discussion $discussion): bool
    {
        if ($actor->hasPermission('nearata-prevent-double-posting.bypassDoublePosting')) {
            return true;
        }

        $exceptThreadAuthor = (bool) $this->settings->get('nearata-prevent-double-posting.except_thread_author');

        if ($exceptThreadAuthor && $actor->id == $discussion->user->id) {
            return true;
        }

        $threshold = (int) $this->settings->get('nearata-prevent-double-posting.sequential_replies_threshold');

        // not enough posts
        if ($threshold > $discussion->comment_count) {
            return true;
        }

        /**
         * @var \Illuminate\Database\Eloquent\Collection
         */
        $posts = $discussion->posts()
            ->where('is_private', false)
            ->where('type', 'comment')
            ->latest()
            ->take($threshold)
            ->get();

        $sameUser = $posts->every(function (Post $post) use ($actor) {
            return $post->user_id == $actor->id;
        });

        return !$sameUser;
    }
}
