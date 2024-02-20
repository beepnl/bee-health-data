@component('mail::message')
Dear, {{$user->fullname}} 
 
Based on your notification settings you hereby receive an alert for the following datasets: 

@foreach($datasets as $dataset)
  **Dataset: {{$dataset->name}}**  
  **Status: {{$dataset->is_published_soon ? 'New dataset': 'Updated dataset'}}**  
  **Access:** @if($dataset->isDownloadable($user)) **You can download this dataset** @else **You can view the dataset, to download it you can request for access** @endif  
  [Click here to open the dataset]({{route('datasets.show', ['dataset'=>$dataset->id])}})

@endforeach
 
To change your notification settings you can [click here]({{route('notifications.index')}})
 
This is an automatically generated email. Replies to this email will not be read. 
In case of issues, please use the contact form on the portal. 
 

Thanks,
{{ config('app.name') }}
@endcomponent
