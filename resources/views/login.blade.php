<x-layout title="Login">
    <form class="w-full max-w-sm mx-auto p-6 flex flex-col justify-center items-center" method="POST" action="/login">
        @csrf
        <div class="mb-5">
            <label for="email" class="block mb-2.5 text-sm font-medium text-gray-900">Your email</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}"
                class="bg-white border border-gray-300 text-gray-900 text-sm rounded-md focus:ring focus:border-blue-300 block w-full px-3 py-2.5 shadow-xs"
                placeholder="name@example.com" required />
        </div>
        <div class="mb-5">
            <label for="password" class="block mb-2.5 text-sm font-medium text-gray-900">Your password</label>
            <input type="password" id="password" name="password"
                class="bg-white border border-gray-300 text-gray-900 text-sm rounded-md focus:ring focus:border-blue-300 block w-full px-3 py-2.5 shadow-xs"
                placeholder="••••••••" required />
        </div>
        <button type="submit"
            class="text-white bg-black/70 hover:bg-black rounded-md text-sm font-medium px-4 py-2.5 focus:outline-none">
            Submit
        </button>
    </form>
</x-layout>