<?php 
include '../headers.php'; 
setcookie("flag", "flag{location_hash_xss_level12}", time() + 3600, "/", "", false, false);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SaaS Dashboard - CloudManage</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f6f8; color: #333; margin: 0; padding: 0; display: flex; height: 100vh; }
        
        /* Sidebar */
        .sidebar { width: 240px; background-color: #2c3e50; color: #ecf0f1; display: flex; flex-direction: column; }
        .sidebar-header { padding: 20px; font-weight: bold; font-size: 1.2em; border-bottom: 1px solid #34495e; display: flex; align-items: center; }
        .sidebar-header span { color: #3498db; margin-right: 8px; }
        .menu { list-style: none; padding: 0; margin: 20px 0; }
        .menu-item { }
        .menu-link { display: block; padding: 15px 25px; color: #bdc3c7; text-decoration: none; transition: 0.3s; cursor: pointer; border-left: 3px solid transparent; }
        .menu-link:hover, .menu-link.active { background-color: #34495e; color: #fff; border-left-color: #3498db; }
        .sidebar-footer { margin-top: auto; padding: 20px; font-size: 0.8em; color: #7f8c8d; }
        
        /* Main Content */
        .main-content { flex: 1; display: flex; flex-direction: column; overflow-y: auto; }
        .top-bar { background: #fff; padding: 15px 30px; border-bottom: 1px solid #e1e4e8; display: flex; justify-content: space-between; align-items: center; }
        .breadcrumbs { color: #7f8c8d; font-size: 0.9em; }
        .user-profile { display: flex; align-items: center; }
        .avatar { width: 32px; height: 32px; background: #3498db; border-radius: 50%; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: bold; margin-right: 10px; }
        
        .content-area { padding: 30px; }
        .card { background: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); padding: 30px; margin-bottom: 20px; min-height: 400px; }
        .card-header { border-bottom: 1px solid #eee; padding-bottom: 15px; margin-bottom: 20px; font-size: 1.2em; font-weight: 600; color: #2c3e50; }
        
        /* Loading & Error States */
        .loading-spinner { border: 4px solid #f3f3f3; border-top: 4px solid #3498db; border-radius: 50%; width: 30px; height: 30px; animation: spin 1s linear infinite; margin: 20px auto; }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        
        .error-message { background-color: #fce4e4; color: #c0392b; padding: 15px; border-radius: 4px; border-left: 4px solid #c0392b; }
        
        /* Content Placeholders */
        .stat-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }
        .stat-box { background: #f8f9fa; padding: 20px; border-radius: 6px; text-align: center; }
        .stat-number { font-size: 2em; font-weight: bold; color: #2c3e50; }
        .stat-label { color: #7f8c8d; font-size: 0.9em; margin-top: 5px; }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <span>☁️</span> CloudManage
        </div>
        <ul class="menu">
            <li class="menu-item"><a class="menu-link" onclick="changeTab('dashboard')">Dashboard</a></li>
            <li class="menu-item"><a class="menu-link" onclick="changeTab('instances')">Instances</a></li>
            <li class="menu-item"><a class="menu-link" onclick="changeTab('billing')">Billing</a></li>
            <li class="menu-item"><a class="menu-link" onclick="changeTab('settings')">Settings</a></li>
        </ul>
        <div class="sidebar-footer">
            <a href="../index.php" style="color: inherit; text-decoration: none;">&larr; Exit Demo</a>
        </div>
    </div>

    <div class="main-content">
        <div class="top-bar">
            <div class="breadcrumbs">Home / <span id="current-page">Dashboard</span></div>
            <div class="user-profile">
                <div class="avatar">A</div>
                <span>Admin User</span>
            </div>
        </div>

        <div class="content-area">
            <div class="card">
                <div id="content-container">
                    <!-- Dynamic Content Loaded Here -->
                </div>
            </div>
        </div>
    </div>

    <script>
        // Simulating a Single Page Application (SPA) Router
        
        function changeTab(tabName) {
            window.location.hash = tabName;
        }

        function loadContent() {
            var hash = window.location.hash;
            var container = document.getElementById('content-container');
            var menuLinks = document.querySelectorAll('.menu-link');
            
            // Default to dashboard if no hash
            if (!hash) {
                changeTab('dashboard');
                return;
            }

            // Extract tab name (remove #)
            var tab = decodeURIComponent(hash.substring(1));
            
            // Update UI Active State
            menuLinks.forEach(link => {
                link.classList.remove('active');
                if (link.getAttribute('onclick').includes("'" + tab + "'")) {
                    link.classList.add('active');
                }
            });
            document.getElementById('current-page').textContent = tab.charAt(0).toUpperCase() + tab.slice(1);

            // Vulnerability: 
            // We reflect the tab name into the DOM while "loading".
            // If the tab doesn't exist, we show an error message that also reflects the input.
            
            container.innerHTML = '<div style="text-align:center; padding: 50px;">' +
                                  '<div class="loading-spinner"></div>' +
                                  '<p>Loading module: <strong>' + tab + '</strong>...</p>' + 
                                  '</div>';
            
            // Simulate Network Request
            setTimeout(function() {
                if (tab === 'dashboard') {
                    container.innerHTML = `
                        <div class="card-header">System Overview</div>
                        <div class="stat-grid">
                            <div class="stat-box"><div class="stat-number">98.5%</div><div class="stat-label">Uptime</div></div>
                            <div class="stat-box"><div class="stat-number">42</div><div class="stat-label">Active Instances</div></div>
                            <div class="stat-box"><div class="stat-number">$1,204</div><div class="stat-label">Est. Cost</div></div>
                        </div>
                    `;
                } else if (tab === 'instances') {
                    container.innerHTML = `
                        <div class="card-header">Manage Instances</div>
                        <table style="width:100%; border-collapse:collapse;">
                            <tr style="border-bottom:1px solid #eee; text-align:left;">
                                <th style="padding:10px;">Name</th><th style="padding:10px;">Status</th><th style="padding:10px;">Region</th>
                            </tr>
                            <tr><td style="padding:10px;">web-prod-01</td><td style="padding:10px; color:green;">Running</td><td style="padding:10px;">us-east-1</td></tr>
                            <tr><td style="padding:10px;">db-primary</td><td style="padding:10px; color:green;">Running</td><td style="padding:10px;">us-east-1</td></tr>
                            <tr><td style="padding:10px;">worker-node</td><td style="padding:10px; color:orange;">Stopped</td><td style="padding:10px;">eu-west-2</td></tr>
                        </table>
                    `;
                } else if (tab === 'billing') {
                    container.innerHTML = '<div class="card-header">Billing Details</div><p>No invoices due.</p>';
                } else if (tab === 'settings') {
                    container.innerHTML = '<div class="card-header">Account Settings</div><p>Settings are read-only in demo mode.</p>';
                } else {
                    // Error State - Vulnerable Reflection
                    container.innerHTML = '<div class="error-message">' +
                                          '<h3>404 Module Not Found</h3>' +
                                          '<p>The requested module "<b>' + tab + '</b>" does not exist or you do not have permission to view it.</p>' +
                                          '</div>';
                }
            }, 600);
        }
        
        window.addEventListener('hashchange', loadContent);
        window.addEventListener('load', loadContent);
    </script>
</body>
</html>
