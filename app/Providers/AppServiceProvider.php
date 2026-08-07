<?php

namespace App\Providers;

use App\Models\Pagamento;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
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
        // Força HTTPS em produção (necessário atrás do proxy do Render)
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }

        View::composer('*', function ($view) {
            if (auth()->check()) {
                $totalReceita = Pagamento::where('status', 'pago')->sum('valor');

                $metas = [
                    350 => '350',
                    1000 => '1k',
                    3000 => '3k',
                    5000 => '5k',
                    10000 => '10k',
                    30000 => '30k',
                    50000 => '50k',
                    100000 => '100k',
                    300000 => '300k',
                    1000000 => '1M',
                    5000000 => '5M',
                    10000000 => '10M',
                ];

                $metaAtualValor = 350;
                $metaAtualLabel = '350';

                foreach ($metas as $valor => $label) {
                    $metaAtualValor = $valor;
                    $metaAtualLabel = $label;
                    if ($totalReceita < $valor) {
                        break;
                    }
                }

                $porcentagem = $metaAtualValor > 0 ? min(100, round(($totalReceita / $metaAtualValor) * 100, 1)) : 0;

                $formatKMB = function ($val) {
                    if ($val < 1000) {
                        return 'R$ ' . number_format($val, 0, ',', '.');
                    } elseif ($val < 1000000) {
                        $num = $val / 1000;
                        return 'R$ ' . (floor($num) == $num ? number_format($num, 0, ',', '.') : number_format($num, 1, ',', '.')) . 'K';
                    } else {
                        $num = $val / 1000000;
                        return 'R$ ' . (floor($num) == $num ? number_format($num, 0, ',', '.') : number_format($num, 1, ',', '.')) . 'M';
                    }
                };

                $metasData = [
                    'totalReceita' => $totalReceita,
                    'totalReceitaFormatada' => $formatKMB($totalReceita),
                    'metaAtualValor' => $metaAtualValor,
                    'metaAtualLabel' => $metaAtualLabel,
                    'metaAtualFormatada' => $formatKMB($metaAtualValor),
                    'porcentagem' => $porcentagem,
                    'metas' => $metas,
                    'formatKMB' => $formatKMB,
                ];

                $view->with('metasData', $metasData);
            }
        });
    }
}
