<?php

namespace App\Observers;

use App\Classes\Notificator;
use App\Models\Dataset;
use App\Models\Notification as ModelNotification;
use Carbon\Carbon;

class DatasetObserver
{
    /**
     * Handle the Dataset "created" event.
     *
     * @param  \App\Models\Dataset  $dataset
     * @return void
     */
    public function created(Dataset $dataset)
    {
        // 
    }

    public function updating(Dataset $dataset)
    {
        $dirtyDataset = $dataset->getDirty();
        if(!empty($dirtyDataset['publication_state']) && $dirtyDataset['publication_state'] == $dataset::PUBLICATION_STATES_PUBLISHED){
            $dataset->setAttribute('published_at', (string)Carbon::now());
        }
    }
    /**
     * Handle the Dataset "updated" event.
     *
     * @param  \App\Models\Dataset  $dataset
     * @return void
     */
    public function updated(Dataset $dataset)
    {
        $oldDataset = $dataset->getOriginal();
        $changesDataset = $dataset->getChanges();

        if($dataset->is_draft || $dataset->is_inactive){
            return;
        }

        if(
            !empty($oldDataset['publication_state']) && 
            !empty($changesDataset['publication_state']) &&
            in_array($oldDataset['publication_state'], [$dataset::PUBLICATION_STATES_INACTIVE, $dataset::PUBLICATION_STATES_DRAFT], true) &&
            $changesDataset['publication_state'] == $dataset::PUBLICATION_STATES_PUBLISHED
        ){
            // Is new dataset
            $notifications = ModelNotification::frequency(ModelNotification::IMMEDIATELY)
            ->whereIn('name', [ModelNotification::ALL_NEW_DATASETS, ModelNotification::NEW_DATASETS_I_HAVE_ACCESS_TO])
            ->get();
        }else{
            // Is updated dataset
            $notifications = ModelNotification::frequency(ModelNotification::IMMEDIATELY)
            ->whereIn('name', [ModelNotification::UPDATES_TO_DATASETS, ModelNotification::UPDATES_TO_DATASETS_I_HAVE_ACCESS_TO])
            ->get();
        }
        
        foreach($notifications as $notification){
            $notificator = new Notificator($notification);
            $notificator->notify($dataset);
        }
    }

    /**
     * Handle the Dataset "deleted" event.
     *
     * @param  \App\Models\Dataset  $dataset
     * @return void
     */
    public function deleted(Dataset $dataset)
    {
        //
    }

    /**
     * Handle the Dataset "restored" event.
     *
     * @param  \App\Models\Dataset  $dataset
     * @return void
     */
    public function restored(Dataset $dataset)
    {
        //
    }

    /**
     * Handle the Dataset "force deleted" event.
     *
     * @param  \App\Models\Dataset  $dataset
     * @return void
     */
    public function forceDeleted(Dataset $dataset)
    {
        //
    }

}
