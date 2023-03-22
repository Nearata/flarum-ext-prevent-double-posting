<?php

namespace Nearata\PreventDoublePosting\Post\Listener;

use Flarum\Foundation\ValidationException;
use Flarum\Post\Event\Saving;
use Flarum\Settings\SettingsRepositoryInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class SavingListener
{
    protected $translator;
    protected $settings;

    public function __construct(TranslatorInterface $translator, SettingsRepositoryInterface $settings)
    {
        $this->translator = $translator;
        $this->settings = $settings;
    }

    public function handle(Saving $event)
    {
        // new discussion
        if (is_null($event->post->discussion->firstPost)) {
            return;
        }

        // user editing
        if ($event->post->exists) {
            return;
        }

        if ($event->actor->cannot('doublePost', $event->post->discussion)) {
            $count = (int) $this->settings->get('nearata-prevent-double-posting.sequential_replies_threshold');
            $message = $this->translator->trans('nearata-prevent-double-posting.forum.exception.too_many_replies', ['count' => $count]);

            throw new ValidationException(['nearataPreventDoublePosting' => $message]);
        }
    }
}
