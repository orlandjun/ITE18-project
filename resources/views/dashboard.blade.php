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
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <!-- Scanner Section -->
                        <div class="scanner-container p-6">
                            <div class="flex items-center justify-between mb-6">
                                <h3 class="text-lg font-semibold text-gray-900">QR Scanner</h3>
                                <div id="scanner-status" class="text-sm text-gray-500"></div>
                            </div>

                            <div class="controls space-y-4">
                                <select id="camera-select" class="modern-select w-full rounded-lg border-gray-300 focus:border-green-500 focus:ring focus:ring-green-200">
                                    <option value="">Select Camera</option>
                                </select>

                                <div class="button-group">
                                    <button onclick="requestCameraPermission()" class="modern-button bg-green-500 text-white hover:bg-green-600">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                        </svg>
                                        Request Permission
                                    </button>

                                    <button onclick="testCamera()" class="modern-button bg-yellow-500 text-white hover:bg-yellow-600">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                        </svg>
                                        Test Camera
                                    </button>

                                    <button onclick="startScanner()" class="modern-button bg-green-500 text-white hover:bg-green-600">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                        </svg>
                                        Start Scanner
                                    </button>

                                    <button onclick="stopScanner()" class="modern-button bg-red-500 text-white hover:bg-red-600">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 10a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z" />
                                        </svg>
                                        Stop Scanner
                                    </button>
                                </div>
                            </div>

                            <div id="error" class="error hidden"></div>
                            <div id="reader" class="mt-6"></div>
                            <div id="result" class="mt-6">No QR Code scanned yet</div>
                            <div id="debug" class="debug-info"></div>
                        </div>

                        <!-- Validated Students Box -->
                        <div class="border rounded-lg p-4 bg-white shadow">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-semibold">Validated Students</h3>
                                <div class="flex space-x-2">
                                    <button onclick="toggleScanHistory()" class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600 flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                        View Scan History
                                    </button>
                                    <button onclick="clearValidatedStudents()" class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">
                                        Clear List
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Validated Students List -->
                            <div id="validated-students" class="space-y-2 max-h-[200px] overflow-y-auto">
                                <!-- Validated students will be displayed here -->
                            </div>

                            <!-- Scan History (Hidden by default) -->
                            <div id="scan-history" class="hidden space-y-2 mt-4 border-t pt-4">
                                <div class="flex justify-between items-center mb-2">
                                    <h4 class="font-medium text-gray-700">Scan History</h4>
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
        let html5QrcodeScanner = null;

        // Debug function
        function updateDebugInfo(message) {
            const debug = document.getElementById('debug');
            debug.innerHTML += `<div>${new Date().toLocaleTimeString()}: ${message}</div>`;
            debug.scrollTop = debug.scrollHeight;
        }

        // Request camera permission explicitly
        async function requestCameraPermission() {
            try {
                updateDebugInfo('Requesting camera permission...');
                const stream = await navigator.mediaDevices.getUserMedia({ 
                    video: true,
                    audio: false
                });
                updateDebugInfo('Camera permission granted!');
                stream.getTracks().forEach(track => track.stop());
                testCamera(); // Refresh camera list after getting permission
            } catch (error) {
                updateDebugInfo(`Permission error: ${error.message}`);
                document.getElementById('error').innerText = 
                    `Permission Error: ${error.message}. Please check your browser settings.`;
            }
        }

        // Test camera access
        async function testCamera() {
            try {
                updateDebugInfo('Checking for cameras...');
                
                if (!navigator.mediaDevices || !navigator.mediaDevices.enumerateDevices) {
                    throw new Error('mediaDevices API not supported');
                }

                await navigator.mediaDevices.getUserMedia({ video: true });
                
                const devices = await navigator.mediaDevices.enumerateDevices();
                updateDebugInfo(`Found ${devices.length} total devices`);
                
                const videoDevices = devices.filter(device => device.kind === 'videoinput');
                updateDebugInfo(`Found ${videoDevices.length} video devices`);
                
                const cameraSelect = document.getElementById('camera-select');
                cameraSelect.innerHTML = '<option value="">Select Camera</option>';
                
                videoDevices.forEach((device, index) => {
                    updateDebugInfo(`Camera ${index + 1}: ${device.label || 'unnamed device'}`);
                    const option = document.createElement('option');
                    option.value = device.deviceId;
                    option.text = device.label || `Camera ${index + 1}`;
                    cameraSelect.appendChild(option);
                });

                if (videoDevices.length === 0) {
                    throw new Error('No cameras found');
                }

                document.getElementById('error').innerText = 'Camera access successful!';
                document.getElementById('result').innerText = 
                    `Found ${videoDevices.length} camera(s). Please select one from the dropdown.`;
                
            } catch (error) {
                updateDebugInfo(`Error: ${error.message}`);
                document.getElementById('error').innerText = 
                    `Camera Error: ${error.message}\n\n` +
                    'Please ensure:\n' +
                    '1. Your camera is connected\n' +
                    '2. You\'ve granted camera permissions\n' +
                    '3. No other app is using the camera\n' +
                    '4. You\'re using HTTPS or localhost';
            }
        }

        function onScanSuccess(decodedText, decodedResult) {
            console.log(`QR Code detected: ${decodedText}`);
            
            // Show loading state
            document.getElementById('result').innerHTML = `
                <div class="p-4 bg-yellow-100 text-yellow-800 rounded-lg">
                    <div class="flex items-center">
                        <svg class="animate-spin h-5 w-5 mr-3 text-yellow-800" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Validating Student ID...
                    </div>
                </div>
            `;

            // Basic validation of QR code format
            if (!decodedText.match(/^221-\d{4}-VALID$/)) {
                document.getElementById('result').innerHTML = `
                    <div class="p-4 bg-red-100 text-red-800 rounded-lg">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="font-medium">Invalid QR Code Format</span>
                        </div>
                        <div class="mt-2 text-sm">
                            Expected format: 221-XXXX-VALID (e.g., 221-2021-VALID)
                        </div>
                    </div>
                `;
                return;
            }
            
            // Send QR data to server
            fetch('/student-scan', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    qr_data: decodedText
                })
            })
            .then(response => {
                if (!response.ok) {
                    if (response.status === 404) {
                        throw new Error('Invalid QR code or student not found');
                    }
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Play success sound
                    const audio = new Audio('/sounds/success.mp3');
                    audio.play().catch(e => console.log('Audio play failed:', e));

                    // Show success message with student details
                    document.getElementById('result').innerHTML = `
                        <div class="p-4 bg-green-100 text-green-800 rounded-lg">
                            <div class="flex items-center mb-2">
                                <svg class="w-5 h-5 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span class="font-medium">Validation Successful</span>
                            </div>
                            <div class="mt-2">
                                <div class="font-medium text-lg">${data.data.student.name}</div>
                                <div class="text-sm">Student ID: ${data.data.student.student_id}</div>
                                <div class="text-sm">Course: ${data.data.student.course}</div>
                                <div class="text-sm">Year Level: ${data.data.student.year_level}</div>
                                <div class="text-xs mt-2 text-green-600">
                                    Validated at ${new Date().toLocaleString()}
                                </div>
                            </div>
                        </div>
                    `;

                    // Add to scan history
                    addToScanHistory(data);

                    // Update status badge
                    updateScannerStatus('Ready');
                } else {
                    // Play error sound
                    const audio = new Audio('/sounds/error.mp3');
                    audio.play().catch(e => console.log('Audio play failed:', e));

                    // Show error message
                    document.getElementById('result').innerHTML = `
                        <div class="p-4 bg-red-100 text-red-800 rounded-lg">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span class="font-medium">Validation Failed</span>
                            </div>
                            <div class="mt-2 text-sm">
                                ${data.message}
                            </div>
                        </div>
                    `;

                    // Update status badge
                    updateScannerStatus('Ready');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                // Show error message
                document.getElementById('result').innerHTML = `
                    <div class="p-4 bg-red-100 text-red-800 rounded-lg">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="font-medium">Error</span>
                        </div>
                        <div class="mt-2 text-sm">
                            ${error.message}
                        </div>
                    </div>
                `;

                // Update status badge
                updateScannerStatus('Error');
            });
        }

        function onScanFailure(error) {
            // console.warn(`Code scan error = ${error}`);
        }

        async function startScanner() {
            const cameraId = document.getElementById('camera-select').value;
            if (!cameraId) {
                document.getElementById('error').innerText = 'Please select a camera first!';
                return;
            }

            try {
                if (html5QrcodeScanner) {
                    await html5QrcodeScanner.clear();
                }

                html5QrcodeScanner = new Html5QrcodeScanner(
                    "reader", 
                    { 
                        fps: 10,
                        qrbox: { width: 250, height: 250 },
                        videoConstraints: {
                            deviceId: cameraId
                        }
                    }
                );
                html5QrcodeScanner.render(onScanSuccess, onScanFailure);
                document.getElementById('error').innerText = '';
            } catch (error) {
                document.getElementById('error').innerText = `Scanner Error: ${error.message}`;
                console.error('Scanner Error:', error);
            }
        }

        async function stopScanner() {
            if (html5QrcodeScanner) {
                try {
                    await html5QrcodeScanner.clear();
                    document.getElementById('reader').innerHTML = '';
                    document.getElementById('result').innerHTML = 'Scanner stopped';
                    document.getElementById('error').innerText = '';
                } catch (error) {
                    document.getElementById('error').innerText = `Stop Error: ${error.message}`;
                }
            }
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateDebugInfo('Page loaded');
            // Check if running on HTTPS or localhost
            if (location.protocol !== 'https:' && location.hostname !== 'localhost') {
                updateDebugInfo('WARNING: Camera API requires HTTPS or localhost');
                document.getElementById('error').innerText = 
                    'Warning: Camera access requires HTTPS or localhost';
            }
            requestCameraPermission();
            
            // Load scan history
            loadScanHistory();
        });

        // Add this new function for loading scan history
        function loadScanHistory() {
            fetch('/student-scan/history')
                .then(response => response.json())
                .then(scans => {
                    const scanHistory = document.getElementById('scan-history');
                    scanHistory.innerHTML = ''; // Clear existing history
                    
                    scans.forEach(scan => {
                        addToScanHistory(scan, false); // false means don't check for duplicates
                    });
                })
                .catch(error => {
                    console.error('Error loading scan history:', error);
                });
        }

        // Modified addToScanHistory function
        function addToScanHistory(scanData, checkDuplicate = true) {
            const scanHistory = document.getElementById('scan-history');
            
            // Check for duplicates only when adding new scans
            if (checkDuplicate) {
                const existingEntry = scanHistory.querySelector(`[data-student-id="${scanData.data.student.student_id}"]`);
                
                if (existingEntry) {
                    // If it's a duplicate validation message, just update the status
                    if (!scanData.success && scanData.message.includes('already validated')) {
                        existingEntry.querySelector('.scan-status').innerHTML = `
                            <span class="text-yellow-600 font-medium">
                                ⚠️ Already validated for ${scanData.data.semester} semester, ${scanData.data.academic_year}
                            </span>`;
                        
                        // Briefly highlight the existing entry
                        existingEntry.classList.add('bg-yellow-50');
                        setTimeout(() => {
                            existingEntry.classList.remove('bg-yellow-50');
                        }, 2000);
                        
                        return;
                    }
                }
            }

            // Create new entry
            const scanElement = document.createElement('div');
            scanElement.className = 'scan-history-item';
            scanElement.setAttribute('data-student-id', scanData.data.student.student_id);
            
            scanElement.innerHTML = `
                <div class="flex justify-between items-start">
                    <div>
                        <div class="font-medium text-gray-900">
                            ${scanData.data.student.name}
                        </div>
                        <div class="text-sm text-gray-500">
                            ID: ${scanData.data.student.student_id}
                        </div>
                        <div class="text-sm text-gray-500">
                            Course: ${scanData.data.student.course}
                        </div>
                        <div class="text-sm text-gray-500">
                            Year Level: ${scanData.data.student.year_level}
                        </div>
                        <div class="text-sm text-gray-500">
                            Scanned: ${new Date(scanData.data.scan.created_at).toLocaleString()}
                        </div>
                        <div class="scan-status mt-2">
                            <span class="text-green-600 font-medium">
                                ✓ Validated for ${scanData.data.semester} semester, ${scanData.data.academic_year}
                            </span>
                        </div>
                    </div>
                    <div class="text-xs text-gray-500">
                        ${scanData.data.scan.status.toUpperCase()}
                    </div>
                </div>
            `;
            
            // Add to the top of the history
            if (scanHistory.firstChild) {
                scanHistory.insertBefore(scanElement, scanHistory.firstChild);
            } else {
                scanHistory.appendChild(scanElement);
            }
        }

        // Add this new function for clearing history
        function clearHistory() {
            const historyDiv = document.getElementById('scan-history');
            historyDiv.innerHTML = '';
        }

        // Update the error display function
        function updateError(message) {
            const errorDiv = document.getElementById('error');
            if (message) {
                errorDiv.textContent = message;
                errorDiv.classList.remove('hidden');
            } else {
                errorDiv.classList.add('hidden');
            }
        }

        // Update scanner status
        function updateScannerStatus(status) {
            document.getElementById('scanner-status').textContent = status;
            document.getElementById('connection-status').innerHTML = `
                <span class="w-2 h-2 ${status === 'Scanning' ? 'bg-green-500' : 'bg-yellow-500'} rounded-full mr-2"></span>
                ${status}
            `;
        }

        // Call this when starting/stopping the scanner
        function onScannerStateChange(isScanning) {
            updateScannerStatus(isScanning ? 'Scanning' : 'Ready');
        }

        // Admin functionality
        function toggleView(view) {
            document.getElementById('scanner-view').style.display = view === 'scanner' ? 'block' : 'none';
            document.getElementById('admin-view').style.display = view === 'admin' ? 'block' : 'none';
        }

        function switchTab(tabId) {
            // Hide all content
            document.querySelectorAll('.admin-content').forEach(content => {
                content.classList.remove('active');
            });
            document.querySelectorAll('.admin-tab').forEach(tab => {
                tab.classList.remove('active');
            });

            // Show selected content
            document.getElementById(tabId).classList.add('active');
            event.target.classList.add('active');
        }

        // Initialize charts
        document.addEventListener('DOMContentLoaded', function() {
            // Validation Trends Chart
            const validationCtx = document.getElementById('validationChart').getContext('2d');
            new Chart(validationCtx, {
                type: 'line',
                data: {
                    labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                    datasets: [{
                        label: 'Validations',
                        data: [65, 59, 80, 81, 56, 55, 40],
                        borderColor: '#10B981',
                        tension: 0.1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Weekly Validation Trends'
                        }
                    }
                }
            });

            // Hourly Activity Chart
            const hourlyCtx = document.getElementById('hourlyChart').getContext('2d');
            new Chart(hourlyCtx, {
                type: 'bar',
                data: {
                    labels: ['8AM', '9AM', '10AM', '11AM', '12PM', '1PM', '2PM', '3PM', '4PM', '5PM'],
                    datasets: [{
                        label: 'Validations per Hour',
                        data: [12, 19, 15, 25, 22, 30, 28, 20, 15, 10],
                        backgroundColor: '#10B981'
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Hourly Validation Activity'
                        }
                    }
                }
            });
        });

        // Bulk Operations Functions
        function importData() {
            const fileInput = document.getElementById('file-upload');
            if (fileInput.files.length > 0) {
                // Handle file upload
                const formData = new FormData();
                formData.append('file', fileInput.files[0]);
                
                fetch('/api/import-students', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    alert('Import successful!');
                })
                .catch(error => {
                    alert('Import failed: ' + error.message);
                });
            }
        }

        function exportData() {
            window.location.href = '/api/export-students';
        }

        function generateReport() {
            // Implement report generation logic
            alert('Generating report...');
        }

        // File upload handling
        const dropZone = document.querySelector('.file-drop-zone');
        
        dropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropZone.classList.add('bg-green-50');
        });

        dropZone.addEventListener('dragleave', () => {
            dropZone.classList.remove('bg-green-50');
        });

        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.classList.remove('bg-green-50');
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                document.getElementById('file-upload').files = files;
            }
        });

        // Add these new functions
        function toggleScanHistory() {
            const scanHistory = document.getElementById('scan-history');
            if (scanHistory.classList.contains('hidden')) {
                loadScanHistory();
                scanHistory.classList.remove('hidden');
            } else {
                scanHistory.classList.add('hidden');
            }
        }

        function clearValidatedStudents() {
            const validatedStudents = document.getElementById('validated-students');
            validatedStudents.innerHTML = '';
            localStorage.removeItem('validatedStudents');
        }

        function refreshScanHistory() {
            loadScanHistory();
        }

        // Modified addToScanHistory function
        function addToScanHistory(scanData, checkDuplicate = true) {
            const scanHistory = document.getElementById('scan-history');
            const validatedStudents = document.getElementById('validated-students');
            
            if (checkDuplicate) {
                // Check for existing validation in localStorage
                const validatedList = JSON.parse(localStorage.getItem('validatedStudents') || '[]');
                const existingValidation = validatedList.find(v => v.student_id === scanData.data.student.student_id);
                
                if (!existingValidation) {
                    // Add to validated students list
                    const validationElement = document.createElement('div');
                    validationElement.className = 'p-3 bg-green-50 rounded-lg border border-green-200';
                    validationElement.innerHTML = `
                        <div class="flex justify-between items-start">
                            <div>
                                <div class="font-medium text-gray-900">${scanData.data.student.name}</div>
                                <div class="text-sm text-gray-500">ID: ${scanData.data.student.student_id}</div>
                                <div class="text-sm text-gray-500">Course: ${scanData.data.student.course}</div>
                                <div class="text-xs text-green-600 mt-1">✓ Validated</div>
                            </div>
                            <div class="text-xs text-gray-500">
                                ${new Date().toLocaleTimeString()}
                            </div>
                        </div>
                    `;
                    
                    if (validatedStudents.firstChild) {
                        validatedStudents.insertBefore(validationElement, validatedStudents.firstChild);
                    } else {
                        validatedStudents.appendChild(validationElement);
                    }

                    // Update localStorage
                    validatedList.push({
                        student_id: scanData.data.student.student_id,
                        name: scanData.data.student.name,
                        timestamp: new Date().toISOString()
                    });
                    localStorage.setItem('validatedStudents', JSON.stringify(validatedList));
                }
            }

            // Add to scan history if visible
            if (!scanHistory.classList.contains('hidden')) {
                const scanElement = document.createElement('div');
                scanElement.className = 'scan-history-item';
                scanElement.setAttribute('data-student-id', scanData.data.student.student_id);
                
                scanElement.innerHTML = `
                    <div class="flex justify-between items-start p-3 bg-white rounded-lg border border-gray-200">
                        <div>
                            <div class="font-medium text-gray-900">${scanData.data.student.name}</div>
                            <div class="text-sm text-gray-500">ID: ${scanData.data.student.student_id}</div>
                            <div class="text-sm text-gray-500">Course: ${scanData.data.student.course}</div>
                            <div class="text-sm text-gray-500">Scanned: ${new Date(scanData.data.scan.created_at).toLocaleString()}</div>
                        </div>
                        <div class="text-xs text-gray-500">${scanData.data.scan.status.toUpperCase()}</div>
                    </div>
                `;
                
                if (scanHistory.firstChild) {
                    scanHistory.insertBefore(scanElement, scanHistory.firstChild);
                } else {
                    scanHistory.appendChild(scanElement);
                }
            }
        }

        // Load validated students from localStorage on page load
        document.addEventListener('DOMContentLoaded', function() {
            const validatedList = JSON.parse(localStorage.getItem('validatedStudents') || '[]');
            const validatedStudents = document.getElementById('validated-students');
            
            validatedList.forEach(validation => {
                const validationElement = document.createElement('div');
                validationElement.className = 'p-3 bg-green-50 rounded-lg border border-green-200';
                validationElement.innerHTML = `
                    <div class="flex justify-between items-start">
                        <div>
                            <div class="font-medium text-gray-900">${validation.name}</div>
                            <div class="text-sm text-gray-500">ID: ${validation.student_id}</div>
                            <div class="text-xs text-green-600 mt-1">✓ Validated</div>
                        </div>
                        <div class="text-xs text-gray-500">
                            ${new Date(validation.timestamp).toLocaleTimeString()}
                        </div>
                    </div>
                `;
                validatedStudents.appendChild(validationElement);
            });
        });
    </script>
</body>
</html>
