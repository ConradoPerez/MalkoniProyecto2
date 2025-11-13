@if ($paginator->hasPages())
    <div class="p-6 border-t border-gray-200 flex justify-between items-center">
        <div class="text-sm text-gray-600">
            Mostrando {{ $paginator->firstItem() }} al {{ $paginator->lastItem() }} de {{ $paginator->total() }} vendedores
        </div>
        <div class="flex gap-2">
            {{-- Botón Anterior --}}
            @if ($paginator->onFirstPage())
                <button disabled class="px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-400 cursor-not-allowed">
                    Anterior
                </button>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="px-3 py-2 border border-gray-300 rounded-lg text-sm hover:bg-gray-50 transition-colors">
                    Anterior
                </a>
            @endif

            {{-- Páginas numeradas --}}
            @php
                $currentPage = $paginator->currentPage();
                $lastPage = $paginator->lastPage();
                $showPages = [];
                
                if ($lastPage <= 7) {
                    // Si hay 7 páginas o menos, mostrar todas
                    for ($i = 1; $i <= $lastPage; $i++) {
                        $showPages[] = $i;
                    }
                } else {
                    // Lógica más inteligente para páginas largas
                    if ($currentPage <= 3) {
                        // Si estamos al inicio, mostrar 1-5 + últimas 2
                        for ($i = 1; $i <= 5; $i++) {
                            $showPages[] = $i;
                        }
                        $showPages[] = '...';
                        $showPages[] = $lastPage - 1;
                        $showPages[] = $lastPage;
                    } elseif ($currentPage >= $lastPage - 2) {
                        // Si estamos al final, mostrar primeras 2 + últimas 5
                        $showPages[] = 1;
                        $showPages[] = 2;
                        $showPages[] = '...';
                        for ($i = $lastPage - 4; $i <= $lastPage; $i++) {
                            $showPages[] = $i;
                        }
                    } else {
                        // Si estamos en el medio, mostrar página actual con contexto
                        $showPages[] = 1;
                        if ($currentPage > 3) {
                            $showPages[] = '...';
                        }
                        
                        // Mostrar 2 páginas antes, actual, y 2 después
                        for ($i = max(2, $currentPage - 2); $i <= min($lastPage - 1, $currentPage + 2); $i++) {
                            if (!in_array($i, $showPages)) {
                                $showPages[] = $i;
                            }
                        }
                        
                        if ($currentPage < $lastPage - 2) {
                            $showPages[] = '...';
                        }
                        $showPages[] = $lastPage;
                    }
                }
            @endphp

            @foreach ($showPages as $page)
                @if ($page === '...')
                    <span class="px-3 py-2 text-gray-500">...</span>
                @elseif ($page == $currentPage)
                    <button class="px-3 py-2 text-white rounded-lg text-sm" style="background-color: #D88429;">
                        {{ $page }}
                    </button>
                @else
                    <a href="{{ $paginator->url($page) }}" class="px-3 py-2 border border-gray-300 rounded-lg text-sm hover:bg-gray-50 transition-colors">
                        {{ $page }}
                    </a>
                @endif
            @endforeach

            {{-- Botón Siguiente --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="px-3 py-2 border border-gray-300 rounded-lg text-sm hover:bg-gray-50 transition-colors">
                    Siguiente
                </a>
            @else
                <button disabled class="px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-400 cursor-not-allowed">
                    Siguiente
                </button>
            @endif
        </div>
    </div>
@endif