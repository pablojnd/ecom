<div class="w-full max-w-[85rem] py-10 px-4 sm:px-6 lg:px-8 mx-auto">
    <div class="flex items-center h-full">
        <main class="w-full max-w-md p-6 mx-auto">
            <div
                class="bg-white border border-gray-200 shadow-sm mt-7 rounded-xl dark:bg-gray-800 dark:border-gray-700">
                <div class="p-4 sm:p-7">
                    <div class="text-center">
                        <h1 class="text-2xl font-bold text-gray-800 dark:text-white">
                            @lang('Reset password')
                        </h1>
                    </div>

                    <div class="mt-5">
                        <form wire:submit.prevent="save">
                            @if (session('error'))
                            <x-alert type="error" :message="session('error')" />
                            @endif

                            <div class="grid gap-y-4">
                                <!-- Password Input Group -->
                                <x-input-group label="Password" for="password" type="password" model="password" />

                                <!-- Confirm Password Input Group -->
                                <x-input-group label="Confirm Password" for="password_confirmation" type="password"
                                    model="password_confirmation" />

                                <x-button type="submit" full class="mt-4">
                                    @lang('Save password')
                                </x-button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
