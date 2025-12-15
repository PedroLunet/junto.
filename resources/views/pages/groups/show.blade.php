@extends('layouts.app')

@section('title', $group->name)

@section('content')

<div class="container mx-auto px-4 py-8"> 
    
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start relative">
        
        <div class="lg:col-span-8 order-2 lg:order-1">
            @if($isOwner && isset($pendingRequests) && $pendingRequests->count())
                <div class="mb-8 animate-fade-in-up">
                    <div class="bg-linear-to-r from-amber-50 to-orange-50 border border-amber-200 rounded-xl shadow-sm p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-lg font-bold text-gray-900 flex items-center">
                                <span class="bg-amber-500 text-white w-6 h-6 rounded-full flex items-center justify-center text-xs mr-2">
                                    {{ $pendingRequests->count() }}
                                </span>
                                Pending Requests
                            </h2>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            @foreach($pendingRequests as $request)
                                @php $sender = $request->senderid ? \App\Models\User\User::find($request->senderid) : null; @endphp
                                <div class="bg-white rounded-lg p-3 shadow-sm border border-amber-100 flex items-center justify-between gap-3">
                                    <div class="flex items-center gap-3">
                                        @if($sender)
                                            <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center text-gray-600 font-bold text-sm">
                                                {{ substr($sender->name, 0, 1) }}
                                            </div>
                                            <div class="min-w-0">
                                                <span class="font-bold text-sm text-gray-900 block truncate">{{ $sender->name }}</span>
                                                <span class="text-gray-500 text-xs truncate">@ {{ $sender->username }}</span>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex gap-2">
                                        <form action="{{ route('groups.acceptRequest', [$group, $request->notificationid]) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="text-green-600 hover:bg-green-50 p-2 rounded-full transition-colors" title="Accept">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('groups.rejectRequest', [$group, $request->notificationid]) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="text-red-600 hover:bg-red-50 p-2 rounded-full transition-colors" title="Reject">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <div class="space-y-6">
                @if($group->isPrivate && !(auth()->check() && $group->members->contains(auth()->user())))
                    <div class="bg-white p-12 rounded-xl shadow-sm text-center border border-gray-200">
                        <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-gray-50 mb-6 text-gray-300">
                            <i class="fas fa-lock text-4xl"></i>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-2">Private Group</h3>
                        <p class="text-gray-500 mb-8 max-w-md mx-auto">This group's content is only visible to members.</p>
                        @if(auth()->check())
                            @if(isset($pendingRequest) && $pendingRequest)
                                <div class="bg-amber-50 text-amber-800 px-6 py-3 rounded-lg inline-flex items-center font-medium border border-amber-200">
                                    <i class="fas fa-clock mr-2"></i> Request Pending
                                </div>
                            @else
                                <form action="{{ route('groups.join', $group) }}" method="POST">
                                    @csrf
                                    <x-ui.button type="submit" variant="primary" class="px-8 py-3">
                                        Request Access
                                    </x-ui.button>
                                </form>
                            @endif
                        @else
                            <x-ui.button href="{{ route('login') }}" variant="primary" class="px-8 py-3">
                                Log in to request access
                            </x-ui.button>
                        @endif
                    </div>
                @else
                    @forelse ($posts as $post)
                        <x-posts.post-list :posts="[$post]" :showAuthor="true" />
                    @empty
                        <div class="bg-white p-12 rounded-xl shadow-sm text-center border border-gray-200">
                            <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-gray-50 mb-6 text-gray-300">
                                <i class="fas fa-comments text-4xl"></i>
                            </div>
                            <h3 class="text-xl font-bold text-gray-800 mb-2">No posts yet</h3>
                            <p class="text-gray-500">Be the first to start the conversation!</p>
                        </div>
                    @endforelse
                @endif
            </div>
        </div>

        <div class="lg:col-span-4 order-1 lg:order-2 space-y-8">
            
            @if(auth()->check() && $group->members->contains(auth()->user()))
                    <div class="grid grid-cols-4 gap-4 pb-2">
                        <x-ui.button id="group-post-button" variant="special" class="aspect-square rounded-3xl transition-transform hover:scale-105 flex items-center justify-center" title="Post" data-modal="create-group-post-modal">
                            <i class="fa-solid fa-plus text-3xl"></i>
                        </x-ui.button>
                        <x-ui.button id="group-music-review-button" variant="special" class="aspect-square rounded-3xl transition-transform hover:scale-105 flex items-center justify-center" title="Music Review" data-modal="create-group-music-review-modal">
                            <i class="fa-solid fa-music text-3xl"></i>
                        </x-ui.button>
                        <x-ui.button id="group-book-review-button" variant="special" class="aspect-square rounded-3xl transition-transform hover:scale-105 flex items-center justify-center" title="Book Review" data-modal="create-group-book-review-modal">
                            <i class="fa-solid fa-book text-3xl"></i>
                        </x-ui.button>
                        <x-ui.button id="group-movie-review-button" variant="special" class="aspect-square rounded-3xl transition-transform hover:scale-105 flex items-center justify-center" title="Movie Review" data-modal="create-group-movie-review-modal">
                            <i class="fa-solid fa-clapperboard text-3xl"></i>
                        </x-ui.button>
                    </div>
                    
                    @include('components.groups.create-group-post-modal', ['group' => $group])
                    @include('components.groups.create-group-music-review-modal', ['group' => $group])
                    @include('components.groups.create-group-book-review-modal', ['group' => $group])
                    @include('components.groups.create-group-movie-review-modal', ['group' => $group])
            @endif

            <div class="bg-white rounded-2xl shadow-md border border-gray-200 overflow-hidden">
                <div class="h-48 bg-linear-to-br from-gray-100 via-gray-200 to-gray-300 relative">
                    <div class="absolute inset-0 opacity-10 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')]"></div>
                </div>
                
                <div class="px-8 pb-10 relative">
                    <div class="-mt-24 mb-6">
                        <div class="bg-white p-3 rounded-4xl shadow-sm inline-block">
                            <div class="h-40 w-40 bg-[#820263] rounded-[1.7rem] flex items-center justify-center text-white text-8xl font-extrabold shadow-inner">
                                {{ substr($group->name, 0, 1) }}
                            </div>
                        </div>
                    </div>
                    
                    <h2 class="text-5xl font-black text-gray-900 mb-4 tracking-tight leading-tight">{{ $group->name }}</h2>
                    
                    <div class="mb-8">
                        @if($group->isprivate)
                            <span class="inline-flex items-center bg-amber-50 text-amber-700 text-base px-5 py-2 rounded-full font-bold border border-amber-200 shadow-sm">
                                <i class="fas fa-lock mr-2"></i> Private
                            </span>
                        @else
                            <span class="inline-flex items-center bg-green-50 text-green-700 text-base px-5 py-2 rounded-full font-bold border border-green-200 shadow-sm">
                                <i class="fas fa-globe mr-2"></i> Public
                            </span>
                        @endif
                    </div>

                    <p class="text-gray-800 mb-10 text-2xl leading-relaxed">
                        {{ $group->description }}
                    </p>

                    <div class="flex items-center justify-center gap-12 text-gray-500 border-t border-gray-100 pt-10 mb-10">
                        <div class="text-center">
                            <span class="block font-black text-gray-900 text-4xl mb-2">{{ $group->users_count ?? 0 }}</span>
                            <span class="font-medium text-xl">Members</span>
                        </div>
                        <div class="text-center border-l border-gray-100 pl-12">
                            <span class="block font-black text-gray-900 text-4xl mb-2">{{ count($posts) }}</span>
                            <span class="font-medium text-xl">Posts</span>
                        </div>
                    </div>

                    @if(auth()->check())
                        <div class="flex flex-col gap-4 pt-2">
                            @if($isOwner)
                                <x-ui.button href="{{ route('groups.edit', $group) }}" variant="secondary" class="w-full justify-center shadow-sm border border-gray-200 py-5 text-xl font-bold">
                                    <i class="fas fa-cog mr-2"></i> Settings
                                </x-ui.button>
                            @endif

                            @if($group->members->contains(auth()->user()))
                                <form action="{{ route('groups.leave', $group) }}" method="POST" class="w-full">
                                    @csrf
                                    <x-ui.button type="submit" variant="danger" class="w-full justify-center shadow-sm py-5 text-xl font-bold">
                                        <i class="fas fa-sign-out-alt mr-2"></i> Leave Group
                                    </x-ui.button>
                                </form>
                            @elseif(isset($pendingRequest) && $pendingRequest)
                                <form action="{{ route('groups.cancelRequest', $group) }}" method="POST" class="w-full">
                                    @csrf
                                    <x-ui.button type="submit" variant="secondary" class="w-full justify-center bg-gray-100 text-gray-700 hover:bg-gray-200 py-5 text-xl font-bold">
                                        <i class="fas fa-times mr-2"></i> Cancel Request
                                    </x-ui.button>
                                </form>
                            @else
                                <form action="{{ route('groups.join', $group) }}" method="POST" class="w-full">
                                    @csrf
                                    <x-ui.button type="submit" variant="primary" class="w-full justify-center shadow-md bg-[#820263] hover:bg-[#600149] py-5 text-xl font-bold">
                                        <i class="fas fa-user-plus mr-2"></i> Join Group
                                    </x-ui.button>
                                </form>
                            @endif
                        </div>
                    @else
                        <x-ui.button href="{{ route('login') }}" variant="primary" class="w-full justify-center shadow-md bg-[#820263] hover:bg-[#600149] mt-2 py-5 text-xl font-bold">
                            Log in to Join
                        </x-ui.button>
                    @endif
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8">
                <h3 class="font-bold text-gray-900 text-3xl mb-6">Members</h3>
                @php
                    $isOwnerView = isset($isOwner) && $isOwner;
                    $members = $isOwnerView ? $group->members :
                        (!$group->isprivate || (auth()->check() && $group->members->contains(auth()->user()))
                            ? $group->members
                            : (auth()->check() ? $group->members->intersect(auth()->user()->friends()) : collect()));
                    $sortedMembers = $members->sortByDesc(function($member) use ($ownerId) {
                        return isset($ownerId) && $member->id === $ownerId;
                    });
                @endphp

                @if($sortedMembers->isEmpty())
                    <p class="text-gray-500 text-lg italic">No visible members.</p>
                @else
                    <div class="space-y-5">
                        @foreach($sortedMembers as $member)
                            @php $isGroupOwner = (isset($ownerId) && $member->id === $ownerId); @endphp
                            <div class="flex items-center p-3 rounded-xl hover:bg-gray-50 transition-colors {{ $isGroupOwner ? 'bg-amber-50/50 border border-amber-100' : '' }}">
                                <div class="w-16 h-16 rounded-full flex items-center justify-center font-bold text-2xl mr-5 shadow-sm border flex-shrink-0 {{ $isGroupOwner ? 'bg-amber-100 text-amber-700 border-amber-200' : 'bg-indigo-50 text-indigo-600 border-indigo-100' }}">
                                    {{ substr($member->name, 0, 1) }}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2">
                                        <p class="text-xl font-bold text-gray-900 truncate">{{ $member->name }}</p>
                                        @if($isGroupOwner)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-amber-100 text-amber-700 uppercase tracking-wide">
                                                Owner
                                            </span>
                                        @endif
                                    </div>
                                    <div class="flex items-center text-base text-gray-500 truncate mt-1">
                                        <span>@ {{ $member->username }}</span>
                                        @if($isGroupOwner)
                                            <i class="fas fa-crown text-amber-400 ml-2 text-sm"></i>
                                        @endif
                                    </div>
                                </div>
                                @if($isOwnerView && !$isGroupOwner)
                                    <form action="{{ route('groups.removeMember', ['group' => $group->id, 'user' => $member->id]) }}" method="POST" class="ml-4">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:bg-red-50 p-3 rounded-full transition-colors" title="Remove from group">
                                            <i class="fas fa-user-minus text-xl"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

        </div>

    </div>
</div>

<x-posts.post-modal />
<x-posts.edit.edit-regular-modal />
<x-posts.edit.edit-review-modal />

@yield('modal-overlay')

<script>
    document.addEventListener('DOMContentLoaded', function() {
        
        const modalTriggers = document.querySelectorAll('[data-modal]');
        modalTriggers.forEach(trigger => {
            trigger.addEventListener('click', function(e) {
                e.preventDefault();
                const targetId = this.getAttribute('data-modal');
                const modal = document.getElementById(targetId);
                if (modal) {
                    modal.classList.remove('hidden');
                    modal.style.display = 'block';
                }
            });
        });
    });

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