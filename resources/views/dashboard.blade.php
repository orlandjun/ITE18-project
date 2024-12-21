<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://unpkg.com/html5-qrcode"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        :root {
            --primary-green: #10B981;
            --dark-green: #059669;
            --light-green: #D1FAE5;
        }

        #reader {
            width: 100%;
            max-width: 640px;
            margin: 20px auto;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        #reader video {
            border-radius: 8px;
        }

        #result {
            text-align: center;
            margin: 20px 0;
            padding: 16px;
            background: var(--light-green);
            border-radius: 8px;
            border: 1px solid var(--primary-green);
        }

        .controls {
            margin: 20px 0;
        }

        .error {
            color: #DC2626;
            margin: 10px 0;
            padding: 12px;
            background: #FEE2E2;
            border-radius: 8px;
            border: 1px solid #DC2626;
        }

        .debug-info {
            margin: 20px 0;
            padding: 12px;
            background: #F9FAFB;
            border-radius: 8px;
            border: 1px solid #E5E7EB;
            max-height: 200px;
            overflow-y: auto;
            font-family: monospace;
            font-size: 0.875rem;
        }

        .scanner-container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .scanner-container:hover {
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        .scan-history-item {
            background: white;
            padding: 16px;
            border-radius: 8px;
            border-left: 4px solid var(--primary-green);
            margin-bottom: 8px;
            transition: all 0.2s ease;
        }

        .scan-history-item:hover {
            transform: translateX(4px);
            box-shadow: 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        .modern-select {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2310B981'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E");
            background-position: right 12px center;
            background-repeat: no-repeat;
            background-size: 16px;
            padding-right: 40px;
        }

        .button-group {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .modern-button {
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .modern-button svg {
            width: 20px;
            height: 20px;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 12px;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 500;
            background: var(--light-green);
            color: var(--dark-green);
        }

        /* New Admin Styles */
        .admin-tabs {
            display: flex;
            border-bottom: 1px solid var(--light-green);
            margin-bottom: 1rem;
        }

        .admin-tab {
            padding: 0.75rem 1.5rem;
            cursor: pointer;
            border-bottom: 2px solid transparent;
            color: var(--dark-green);
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .admin-tab.active {
            border-bottom-color: var(--primary-green);
            color: var(--primary-green);
        }

        .admin-content {
            display: none;
        }

        .admin-content.active {
            display: block;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .stat-card {
            background: white;
            padding: 1rem;
            border-radius: 0.5rem;
            border: 1px solid var(--light-green);
        }

        .chart-container {
            background: white;
            padding: 1rem;
            border-radius: 0.5rem;
            border: 1px solid var(--light-green);
            margin-bottom: 1.5rem;
        }

        .file-drop-zone {
            border: 2px dashed var(--light-green);
            border-radius: 0.5rem;
            padding: 2rem;
            text-align: center;
            background: white;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .file-drop-zone:hover {
            border-color: var(--primary-green);
            background: var(--light-green);
        }
    </style>
</head>
<body class="font-sans antialiased bg-gray-50">
    <div class="min-h-screen">
        @include('layouts.navigation')

        <!-- Page Heading -->
        <header class="bg-white shadow-sm">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between">
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                        {{ __('Dashboard') }}
                    </h2>
                    <div class="flex space-x-4">
                        <div class="status-badge" id="connection-status">
                            <span class="w-2 h-2 bg-green-500 rounded-full mr-2"></span>
                            Ready to Scan
                        </div>
                        <button onclick="toggleView('scanner')" class="text-sm text-green-600 hover:text-green-800">
                            Scanner View
                        </button>
                        <button onclick="toggleView('admin')" class="text-sm text-green-600 hover:text-green-800">
                            Admin View
                        </button>
                    </div>
                </div>
            </div>
        </header>

        <!-- Page Content -->
        <main class="py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Scanner View -->
                <div id="scanner-view" class="view-content">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <!-- QR Scanner Column -->
                        <div class="lg:col-span-1">
                            <div class="scanner-container p-4 bg-white rounded-lg shadow-sm">
                                <div class="flex items-center justify-between mb-4">
                                    <h3 class="text-lg font-semibold text-gray-900">QR Scanner</h3>
                                    <div id="scanner-status" class="status-badge">
                                        <span class="w-2 h-2 bg-green-500 rounded-full mr-2"></span>
                                        Ready
                                    </div>
                                </div>

                                <div class="controls space-y-3">
                                    <select id="camera-select" class="modern-select w-full rounded-lg border-gray-300 focus:border-green-500 focus:ring focus:ring-green-200">
                                        <option value="">Select Camera</option>
                                    </select>

                                    <div class="button-group flex flex-wrap gap-2">
                                        <button onclick="requestCameraPermission()" class="modern-button bg-green-500 text-white hover:bg-green-600 flex-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                            </svg>
                                            Permission
                                        </button>

                                        <button onclick="testCamera()" class="modern-button bg-yellow-500 text-white hover:bg-yellow-600 flex-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                            </svg>
                                            Test
                                        </button>

                                        <button onclick="startScanner()" class="modern-button bg-green-500 text-white hover:bg-green-600 flex-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                            </svg>
                                            Start
                                        </button>

                                        <button onclick="stopScanner()" class="modern-button bg-red-500 text-white hover:bg-red-600 flex-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 10a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z" />
                                            </svg>
                                            Stop
                                        </button>
                                    </div>
                                </div>

                                <div id="error" class="error hidden mt-3"></div>
                                <div id="reader" class="mt-4 rounded-lg overflow-hidden"></div>
                                <div id="result" class="mt-4 text-center text-gray-600">No QR Code scanned yet</div>
                                <div id="debug" class="debug-info"></div>
                            </div>
                        </div>

                        <!-- Validation Results Column -->
                        <div class="lg:col-span-2">
                            <!-- Today's Statistics -->
                            <div class="grid grid-cols-3 gap-4 mb-6">
                                <div class="bg-white p-4 rounded-lg shadow-sm">
                                    <h4 class="text-sm font-medium text-gray-500">Today's Scans</h4>
                                    <p class="text-2xl font-bold text-green-600" id="today-scans">0</p>
                                </div>
                                <div class="bg-white p-4 rounded-lg shadow-sm">
                                    <h4 class="text-sm font-medium text-gray-500">Successful</h4>
                                    <p class="text-2xl font-bold text-green-600" id="successful-scans">0</p>
                                </div>
                                <div class="bg-white p-4 rounded-lg shadow-sm">
                                    <h4 class="text-sm font-medium text-gray-500">Failed</h4>
                                    <p class="text-2xl font-bold text-red-600" id="failed-scans">0</p>
                                </div>
                            </div>

                            <!-- Validated Students Box -->
                            <div class="bg-white rounded-lg shadow-sm">
                                <div class="p-4 border-b">
                                    <div class="flex justify-between items-center">
                                        <h3 class="text-lg font-semibold text-gray-900">Validated Students</h3>
                                        <div class="flex space-x-2">
                                            <button onclick="toggleScanHistory()" class="px-3 py-1.5 bg-green-500 text-white text-sm rounded hover:bg-green-600 flex items-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                                </svg>
                                                History
                                            </button>
                                            <button onclick="clearValidatedStudents()" class="px-3 py-1.5 bg-gray-500 text-white text-sm rounded hover:bg-gray-600">
                                                Clear
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Validated Students List -->
                                <div class="p-4">
                                    <div id="validated-students" class="space-y-2 max-h-[300px] overflow-y-auto">
                                        <!-- Validated students will be displayed here -->
                                    </div>

                                    <!-- Scan History (Hidden by default) -->
                                    <div id="scan-history" class="hidden space-y-2 mt-4 pt-4 border-t">
                                        <div class="flex justify-between items-center mb-2">
                                            <h4 class="font-medium text-gray-700">Recent Scans</h4>
                                            <button onclick="refreshScanHistory()" class="text-green-600 hover:text-green-800">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                                </svg>
                                            </button>
                                        </div>
                                        <!-- Scan history entries will be displayed here -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Admin View -->
                <div id="admin-view" class="view-content" style="display: none;">
                    <div class="admin-tabs">
                        <div class="admin-tab active" onclick="switchTab('bulk-operations')">Bulk Operations</div>
                        <div class="admin-tab" onclick="switchTab('analytics')">Analytics</div>
                        <div class="admin-tab" onclick="switchTab('reports')">Reports</div>
                    </div>

                    <!-- Bulk Operations -->
                    <div id="bulk-operations" class="admin-content active">
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Import/Export Student Data</h3>
                            <div class="file-drop-zone" onclick="document.getElementById('file-upload').click()">
                                <input type="file" id="file-upload" class="hidden" accept=".csv,.xlsx">
                                <svg class="w-12 h-12 mx-auto text-green-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                </svg>
                                <p class="text-gray-600">Drop your Excel/CSV file here or click to browse</p>
                            </div>
                            <div class="flex justify-end mt-4 space-x-4">
                                <button onclick="exportData()" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                                    Export Data
                                </button>
                                <button onclick="importData()" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                                    Import Data
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Analytics -->
                    <div id="analytics" class="admin-content">
                        <div class="stats-grid">
                            <div class="stat-card">
                                <h4 class="text-sm font-medium text-gray-500">Total Validations</h4>
                                <p class="text-2xl font-bold text-green-600">1,234</p>
                            </div>
                            <div class="stat-card">
                                <h4 class="text-sm font-medium text-gray-500">Today's Validations</h4>
                                <p class="text-2xl font-bold text-green-600">42</p>
                            </div>
                            <div class="stat-card">
                                <h4 class="text-sm font-medium text-gray-500">Success Rate</h4>
                                <p class="text-2xl font-bold text-green-600">98.5%</p>
                            </div>
                        </div>
                        <div class="chart-container">
                            <canvas id="validationChart"></canvas>
                        </div>
                        <div class="chart-container">
                            <canvas id="hourlyChart"></canvas>
                        </div>
                    </div>

                    <!-- Reports -->
                    <div id="reports" class="admin-content">
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Generate Reports</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Date Range</label>
                                    <div class="flex space-x-4">
                                        <input type="date" class="rounded-lg border-gray-300 flex-1">
                                        <input type="date" class="rounded-lg border-gray-300 flex-1">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Report Type</label>
                                    <select class="rounded-lg border-gray-300 w-full">
                                        <option>Validation Summary</option>
                                        <option>Detailed Activity Log</option>
                                        <option>Failed Validations</option>
                                        <option>Usage Statistics</option>
                                    </select>
                                </div>
                            </div>
                            <div class="flex justify-end mt-4">
                                <button onclick="generateReport()" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                                    Generate Report
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Core scanner variables
        let html5QrcodeScanner = null;
        let hasPermission = false;

        // Request camera permission explicitly
        async function requestCameraPermission() {
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ 
                    video: true,
                    audio: false
                });
                
                hasPermission = true;
                updateScannerStatus('Permission Granted');
                
                // Stop the test stream
                stream.getTracks().forEach(track => track.stop());
                
                // Update camera list
                const devices = await navigator.mediaDevices.enumerateDevices();
                const videoDevices = devices.filter(device => device.kind === 'videoinput');
                
                const cameraSelect = document.getElementById('camera-select');
                cameraSelect.innerHTML = '<option value="">Select Camera</option>';
                
                videoDevices.forEach((device, index) => {
                    const option = document.createElement('option');
                    option.value = device.deviceId;
                    option.text = device.label || `Camera ${index + 1}`;
                    cameraSelect.appendChild(option);
                });

                if (videoDevices.length === 1) {
                    cameraSelect.value = videoDevices[0].deviceId;
                }

                document.getElementById('error').classList.add('hidden');
            } catch (error) {
                updateScannerStatus('Permission Denied');
                document.getElementById('error').classList.remove('hidden');
                document.getElementById('error').innerText = 
                    `Permission Error: ${error.message}. Please check your browser settings.`;
            }
        }

        // Start QR scanner with optimized settings
        async function startScanner() {
            if (!hasPermission) {
                document.getElementById('error').classList.remove('hidden');
                document.getElementById('error').innerText = 'Please request camera permission first.';
                return;
            }

            const cameraId = document.getElementById('camera-select').value;
            if (!cameraId) {
                document.getElementById('error').classList.remove('hidden');
                document.getElementById('error').innerText = 'Please select a camera first.';
                return;
            }

            try {
                // Stop any existing scanner
                if (html5QrcodeScanner) {
                    await html5QrcodeScanner.stop();
                }

                // Create new scanner instance
                const html5Qrcode = new Html5Qrcode("reader");
                html5QrcodeScanner = html5Qrcode;

                const config = {
                    fps: 30,
                    qrbox: { width: 300, height: 300 },
                    aspectRatio: 1.0,
                    formatsToSupport: [ Html5QrcodeSupportedFormats.QR_CODE ],
                    videoConstraints: {
                        deviceId: cameraId,
                        width: { min: 640, ideal: 1080, max: 1920 },
                        height: { min: 480, ideal: 720, max: 1080 },
                        facingMode: "environment",
                        focusMode: "continuous"
                    }
                };

                // Start scanning
                await html5Qrcode.start(
                    { deviceId: cameraId },
                    config,
                    onScanSuccess,
                    onScanFailure
                );

                updateScannerStatus('Scanning');
                document.getElementById('error').classList.add('hidden');
                document.getElementById('result').innerHTML = 'Scanner ready. Position QR code in the frame.';
            } catch (error) {
                document.getElementById('error').classList.remove('hidden');
                document.getElementById('error').innerText = `Scanner Error: ${error.message}`;
            }
        }

        // Stop scanner gracefully
        async function stopScanner() {
            try {
                if (html5QrcodeScanner) {
                    await html5QrcodeScanner.stop();
                    document.getElementById('reader').innerHTML = '';
                    document.getElementById('result').innerHTML = 'Scanner stopped';
                    document.getElementById('error').classList.add('hidden');
                    updateScannerStatus('Ready');
                }
            } catch (error) {
                document.getElementById('error').classList.remove('hidden');
                document.getElementById('error').innerText = `Stop Error: ${error.message}`;
            }
        }

        // Handle successful QR code scan
        function onScanSuccess(qrCodeMessage) {
            // Basic validation of QR code format (221-XXXX-VALID)
            if (!qrCodeMessage.match(/^221-\d{4}-VALID$/)) {
                return; // Silently ignore invalid formats
            }

            // Pause scanner to prevent duplicate scans
            if (html5QrcodeScanner) {
                html5QrcodeScanner.pause();
            }

            // Play success sound
            const audio = new Audio('/sounds/beep.mp3');
            audio.play().catch(e => console.log('Audio play failed:', e));

            // Show loading state
            document.getElementById('result').innerHTML = `
                <div class="p-4 bg-blue-50 text-blue-800 rounded-lg">
                    <div class="flex items-center">
                        <svg class="animate-spin h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Processing scan...
                    </div>
                </div>
            `;

            // Send QR code data to server
            fetch('/student-scan', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ qr_data: qrCodeMessage })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update statistics
                    updateStatistics(true);
                    
                    // Show success message with student info
                    document.getElementById('result').innerHTML = `
                        <div class="p-4 bg-green-50 text-green-800 rounded-lg">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span class="font-medium">Success!</span>
                            </div>
                            <div class="mt-2">
                                <p class="text-sm font-medium">Student Information:</p>
                                <ul class="mt-1 text-sm">
                                    <li>Name: ${data.data.student.name}</li>
                                    <li>ID: ${data.data.student.student_id}</li>
                                    <li>Course: ${data.data.student.course}</li>
                                    <li>Year Level: ${data.data.student.year_level}</li>
                                </ul>
                                <p class="mt-2 text-sm text-green-600">
                                    ✓ Validated for ${data.data.semester} Semester, ${data.data.academic_year}
                                </p>
                            </div>
                        </div>
                    `;

                    // Add to scan history
                    addToScanHistory(data);

                    // Resume scanner after success
                    setTimeout(() => {
                        if (html5QrcodeScanner) {
                            html5QrcodeScanner.resume();
                        }
                    }, 1500);
                } else {
                    // Play error sound
                    const errorAudio = new Audio('/sounds/error.mp3');
                    errorAudio.play().catch(e => console.log('Audio play failed:', e));

                    // Show error message
                    document.getElementById('result').innerHTML = `
                        <div class="p-4 bg-red-50 text-red-800 rounded-lg">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                <span class="font-medium">Invalid QR Code</span>
                            </div>
                            <div class="mt-2 text-sm">
                                ${data.message}
                            </div>
                        </div>
                    `;

                    // Update statistics
                    updateStatistics(false);

                    // Resume scanner after error
                    setTimeout(() => {
                        if (html5QrcodeScanner) {
                            html5QrcodeScanner.resume();
                        }
                    }, 1000);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('result').innerHTML = `
                    <div class="p-4 bg-red-50 text-red-800 rounded-lg">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span class="font-medium">Error</span>
                        </div>
                        <div class="mt-2 text-sm">
                            Failed to process QR code. Please try again.
                        </div>
                    </div>
                `;

                // Resume scanner after error
                setTimeout(() => {
                    if (html5QrcodeScanner) {
                        html5QrcodeScanner.resume();
                    }
                }, 1000);
            });
        }

        function onScanFailure(error) {
            // Silent failure - don't show errors for failed scans
            return;
        }

        // Update scanner status with visual feedback
        function updateScannerStatus(status) {
            const statusBadge = document.getElementById('scanner-status');
            const connectionStatus = document.getElementById('connection-status');
            
            let color = 'bg-yellow-500';
            if (status === 'Scanning') color = 'bg-green-500';
            else if (status === 'Error' || status === 'Permission Denied') color = 'bg-red-500';
            
            statusBadge.innerHTML = `
                <span class="w-2 h-2 ${color} rounded-full mr-2"></span>
                ${status}
            `;
            
            connectionStatus.innerHTML = `
                <span class="w-2 h-2 ${color} rounded-full mr-2"></span>
                ${status}
            `;
        }

        // Statistics functions
        let todayScans = 0;
        let successfulScans = 0;
        let failedScans = 0;

        function updateStatistics(success = true) {
            todayScans++;
            if (success) {
                successfulScans++;
            } else {
                failedScans++;
            }
            
            document.getElementById('today-scans').textContent = todayScans;
            document.getElementById('successful-scans').textContent = successfulScans;
            document.getElementById('failed-scans').textContent = failedScans;
        }

        // Add to scan history
        function addToScanHistory(scanData) {
            if (!scanData.success) return;

            const scanHistory = document.getElementById('scan-history');
            const validatedStudents = document.getElementById('validated-students');
            
            // Add to scan history
            const historyEntry = document.createElement('div');
            historyEntry.className = 'p-4 bg-white rounded-lg shadow mb-3';
            historyEntry.innerHTML = `
                <div class="flex justify-between items-start">
                    <div class="flex-grow">
                        <div class="flex items-center">
                            <div class="font-medium text-gray-900">${scanData.data.student.name}</div>
                            <span class="ml-2 px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full">Validated</span>
                        </div>
                        <div class="text-sm text-gray-600 mt-1">ID: ${scanData.data.student.student_id}</div>
                        <div class="text-sm text-gray-600">Course: ${scanData.data.student.course}</div>
                        <div class="text-sm text-gray-600">Year Level: ${scanData.data.student.year_level}</div>
                        <div class="mt-2 text-xs text-gray-500">
                            Scanned on ${new Date().toLocaleString()}
                        </div>
                    </div>
                </div>
            `;
            
            scanHistory.insertBefore(historyEntry, scanHistory.firstChild);

            // Add to validated students if not already present
            const studentId = scanData.data.student.student_id;
            if (!document.querySelector(`[data-student-id="${studentId}"]`)) {
                const validationEntry = document.createElement('div');
                validationEntry.className = 'p-4 bg-green-50 rounded-lg border border-green-200 mb-3';
                validationEntry.setAttribute('data-student-id', studentId);
                validationEntry.innerHTML = `
                    <div class="flex justify-between items-start">
                        <div class="flex-grow">
                            <div class="flex items-center">
                                <div class="font-medium text-gray-900 text-lg">${scanData.data.student.name}</div>
                                <span class="ml-2 px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full">Validated</span>
                            </div>
                            <div class="text-sm text-gray-600 mt-1">ID: ${scanData.data.student.student_id}</div>
                            <div class="text-sm text-gray-600">Course: ${scanData.data.student.course}</div>
                            <div class="text-sm text-gray-600">Year Level: ${scanData.data.student.year_level}</div>
                            <div class="mt-2 text-sm text-green-600">
                                ✓ Validated for ${scanData.data.semester} Semester, ${scanData.data.academic_year}
                            </div>
                            <div class="text-xs text-gray-500 mt-1">
                                Validated on ${new Date().toLocaleString()}
                            </div>
                        </div>
                    </div>
                `;
                
                validatedStudents.insertBefore(validationEntry, validatedStudents.firstChild);
            }
        }

        // Load scan history
        function loadScanHistory() {
            fetch('/student-scan/history')
                .then(response => response.json())
                .then(data => {
                    const scanHistory = document.getElementById('scan-history');
                    const validatedStudents = document.getElementById('validated-students');
                    
                    // Clear existing entries
                    scanHistory.innerHTML = '';
                    validatedStudents.innerHTML = '';
                    
                    // Add each scan to history
                    data.forEach(scan => {
                        addToScanHistory({
                            success: true,
                            data: scan
                        });
                    });
                })
                .catch(error => {
                    console.error('Error loading scan history:', error);
                    document.getElementById('scan-history').innerHTML = `
                        <div class="p-4 bg-red-50 text-red-800 rounded-lg">
                            Error loading scan history. Please refresh the page.
                        </div>
                    `;
                });
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            if (location.protocol !== 'https:' && location.hostname !== 'localhost') {
                document.getElementById('error').classList.remove('hidden');
                document.getElementById('error').innerText = 'Warning: Camera access requires HTTPS or localhost';
            }
            updateScannerStatus('Ready');
            loadScanHistory();
        });
    </script>
</body>
</html>
