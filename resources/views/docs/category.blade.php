@extends('docs.layout')

@section('content')
    <div class="breadcrumb">
        <a href="{{ route('docs.code.index') }}">Home</a>
        <span>›</span>
        <span>{{ $categoryLabel }}</span>
    </div>

    <div class="page-title">{{ $categoryLabel }}</div>
    <div class="page-subtitle">{{ count($fileSummaries) }} files in <code>app/{{ $matchedKey }}</code></div>

    <div class="file-grid">
        @foreach($fileSummaries as $fs)
            <a href="{{ route('docs.code.show', [$category, $fs['name']]) }}" class="file-card">
                <div class="file-icon">
                    @if($fs['classType'] === 'interface') 📋
                    @elseif($fs['classType'] === 'trait') 🧬
                    @elseif($fs['classType'] === 'enum') 🏷️
                    @else 📄
                    @endif
                </div>
                <div class="file-info">
                    <div class="file-name">{{ str_replace('.', ' / ', $fs['name']) }}</div>
                    <div class="file-meta">
                        <span class="badge badge-type">{{ $fs['classType'] }}</span>
                        @if(!empty($fs['businessContext']['business_domain']))
                            <span
                                style="background: linear-gradient(135deg, #10b981, #059669); color: #fff; padding: 2px 8px; border-radius: 8px; font-size: 10px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.3px; margin-left: 4px;">{{ $fs['businessContext']['business_domain'] }}</span>
                        @endif
                        · {{ $fs['methodCount'] }} methods · {{ $fs['size'] }}
                    </div>
                    @if($fs['classSummary'])
                        <div class="file-summary">{{ Str::limit($fs['classSummary'], 200) }}</div>
                    @endif
                </div>
            </a>
        @endforeach
    </div>
@endsection