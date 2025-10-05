<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fuel Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="max-w-4xl mx-auto text-center">
        <h1 class="text-4xl font-bold text-gray-800 mb-4">Fuel Management System</h1>
        <p class="text-lg text-gray-600 mb-8">Sistem Manajemen Pengisian Bahan Bakar Pesawat</p>
        
        <div class="bg-white rounded-lg shadow-md p-8">
            <h2 class="text-2xl font-semibold text-gray-800 mb-6">Selamat Datang</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-blue-50 p-6 rounded-lg">
                    <h3 class="text-lg font-medium text-blue-800 mb-2">CRS</h3>
                    <p class="text-blue-600">Input dan kelola data flight</p>
                </div>
                
                <div class="bg-green-50 p-6 rounded-lg">
                    <h3 class="text-lg font-medium text-green-800 mb-2">Pengawas</h3>
                    <p class="text-green-600">Monitoring dan laporan shift</p>
                </div>
                
                <div class="bg-orange-50 p-6 rounded-lg">
                    <h3 class="text-lg font-medium text-orange-800 mb-2">CRO</h3>
                    <p class="text-orange-600">Jadwal pengisian bahan bakar</p>
                </div>
            </div>

            <a href="{{ route('login') }}" 
               class="inline-block bg-blue-600 text-white px-8 py-3 rounded-lg hover:bg-blue-700 transition duration-200">
                Login ke Sistem
            </a>

            <div class="mt-8 p-4 bg-gray-50 rounded-md text-left">
                <h3 class="font-bold mb-3">Test Accounts:</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                    <div>
                        <strong>CRS</strong><br>
                        Email: crs@airport.com<br>
                        Password: password
                    </div>
                    <div>
                        <strong>Pengawas</strong><br>
                        Email: pengawas.pagi@airport.com<br>
                        Password: password
                    </div>
                    <div>
                        <strong>CRO</strong><br>
                        Email: cro1@airport.com<br>
                        Password: password
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>