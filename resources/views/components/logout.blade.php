<div x-data="{ logout: false }">
    <button x-on:click="logout = true" class="grid place-content-center fill-gray-300 hover:fill-white">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-7 rotate-90 w-7">
            <path fill="none" d="M0 0h24v24H0z" />
            <path
                d="M5 11h8v2H5v3l-5-4 5-4v3zm-1 7h2.708a8 8 0 1 0 0-12H4A9.985 9.985 0 0 1 12 2c5.523 0 10 4.477 10 10s-4.477 10-10 10a9.985 9.985 0 0 1-8-4z" />
        </svg>
    </button>

    <div x-show="logout" x-cloak class="relative z-10" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div x-show="logout" x-cloak x-transacion:enter="ease-out duration-300" x-transacion:enter-start="opacity-0"
            x-transacion:enter-end="opacity-100" x-transacion:leave="ease-in duration-200"
            x-transacion:leave-start="opacity-100" x-transacion:leave-end="opacity-0"
            class="fixed inset-0 bg-gray-500 bg-opacity-80 transition-opacity"></div>

        <div class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">

                <div x-show="logout" x-cloak x-transacion:enter="ease-out duration-300"
                    x-transacion:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transacion:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transacion:leave="ease-in duration-200"
                    x-transacion:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transacion:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="relative transform overflow-hidden rounded-lg bg-white px-4 pt-5 pb-4 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-sm sm:p-6">
                    <div class="sm:flex sm:items-start">
                        <div
                            class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-6 w-6 fill-red-700">
                                <path fill="none" d="M0 0h24v24H0z" />
                                <path
                                    d="M12 22C6.477 22 2 17.523 2 12S6.477 2 12 2s10 4.477 10 10-4.477 10-10 10zM7 11V8l-5 4 5 4v-3h8v-2H7z" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg font-medium leading-6 text-gray-900" id="modal-title">Logout
                                account
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">Are you sure you want to logout your account?</p>
                            </div>
                        </div>
                    </div>
                    <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">

                        <form method="POST" action="{{ route('logout') }}" role="none">
                            @csrf
                            <a href="{{ route('logout') }}"
                                onclick="event.preventDefault();
                this.closest('form').submit();"
                                class="inline-flex w-full justify-center rounded-md border border-transparent bg-red-600 px-4 py-2 text-base font-medium text-white shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 sm:ml-3 sm:w-auto sm:text-sm">Logout</a>
                        </form>
                        <button x-on:click="logout = false"
                            class="mt-3 inline-flex w-full justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-base font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 sm:mt-0 sm:w-auto sm:text-sm">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
