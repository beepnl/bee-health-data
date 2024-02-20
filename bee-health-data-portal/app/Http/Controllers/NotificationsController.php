<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Auth;

class NotificationsController extends Controller
{
    /**
     * Display a listing of notifications.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index()
    {
        $user = Auth::user();
        $notifications = $user->notifications()->get();
        $records = [
            'name' => Notification::$allowNotificationsType, 
            'frequency' => Notification::$allowFrequenciesType
        ];

        foreach($records as $column => $data){
        
            $fields = $notifications
                ->pluck('id', $column)
                ->filter(function($row, $key) use($data){
                    return array_key_exists($key, $data);
                })
                ->map(function($row, $key){
                    return ['checked' => true];
                });
            // If is not empty clear default checked fields
            if(!$fields->isEmpty()){
                $data = array_map(function($value){
                    if(isset($value['checked'])){
                        $value['checked'] = false;
                    }
                    return $value;
                }, $data);
            }
            $records[$column] = collect($data)->mergeRecursive($fields)->toArray();
        }

        return view('notifications.index', $records);
    }

    /**
    * Update notifications
    *
    * @param \Illuminate\Http\Request $request
    * @return \Illuminate\Routing\Redirector
     */
    public function update(Request $request)
    {
        $user = $request->user();
        Notification::own($user)->delete();

        foreach($request->input('notifications', []) as $notification){
            Notification::create([
                'user_id' => $user->id,
                'name' => $notification,
                'frequency' => $request->input('frequency')
            ]);
        }
        return redirect('/account');
    }
}
