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
                        {{ __('QR Scanner Dashboard') }}
                    </h2>
                    <div class="status-badge" id="connection-status">
                        <span class="w-2 h-2 bg-green-500 rounded-full mr-2"></span>
                        Ready to Scan
                    </div>
                </div>
            </div>
        </header>

        <!-- Page Content -->
        <main class="py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
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

                    <!-- Scan History Section -->
                    <div class="scanner-container p-6">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-lg font-semibold text-gray-900">Recent Scans</h3>
                            <button onclick="clearHistory()" class="text-sm text-gray-500 hover:text-gray-700">
                                Clear History
                            </button>
                        </div>
                        <div id="scan-history" class="space-y-4 max-h-[600px] overflow-y-auto pr-2">
                            <!-- Scan history will be populated here -->
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
            console.log(`Code matched = ${decodedText}`, decodedResult);
            
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
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('result').innerHTML = `
                        <div class="p-4 bg-green-100 text-green-800 rounded-lg">
                            <div class="font-medium">${data.data.student.name}</div>
                            <div class="text-sm">ID: ${data.data.student.student_id}</div>
                            <div class="text-sm">${data.data.student.course} - ${data.data.student.year_level} Year</div>
                        </div>
                    `;
                    addToScanHistory(data);
                } else {
                    document.getElementById('result').innerHTML = `
                        <div class="p-4 bg-red-100 text-red-800 rounded-lg">
                            Invalid QR Code
                        </div>
                    `;
                }
            })
            .catch(error => {
                document.getElementById('error').innerText = `Failed to process scan: ${error.message}`;
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

        // Add scan to history
        function addToScanHistory(scanData) {
            const historyDiv = document.getElementById('scan-history');
            const scanElement = document.createElement('div');
            scanElement.className = 'scan-history-item';
            
            const studentInfo = scanData.data.student;
            
            scanElement.innerHTML = `
                <div class="flex justify-between items-start">
                    <div>
                        <div class="text-sm font-medium text-gray-900">
                            ${studentInfo.name}
                        </div>
                        <div class="text-xs text-gray-600">
                            ID: ${studentInfo.student_id}
                        </div>
                        <div class="text-xs text-gray-500 mt-1">
                            ${new Date().toLocaleString()}
                        </div>
                    </div>
                    <div class="status-badge">
                        Validated
                    </div>
                </div>
            `;
            
            historyDiv.insertBefore(scanElement, historyDiv.firstChild);
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
        });

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
    </script>
</body>
</html>
