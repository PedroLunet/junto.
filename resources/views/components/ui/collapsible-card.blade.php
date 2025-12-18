@props(['id', 'toggleFunction', 'dataAttribute' => 'id'])

<div class="bg-white rounded-2xl shadow border border-gray-200" data-{{ $dataAttribute }}="{{ $id }}">
    <!-- card header -->
    <div class="flex items-center justify-between p-6 cursor-pointer hover:bg-gray-50 transition-colors rounded-2xl"
        onclick="{{ $toggleFunction }}({{ $id }})">
        {{ $header }}

        <!-- chevron icon -->
        <i class="fas fa-chevron-down text-gray-400 text-base transition-transform duration-300 ml-4 self-center"
            id="chevron-{{ $id }}"></i>
    </div>

    <!-- collapsible content -->
    <div class="hidden px-6 pb-6" id="content-{{ $id }}">
        {{ $slot }}
    </div>
</div>
