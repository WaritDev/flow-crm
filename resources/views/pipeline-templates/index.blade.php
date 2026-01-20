@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto py-10 px-4 sm:px-6 lg:px-8">

        <div class="mb-8">
            <h1 class="text-3xl font-bold text-slate-900">Workflow Templates</h1>
            <p class="text-slate-500 mt-2 text-lg">เลือก Template ตามอุตสาหกรรมของคุณ พร้อม Pipeline และ Script ภาษาไทย</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($templates as $template)
                <x-pipeline-template.card :template="$template" />
            @endforeach
        </div>

    </div>
@endsection
