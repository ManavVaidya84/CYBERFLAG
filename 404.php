<?php
http_response_code(404);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 :: ACCESS DENIED - CYBERFLAG</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'cyber-green': '#00ff41',
                        'cyber-blue': '#00d4ff',
                        'cyber-red': '#ff073a',
                        'dark-bg': '#0a0a0a',
                        'dark-panel': '#111827',
                    },
                    fontFamily: {
                        'mono': ['JetBrains Mono', 'Consolas', 'monospace'],
                        'cyber': ['Orbitron', 'sans-serif'],
                    },
                    animation: {
                        'glow-pulse': 'glow-pulse 2s ease-in-out infinite',
                        'matrix-rain': 'matrix-rain 20s linear infinite',
                        'scan-line': 'scan-line 3s ease-in-out infinite',
                        'cyber-blink': 'cyber-blink 1.5s ease-in-out infinite',
                        'glitch': 'glitch 0.5s ease-in-out infinite alternate',
                        'error-flash': 'error-flash 2s ease-in-out infinite',
                    }
                }
            }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600;700&family=Orbitron:wght@400;500;700;900&display=swap" rel="stylesheet">
    <style>
        @keyframes glow-pulse {
            0%, 100% { box-shadow: 0 0 20px rgba(255, 7, 58, 0.3); }
            50% { box-shadow: 0 0 40px rgba(255, 7, 58, 0.6); }
        }
        
        @keyframes matrix-rain {
            0% { transform: translateY(-100vh) translateX(0); }
            100% { transform: translateY(100vh) translateX(20px); }
        }
        
        @keyframes scan-line {
            0% { transform: translateX(-100%); opacity: 0; }
            50% { opacity: 1; }
            100% { transform: translateX(100%); opacity: 0; }
        }
        
        @keyframes cyber-blink {
            0%, 50%, 100% { opacity: 1; }
            25%, 75% { opacity: 0.3; }
        }
        
        @keyframes glitch {
            0% { transform: translate(0); }
            20% { transform: translate(-2px, 2px); }
            40% { transform: translate(-2px, -2px); }
            60% { transform: translate(2px, 2px); }
            80% { transform: translate(2px, -2px); }
            100% { transform: translate(0); }
        }
        
        @keyframes error-flash {
            0%, 100% { background-color: rgba(255, 7, 58, 0.1); }
            50% { background-color: rgba(255, 7, 58, 0.3); }
        }
        
        .matrix-bg {
            background: 
                radial-gradient(circle at 20% 20%, rgba(255, 7, 58, 0.05) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(255, 0, 0, 0.03) 0%, transparent 50%),
                linear-gradient(135deg, #0a0a0a 0%, #1a0a0a 50%, #0a0a0a 100%);
        }
        
        .cyber-card {
            background: linear-gradient(135deg, rgba(17, 24, 39, 0.9) 0%, rgba(31, 20, 20, 0.8) 100%);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 7, 58, 0.3);
        }
        
        .cyber-btn {
            background: linear-gradient(135deg, rgba(255, 7, 58, 0.1) 0%, rgba(255, 0, 0, 0.1) 100%);
            border: 2px solid #ff073a;
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .cyber-btn:hover {
            background: linear-gradient(135deg, rgba(255, 7, 58, 0.2) 0%, rgba(255, 0, 0, 0.2) 100%);
            box-shadow: 0 0 30px rgba(255, 7, 58, 0.5);
            transform: translateY(-2px);
        }
        
        .cyber-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            transition: left 0.5s ease;
        }
        
        .cyber-btn:hover::before {
            left: 100%;
        }
        
        .status-indicator {
            position: relative;
        }
        
        .status-indicator::before {
            content: '';
            position: absolute;
            top: 50%;
            left: -15px;
            transform: translateY(-50%);
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #ff073a;
            animation: cyber-blink 2s infinite;
        }
        
        .scan-line-container {
            position: relative;
            overflow: hidden;
        }
        
        .scan-line-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 2px;
            background: linear-gradient(90deg, transparent, #ff073a, transparent);
            animation: scan-line 4s infinite;
        }
        
        .matrix-char {
            position: absolute;
            color: #ff073a;
            font-family: 'JetBrains Mono', monospace;
            font-size: 14px;
            opacity: 0.7;
            animation: matrix-rain linear infinite;
            pointer-events: none;
        }
        
        .glow-text {
            text-shadow: 0 0 10px currentColor;
        }
        
        .cyber-panel {
            background: linear-gradient(135deg, rgba(17, 24, 39, 0.95) 0%, rgba(31, 20, 20, 0.9) 100%);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 7, 58, 0.2);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }
        
        .error-code {
            font-size: 8rem;
            font-weight: 900;
            background: linear-gradient(45deg, #ff073a, #ff6b6b, #ff073a);
            background-size: 200% 200%;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            animation: glow-pulse 2s infinite, glitch 0.5s infinite;
        }
        
        .skull-ascii {
            color: #ff073a;
            text-shadow: 0 0 10px #ff073a;
            font-size: 0.7rem;
            line-height: 0.8;
        }
        
        .terminal-output {
            background: rgba(0, 0, 0, 0.8);
            border: 1px solid #ff073a;
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.9rem;
        }
        
        .error-flash {
            animation: error-flash 2s infinite;
        }
        
        .countdown {
            font-size: 2rem;
            color: #ff073a;
            text-shadow: 0 0 20px #ff073a;
        }
    </style>
</head>
<body class="matrix-bg min-h-screen text-white font-mono overflow-x-hidden">
    <!-- Matrix Rain Background -->
    <div id="matrix-container" class="fixed inset-0 pointer-events-none z-0"></div>
    
    <!-- Header -->
    <header class="relative z-10 border-b border-red-800 bg-black/70 backdrop-blur-sm">
        <div class="container mx-auto px-6 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div class="w-8 h-8 bg-cyber-red rounded-full animate-pulse"></div>
                    <h1 class="text-2xl font-cyber font-bold text-cyber-red glow-text">CYBERFLAG</h1>
                    <span class="text-xs text-gray-400 bg-red-900 px-2 py-1 rounded">ERROR</span>
                </div>
                <div class="text-right">
                    <div class="text-cyber-red font-mono text-sm" id="systemTime">00:00:00</div>
                    <div class="text-xs text-gray-400">SYSTEM TIME</div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="relative z-10 container mx-auto px-6 py-8">
        <div class="flex items-center justify-center min-h-[80vh]">
            <div class="cyber-card rounded-xl p-12 max-w-4xl mx-auto text-center animate-glow-pulse">
                <!-- ASCII Skull -->
                <div class="mb-8">
                    <pre class="skull-ascii">
        ⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⣀⣤⣤⣤⣤⣤⣤⣤⣤⣤⣤⣄⡀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀
        ⠀⠀⠀⠀⠀⠀⠀⠀⠀⢀⣴⣿⡿⠛⠉⠙⠛⠛⠛⠛⠛⠛⠛⠿⣿⣷⣄⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀
        ⠀⠀⠀⠀⠀⠀⠀⠀⣠⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣧⠀⠀⠀⠀⠀⠀⠀⠀⠀
        ⠀⠀⠀⠀⠀⠀⠀⣸⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣷⡀⠀⠀⠀⠀⠀⠀⠀
        ⠀⠀⠀⠀⠀⠀⢰⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⡀⠀⠀⠀⠀⠀⠀
        ⠀⠀⠀⠀⠀⠀⢸⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⡇⠀⠀⠀⠀⠀⠀
        ⠀⠀⠀⠀⠀⠀⢸⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⡇⠀⠀⠀⠀⠀⠀
        ⠀⠀⠀⠀⠀⠀⠘⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⠃⠀⠀⠀⠀⠀⠀
        ⠀⠀⠀⠀⠀⠀⠀⠙⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⠋⠀⠀⠀⠀⠀⠀⠀
        ⠀⠀⠀⠀⠀⠀⠀⠀⠀⠉⠻⠿⠿⠿⠿⠿⠿⠿⠿⠿⠿⠿⠿⠿⠿⠿⠟⠉⠀⠀⠀⠀⠀⠀⠀⠀⠀
                    </pre>
                </div>

                <!-- Error Code -->
                <div class="error-code mb-8">404</div>
                
                <!-- Error Message -->
                <div class="mb-8">
                    <h1 class="text-4xl font-cyber font-bold text-cyber-red glow-text mb-4">
                        ACCESS DENIED
                    </h1>
                    <h2 class="text-2xl text-gray-300 mb-6">
                        TARGET NOT FOUND
                    </h2>
                    <p class="text-gray-400 text-lg mb-4">
                        The system you are trying to infiltrate does not exist or has been secured.
                    </p>
                </div>

                <!-- Terminal Output -->
                <div class="terminal-output rounded-lg p-6 mb-8 text-left">
                    <div class="text-cyber-red mb-2">[ERROR] Connection failed</div>
                    <div class="text-yellow-400 mb-2">[WARNING] Unauthorized access attempt detected</div>
                    <div class="text-cyber-blue mb-2">[INFO] Initiating security protocols...</div>
                    <div class="text-gray-400 mb-2">[SYSTEM] Logging incident: <?php echo date('Y-m-d H:i:s'); ?></div>
                    <div class="text-cyber-red mb-2">[ALERT] IP Address: <?php echo $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN'; ?> flagged</div>
                    <div class="text-cyber-green">[STATUS] Redirecting to secure zone...</div>
                </div>

                <!-- Countdown Timer -->
                <div class="mb-8">
                    <div class="text-gray-400 mb-2">Auto-redirect in:</div>
                    <div class="countdown font-mono" id="countdown">10</div>
                    <div class="text-sm text-gray-500 mt-2">seconds</div>
                </div>

                <!-- Action Buttons -->
                <div class="grid md:grid-cols-2 gap-4 mb-8">
                    <a href="/" class="cyber-btn py-4 px-6 rounded-lg font-bold text-lg text-cyber-red relative z-10 block">
                        <span class="flex items-center justify-center space-x-3">
                            <span>🏠</span>
                            <span>RETURN TO BASE</span>
                        </span>
                    </a>
                    <a href="javascript:history.back()" class="cyber-btn py-4 px-6 rounded-lg font-bold text-lg text-cyber-red relative z-10 block">
                        <span class="flex items-center justify-center space-x-3">
                            <span>←</span>
                            <span>RETREAT</span>
                        </span>
                    </a>
                </div>

                <!-- System Status -->
                <div class="grid md:grid-cols-3 gap-4 text-sm">
                    <div class="error-flash rounded-lg p-4 border border-red-500">
                        <div class="status-indicator text-cyber-red font-semibold mb-1">FIREWALL</div>
                        <div class="text-gray-400">ACTIVE</div>
                    </div>
                    <div class="error-flash rounded-lg p-4 border border-red-500" style="animation-delay: 0.5s;">
                        <div class="status-indicator text-cyber-red font-semibold mb-1">INTRUSION DETECTION</div>
                        <div class="text-gray-400">MONITORING</div>
                    </div>
                    <div class="error-flash rounded-lg p-4 border border-red-500" style="animation-delay: 1s;">
                        <div class="status-indicator text-cyber-red font-semibold mb-1">SECURITY LEVEL</div>
                        <div class="text-gray-400">MAXIMUM</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Additional Info Panel -->
        <div class="cyber-panel rounded-xl p-8 max-w-2xl mx-auto mt-8">
            <h3 class="text-xl font-semibold text-cyber-red mb-4 flex items-center">
                <span class="w-2 h-2 bg-cyber-red rounded-full mr-3 animate-pulse"></span>
                System Information
            </h3>
            <div class="grid md:grid-cols-2 gap-4 text-sm font-mono">
                <div>
                    <div class="text-gray-400">Requested URL:</div>
                    <div class="text-cyber-red break-all"><?php echo htmlspecialchars($_SERVER['REQUEST_URI'] ?? 'UNKNOWN'); ?></div>
                </div>
                <div>
                    <div class="text-gray-400">User Agent:</div>
                    <div class="text-cyber-red text-xs break-all"><?php echo htmlspecialchars(substr($_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN', 0, 50)); ?>...</div>
                </div>
                <div>
                    <div class="text-gray-400">Timestamp:</div>
                    <div class="text-cyber-red"><?php echo date('Y-m-d H:i:s T'); ?></div>
                </div>
                <div>
                    <div class="text-gray-400">Server:</div>
                    <div class="text-cyber-red"><?php echo htmlspecialchars($_SERVER['SERVER_NAME'] ?? 'CYBERFLAG'); ?></div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="relative z-10 border-t border-red-800 bg-black/70 backdrop-blur-sm mt-12">
        <div class="container mx-auto px-6 py-6">
            <div class="flex flex-col md:flex-row justify-between items-center text-sm text-gray-400">
                <div class="flex items-center space-x-2 mb-4 md:mb-0">
                    <span class="w-2 h-2 bg-cyber-red rounded-full animate-pulse"></span>
                    <span>© 2025 CYBERFLAG CTF Platform</span>
                </div>
                <div class="flex space-x-6">
                    <span class="flex items-center space-x-2">
                        <span class="w-2 h-2 bg-cyber-red rounded-full"></span>
                        <span>Status: ERROR 404</span>
                    </span>
                    <span class="flex items-center space-x-2">
                        <span class="w-2 h-2 bg-yellow-400 rounded-full"></span>
                        <span>Security: BREACH DETECTED</span>
                    </span>
                </div>
            </div>
        </div>
    </footer>

    <script>
        // Matrix Rain Effect (Red theme)
        function createMatrixRain() {
            const container = document.getElementById('matrix-container');
            const chars = '01ABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*()<>[]{}ERROR404ACCESSDENIED';
            
            function createChar() {
                const char = document.createElement('div');
                char.className = 'matrix-char';
                char.textContent = chars[Math.floor(Math.random() * chars.length)];
                char.style.left = Math.random() * 100 + 'vw';
                char.style.animationDuration = (Math.random() * 3 + 2) + 's';
                char.style.animationDelay = Math.random() * 2 + 's';
                
                container.appendChild(char);
                
                setTimeout(() => {
                    if (char.parentNode) char.remove();
                }, 5000);
            }
            
            setInterval(createChar, 150);
        }

        // System Time Update
        function updateSystemTime() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('en-US', {
                hour12: false,
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
            document.getElementById('systemTime').textContent = timeString;
        }

        // Countdown Timer
        let countdownValue = 10;
        

        // Enhanced Button Effects
        function enhanceButtons() {
            document.querySelectorAll('.cyber-btn').forEach(btn => {
                btn.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-2px)';
                });
                btn.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                });
            });
        }

        // Add glitch effect to error code randomly
        function addGlitchEffect() {
            const errorCode = document.querySelector('.error-code');
            
            setInterval(() => {
                if (Math.random() < 0.1) { // 10% chance every interval
                    errorCode.style.transform = 'translate(' + (Math.random() * 4 - 2) + 'px, ' + (Math.random() * 4 - 2) + 'px)';
                    setTimeout(() => {
                        errorCode.style.transform = 'translate(0)';
                    }, 100);
                }
            }, 500);
        }

        // Initialize everything
        document.addEventListener('DOMContentLoaded', function() {
            createMatrixRain();
            updateSystemTime();
            enhanceButtons();
            addGlitchEffect();
            
            // Update system time
            setInterval(updateSystemTime, 1000);
            
            // Update countdown
            setInterval(updateCountdown, 1000);
            
            // Keyboard shortcuts
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    window.history.back();
                } else if (e.key === 'Home') {
                    window.location.href = '/';
                }
            });

            // Console message for hackers
            console.log('%c💀 SYSTEM BREACH DETECTED 💀', 'color: #ff073a; font-size: 20px; font-weight: bold;');
            console.log('%cAccess to this resource is DENIED.', 'color: #ff073a; font-size: 14px;');
            console.log('%cAll unauthorized access attempts are being logged and monitored.', 'color: #ff6b6b; font-size: 12px;');
        });
    </script>
</body>
</html>