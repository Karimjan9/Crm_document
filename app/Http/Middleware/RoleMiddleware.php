<?php

namespace App\Http\Middleware;

use auth;
use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    
    public function handle(Request $request, Closure $next, $role1,$role2=null,$role3=null)
    {
        // dd($role3);
        $role="";
        $array=[];
        for ($i=1; $i <4 ; $i++) { 
            $role="role".$i;
            // dd(${ $role });
            if(${$role}){
                $array[]=${$role};
            }
        }
        // dd($array);
        if(!auth()->user()->hasRole($array)) {
            abort(404);
        }
        
        return $next($request);
    }
}
