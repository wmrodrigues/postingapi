<?php

namespace App\Providers;

use App\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Crypt;
use App\Http\Helpers\TokenData;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Boot the authentication services for the application.
     *
     * @return void
     */
    public function boot()
    {
        // Here you may define how you wish users to be authenticated for your Lumen
        // application. The callback which receives the incoming request instance
        // should return either a User instance or null. You're free to obtain
        // the User instance via an API token or any other method necessary.

        $this->app['auth']->viaRequest('api', function ($request) {
            $token = $request->header('Authorization');
            if (!empty($token)) {
                $split = explode(" ", $token);
                if (strtolower($split[0]) == "bearer") {
                    $token = $split[1];
                    $token = Crypt::decrypt($token);
                    $values = explode('|', $token);
                    $date = $values[TokenData::DATETIME];
                    $now = new \DateTime();
                    $expires = new \DateTime($date);
                    if ($now <= $expires) {
                        return true;
                    }
                }
            }
            return null;
        });
    }
}
