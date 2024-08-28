<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use GuzzleHttp\Client;

class FlaskAPI
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if ($request->isMethod('post') && $request->path() == 'rekomendasi') {
            $diagnosa = $request->input('diagnosa');
            $client = new Client();
            $response = $client->post('http://localhost:5000/rekomendasi', [
                'json' => ['diagnosa' => $diagnosa]
            ]);

            $responseBody = json_decode($response->getBody(), true);
            if (isset($responseBody['rekomendasi_obat'])) {
                return response()->json(['rekomendasi_obat' => $responseBody['rekomendasi_obat']]);
            } else {
                return response()->json(['error' => 'Diagnosa tidak ditemukan'], 404);
            }
        }

        return $next($request);
    }
}
