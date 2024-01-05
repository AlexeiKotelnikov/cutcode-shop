<?php

namespace App\Providers;

use App\Http\Kernel;
use Carbon\CarbonInterval;
use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // данные два метода работают пока проект в разработке (2я строка сработает если забыть прописать в модели поля $fillable)
        Model::preventLazyLoading(!app()->isProduction());
        Model::preventSilentlyDiscardingAttributes(!app()->isProduction());


        //если какой-то запрос к бд дольше чем 500 сек, то идет отправка в телегу
        DB::whenQueryingForLongerThan(500, function (Connection $connection) {
            logger()->channel('telegram')->debug('whenQueryingForLongerThan:' . $connection->query()->toSql());
        });


        $kernel = app(Kernel::class);
        $kernel->whenRequestLifecycleIsLongerThan(
            CarbonInterval::seconds(4),
            function () {
                logger()->channel('telegram')->debug('whenRequestLifecycleIsLongerThan:' . request()->url());
            }
        );
    }
}
