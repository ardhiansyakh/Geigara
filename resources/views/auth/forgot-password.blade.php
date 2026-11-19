<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password - Monitoring Radiasi</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>

<body class="min-h-screen bg-slate-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8 text-slate-800">

    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <!-- Logo / Icon -->
        <div class="flex justify-center">
            <div
                class="w-14 h-14 rounded-2xl bg-amber-500 text-white flex items-center justify-center text-2xl shadow-md">
                <i class="fa-solid fa-key"></i>
            </div>
        </div>
        <h2 class="mt-4 text-center text-2xl font-bold tracking-tight text-slate-900">
            Lupa Password?
        </h2>
        <p class="mt-1 text-center text-sm text-slate-600">
            Masukkan username akun Anda untuk mereset password
        </p>
    </div>

    <div class="mt-6 sm:mx-auto sm:w-full sm:max-w-md px-4">
        <div class="bg-white py-8 px-6 shadow-sm border border-slate-200 rounded-2xl sm:px-10">

            <!-- Flash Alert Status / Direct Link -->
            @if (session('status'))
                <div
                    class="mb-5 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm space-y-2">
                    <div class="flex items-center gap-2 font-semibold text-emerald-700">
                        <i class="fa-solid fa-circle-check"></i>
                        <span>{{ session('status') }}</span>
                    </div>
                    @if (session('reset_url'))
                        <p class="text-xs text-slate-600">
                            Untuk kemudahan pengujian, klik tombol di bawah untuk membuat password baru:
                        </p>
                        <a href="{{ session('reset_url') }}"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-sm transition">
                            <i class="fa-solid fa-arrow-right"></i>
                            Buka Halaman Buat Password Baru
                        </a>
                    @endif
                </div>
            @endif

            <!-- Flash Alert Error -->
            @if ($errors->any())
                <div class="mb-5 p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm">
                    <div class="flex items-center gap-2 font-medium mb-1 text-rose-700">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                        <span>Terjadi kendala:</span>
                    </div>
                    <ul class="list-disc list-inside text-xs space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Form Forgot Password (Username) -->
            <form action="{{ route('password.email') }}" method="POST" class="space-y-4">
                @csrf

                <!-- Username -->
                <div>
                    <label for="username" class="block text-sm font-medium text-slate-700 mb-1">
                        Username Akun
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                            <i class="fa-solid fa-user"></i>
                        </span>
                        <input id="username" name="username" type="text" value="{{ old('username') }}" required
                            autofocus placeholder="Masukkan username akun Anda"
                            class="block w-full pl-10 pr-3 py-2.5 border border-slate-300 rounded-xl text-sm placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">
                    </div>
                </div>

                <!-- Tombol Submit -->
                <div class="pt-2">
                    <button type="submit"
                        class="w-full flex justify-center items-center gap-2 py-2.5 px-4 border border-transparent rounded-xl shadow-sm text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition">
                        <i class="fa-regular fa-paper-plane"></i>
                        Buat Tautan Reset Password
                    </button>
                </div>
            </form>

            <!-- Kembali ke Login -->
            <div class="mt-6 text-center border-t border-slate-100 pt-5 text-sm">
                <a href="{{ route('login') }}"
                    class="font-semibold text-slate-600 hover:text-slate-900 inline-flex items-center gap-1.5">
                    <i class="fa-solid fa-arrow-left text-xs"></i>
                    Kembali ke Halaman Login
                </a>
            </div>

        </div>

        <p class="text-center text-xs text-slate-400 mt-6">
            Sistem Monitoring Radiasi Ruang Tunggu Radiologi
        </p>
    </div>

</body>

</html>
