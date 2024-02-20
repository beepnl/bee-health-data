<?php

namespace App\Http\Controllers;

use App\Models\Dataset;
use App\Models\FileVersion;
use App\Models\Keyword;
use App\Models\Organisation;
use App\Repositories\ChartRepository;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request, ChartRepository $chartRepository){

        $datasets = Dataset::published()->ofName($request->input('query', ''));
        $total_datasets = $datasets->count();
        // if null
        if(!$request->user()){
            $datasets = $datasets->openAccess();
        }
        
        $datasets = $datasets->get();
        
        $initialStat = [
            'total_datasets' => 0,
            'total_datasets_with_own_access' => 0,
            'total_file_size' => 0,
            'avg_file_size' => 0,
            'max_files_in_dataset' => 0,
            'avg_files_per_dataset' => 0,
        ];

        // DATASET STATISTICS
        // total numbers of datasets
        $initialStat['total_datasets'] = $total_datasets;
        
        // total datasets i can download
        $total_datasets_with_access = $datasets->filter(function($dataset){
            return $dataset->is_downloadable;
        });
        $initialStat['total_datasets_with_own_access'] = $total_datasets_with_access->count();
        
        // total size of files uploaded
        $fileVersion = (new FileVersion())->used_files_total_size()->first();
        if($fileVersion){
            $initialStat['total_file_size'] = $fileVersion->total_size;
        }
        
        // average size of files uploaded
        $fileVersion = (new FileVersion())->used_files_avg_size()->first();
        if($fileVersion){
            $initialStat['avg_file_size'] = $fileVersion->avg_size;
        }
        
        // maximum number of files per datasets
        $fileVersion = (new FileVersion())->used_files_total_per_dataset()->first();
        if($fileVersion){
            $initialStat['max_files_in_dataset'] = $fileVersion->total_per_dataset;
        }

        // average number of files per dataset
        $fileVersion = (new FileVersion())->used_files_avg_per_dataset()->first();
        if($fileVersion){
            $initialStat['avg_files_per_dataset'] = $fileVersion->avg_per_dataset;
        }

        // FORMATS
        // Count type of files uploaded
        $fileFormats = (new FileVersion())->used_files()->get()->sortByDesc('total');

        $labels = $fileFormats->pluck('file_format')->toArray();
        $data = $fileFormats->map(function($data){
            $baseUrl = rtrim(config('app.url'), '/');
            $endpoint = 'datasets';
            $queries = ['formats' => [$data['file_format']]];
            $queryString = http_build_query($queries);
            $url = "{$baseUrl}/{$endpoint}?{$queryString}";
            return ["total" => $data['total'], "url" => $url];
        })->toArray();
        $chartRepository->reset();
        $chartRepository->setLabels($labels);
        $chartRepository->addDataset(["data" => array_values($data)]);
        $fileFormats = $chartRepository->toJson();

        // Count organisations per owning organisation
        $organisationStat = (new Organisation())->used_organisations()->get()->sortByDesc('total');
        $labels = $organisationStat->pluck('name')->toArray();
        $data = $organisationStat->map(function($data){
            $baseUrl = rtrim(config('app.url'), '/');
            $endpoint = 'datasets';
            $queries = ['organisations' => [$data['id']]];
            $queryString = http_build_query($queries);
            $url = "{$baseUrl}/{$endpoint}?{$queryString}";
            return ["total" => $data['total'], "url" => $url];

        })->toArray();
        $chartRepository->reset();
        $chartRepository->setLabels($labels);
        $chartRepository->addDataset(["data" => array_values($data)]);


        $organisationStat = $chartRepository->toJson();

        // KEYWORDS
        // count by used keywords
        $keywordStat = (new Keyword)->used_keywords()->take(30)->get()->sortByDesc('total')->map(function($data){
            $baseUrl = rtrim(config('app.url'), '/');
            $endpoint = 'datasets';
            $queries = ['keywords' => [$data['id']]];
            $queryString = http_build_query($queries);
            $url = "{$baseUrl}/{$endpoint}?{$queryString}";
            return ["total" => $data['total'], "url" => $url, "name" => $data['name']];
        })->toArray();
        
        return view('home', compact('datasets', 'initialStat', 'fileFormats', 'keywordStat', 'organisationStat'));
    }
}
