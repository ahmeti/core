<?php

namespace App\Exceptions;

use App\Core;
use App\Response;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        if( $exception->getMessage() == 'Unauthenticated.' ){

            if( $request->method() == 'GET' ){
                # Giriş yaqptıktan sonra yönlendirmek için url adresini cookie olarak kaydediyoruz.
                Cookie::queue('redirect_url', url()->full(), config('session.lifetime'));
            }

            // Oturum süresi dolmuş.
            session()->flush();

            if( $request->wantsJson() ){
                return Response::status(false)
                    ->message(__('Oturum süreniz dolmuş.'))
                    ->title(__('Oturum Süreniz Dolmuş'))
                    ->body(Core::alert(false, __('Oturum süreniz dolmuş. Lütfen tekrar giriş yapın.')))
                    ->jsCode('setTimeout(function(){ window.location.href = "'.url('/login?error=1').'"; }, 2000)')
                    ->get();
            }

            return redirect('/login?error=1');

        }else if( $exception->getMessage() == 'Permission-Error.' ){
            // Bu sayfayı görüntüleme yetkisi yok
            if( $request->wantsJson() ){
                return Response::status(false)
                    ->message(__('Bu işlem için yetkiniz bulunmuyor.'))
                    ->title(__('Erişiminiz Engellendi'))
                    ->body(Core::alert(false, __('Bu işlem için yetkiniz bulunmuyor. Yetki almak için sistem yöneticinize başvurun.')))
                    ->get();
            }

            return redirect()->route('page.error-403');

        }else if ( $this->isHttpException($exception) ) {
            Log::info(print_r([
                'url' => request()->url(),
                'params' => request()->all(),
                'company_id' => session()->get('company_id'),
                'user_id' => session()->get('user_id'),
            ], true));

        }else if( config('app.env') === 'production' && $exception instanceof QueryException){

            if( $request->wantsJson() && $request->isMethod('post') ){
                return Response::status(false)
                    ->message(__('Bir hata oluştu ve raporlandı. En kısa sürede bu hatanın tekrar oluşmasını engelleyeceğiz.'))
                    ->errorName('gonder')
                    ->get();

            }elseif( $request->wantsJson() && $request->isMethod('get') ){
                return Response::status(false)
                    ->message(__('Bir hata oluştu ve raporlandı. En kısa sürede bu hatanın tekrar oluşmasını engelleyeceğiz.'))
                    ->title(__('Bir Hata Oluştu'))
                    ->body(Core::alert(false, __('Bir hata oluştu ve raporlandı. En kısa sürede bu hatanın tekrar oluşmasını engelleyeceğiz.')))
                    ->get();
            }
        }

        return parent::render($request, $exception);
    }
}
