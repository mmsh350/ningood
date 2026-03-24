@props(['title', 'value','icon' => 'bi-info-circle', 'bg' => 'primary','href'])

@if ($href == '#')
    @php $href = url('user/dashboard'); @endphp
@endif

<a href="{{ $href }}" style="text-decoration: none !important;">
<div class="card text-white bg-{{ $bg }} shadow-sm metric-card">

    <div class="card-body d-flex flex-column justify-content-between">
        <div class="d-flex align-items-center mb-2">
            <i class="bi {{ $icon }} metric-icon"></i>
            <h6 class="metric-title">{{ $title }}</h6>
        </div>
        <h3 class="metric-value">{{ $value }}</h3>
    </div>

</div>
</a>
