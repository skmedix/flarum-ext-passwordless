<?php

namespace ClarkWinkelmann\PasswordLess\Listeners;

use ClarkWinkelmann\PasswordLess\Token;
use Flarum\Foundation\ValidationException;
use Flarum\Locale\Translator;
use Flarum\User\Event\CheckingPassword;
use Illuminate\Contracts\Events\Dispatcher;

class CheckPassword
{
    public function subscribe(Dispatcher $events)
    {
        $events->listen(CheckingPassword::class, [$this, 'checkPassword']);
    }

    public function checkPassword(CheckingPassword $event)
    {
        /**
         * @var Token $token
         */
        $token = Token::query()->where('user_id', $event->user->id)->where('token', $event->password)->first();

        if ($token) {
            if ($token->isExpired()) {
                /**
                 * @var Translator $translator
                 */
                $translator = app(Translator::class);

                throw new ValidationException([
                    'password' => [
                        $translator->trans('clarkwinkelmann-passwordless.api.expired-token-error'),
                    ],
                ]);
            }

            Token::deleteOldTokens();

            return true;
        }

        // If it's not a passwordless attempt, let the normal login process continue
        return null;
    }
}
