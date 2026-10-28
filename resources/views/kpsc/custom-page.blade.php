@extends('layouts.kpsc-app')
@extends('kpsc.section')
@section('title', $title)
@section('content')
<div class="page-content-wrapper"><div class="container py-3" style="max-width:860px">
    <h2 class="fw-bold mb-3">{{ $page->title }}</h2>
    <div class="art-body" style="line-height:1.7;overflow-wrap:anywhere">{!! $page->bodyHtml() !!}</div>
    <style>.art-body img{max-width:100%;height:auto;border-radius:10px}.art-body table{max-width:100%;display:block;overflow-x:auto}.art-body iframe{max-width:100%}</style>
</div></div>
@endsection
