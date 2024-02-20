<?php

namespace App\Classes;

use App\Models\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Dataset;
use App\Mail\Notification as MailNotification;
use Carbon\Carbon;

class Notificator {

    /**
    * The model user instance
    *
    * @var \App\Models\User
    */
    private $user;

    /**
    * The name of notification
    *
    * @var string
    */
    private $name;

    /**
    * The frequency of notification
    *
    * @var string
    */
    private $frequency;

    /**
    * Create a new Notificator instance
    *
    * @param \App\Models\Notification $notification
    * @return void
    */
    public function __construct(Notification $notification)
    {
        $this->user = User::find($notification->user_id);
        $this->name = $notification->name;
        $this->frequency = $notification->frequency;
    }

    /**
    * Get the notification function name
    *
    * @return string
    */
    private function getName()
    {
        $lowerName = Str::lower($this->name .'_'. $this->frequency);
        $camelName = Str::camel($lowerName);
        $name = Str::ucfirst($camelName);
        return 'listen'.$name;
    }

    public function listenAllNewDatasetsImmediately($payload)
    {   
        if( ! $payload instanceof Model ){
            return;
        }

        $mail = new MailNotification($this->user, collect([$payload]));
        Mail::to($this->user)->queue($mail);
    }
    
    public function listenNewDatasetsIHaveAccessToImmediately($payload)
    {   
        if( ! $payload instanceof Model ){
            return;
        }

        if( !$payload->isDownloadable($this->user) ){
            return;
        }
        
        $mail = new MailNotification($this->user, collect([$payload]));
        Mail::to($this->user)->queue($mail);
    }

    public function listenUpdatesToDatasetsImmediately($payload)
    {   
        $this->listenAllNewDatasetsImmediately($payload);
    }

    public function listenUpdatesToDatasetsIHaveAccessToImmediately($payload)
    {   
        $this->listenNewDatasetsIHaveAccessToImmediately($payload);
    }

    public function listenAllNewDatasetsWeekly($payload)
    {
        $datasets = Dataset::active()->whereBetween('created_at', [Carbon::today()->subWeek(), Carbon::now()])->get();

        $mail = new MailNotification($this->user, $datasets);
        Mail::to($this->user)->queue($mail);
    
    }

    public function listenNewDatasetsIHaveAccessToWeekly($payload)
    {   
        $datasets = Dataset::active()->whereBetween('created_at', [Carbon::today()->subWeek(), Carbon::now()])->get();
        
        $user = $this->user;
        $datasets = $datasets->filter(function($dataset) use ($user){
            return $dataset->isDownloadable($user);
        });
        
        $mail = new MailNotification($user, $datasets);
        Mail::to($this->user)->queue($mail);
    }

    public function listenUpdatesToDatasetsWeekly($payload)
    {   
        $datasets = Dataset::active()->whereBetween('updated_at', [Carbon::today()->subWeek(), Carbon::now()])->get();
        
        $user = $this->user;
        $datasets = $datasets->filter(function($dataset) use ($user){
            return $dataset->isDownloadable($user);
        });
        
        $mail = new MailNotification($user, $datasets);
        Mail::to($this->user)->queue($mail);
    }

    public function listenUpdatesToDatasetsIHaveAccessToWeekly($payload)
    {   
        $datasets = Dataset::active()->whereBetween('updated_at', [Carbon::today()->subWeek(), Carbon::now()])->get();
        
        $user = $this->user;
        $datasets = $datasets->filter(function($dataset) use ($user){
            return $dataset->isDownloadable($user);
        });
        
        $mail = new MailNotification($user, $datasets);
        Mail::to($this->user)->queue($mail);
    }

    /**
    * Involke notification function with arguments
    *
    * @param mixed $arguments
    * @return string
    */
    public function notify(...$arguments)
    {
        $function = [$this, $this->getName()];
        if(is_callable($function)){
            return call_user_func($function, ...$arguments);
        }
    }
}
