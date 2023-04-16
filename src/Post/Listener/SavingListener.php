<?php

namespace Nearata\PreventDoublePosting\Post\Listener;

use Flarum\Discussion\Discussion;
use Flarum\Foundation\ValidationException;
use Flarum\Post\Event\Saving;
use Flarum\Post\Post;
use Flarum\Settings\SettingsRepositoryInterface;
use Flarum\User\User;
use Symfony\Contracts\Translation\TranslatorInterface;

class SavingListener
{
    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var SettingsRepositoryInterface
     */
    protected $settings;

    public function __construct(TranslatorInterface $translator, SettingsRepositoryInterface $settings)
    {
        $this->translator = $translator;
        $this->settings = $settings;
    }

    public function handle(Saving $event)
    {
        // new discussion
        if (is_null($event->post->discussion->first_post_id)) {
            return;
        }

        // user editing
        if ($event->post->exists) {
            return;
        }

        /**
         * user has permission to bypass the below checks
         * like a tag-scoped one (et. tag5.discussion.bypassDoublePosting)
         */
        if ($event->actor->can('bypassDoublePosting', $event->post->discussion)) {
            return;
        }

        if (!$this->can($event->actor, $event->post->discussion)) {
            $count = (int) $this->settings->get('nearata-prevent-double-posting.sequential_replies_threshold');
            $message = $this->translator->trans('nearata-prevent-double-posting.forum.exception.too_many_replies', ['count' => $count]);

            throw new ValidationException(['nearataPreventDoublePosting' => $message]);
        }
    }

    private function can(User $actor, Discussion $discussion): bool
    {
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
