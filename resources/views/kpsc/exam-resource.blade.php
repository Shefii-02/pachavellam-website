{{-- Syllabus / study materials / results of an exam type, in folders: /{exam}/syllabus, /{exam}/materials, /{exam}/results --}}
@extends('layouts.kpsc-app')
@extends('kpsc.section')
@section('title', $title)
@section('styles')
@include('kpsc.partials.ca-style')
<style>
    .ca-tabs { display: flex; gap: 8px; overflow-x: auto; scrollbar-width: none; margin: 16px 0 0; } .ca-tabs::-webkit-scrollbar { display: none; }
    .ca-tabs a { flex: none; border: 1px solid var(--pv-border, #e5e7eb); background: var(--pv-surface, #fff); color: var(--pv-text, #16202e) !important; border-radius: 20px; padding: 6px 15px; font-size: .84rem; font-weight: 700; text-decoration: none !important; }
    .ca-tabs a.on { background: var(--pv-text, #16202e); color: var(--pv-surface, #fff) !important; border-color: var(--pv-text, #16202e); }
    .ca-ct { display: block; font-size: .72rem; font-weight: 600; color: var(--pv-muted, #667); }
</style>
@endsection
@section('content')
@php
    $name = $exam->short_name ?: $exam->name;
    $base = url($exam->slug . '/' . $resource);
    $crumbs = [$name => url($exam->slug)];
    if ($current) {
        $crumbs[$label] = $base;
        foreach (array_slice($trail, 0, -1) as $t) { $crumbs[$t->title] = $base . '?folder=' . $t->id; }
    }
    $heading = $current ? $current->title : $label . ' - ' . $name;
    $msg = ['syllabus' => 'Syllabus of ' . $name . ' exams', 'materials' => 'Study materials for ' . $name . ' exams', 'results' => 'Results of ' . $name . ' exams'][$resource];
    $tabs = ['syllabus' => 'Syllabus', 'materials' => 'Study materials', 'results' => 'Results'];
@endphp
<div class="page-content-wrapper ca-page">
    <div class="container">
        @include('kpsc.partials.ca-hero', ['heading' => $heading, 'msg' => $msg, 'crumbs' => $crumbs])

        <div class="ca-tabs">@foreach ($tabs as $k => $l)<a class="{{ $k === $resource ? 'on' : '' }}" href="{{ url($exam->slug . '/' . $k) }}">{{ $l }}</a>@endforeach</div>

        @if ($folders->count())
            <h2 class="ca-sec">{{ $current ? 'Folders' : 'Choose a folder' }}</h2>
            <div class="ca-grid">
                @foreach ($folders as $key => $f)
                    <div class="ca-cell">
                        <a href="{{ $base . '?folder=' . $f->id }}" class="ca-tile">
                            <span class="ca-ic"><i class="fa fa-folder-open-o"></i></span>
                            <span class="ca-tl">{{ $f->title }}</span>
                            <span class="ca-ct">{{ $f->items_count }} file{{ $f->items_count == 1 ? '' : 's' }}</span>
                        </a>
                    </div>
                    {!! ads_space_sport($key) !!}
                @endforeach
            </div>
        @endif

        @if ($items->count())
            @if ($folders->count())<h2 class="ca-sec">Files</h2>@endif
            <div class="ca-list" id="caItems">
                @foreach ($items as $m)
                    <div class="ca-row">
                        <span class="ca-ic"><i class="fa {{ $m->isFile ? 'fa-file-pdf-o' : 'fa-external-link' }}"></i></span>
                        <div class="t">
                            {{ $m->title }}
                            @if ($m->published_at ?? $m->created_at)<div class="small text-muted fw-normal"><i class="fa fa-calendar"></i> {{ ($m->published_at ?? $m->created_at)->format('d M Y') }}</div>@endif
                        </div>
                        @if ($m->open)
                            <a class="ca-go" target="_blank" rel="noopener" href="{{ $m->open }}" @if ($m->isFile) download="{{ $m->title }}" @endif>@if ($m->isFile)<i class="fa fa-download"></i> Download @else Open @endif</a>
                        @endif
                    </div>
                @endforeach
            </div>
            @include('kpsc.partials.load-more', ['paginator' => $items, 'target' => '#caItems'])
        @elseif (!$folders->count())
            <div class="ca-empty">Nothing added here yet. It will appear as soon as it is published.</div>
        @endif
    </div>
</div>
@endsection
