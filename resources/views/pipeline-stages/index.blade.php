@extends('layouts.app')

@section('content')
    <div class="min-h-screen bg-white" x-data="pipelineBoard()">

        <div class="px-6 py-6 border-b border-slate-100 flex items-center justify-between bg-white sticky top-0 z-20">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Sales Pipeline</h1>
                <p class="text-slate-500 mt-1">
                    มูลค่ารวม <span
                        class="font-bold text-slate-800">฿{{ number_format(collect($deals)->sum('value')) }}</span>
                </p>
            </div>

            <div class="flex items-center gap-3">
                @can('create', App\Models\PipelineStage::class)
                    <button onclick="window.location='{{ route('pipeline-stages.create') }}'"
                        class="px-4 py-2.5 rounded-lg border border-slate-200 text-slate-600 font-medium hover:bg-slate-50 hover:border-slate-300 transition-all flex items-center gap-2">
                        <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        เพิ่ม Stage
                    </button>
                @endcan

                <button onclick="window.location='{{ route('deals.create') }}'"
                    class="bg-slate-900 text-white px-5 py-2.5 rounded-lg hover:bg-slate-800 flex items-center gap-2 shadow-lg shadow-slate-900/20 transition-all font-medium">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    เพิ่มดีลใหม่
                </button>
            </div>
        </div>

        <div class="p-6 overflow-x-auto">
            <div class="flex gap-6 min-w-max pb-10">
                @foreach($stages as $stage)
                    <x-pipeline.column :stage="$stage" :deals="$deals" />
                @endforeach
            </div>
        </div>

        <div x-show="showToast" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="translate-y-2 opacity-0" x-transition:enter-end="translate-y-0 opacity-100"
            x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed bottom-6 left-1/2 transform -translate-x-1/2 bg-slate-800 text-white px-6 py-3 rounded-lg shadow-xl flex items-center gap-3 z-50"
            style="display: none;">
            <svg class="w-5 h-5 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 8v4m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <span x-text="toastMessage"></span>
        </div>

    </div>

    <script>
        function pipelineBoard() {
            return {
                draggedItem: null,
                sourceStageIndex: null,
                showToast: false,
                toastMessage: '',

                startDrag(event, deal) {
                    this.draggedItem = deal;
                    const stages = @json($stages);
                    const currentStage = stages.find(s => s.id === deal.stage_id);
                    this.sourceStageIndex = currentStage ? currentStage.position : 0;
                    event.dataTransfer.effectAllowed = 'move';
                    event.target.style.opacity = '0.5';
                },

                dragOver(event, targetIndex) {
                    if (targetIndex >= this.sourceStageIndex) { }
                },

                isInvalidDrop(targetIndex) {
                    if (this.draggedItem === null) return false;
                    return targetIndex < this.sourceStageIndex;
                },

                drop(event, targetStageId, targetIndex) {
                    event.target.style.opacity = '1';
                    if (targetIndex < this.sourceStageIndex) {
                        this.triggerToast('ห้ามย้อนสถานะการขาย เพื่อรักษาความถูกต้องของ Process');
                        return;
                    }
                    if (targetIndex === this.sourceStageIndex) {
                        return;
                    }
                    console.log(`Moving Deal ${this.draggedItem.id} to ${targetStageId}`);
                    window.location.reload();
                },

                triggerToast(message) {
                    this.toastMessage = message;
                    this.showToast = true;
                    setTimeout(() => this.showToast = false, 3000);
                }
            }
        }
    </script>
@endsection