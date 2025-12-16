@extends('layouts.app')

@section('content')
    <div class="flex flex-col min-h-0 h-full -m-6 items-center justify-center">
        <div class="bg-white rounded-2xl shadow-xl max-w-2xl w-full mx-4 mt-20 mb-10">
            <!-- header -->
            <div class="flex items-center justify-between p-8">
                <h2 class="text-4xl font-bold text-gray-900">Edit Profile</h2>
            </div>

            <!-- body -->
            <div class="flex-1 px-8">
                <form id="editProfileForm" class="space-y-6" method="POST" action="{{ route('profile.update') }}">
                    @csrf
                    @method('PUT')

                    <div class="flex items-center gap-6">
                        <!-- profile picture -->
                        <div class="shrink-0 w-40 h-40 relative">
                            <div class="w-full h-full rounded-full overflow-hidden relative">
                                <img id="profileImagePreview"
                                    src="{{ $user->profile_picture ?? asset('profile/default.png') }}" alt="Profile Picture"
                                    class="absolute inset-0 w-full h-full object-cover">
                            </div>
                            <x-ui.button variant="outline"
                                class="absolute -top-2 -right-2 w-10 h-10 rounded-full flex items-center justify-center text-2xl font-bold z-10 px-0 py-0 bg-white border border-gray-300 shadow-lg hover:bg-gray-100">
                                <i class="fas fa-edit text-purple-500"></i>
                            </x-ui.button>
                            <x-ui.button variant="outline"
                                class="absolute -top-2 -left-2 w-10 h-10 rounded-full flex items-center justify-center text-2xl font-bold z-10 px-0 py-0 bg-white border border-gray-300 shadow-lg hover:bg-gray-100">
                                <i class="fas fa-trash text-red-500"></i>
                            </x-ui.button>
                        </div>

                        <div class="flex-1 space-y-6">
                            <!-- name -->
                            <div>
                                <label for="editName" class="block text-2xl font-medium text-gray-700 mb-2">Name</label>
                                <input type="text" id="editName" name="name"
                                    value="{{ old('name', $user->name ?? '') }}"
                                    class="w-full px-4 py-3 text-2xl border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#38157a] focus:border-transparent">
                            </div>

                            <!-- username -->
                            <div>
                                <label for="editUsername"
                                    class="block text-2xl font-medium text-gray-700 mb-2">Username</label>
                                <input type="text" id="editUsername" name="username"
                                    value="{{ old('username', $user->username ?? '') }}"
                                    class="w-full px-4 py-3 text-2xl border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#38157a] focus:border-transparent">
                            </div>
                        </div>
                    </div>

                    <!-- bio -->
                    <div>
                        <label for="editBio" class="block text-2xl font-medium text-gray-700 mb-2">Bio</label>
                        <textarea id="editBio" name="bio" rows="4" placeholder="Tell others about yourself..."
                            class="w-full px-4 py-3 text-2xl border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#38157a] focus:border-transparent resize-none">{{ old('bio', $user->bio ?? '') }}</textarea>
                    </div>

                    <!-- password (fake input, styled like others) -->
                    <div>
                        <label class="block text-2xl font-medium text-gray-700 mb-2">Password</label>
                        <div class="relative">
                            <div class="w-full px-4 py-3 text-2xl border border-gray-300 rounded-lg bg-gray-100 focus:ring-2 focus:ring-[#38157a] focus:border-transparent flex items-center select-none pr-14"
                                style="letter-spacing: 0.3em;">
                                <span aria-label="Password hidden" class="tracking-widest text-gray-500 flex-1">
                                    <span
                                        class="inline-block mx-0.5 text-3xl align-middle">&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;</span>
                                </span>
                                <x-ui.button variant="outline"
                                    class="absolute right-2 top-0 bottom-0 my-auto h-12 w-12 p-2 flex items-center justify-center text-2xl font-bold px-0 py-0 bg-white border border-gray-300 shadow-lg hover:bg-gray-100"
                                    title="Edit Password" type="button">
                                    <i
                                        class="fas fa-edit text-purple-500 leading-none align-middle flex items-center justify-center"></i>
                                </x-ui.button>
                            </div>
                        </div>
                    </div>

                    <!-- form actions -->
                    <div class="flex justify-end gap-4 pt-4">
                        <a href="{{ route('profile') }}"
                            class="text-2xl font-medium px-6 py-3 rounded-lg bg-gray-200 hover:bg-gray-300">Cancel</a>
                        <x-ui.button type="submit" variant="primary" class="text-2xl font-medium">Save Changes</x-ui.button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
