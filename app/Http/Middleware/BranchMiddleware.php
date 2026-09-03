<?php
namespace App\Http\Middleware;
use Closure; use Illuminate\Http\Request;
class BranchMiddleware { public function handle(Request $request, Closure $next){ if($request->user() && $request->user()->branch_id){ app()->instance('currentBranchId',$request->user()->branch_id); } return $next($request);} }
