<div class="w-full max-w-[85rem] py-10 px-4 sm:px-6 lg:px-8 mx-auto">
    <div class="flex items-center h-full">
        <main class="w-full max-w-md p-6 mx-auto">
            <div
                class="bg-white border border-gray-200 shadow-sm mt-7 rounded-xl dark:bg-gray-800 dark:border-gray-700">
                <div class="p-4 sm:p-7">
                    <div class="text-center">
                        <h1 class="text-2xl font-bold text-gray-800 dark:text-white">
                            @lang('Forgot password?')
                        </h1>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                            @lang('Remember your password?')
                            <a href="/login"
                                class="font-medium text-blue-600 hover:underline dark:focus:outline-none dark:focus:ring-1 dark:focus:ring-gray-600">
                                @lang('Sign in here')
                            </a>
                        </p>
                    </div>

                    <div class="mt-5">
                        <form wire:submit.prevent="save">
                            <!-- Alert Message -->
                            @if (session('success'))
                            <x-alert type="success" :message="session('success')" />
                            @elseif (session('error'))
                            <x-alert type="error" :message="session('error')" />
                            @endif

                            <!-- Email Input Group -->
                            <x-input-group label="Email address" for="email" type="email" model="email" />

                            <!-- Submit Button -->
                            <x-button type="submit" full class="mt-4">
                                @lang('Reset password')
                            </x-button>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
