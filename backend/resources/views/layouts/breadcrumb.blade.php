<!-- [ breadcrumb ] start -->
<div class="page-header">
  <div class="page-block">
    <div class="row align-items-center">
      <div class="col-md-12">
        <div class="page-header-title">
          <h5 class="m-b-10">@yield('page-title', 'Halaman')</h5>
        </div>
        <ul class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Home</a></li>
          @hasSection('breadcrumb')
            @yield('breadcrumb')
          @endif
          <li class="breadcrumb-item active" aria-current="page">@yield('page-title')</li>
        </ul>
      </div>
    </div>
  </div>
</div>

<!-- [ breadcrumb ] end -->