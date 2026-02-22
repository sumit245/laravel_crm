@extends('docs.layout')

@section('content')
    <div class="breadcrumb">
        <a href="{{ route('docs.code.index') }}">Home</a>
        <span>›</span>
        <a href="{{ route('docs.code.show', [$category]) }}">{{ $categoryLabel }}</a>
        <span>›</span>
        <span>{{ str_replace('.', ' / ', $file) }}</span>
    </div>

    {{-- File Header --}}
    <div class="page-title">{{ $fileData['className'] ?? str_replace('.', ' / ', $file) }}</div>
    <div class="meta-row">
        <div class="meta-item"><strong>File:</strong> {{ $fileData['filePath'] }}</div>
        <div class="meta-item"><strong>Size:</strong> {{ number_format($fileData['fileSize']) }} bytes</div>
        <div class="meta-item"><strong>Modified:</strong> {{ $fileData['lastModified'] }}</div>
    </div>

    @if($fileData['namespace'])
        <div class="meta-row">
            <div class="meta-item"><strong>Namespace:</strong> <code>{{ $fileData['namespace'] }}</code></div>
            @if($fileData['parentClass'])
                <div class="meta-item"><strong>Extends:</strong> <code>{{ class_basename($fileData['parentClass']) }}</code></div>
            @endif
            @if(isset($fileData['classType']))
                <div class="meta-item"><strong>Type:</strong> <span class="badge badge-type">{{ $fileData['classType'] }}</span>
                </div>
            @endif
        </div>
    @endif

    @if(!empty($fileData['interfaces']))
        <div class="meta-row">
            <div class="meta-item"><strong>Implements:</strong>
                @foreach($fileData['interfaces'] as $iface)
                    <code>{{ class_basename($iface) }}</code>@if(!$loop->last), @endif
                @endforeach
            </div>
        </div>
    @endif

    @if(!empty($fileData['traits']))
        <div class="meta-row">
            <div class="meta-item"><strong>Uses Traits:</strong>
                @foreach($fileData['traits'] as $trait)
                    <code>{{ class_basename($trait) }}</code>@if(!$loop->last), @endif
                @endforeach
            </div>
        </div>
    @endif

    {{-- Business Context (from config/docs_business_context.php) --}}
    @if(!empty($fileData['businessContext']))
        @php $bctx = $fileData['businessContext']; @endphp
        <div class="card"
            style="margin-top: 16px; border-left: 4px solid #10b981; background: linear-gradient(135deg, rgba(16,185,129,0.08) 0%, rgba(59,130,246,0.05) 100%);">
            {{-- Domain badge --}}
            @if(!empty($bctx['business_domain']))
                <div style="margin-bottom: 12px;">
                    <span
                        style="background: linear-gradient(135deg, #10b981, #059669); color: #fff; padding: 4px 12px; border-radius: 12px; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
                        {{ $bctx['business_domain'] }}
                    </span>
                </div>
            @endif

            {{-- Business Summary --}}
            @if(!empty($bctx['business_summary']))
                <div style="margin-bottom: 16px;">
                    <div style="font-weight: 700; font-size: 15px; margin-bottom: 6px; color: #10b981;">🏢 Business Purpose</div>
                    <div style="color: var(--text-primary); font-size: 14px; line-height: 1.7;">
                        {{ $bctx['business_summary'] }}
                    </div>
                </div>
            @endif

            {{-- Data Flow --}}
            @if(!empty($bctx['data_flow']))
                <div style="margin-bottom: 16px; background: rgba(0,0,0,0.15); border-radius: 8px; padding: 12px 16px;">
                    <div style="font-weight: 700; font-size: 14px; margin-bottom: 8px; color: #3b82f6;">🔄 Data Flow</div>
                    <div
                        style="font-family: 'SF Mono', 'Fira Code', monospace; font-size: 13px; color: var(--text-primary); line-height: 1.8; word-break: break-word;">
                        @foreach(explode('→', $bctx['data_flow']) as $step)
                            @if(!$loop->first)
                                <span style="color: #f59e0b; font-weight: bold; margin: 0 4px;">→</span>
                            @endif
                            <span
                                style="background: rgba(59,130,246,0.15); padding: 2px 8px; border-radius: 4px; white-space: nowrap;">{{ trim($step) }}</span>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Dependencies --}}
            @if(!empty($bctx['depends_on']))
                <div>
                    <div style="font-weight: 700; font-size: 14px; margin-bottom: 8px; color: #8b5cf6;">🔗 Key Dependencies</div>
                    <div style="display: flex; flex-wrap: wrap; gap: 6px;">
                        @foreach($bctx['depends_on'] as $dep)
                            <span
                                style="background: rgba(139,92,246,0.15); color: #a78bfa; padding: 3px 10px; border-radius: 6px; font-size: 12px; font-family: monospace;">{{ $dep }}</span>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    @endif

    {{-- Class Description --}}
    @if(!empty($fileData['classDocblock']['summary']))
        <div class="card" style="margin-top: 16px; border-left: 3px solid var(--accent);">
            <div style="font-weight:600; margin-bottom:6px;">📝 Technical Description</div>
            <div style="color:var(--text-secondary); font-size:14px;">
                {{ $fileData['classDocblock']['summary'] }}
                @if(!empty($fileData['classDocblock']['description']))
                    <br><br>{{ $fileData['classDocblock']['description'] }}
                @endif
            </div>
        </div>
    @endif

    {{-- Constants --}}
    @if(!empty($fileData['constants']))
        <div class="card" style="margin-top: 24px;">
            <h2 class="section-header">🏷️ Constants</h2>
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Visibility</th>
                        <th>Name</th>
                        <th>Value</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($fileData['constants'] as $const)
                        <tr>
                            <td><span class="badge badge-{{ $const['visibility'] }}">{{ $const['visibility'] }}</span></td>
                            <td><code>{{ $const['name'] }}</code></td>
                            <td><code>{{ $const['value'] }}</code></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    {{-- Properties --}}
    @if(!empty($fileData['properties']))
        <div class="card" style="margin-top: 24px;">
            <h2 class="section-header">📌 Properties</h2>
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Visibility</th>
                        <th>Type</th>
                        <th>Name</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($fileData['properties'] as $prop)
                        <tr>
                            <td>
                                <span class="badge badge-{{ $prop['visibility'] }}">{{ $prop['visibility'] }}</span>
                                @if($prop['isStatic']) <span class="badge badge-static">static</span> @endif
                            </td>
                            <td><code>{{ $prop['type'] }}</code></td>
                            <td><code>{{ $prop['name'] }}</code></td>
                            <td style="color:var(--text-secondary); font-size:12px;">{{ $prop['docblock']['summary'] ?? '' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    {{-- Method Quick Index --}}
    @if(!empty($fileData['methods']))
        <div class="card" style="margin-top: 24px;">
            <h2 class="section-header">📇 Method Index</h2>
            <div style="columns: 2; column-gap: 24px; font-size: 13px;">
                @foreach($fileData['methods'] as $method)
                    <div style="break-inside: avoid; margin-bottom: 4px;">
                        <span class="badge badge-{{ $method['visibility'] }}"
                            style="font-size:9px; min-width:58px; text-align:center; display:inline-block;">{{ $method['visibility'] }}</span>
                        <a href="#method-{{ $method['name'] }}"
                            style="color:var(--accent); text-decoration:none;">{{ $method['name'] }}()</a>
                        @if($method['isStatic']) <span class="badge badge-static" style="font-size:9px;">static</span> @endif
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Method Details --}}
        <div style="margin-top: 32px;">
            <h2 class="section-header">⚙️ Methods ({{ count($fileData['methods']) }})</h2>

            @foreach($fileData['methods'] as $method)
                <div class="method-card" id="method-{{ $method['name'] }}">
                    <div class="method-header">
                        <span class="badge badge-{{ $method['visibility'] }}">{{ $method['visibility'] }}</span>
                        @if($method['isStatic']) <span class="badge badge-static">static</span> @endif
                        @if($method['isAbstract']) <span class="badge badge-protected">abstract</span> @endif
                        <span class="method-name">{{ $method['name'] }}()</span>
                        <span class="return-type" style="margin-left:auto;">→ {{ $method['returnType'] }}</span>
                    </div>
                    <div class="method-body">
                        @if($method['summary'])
                            <div class="method-desc">
                                <strong>{{ $method['summary'] }}</strong>
                                @if($method['description'])
                                    <br>{{ $method['description'] }}
                                @endif
                            </div>
                        @endif

                        @if(!empty($method['parameters']))
                            <table class="doc-table param-table">
                                <thead>
                                    <tr>
                                        <th>Parameter</th>
                                        <th>Type</th>
                                        <th>Default</th>
                                        <th>Description</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($method['parameters'] as $param)
                                        <tr>
                                            <td><code>{{ $param['name'] }}</code></td>
                                            <td><code>{{ $param['type'] }}{{ $param['isNullable'] && !str_contains($param['type'], '?') ? '|null' : '' }}</code>
                                            </td>
                                            <td>
                                                @if($param['hasDefault'])
                                                    <code>{{ $param['default'] }}</code>
                                                @else
                                                    <span style="color:var(--text-muted)">—</span>
                                                @endif
                                            </td>
                                            <td style="color:var(--text-secondary);">{{ $param['description'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif

                        <div style="margin-top:8px; font-size:12px; color:var(--text-muted);">
                            Lines {{ $method['startLine'] }}–{{ $method['endLine'] }}
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Source Code --}}
    <div class="source-section" style="margin-top: 32px;">
        <div class="card">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                <h2 class="section-header" style="margin-bottom:0; border:none; padding:0;">💻 Source Code</h2>
                <button onclick="toggleSource()" class="theme-toggle" style="font-size:12px;" id="sourceToggleBtn">Show
                    Source</button>
            </div>
            <div id="sourceBlock" style="display:none;">
                <pre class="line-numbers"><code class="language-php">{{ $fileData['sourceCode'] }}</code></pre>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function toggleSource() {
                const block = document.getElementById('sourceBlock');
                const btn = document.getElementById('sourceToggleBtn');
                if (block.style.display === 'none') {
                    block.style.display = 'block';
                    btn.textContent = 'Hide Source';
                    Prism.highlightAll();
                } else {
                    block.style.display = 'none';
                    btn.textContent = 'Show Source';
                }
            }
        </script>
    @endpush
@endsection