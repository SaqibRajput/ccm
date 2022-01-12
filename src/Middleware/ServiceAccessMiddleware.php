<?php
    /**
     * Developed by Saqib Rajput.
     * Email: rajput.saqib@hotmail.com
     * Mobile: 00-92-300-6710419
     * Date: 12/01/2022
     * Time: 21:10
     */

    namespace CCM\Leads\Middleware;

    use Closure;
    use Illuminate\Http\Response;

    class ServiceAccessMiddleware
    {
        /**
         * Handle an incoming request.
         *
         * @param \Illuminate\Http\Request $request
         * @param \Closure                 $next
         * @return mixed
         */
        public function handle($request, Closure $next)
        {
            $validSecrets = explode(',', env('SERVICE_SECRET'));

            if(in_array($request->header('service-secret-token'), $validSecrets))
            {
                return $next($request);
            }

            return createResponseData(Response::HTTP_UNAUTHORIZED, false, 'Invalid service token');
        }
    }
