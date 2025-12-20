@props(['group'])

<div class="bg-white rounded-2xl border border-gray-200 p-6 hover:shadow-md transition-shadow"
    data-group-id="{{ $group->id }}" data-name="{{ $group->name }}"
    data-is-private="{{ $group->isprivate ? '1' : '0' }}" data-created-at="{{ $group->createdat }}"
    data-members-count="{{ $group->members_count }}" data-posts-count="{{ $group->posts_count }}">

    <div class="flex items-start justify-between">
        <!-- group info -->
        <div class="flex items-start gap-4 flex-1">
            <!-- icon -->
            <div class="shrink-0">
                @if ($group->icon)
                    <img src="{{ asset('groups/' . $group->icon) }}" alt="{{ $group->name }}"
                        class="w-16 h-16 rounded-full object-cover"
                        onerror="this.onerror=null; this.src='{{ asset('profile/default.png') }}'">
                @else
                    <div class="w-16 h-16 rounded-full bg-purple-100 flex items-center justify-center">
                        <i class="fas fa-users text-purple-600 text-2xl"></i>
                    </div>
                @endif
            </div>

            <!-- details -->
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 mb-1">
                    <p class="text-xl font-semibold text-gray-900 transition-colors group-name">
                        {{ $group->name }}
                    </p>
                    @if ($group->isprivate)
                        <x-ui.badge variant="private" size="xs" icon="fas fa-lock">
                            Private
                        </x-ui.badge>
                    @else
                        <x-ui.badge variant="public" size="xs" icon="fas fa-globe">
                            Public
                        </x-ui.badge>
                    @endif
                </div>

                @if ($group->description)
                    <p class="text-gray-600 text-sm mb-3 line-clamp-2">{{ $group->description }}</p>
                @endif

                <!-- stats -->
                <div class="flex items-center gap-6 text-sm text-gray-500 mb-3">
                    <div class="flex items-center gap-1">
                        <i class="fas fa-users text-xs"></i>
                        <span>{{ $group->members_count }} {{ Str::plural('member', $group->members_count) }}</span>
                    </div>
                    <div class="flex items-center gap-1">
                        <i class="fas fa-file-alt text-xs"></i>
                        <span>{{ $group->posts_count }} {{ Str::plural('post', $group->posts_count) }}</span>
                    </div>
                    <div class="flex items-center gap-1">
                        <i class="fas fa-calendar text-xs"></i>
                        <span>Created {{ \Carbon\Carbon::parse($group->createdat)->diffForHumans() }}</span>
                    </div>
                </div>

                <!-- owner -->
                @if ($group->owner_name)
                    <div class="flex items-center gap-2 text-sm text-gray-600">
                        <i class="fas fa-crown text-yellow-500 text-xs"></i>
                        <span>Owner:</span>
                        <p class="text-gray-600 font-medium">
                            {{ $group->owner_name }}
                        </p>
                    </div>
                @endif
            </div>
        </div>

        <!-- delete button -->
        <div class="flex items-center gap-2 ml-4">
            <button onclick="deleteGroup({{ $group->id }}, '{{ addslashes($group->name) }}')"
                class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors text-sm font-medium">
                <i class="fas fa-trash mr-1"></i>
                Delete
            </button>
        </div>
    </div>
</div>
