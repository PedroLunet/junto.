<div class="space-y-8">
    <div>
        <h2 class="text-4xl font-semibold text-gray-900 py-4 mb-6 border-b border-[#7a5466]">Account Settings</h2>

        <!-- Email -->
        <div class="flex items-end gap-4 mb-6">
            <div class="flex-1">
                <x-ui.input label="Email" name="email" type="email" value="{{ old('email', $user->email ?? '') }}"
                    :error="$errors->first('email')" disabled />
            </div>
            <x-ui.button type="button" variant="secondary" class="text-2xl mb-7 text-gray-700 whitespace-nowrap">
                Change email
            </x-ui.button>
        </div>

        <!-- Password -->
        <div class="flex items-end gap-4">
            <div class="flex-1">
                <x-ui.input label="Password" name="password_display" type="password" value="••••••••••••"
                    :error="$errors->first('password')" disabled />
            </div>
            <x-ui.button type="button" variant="secondary" class="text-2xl mb-7 text-gray-700 whitespace-nowrap"
                onclick="openPasswordModal()">
                Change password
            </x-ui.button>
        </div>
    </div>

    <div>
        <h2 class="text-4xl font-semibold text-gray-900 py-4 mb-6 border-b border-[#7a5466]">Privacy & Security</h2>

        <!-- Make account private -->
        <div class="flex items-center justify-between py-6 border-b border-gray-200">
            <div>
                <h3 class="text-3xl font-medium text-gray-900">Make account private</h3>
                <p class="text-2xl text-gray-500 mt-1">Only your friends can see your posts.</p>
            </div>
            <x-ui.toggle id="privacy-toggle" :checked="$user->isprivate" />
        </div>

        <!-- Log out of all devices -->
        <div class="flex items-center justify-between py-6 border-b border-gray-200">
            <div>
                <h3 class="text-3xl font-medium text-gray-900">Log out</h3>
                <p class="text-2xl text-gray-500 mt-1">Log out of this device.</p>
            </div>
            <x-ui.button href="{{ url('/logout') }}" type="button" variant="secondary"
                class="text-2xl text-gray-700 whitespace-nowrap">
                Log out
            </x-ui.button>
        </div>

        <!-- Delete my account -->
        <div class="flex items-center justify-between py-6">
            <div>
                <h3 class="text-3xl font-medium text-red-700">Delete my account</h3>
                <p class="text-2xl text-gray-500 mt-1">Permanently delete the account.</p>
            </div>
            <x-ui.button type="button" variant="danger" class="text-2xl whitespace-nowrap">
                Delete Account
            </x-ui.button>
        </div>
    </div>
</div>
