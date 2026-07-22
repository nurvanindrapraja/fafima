<div wire:init="loadAdvice" class="mb-6 card-glass p-5 rounded-2xl border border-blue-500/30 bg-blue-900/10 relative overflow-hidden">
    <div class="flex items-start gap-4">
        <div class="flex-shrink-0 w-10 h-10 rounded-full bg-blue-500/20 flex items-center justify-center border border-blue-400/30">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
            </svg>
        </div>
        <div class="flex-1">
            <h3 class="text-sm font-bold text-white mb-1 flex items-center gap-2">
                Smart Advisor AI 
                <span class="px-2 py-0.5 rounded-full bg-blue-500/20 text-blue-300 text-[10px] font-medium tracking-wide uppercase">BETA</span>
            </h3>
            
            @if($isLoading)
                <div class="animate-pulse flex flex-col gap-2 mt-2">
                    <div class="h-3 bg-slate-700/50 rounded w-3/4"></div>
                    <div class="h-3 bg-slate-700/50 rounded w-1/2"></div>
                </div>
            @else
                <p class="text-sm text-slate-300 leading-relaxed mt-1">
                    {{ $isOwner ? $ownerAdvice : $memberAdvice }}
                </p>
            @endif
        </div>
    </div>
</div>
