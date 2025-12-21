<div class="space-y-8">
    <div>
        <h2 class="text-xl sm:text-2xl font-semibold text-gray-900 py-4 mb-6 border-b border-[#7a5466]">Security Settings</h2>

        <!-- Password -->
        <div class="flex flex-col sm:flex-row items-stretch sm:items-end gap-2 sm:gap-4">
            <div class="flex-1">
                <x-ui.input label="Password" name="password_display" type="password" value="••••••••••••" disabled />
            </div>
            <x-ui.button type="button" variant="secondary"
                class="text-base sm:mb-7 text-gray-700 whitespace-nowrap w-full sm:w-auto"
                onclick="openPasswordModal()">
                Change password
            </x-ui.button>
        </div>
    </div>

    <div>
        <h2 class="text-xl sm:text-2xl font-semibold text-gray-900 py-4 mb-6 border-b border-[#7a5466]">Session Management</h2>

        <!-- Log out -->
        <div class="flex items-center justify-between py-6 border-b border-gray-200">
            <div>
                <h3 class="text-lg sm:text-xl font-medium text-gray-900">Log out</h3>
                <p class="text-sm sm:text-base text-gray-500 mt-1">Log out of this device.</p>
            </div>
            <x-ui.button href="{{ url('/logout') }}" type="button" variant="secondary"
                class="text-base text-gray-700 whitespace-nowrap">
                Log out
            </x-ui.button>
        </div>
    </div>
</div>
