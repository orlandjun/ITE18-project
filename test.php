<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Scanner Test</title>
    <script src="https://unpkg.com/html5-qrcode"></script>
    <style>
        #reader {
            width: 600px;
            margin: 20px auto;
        }
        #result {
            text-align: center;
            margin: 20px;
            padding: 10px;
            background: #f0f0f0;
        }
        .controls {
            text-align: center;
            margin: 20px;
        }
        button {
            padding: 10px 20px;
            margin: 0 10px;
            cursor: pointer;
        }
        #camera-select {
            margin: 20px;
            padding: 5px;
        }
        .error {
            color: red;
            margin: 10px;
            text-align: center;
        }
        .debug-info {
            margin: 20px;
            padding: 10px;
            background: #f8f8f8;
            border: 1px solid #ddd;
        }
    </style>
</head>
<body>
    <div class="controls">
        <select id="camera-select">
            <option value="">Select Camera</option>
        </select>
        <button onclick="requestCameraPermission()">Request Permission</button>
        <button onclick="testCamera()">Test Camera Access</button>
        <button onclick="startScanner()">Start Scanner</button>
        <button onclick="stopScanner()">Stop Scanner</button>
    </div>
    <div id="error" class="error"></div>
    <div id="reader"></div>
    <div id="result">No QR Code scanned yet</div>
    <div id="debug" class="debug-info"></div>

    <script>
        let html5QrcodeScanner = null;

        // Debug function
        function updateDebugInfo(message) {
            const debug = document.getElementById('debug');
            debug.innerHTML += `<div>${new Date().toLocaleTimeString()}: ${message}</div>`;
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
                
                // First, check if mediaDevices is supported
                if (!navigator.mediaDevices || !navigator.mediaDevices.enumerateDevices) {
                    throw new Error('mediaDevices API not supported');
                }

                // Request permission first
                await navigator.mediaDevices.getUserMedia({ video: true });
                
                const devices = await navigator.mediaDevices.enumerateDevices();
                updateDebugInfo(`Found ${devices.length} total devices`);
                
                const videoDevices = devices.filter(device => device.kind === 'videoinput');
                updateDebugInfo(`Found ${videoDevices.length} video devices`);
                
                // Clear and populate camera select
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
                    '2. You\'ve granted camera permissions (check browser settings)\n' +
                    '3. No other app is using the camera\n' +
                    '4. You\'re using HTTPS or localhost';
            }
        }

        function onScanSuccess(decodedText, decodedResult) {
            console.log(`Code matched = ${decodedText}`, decodedResult);
            document.getElementById('result').innerHTML = `
                QR Code detected: ${decodedText}
            `;
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

        // On page load
        window.onload = function() {
            updateDebugInfo('Page loaded');
            // Check if running on HTTPS or localhost
            if (location.protocol !== 'https:' && location.hostname !== 'localhost') {
                updateDebugInfo('WARNING: Camera API requires HTTPS or localhost');
                document.getElementById('error').innerText = 
                    'Warning: Camera access requires HTTPS or localhost';
            }
            requestCameraPermission();
        };
    </script>
</body>
</html>