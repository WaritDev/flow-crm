@extends('layouts.app')

@section('content')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <div x-data="{ showTargetModal: false }" class="max-w-7xl mx-auto p-4 md:p-6 space-y-6">

        @if(session('success'))
            <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show"
                    x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-8" x-transition:enter-end="opacity-100 transform translate-y-0"
                    x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                    class="fixed bottom-5 right-5 z-[100] max-w-sm w-full bg-white border border-slate-200 shadow-2xl rounded-2xl p-4 flex items-center gap-4 ring-1 ring-black/5">
                <div class="flex-shrink-0 bg-emerald-500 p-2 rounded-xl shadow-lg shadow-emerald-200">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                </div>
                <div class="flex-1">
                    <h4 class="text-sm font-bold text-slate-900">Success!</h4>
                    <p class="text-xs text-slate-500 mt-0.5">{{ session('success') }}</p>
                </div>
                <button @click="show = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-100 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
        @endif

        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Manager Dashboard</h1>
                <p class="text-slate-500">Overview of sales team performance</p>
            </div>

            <div class="flex items-center gap-3">
                <button @click="showTargetModal = true" class="bg-indigo-50 text-indigo-600 hover:bg-indigo-100 border border-indigo-100 px-4 py-2 rounded-xl text-sm font-semibold transition-colors flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                    Set Targets
                </button>

                <form method="GET" action="{{ url()->current() }}" class="flex items-center gap-2">
                    <div class="flex items-center bg-white border border-slate-200 rounded-xl p-1 shadow-sm focus-within:ring-2 focus-within:ring-indigo-500 transition-all">
                        <div class="pl-3 pr-2 text-indigo-500">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        </div>
                        
                        <select name="month" onchange="this.form.submit()" class="border-0 bg-transparent text-sm text-slate-700 font-bold focus:ring-0 cursor-pointer py-2 pl-1 pr-6 outline-none">
                            @foreach(['01'=>'January', '02'=>'February', '03'=>'March', '04'=>'April', '05'=>'May', '06'=>'June', '07'=>'July', '08'=>'August', '09'=>'September', '10'=>'October', '11'=>'November', '12'=>'December'] as $num => $name)
                                @if($reqYear == now()->year && (int)$num > now()->month)
                                    @continue
                                @endif
                                <option value="{{ $num }}" {{ $reqMonth == $num ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                        
                        <div class="w-px h-5 bg-slate-200 mx-1"></div>
                        
                        <select name="year" onchange="this.form.submit()" class="border-0 bg-transparent text-sm text-slate-700 font-bold focus:ring-0 cursor-pointer py-2 pl-2 pr-6 outline-none">
                            @foreach(range(now()->year - 3, now()->year) as $y)
                                <option value="{{ $y }}" {{ $reqYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-slate-900 text-white rounded-2xl p-5 shadow-lg relative overflow-hidden">
                <div class="absolute right-0 top-0 opacity-10 transform translate-x-2 -translate-y-2">
                    <svg class="w-24 h-24" fill="currentColor" viewBox="0 0 24 24"><path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
                <p class="text-slate-400 text-sm mb-1">Total Revenue</p>
                <h3 class="text-3xl font-bold">THB {{ number_format($stats['total_revenue']/1000000, 2) }}M</h3>
                <p class="{{ $stats['revenue_growth'] >= 0 ? 'text-emerald-400' : 'text-red-400' }} text-xs mt-2 flex items-center gap-1">
                    <svg class="w-3 h-3 {{ $stats['revenue_growth'] < 0 ? 'rotate-180' : '' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                    </svg>
                    {{ $stats['revenue_growth'] }}% from previous month
                </p>
            </div>

            <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
                <p class="text-slate-500 text-sm mb-1">Target Achievement</p>
                <div class="flex items-end gap-2">
                    <h3 class="text-3xl font-bold text-slate-800">{{ $stats['target_achievement'] }}%</h3>
                    <span class="text-slate-400 text-sm mb-1">of target</span>
                </div>
                <div class="w-full bg-slate-100 rounded-full h-1.5 mt-3">
                    <div class="bg-emerald-500 h-1.5 rounded-full" 
                        x-data="{ progress: '{{ $stats['target_achievement'] > 100 ? 100 : $stats['target_achievement'] }}%' }"
                        :style="{ width: progress }">
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
                <p class="text-slate-500 text-sm mb-1">Active Deals (team)</p>
                <h3 class="text-3xl font-bold text-slate-800">{{ $stats['active_deals'] }}</h3>
                <p class="text-xs text-slate-400 mt-2">Pending value THB {{ number_format($stats['active_deals_value']/1000000, 2) }}M</p>
            </div>

            <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
                <p class="text-slate-500 text-sm mb-1">Win Rate</p>
                <h3 class="text-3xl font-bold text-slate-800">{{ $stats['avg_conversion'] }}%</h3>
                <p class="{{ $stats['conversion_growth'] >= 0 ? 'text-emerald-500' : 'text-red-500' }} text-xs mt-2 flex items-center gap-1">
                    <svg class="w-3 h-3 {{ $stats['conversion_growth'] < 0 ? 'rotate-180 text-red-500' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path>
                    </svg>
                    {{ $stats['conversion_growth'] >= 0 ? 'Up' : 'Down' }} {{ abs($stats['conversion_growth']) }}% from previous month
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
            <div class="lg:col-span-2 flex flex-col gap-6">
                
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="font-bold text-slate-800">Team Performance</h2>
                    </div>
                    <div class="relative h-[380px] w-full">
                        <canvas id="teamChart"></canvas>
                    </div>
                </div>

                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
                    <h2 class="font-bold text-slate-800 mb-6">Pipeline Health</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4">
                        @foreach($pipelineSummary as $stage)
                            @php
                                $percent = $stage['max_value'] > 0 ? ($stage['value'] / $stage['max_value']) * 100 : 0;
                            @endphp
                            <div>
                                <div class="flex justify-between text-sm mb-1.5">
                                    <span class="text-slate-700 font-medium">{{ $stage['stage'] }}</span>
                                    <span class="text-slate-500 text-xs">{{ $stage['count'] }} deals (THB {{ number_format($stage['value']/1000) }}k)</span>
                                </div>
                                <div class="w-full bg-slate-100 rounded-full h-2">
                                    <div class="bg-indigo-500 h-2 rounded-full" style="width: <?php echo $percent; ?>%;"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 relative overflow-hidden">
                    <div class="absolute right-0 top-0 w-24 h-24 bg-gradient-to-br from-indigo-50 to-white rounded-bl-full z-0 opacity-60"></div>
                    <div class="relative z-10">
                        <h2 class="font-bold text-slate-800 mb-1 flex items-center gap-2">
                            <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                            Quick Invite
                        </h2>
                        <p class="text-xs text-slate-500 mb-4">Send invitation link to sales to join your team</p>
                        
                        <form action="{{ route('invitations.store') }}" method="POST" class="space-y-3">
                            @csrf
                            <div>
                                <input type="email" name="email" required 
                                        class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all placeholder-slate-400" 
                                        placeholder="sales@example.com">
                            </div>

                            <button type="submit" class="w-full flex justify-center items-center gap-2 bg-slate-900 text-white text-sm font-medium py-2 rounded-lg hover:bg-slate-800 transition-colors shadow-sm active:scale-95">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                                Send Invite Link
                            </button>
                        </form>

                        <div class="mt-5 pt-4 border-t border-slate-100">
                            <p class="text-[10px] text-slate-400 uppercase font-bold tracking-wider mb-2">Or Share Organization Code</p>
                            
                            <div class="flex items-center gap-2" 
                                x-data="{ 
                                    copied: false, 
                                    code: '{{ auth()->user()->organization->invite_code ?? 'No Code Found' }}' 
                                }">
                                <code class="flex-1 block px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm text-slate-700 font-mono text-center tracking-wide" x-text="code"></code>
                                
                                <button type="button" 
                                        @click="navigator.clipboard.writeText(code); copied = true; setTimeout(() => copied = false, 2000)"
                                        class="p-2 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors focus:outline-none"
                                        :title="copied ? 'Copied!' : 'Copy to clipboard'">
                                    <svg x-show="!copied" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                                    <svg x-show="copied" x-cloak class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
                    <h2 class="font-bold text-slate-800 mb-4 flex items-center gap-2">
                        <span class="text-xl">🏆</span> Top Sales
                    </h2>
                    <div class="space-y-4">
                        @foreach($topPerformers as $index => $sale)
                            <div class="flex items-center justify-between p-3 rounded-xl {{ $index == 0 ? 'bg-amber-50 border border-amber-100' : 'bg-slate-50' }}">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold {{ $index == 0 ? 'bg-amber-100 text-amber-600' : 'bg-white text-slate-600' }}">
                                        {{ $sale['avatar'] }}
                                    </div>
                                    <div>
                                        <p class="font-bold text-slate-800 text-sm">{{ $sale['name'] }}</p>
                                        <p class="text-xs text-slate-500">{{ $index + 1 }}st Place</p>
                                    </div>
                                </div>
                                <span class="font-bold {{ $index == 0 ? 'text-amber-600' : 'text-slate-700' }}">
                            THB {{ number_format($sale['amount']/1000) }}K
                        </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

            <div x-show="showTargetModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center">
                <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm" @click="showTargetModal = false"
                    x-show="showTargetModal"
                    x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"></div>
                
                <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full p-6 relative z-10 mx-4 max-h-[90vh] overflow-y-auto"
                    x-show="showTargetModal"
                    x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                    
                    <div class="flex justify-between items-center mb-5">
                        <div>
                            <h2 class="text-xl font-bold text-slate-900">Set Monthly Targets</h2>
                            <p class="text-sm text-slate-500">For month: {{ DateTime::createFromFormat('!m', $reqMonth)->format('F') }} {{ $reqYear }}</p>
                        </div>
                        <button @click="showTargetModal = false" class="text-slate-400 hover:bg-slate-100 p-2 rounded-lg transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <form action="{{ route('targets.update') }}" method="POST">
                        @csrf
                        <input type="hidden" name="month" value="{{ $reqMonth }}">
                        <input type="hidden" name="year" value="{{ $reqYear }}">
                        <div class="mb-6 p-4 bg-slate-50 rounded-xl border border-slate-200">
                            <label class="block text-sm font-bold text-slate-700 mb-1">Organization target (THB)</label>
                            <p class="text-xs text-slate-500 mb-2">Total company sales target</p>
                            
                            <div x-data="{
                                raw: '{{ $companyTargetAmount ?: '' }}',
                                formatted: '{{ $companyTargetAmount ? number_format($companyTargetAmount) : '' }}',
                                formatInput() {
                                    let cleaned = this.formatted.replace(/\D/g, '');
                                    this.raw = cleaned;
                                    this.formatted = cleaned ? new Intl.NumberFormat('en-US').format(cleaned) : '';
                                }
                            }">
                                <input type="text" x-model="formatted" @input="formatInput" placeholder="e.g. 5,000,000" class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none font-medium">
                                <input type="hidden" name="org_target" :value="raw">
                            </div>
                        </div>

                        <h3 class="font-bold text-slate-800 mb-3 border-b border-slate-100 pb-2">Sales Rep Targets</h3>
                        <div class="space-y-3">
                            @foreach($allSales as $sale)
                                @php
                                    $userTarget = $sale->targets->first()->amount ?? '';
                                @endphp
                                <div class="flex items-center justify-between gap-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 bg-slate-100 text-slate-600 rounded-full flex items-center justify-center font-bold text-xs">
                                            {{ substr($sale->name, 0, 1) }}
                                        </div>
                                        <span class="text-sm font-medium text-slate-700">{{ $sale->name }}</span>
                                    </div>
                                    
                                    <div x-data="{
                                        raw: '{{ $userTarget }}',
                                        formatted: '{{ $userTarget ? number_format($userTarget) : '' }}',
                                        formatInput() {
                                            let cleaned = this.formatted.replace(/\D/g, '');
                                            this.raw = cleaned;
                                            this.formatted = cleaned ? new Intl.NumberFormat('en-US').format(cleaned) : '';
                                        }
                                    }">
                                        <input type="text" x-model="formatted" @input="formatInput" placeholder="0" class="w-32 px-3 py-1.5 border border-slate-300 rounded-lg text-sm text-right focus:ring-2 focus:ring-indigo-500 outline-none font-medium">
                                        <input type="hidden" name="user_targets[{{ $sale->id }}]" :value="raw">
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-8 flex gap-3 justify-end">
                            <button type="button" @click="showTargetModal = false" class="px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100 rounded-lg transition-colors">Cancel</button>
                            <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg shadow-sm transition-colors">Save Targets</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        const ctx = document.getElementById('teamChart').getContext('2d');
        const teamLabels = JSON.parse('{!! json_encode($teamPerformance["labels"]) !!}');
        const teamData = JSON.parse('{!! json_encode($teamPerformance["data"]) !!}');
        const teamTargets = JSON.parse('{!! json_encode($teamPerformance["targets"]) !!}');

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: teamLabels,
                datasets: [
                    {
                        label: 'Actual Sales',
                        data: teamData,
                        backgroundColor: '#10b981',
                        borderRadius: 6,
                        barPercentage: 0.6
                    },
                    {
                        label: 'Target',
                        data: teamTargets,
                        backgroundColor: '#e2e8f0',
                        borderRadius: 6,
                        barPercentage: 0.6,
                        hidden: false
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' },
                    tooltip: {
                        backgroundColor: '#1e293b',
                        padding: 10,
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': THB ' + context.parsed.y.toLocaleString();
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { borderDash: [2, 2], color: '#f1f5f9' },
                        ticks: { callback: value => 'THB ' + (value/1000) + 'k' }
                    },
                    x: { grid: { display: false } }
                }
            }
        });
    </script>
@endsection