@props(['id', 'toggleFunction', 'dataAttribute' => 'id'])

<div class="bg-white rounded-2xl shadow border border-gray-200" data-{{ $dataAttribute }}="{{ $id }}">
    <!-- card header -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 sm:gap-4 p-4 sm:p-6 cursor-pointer hover:bg-gray-50 transition-colors rounded-2xl"
        onclick="{{ $toggleFunction }}({{ $id }})">
        <div class="flex-1 w-full sm:w-auto">
            {{ $header }}
        </div>

        <!-- chevron icon -->
        <i class="fas fa-chevron-down text-gray-400 text-base transition-transform duration-300 self-end sm:self-center sm:ml-4"
            id="chevron-{{ $id }}"></i>
    </div>

    <!-- collapsible content -->
    <div class="hidden px-4 sm:px-6 pb-4 sm:pb-6" id="content-{{ $id }}">
        {{ $slot }}
    </div>
</div>
