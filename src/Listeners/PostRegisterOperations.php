<?php

/*
 * This file is part of fof/doorman.
 *
 * Copyright (c) Reflar.
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace FoF\Doorman\Listeners;

use Flarum\Settings\SettingsRepositoryInterface;
use Flarum\User\Event\GroupsChanged;
use Flarum\User\Event\Registered;
use FoF\Doorman\Doorkey;
use Illuminate\Contracts\Events\Dispatcher;

class PostRegisterOperations
{
    protected $settings;

    public function __construct(SettingsRepositoryInterface $settings, Dispatcher $events)
    {
        $this->settings = $settings;
        $this->events = $events;
    }

    public function handle(Registered $event)
    {
        $user = $event->user;
        $doorkey = null;
        
        $doorkeyText = trim($user->invite_code);
        
        // Allows the invitation key to be optional if the setting was enabled
        $allow = json_decode($this->settings->get('fof-doorman.allowPublic'));

        
        $hasDoorkey = false;
        if ($doorkeyText != ""){
            $hasDoorkey = true;
        }

        if ($allow && !$hasDoorkey) {
            return;
        }
        
        $doorkey = Doorkey::where('key', $doorkeyText)->first();
        
        if (!$doorkey){
            $doorkey = Doorkey::build($user->invite_code, 3, 0, 0);
            $doorkey->save();
        }


        if ($doorkey->activates) {
            $user->activate();
        }

        if ($doorkey->group_id !== 3) {
            $oldGroups = $user->groups()->get()->all();

            $user->groups()->attach($doorkey->group_id);

            $this->events->dispatch(
                new GroupsChanged($user, $oldGroups)
            );
        }

        $doorkey->increment('uses');

        $user->save();
    }
}
