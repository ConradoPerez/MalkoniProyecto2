@if ($paginator->hasPages())
    <div class="p-4 sm:p-6 border-t border-gray-200">
        <!-- Info text - hidden on mobile, shown on larger screens -->
        <div class="hidden sm:block text-sm text-gray-600 mb-4 sm:mb-0">
            Mostrando {{ $paginator->firstItem() }} al {{ $paginator->lastItem() }} de {{ $paginator->total() }} {{ $paginator->total() == 1 ? 'registro' : 'registros' }}
        </div>
        
        <!-- Mobile-first pagination -->
        <div class="flex justify-center sm:justify-end">
            <div class="flex gap-1 sm:gap-2 overflow-x-auto pb-2 sm:pb-0">
                {{-- Botón Anterior --}}
                @if ($paginator->onFirstPage())
                    <button disabled class="px-2 sm:px-3 py-2 border border-gray-300 rounded-lg text-xs sm:text-sm text-gray-400 cursor-not-allowed whitespace-nowrap">
                        <span class="sm:hidden">‹</span>
                        <span class="hidden sm:inline">Anterior</span>
                    </button>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" class="px-2 sm:px-3 py-2 border border-gray-300 rounded-lg text-xs sm:text-sm hover:bg-gray-50 transition-colors whitespace-nowrap">
                        <span class="sm:hidden">‹</span>
                        <span class="hidden sm:inline">Anterior</span>
                    </a>
                @endif

                {{-- Páginas numeradas con lógica responsive --}}
                @php
                    $currentPage = $paginator->currentPage();
                    $lastPage = $paginator->lastPage();
                    
                    // Crear lógica simplificada para responsive
                    $mobilePages = [];
                    $desktopPages = [];
                    
                    // Para móvil: máximo 5 elementos (incluyendo ... y botones)
                    if ($lastPage <= 5) {
                        for ($i = 1; $i <= $lastPage; $i++) {
                            $mobilePages[] = $i;
                        }
                    } else {
                        // Móvil: página actual + 1 a cada lado + primera/última
                        if ($currentPage <= 2) {
                            $mobilePages = [1, 2, 3, '...', $lastPage];
                        } elseif ($currentPage >= $lastPage - 1) {
                            $mobilePages = [1, '...', $lastPage - 2, $lastPage - 1, $lastPage];
                        } else {
                            $mobilePages = [1, '...', $currentPage, '...', $lastPage];
                        }
                    }
                    
                    // Para desktop: lógica más completa
                    if ($lastPage <= 7) {
                        for ($i = 1; $i <= $lastPage; $i++) {
                            $desktopPages[] = $i;
                        }
                    } else {
                        if ($currentPage <= 3) {
                            $desktopPages = array_merge(range(1, 5), ['...', $lastPage]);
                        } elseif ($currentPage >= $lastPage - 2) {
                            $desktopPages = array_merge([1, '...'], range($lastPage - 4, $lastPage));
                        } else {
                            $desktopPages = array_merge([1, '...'], range($currentPage - 1, $currentPage + 1), ['...', $lastPage]);
                        }
                    }
                @endphp

                {{-- Páginas para móvil --}}
                <div class="flex gap-1 sm:hidden">
                    @foreach ($mobilePages as $page)
                        @if ($page === '...')
                            <span class="px-2 py-2 text-gray-500 text-xs">...</span>
                        @elseif ($page == $currentPage)
                            <button class="px-2 py-2 text-white rounded-lg text-xs font-medium min-w-7" style="background-color: #D88429;">
                                {{ $page }}
                            </button>
                        @else
                            <a href="{{ $paginator->url($page) }}" class="px-2 py-2 border border-gray-300 rounded-lg text-xs hover:bg-gray-50 transition-colors min-w-7 text-center">
                                {{ $page }}
                            </a>
                        @endif
                    @endforeach
                </div>
                
                {{-- Páginas para desktop --}}
                <div class="hidden sm:flex gap-2">
                    @foreach ($desktopPages as $page)
                        @if ($page === '...')
                            <span class="px-3 py-2 text-gray-500 text-sm">...</span>
                        @elseif ($page == $currentPage)
                            <button class="px-3 py-2 text-white rounded-lg text-sm font-medium min-w-9" style="background-color: #D88429;">
                                {{ $page }}
                            </button>
                        @else
                            <a href="{{ $paginator->url($page) }}" class="px-3 py-2 border border-gray-300 rounded-lg text-sm hover:bg-gray-50 transition-colors min-w-9 text-center">
                                {{ $page }}
                            </a>
                        @endif
                    @endforeach
                </div>

                {{-- Botón Siguiente --}}
                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" class="px-2 sm:px-3 py-2 border border-gray-300 rounded-lg text-xs sm:text-sm hover:bg-gray-50 transition-colors whitespace-nowrap">
                        <span class="sm:hidden">›</span>
                        <span class="hidden sm:inline">Siguiente</span>
                    </a>
                @else
                    <button disabled class="px-2 sm:px-3 py-2 border border-gray-300 rounded-lg text-xs sm:text-sm text-gray-400 cursor-not-allowed whitespace-nowrap">
                        <span class="sm:hidden">›</span>
                        <span class="hidden sm:inline">Siguiente</span>
                    </button>
                @endif
            </div>
        </div>
        
        <!-- Mobile info text - shown only on mobile -->
        <div class="sm:hidden text-xs text-gray-500 text-center mt-3">
            {{ $paginator->firstItem() }}-{{ $paginator->lastItem() }} de {{ $paginator->total() }}
        </div>
    </div>
@endif