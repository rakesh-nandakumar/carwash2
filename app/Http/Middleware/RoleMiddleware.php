<?php
namespace App\Http\Middleware;
use Closure; use Illuminate\Http\Request;
class RoleMiddleware { public function handle(Request $request, Closure $next, ...$roles){$u=$request->user(); if(!$u || !in_array($u->role,$roles,true)) abort(403); return $next($request);} }
