@extends('layouts.app')

@section('title', __('About'))

@section('carosel')
    @include('sites.carosel')
@endsection

@section('content')
    <h1 class="text-left mb-4">B-GOOD Bee Health Data Portal</h1>
    <p>
        This data portal website is used to manage B-GOOD datasets. Honeybee colony health data is the main subject. The main purpose of the portal is to store raw and pre-processed data generated in the B-GOOD project. This data is used for research purposes by B-GOOD partners. The project policy ‘publication and data sharing’ guides which data is accessible to whom. Datasets can be uploaded and retrieved depending on the access rights. Access can also be requested. A dataset can consist of multiple files and metadata is used to describe the datasets. A large variation of file types is supported. Where and when possible datasets are shared openly. An account is required to be able to use the portal.
    </p>
    <h1 class="text-left mb-4">About B-GOOD</h1>
    <p>
        B-GOOD will pave the way towards healthy and sustainable beekeeping within the European Union by following a collaborative and interdisciplinary approach. Merging data from within and around beehives as well as wider socioeconomic conditions, B-GOOD will develop and test innovative tools to perform risk assessments according to the Health Status Index (HSI).
        B-GOOD has the overall goal to provide guidance for beekeepers and help them make better and more informed decisions.
    </p>
    <p>
        B-GOOD website: <a href="https://b-good-project.eu/">https://b-good-project.eu/</a><br/>
        B-GOOD’s scientific publications: <a href="https://b-good-project.eu/documents">https://b-good-project.eu/documents</a><br/>
    </p>
    <p>
        This project receives funding from the European Union’s Horizon 2020 research and innovation programme under grant agreement No 817622.
    </p>
@endsection
