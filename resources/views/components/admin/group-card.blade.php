@props(['group'])

<div class="bg-white rounded-lg border border-gray-200 p-6 hover:shadow-md transition-shadow"
    data-group-id="{{ $group->id }}" data-name="{{ $group->name }}"
    data-is-private="{{ $group->isprivate ? '1' : '0' }}" data-created-at="{{ $group->createdat }}"
    data-members-count="{{ $group->members_count }}" data-posts-count="{{ $group->posts_count }}">

    <div class="flex items-start justify-between">
        <!-- Group Info -->
        <div class="flex items-start gap-4 flex-1">
            <!-- Group Icon -->
            <div class="flex-shrink-0">
                @if ($group->icon)
                    <img src="{{ asset('groups/' . $group->icon) }}" alt="{{ $group->name }}"
                        class="w-16 h-16 rounded-lg object-cover"
                        onerror="this.onerror=null; this.src='{{ asset('profile/default.png') }}'">
                @else
                    <div class="w-16 h-16 rounded-lg bg-purple-100 flex items-center justify-center">
                        <i class="fas fa-users text-purple-600 text-2xl"></i>
                    </div>
                @endif
            </div>

            <!-- Group Details -->
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 mb-1">
                    <a href="{{ route('groups.show', $group->id) }}"
                        class="text-xl font-semibold text-gray-900 hover:text-purple-600 transition-colors"
                        target="_blank">
                        {{ $group->name }}
                    </a>
                    @if ($group->isprivate)
                        <span
                            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                            <i class="fas fa-lock mr-1 text-xs"></i>
                            Private
                        </span>
                    @else
                        <span
                            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            <i class="fas fa-globe mr-1 text-xs"></i>
                            Public
                        </span>
                    @endif
                </div>

                @if ($group->description)
                    <p class="text-gray-600 text-sm mb-3 line-clamp-2">{{ $group->description }}</p>
                @endif

                <!-- Stats -->
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

                <!-- Owner Info -->
                @if ($group->owner_name)
                    <div class="flex items-center gap-2 text-sm text-gray-600">
                        <i class="fas fa-crown text-yellow-500 text-xs"></i>
                        <span>Owner:</span>
                        <a href="{{ route('profile.show', $group->owner_username) }}"
                            class="text-purple-600 hover:underline font-medium" target="_blank">
                            {{ $group->owner_name }}
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center gap-2 ml-4">
            <button onclick="deleteGroup({{ $group->id }}, '{{ addslashes($group->name) }}')"
                class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors text-sm font-medium">
                <i class="fas fa-trash mr-1"></i>
                Delete
            </button>
        </div>
    </div>
</div>
