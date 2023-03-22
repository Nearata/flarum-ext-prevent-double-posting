<?php

namespace Nearata\PreventDoublePosting;

use Flarum\Discussion\Discussion;
use Flarum\Extend;
use Flarum\Post\Event\Saving;
use Nearata\PreventDoublePosting\Discussion\Access\DiscussionPolicy;
use Nearata\PreventDoublePosting\Post\Listener\SavingListener;

return [
    (new Extend\Frontend('admin'))
        ->js(__DIR__.'/js/dist/admin.js'),

    new Extend\Locales(__DIR__.'/locale'),

    (new Extend\Settings)
        ->default('nearata-prevent-double-posting.except_thread_author', true)
        ->default('nearata-prevent-double-posting.sequential_replies_threshold', 2),

    (new Extend\Policy)
        ->modelPolicy(Discussion::class, DiscussionPolicy::class),

    (new Extend\Event)
        ->listen(Saving::class, SavingListener::class)
];
