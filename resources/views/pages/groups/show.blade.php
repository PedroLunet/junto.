@extends('layouts.app')

@section('title', $group->name)

@section('content')
<div class="container mx-auto px-4 py-12"> 
    
    {{-- 1. Breadcrumb navigation --}}
    <nav class="flex text-gray-500 text-lg mb-8 max-w-4xl mx-auto items-center" aria-label="Breadcrumb">
        <a href="{{ route('home') }}" class="hover:text-[#820263] transition-colors">Home</a>
        <i class="fas fa-chevron-right text-xs mx-4 text-gray-400"></i>
        <a href="{{ route('groups.index') }}" class="hover:text-[#820263] transition-colors">Groups</a>
        <i class="fas fa-chevron-right text-xs mx-4 text-gray-400"></i>
        <span class="text-gray-900 font-semibold">{{ $group->name }}</span>
    </nav>

    {{-- 2. Group Header Card --}}
    <div class="max-w-4xl mx-auto bg-white rounded-2xl shadow-xl overflow-hidden mb-12 ring-1 ring-gray-100">
        {{-- Banner --}}
        <div class="h-48 bg-gradient-to-br from-gray-100 via-gray-200 to-gray-300 w-full relative">
            {{-- Optional: Add a subtle pattern overlay if you have one --}}
            <div class="absolute inset-0 opacity-10 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')]"></div>
        </div>

        <div class="px-10 pb-10">
            <div class="relative flex flex-col md:flex-row justify-between items-end -mt-20 mb-8 gap-6">
                
                {{-- Group Icon --}}
                <div class="bg-white p-2 rounded-3xl shadow-md z-10">
                    <div class="h-36 w-36 bg-[#820263] rounded-2xl flex items-center justify-center text-white text-7xl font-extrabold shadow-inner">
                        {{ substr($group->name, 0, 1) }}
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex flex-wrap gap-4 mb-2 z-10 w-full md:w-auto md:justify-end">
                    @if(auth()->id() === $group->owner_id)
                        <x-ui.button href="{{ route('groups.edit', $group) }}" variant="secondary" class="text-lg px-6 py-3 shadow-sm border border-gray-200 bg-white hover:bg-gray-50">
                            <i class="fas fa-cog mr-2"></i> Settings
                        </x-ui.button>
                    @endif

                    @if(auth()->check())
                        @if($group->members->contains(auth()->user()))
                            <form action="{{ route('groups.leave', $group) }}" method="POST">
                                @csrf
                                <x-ui.button type="submit" variant="danger" class="text-lg px-8 py-3 shadow-md hover:shadow-lg transition-shadow">
                                    <i class="fas fa-sign-out-alt mr-2"></i> Leave
                                </x-ui.button>
                            </form>
                        @elseif(isset($pendingRequest) && $pendingRequest)
                            <form action="{{ route('groups.cancelRequest', $group) }}" method="POST">
                                @csrf
                                <x-ui.button type="submit" variant="secondary" class="text-lg px-8 py-3 shadow-md bg-gray-100 text-gray-700 hover:bg-gray-200">
                                    <i class="fas fa-times mr-2"></i> Cancel Request
                                </x-ui.button>
                            </form>
                        @else
                            <form action="{{ route('groups.join', $group) }}" method="POST">
                                @csrf
                                <x-ui.button type="submit" variant="primary" class="text-lg px-8 py-3 shadow-md hover:shadow-lg transition-shadow bg-[#820263] hover:bg-[#600149]">
                                    <i class="fas fa-user-plus mr-2"></i> Join Group
                                </x-ui.button>
                            </form>
                        @endif
                    @endif
                </div>
            </div>

            {{-- Group Info --}}
            <div>
                <div class="flex flex-col md:flex-row md:items-center gap-4 mb-6">
                    <h1 class="text-5xl font-black text-gray-900 tracking-tight leading-tight">{{ $group->name }}</h1>
                    
                    {{-- Status Badges --}}
                    <div class="flex-shrink-0">
                        @if($group->isPrivate)
                            <span class="inline-flex items-center bg-amber-50 text-amber-700 text-base px-4 py-2 rounded-full font-bold border border-amber-200 shadow-sm">
                                <i class="fas fa-lock mr-2 text-amber-600"></i> Private
                            </span>
                        @else
                            <span class="inline-flex items-center bg-green-50 text-green-700 text-base px-4 py-2 rounded-full font-bold border border-green-200 shadow-sm">
                                <i class="fas fa-globe mr-2 text-green-600"></i> Public
                            </span>
                        @endif
                    </div>
                </div>

                <p class="text-gray-600 text-2xl leading-relaxed mb-10 font-light max-w-3xl">
                    {{ $group->description }}
                </p>

                {{-- Stats Row --}}
                <div class="flex flex-wrap items-center gap-12 pt-8 border-t border-gray-100 text-lg text-gray-500">
                    <div class="flex items-center group">
                        <div class="w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center mr-3 group-hover:bg-blue-100 transition-colors">
                            <i class="fas fa-users text-blue-500 text-xl"></i>
                        </div>
                        <div>
                            <span class="font-bold text-gray-900 text-xl block">{{ $group->users_count ?? 0 }}</span>
                            <span class="text-sm">Members</span>
                        </div>
                    </div>
                    <div class="flex items-center group">
                        <div class="w-10 h-10 rounded-full bg-purple-50 flex items-center justify-center mr-3 group-hover:bg-purple-100 transition-colors">
                            <i class="fas fa-layer-group text-purple-500 text-xl"></i>
                        </div>
                        <div>
                            <span class="font-bold text-gray-900 text-xl block">{{ count($posts) }}</span>
                            <span class="text-sm">Posts</span>
                        </div>
                    </div>
                    <div class="flex items-center ml-auto text-gray-400 text-base bg-gray-50 px-4 py-2 rounded-lg">
                        <i class="far fa-calendar-alt mr-2"></i> Created {{ $group->created_at ? $group->created_at->format('F j, Y') : '' }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 3. Owner Section: Pending Requests --}}
    @if($isOwner && isset($pendingRequests) && $pendingRequests->count())
        <div class="max-w-4xl mx-auto mb-10 animate-fade-in-up">
            <div class="bg-gradient-to-r from-amber-50 to-orange-50 border border-amber-200 rounded-xl shadow-md p-8">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-bold text-gray-900 flex items-center">
                        <span class="bg-amber-500 text-white w-8 h-8 rounded-full flex items-center justify-center text-sm mr-3">
                            {{ $pendingRequests->count() }}
                        </span>
                        Pending Join Requests
                    </h2>
                    <span class="text-amber-700 text-sm font-semibold uppercase tracking-wider">Action Required</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($pendingRequests as $request)
                        @php $sender = $request->senderid ? \App\Models\User\User::find($request->senderid) : null; @endphp
                        <div class="bg-white rounded-xl p-5 shadow-sm border border-amber-100 flex flex-col justify-between h-full hover:shadow-md transition-shadow">
                            <div class="flex items-center gap-4 mb-4">
                                @if($sender)
                                    <div class="w-14 h-14 rounded-full bg-gray-200 overflow-hidden ring-2 ring-white shadow-sm flex-shrink-0">
                                        <img src="{{ $sender->avatar ?? asset('default-avatar.png') }}" alt="User" class="w-full h-full object-cover">
                                    </div>
                                    <div>
                                        <span class="font-bold text-lg text-gray-900 block">{{ $sender->name }}</span>
                                        <span class="text-gray-500 text-sm">@ {{ $sender->username }}</span>
                                    </div>
                                @else
                                    <span class="text-gray-500 italic">Unknown user</span>
                                @endif
                            </div>
                            
                            <div class="flex gap-3 mt-auto">
                                <form action="{{ route('groups.acceptRequest', [$group, $request->notificationid]) }}" method="POST" class="flex-1">
                                    @csrf
                                    <button type="submit" class="w-full bg-green-100 text-green-700 hover:bg-green-200 py-2.5 rounded-lg font-semibold transition-colors flex items-center justify-center">
                                        <i class="fas fa-check mr-2"></i> Accept
                                    </button>
                                </form>
                                <form action="{{ route('groups.rejectRequest', [$group, $request->notificationid]) }}" method="POST" class="flex-1">
                                    @csrf
                                    <button type="submit" class="w-full bg-red-100 text-red-700 hover:bg-red-200 py-2.5 rounded-lg font-semibold transition-colors flex items-center justify-center">
                                        <i class="fas fa-times mr-2"></i> Reject
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    {{-- 4. Create Post Area --}}
    {{-- Only show this if the user is actually a member, otherwise it clutters the view --}}
    @if(auth()->check() && $group->members->contains(auth()->user()))
        <div class="max-w-4xl mx-auto mb-10">
            <div class="bg-white rounded-2xl shadow-lg p-6 flex gap-6 items-center cursor-pointer hover:bg-gray-50 transition-all transform hover:-translate-y-1 border border-gray-100"
                onclick="document.getElementById('create-post-modal').classList.remove('hidden')">
                
                <div class="h-16 w-16 rounded-full bg-gray-200 flex-shrink-0 overflow-hidden ring-4 ring-gray-50">
                    <img src="{{ auth()->user()->avatar ?? asset('default-avatar.png') }}" alt="User" class="h-full w-full object-cover">
                </div>
                
                <div class="flex-grow">
                    <div class="w-full bg-gray-100 text-gray-500 text-xl rounded-full px-8 py-4 border-transparent hover:ring-2 hover:ring-[#820263]/20 transition-all flex items-center shadow-inner">
                        <span>Write something to the community...</span>
                    </div>
                </div>
                
                <div class="text-white bg-[#820263] h-14 w-14 rounded-full flex items-center justify-center shadow-lg hover:bg-[#600149] transition-colors">
                    <i class="fas fa-plus text-2xl"></i>
                </div>
            </div>
        </div>
    @endif

    {{-- 5. The Feed --}}
    <div class="max-w-4xl mx-auto space-y-8">
        @if($group->isPrivate && !(auth()->check() && $group->members->contains(auth()->user())))
            {{-- Private Group Lock Screen --}}
            <div class="bg-white p-20 rounded-2xl shadow-xl text-center border border-gray-100">
                <div class="inline-flex items-center justify-center w-32 h-32 rounded-full bg-gray-50 mb-8 text-gray-300">
                    <i class="fas fa-lock text-6xl"></i>
                </div>
                <h3 class="text-4xl font-black text-gray-900 mb-4 tracking-tight">This Group is Private</h3>
                <p class="text-gray-500 text-xl mb-10 max-w-lg mx-auto leading-relaxed">The content of this group is only visible to members. You need to be approved by an administrator to join.</p>
                @if(auth()->check())
                    @if(isset($pendingRequest) && $pendingRequest)
                        <div class="bg-amber-50 text-amber-800 px-8 py-4 rounded-xl inline-flex items-center font-bold text-lg border border-amber-200">
                            <i class="fas fa-clock mr-3"></i> Your join request is pending approval.
                        </div>
                    @else
                        <form action="{{ route('groups.join', $group) }}" method="POST">
                            @csrf
                            <x-ui.button type="submit" variant="primary" class="text-xl px-12 py-4 shadow-xl hover:shadow-2xl transform hover:-translate-y-1 transition-all">
                                <i class="fas fa-key mr-3"></i> Request Access
                            </x-ui.button>
                        </form>
                    @endif
                @else
                    <a href="{{ route('login') }}" class="inline-flex items-center text-[#820263] font-bold text-xl hover:underline">
                        <i class="fas fa-sign-in-alt mr-2"></i> Log in to request access
                    </a>
                @endif
            </div>
        @else
            {{-- Public or Member Feed --}}
            @forelse ($posts as $post)
                <x-posts.post-list :posts="[$post]" :showAuthor="true" />
            @empty
                @if($group->isprivate && !(auth()->check() && $group->members->contains(auth()->user())))
                    <div class="bg-white p-20 rounded-2xl shadow-lg text-center border border-gray-100">
                        <div class="inline-flex items-center justify-center w-28 h-28 rounded-full bg-gray-50 mb-8 text-gray-300">
                            <i class="fas fa-lock text-5xl"></i>
                        </div>
                        <h3 class="text-3xl font-bold text-gray-800 mb-4">This group is private</h3>
                        <p class="text-gray-500 text-xl mb-8">Join to see the posts and participate in the conversation!</p>
                    </div>
                @else
                    <div class="bg-white p-20 rounded-2xl shadow-lg text-center border border-gray-100">
                        <div class="inline-flex items-center justify-center w-28 h-28 rounded-full bg-gray-50 mb-8 text-gray-300">
                            <i class="fas fa-comments text-5xl"></i>
                        </div>
                        <h3 class="text-3xl font-bold text-gray-800 mb-4">No posts yet</h3>
                        <p class="text-gray-500 text-xl mb-8">This feed is looking a little quiet. Be the first to start the conversation!</p>
                    </div>
                @endif
            @endforelse
        @endif
    </div>

</div>

{{-- Modals --}}
<x-posts.post-modal />
<x-posts.edit.edit-regular-modal />
<x-posts.edit.edit-review-modal />

@yield('modal-overlay')

<script>
    function toggleLike(postId) {
        if (!window.isAuthenticated) {
            window.location.href = '/login';
            return;
        }

        fetch(`/posts/${postId}/like`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const likeBtn = document.getElementById(`like-btn-${postId}`);
                    const likeCount = document.getElementById(`like-count-${postId}`);
                    const likeIcon = document.getElementById(`like-icon-${postId}`);

                    likeCount.textContent = data.likes_count;

                    if (data.liked) {
                        likeBtn.classList.remove('text-gray-600', 'focus:text-gray-600');
                        likeBtn.classList.add('text-red-500', 'focus:text-red-500');
                        likeIcon.classList.remove('far');
                        likeIcon.classList.add('fas');
                    } else {
                        likeBtn.classList.remove('text-red-500', 'focus:text-red-500');
                        likeBtn.classList.add('text-gray-600', 'focus:text-gray-600');
                        likeIcon.classList.remove('fas');
                        likeIcon.classList.add('far');
                    }
                }
            })
            .catch(error => console.error('Error:', error));
    }
</script>
@endsection