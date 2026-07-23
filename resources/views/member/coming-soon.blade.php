@extends('member.layouts.app')

@section('title', $pageTitle)

@section('content')
  <div class="breadcrumb"><a href="{{ route('member.dashboard') }}">داشبورد</a> ‹ <span>{{ $pageTitle }}</span></div>
  <div class="page-head"><h1>{{ $pageTitle }}</h1><p>{{ $pageDescription }}</p></div>

  <div class="card">
    <div class="soon-wrap">
      <span class="ic" data-icon="clock"></span>
      <div class="soon-pill">به‌زودی</div>
      <h2>این بخش در حال آماده‌سازی است</h2>
      <p>«{{ $pageTitle }}» در فازهای بعدی باشگاه اعضای صبح ساحل فعال می‌شود. از شکیبایی شما سپاسگزاریم.</p>
      <a class="btn btn-plum" href="{{ route('member.dashboard') }}">بازگشت به داشبورد</a>
    </div>
  </div>
@endsection
