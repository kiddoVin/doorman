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

use Flarum\Foundation\AbstractValidator;
use Flarum\Settings\SettingsRepositoryInterface;
use FoF\Doorman\Doorkey;
use Illuminate\Validation\Validator;

class AddValidatorRule
{
    protected $settings;

    public function __construct(SettingsRepositoryInterface $settings)
    {
        $this->settings = $settings;
    }

    public function __invoke(AbstractValidator $flarumValidator, Validator $validator)
    {
        $validator->addExtension(
            'doorkey',
            function ($attribute, $value, $parameters) {

                $doorkeyText = trim($value);

                // Allows the invitation key to be optional if the setting was enabled
                $allow = json_decode($this->settings->get('fof-doorman.allowPublic'));

                $hasDoorkey = false;
                if ($doorkeyText != ""){
                    $hasDoorkey = true;
                }

                if (!$hasDoorkey) {
                    return $allow ? true : false;
                }

                $doorkey = null;
                $doorkey = Doorkey::where('key', $doorkeyText)->first();
                if (!$doorkey) {
                    return $allow ? true : false;
                }

                if ($doorkey->max_uses === 0 || $doorkey->uses < $doorkey->max_uses) {
                    return true;
                } 

                return false;
            }
        );
    }
}
