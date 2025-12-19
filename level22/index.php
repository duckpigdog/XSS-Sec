<?php
setcookie("flag", "flag{7f3c4d5e-9a0b-1c2d-3e4f-5a6b7c8d9e0f}", time() + 3600, "/", "", false, false);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TechWire - Global Tech News Search</title>
    <style>
        :root {
            --primary-color: #2c3e50;
            --accent-color: #e74c3c;
            --bg-color: #f8f9fa;
            --text-color: #333;
            --border-color: #ddd;
        }

        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            background-color: var(--bg-color);
            color: var(--text-color);
            margin: 0;
            padding: 0;
            line-height: 1.6;
        }

        /* Navbar */
        .navbar {
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            padding: 0 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 60px;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .navbar-brand {
            font-size: 24px;
            font-weight: bold;
            color: var(--primary-color);
            text-decoration: none;
            display: flex;
            align-items: center;
        }

        .navbar-brand span {
            color: var(--accent-color);
        }

        .nav-items a {
            color: #666;
            text-decoration: none;
            margin-left: 20px;
            font-weight: 500;
            font-size: 14px;
            transition: color 0.3s;
        }

        .nav-items a:hover {
            color: var(--primary-color);
        }

        /* Hero Section */
        .hero {
            background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
            color: white;
            padding: 60px 20px;
            text-align: center;
            margin-bottom: 40px;
        }

        .hero h1 {
            margin: 0 0 10px;
            font-size: 32px;
        }

        .hero p {
            margin: 0 0 30px;
            opacity: 0.9;
            font-size: 18px;
        }

        /* Search Container */
        .search-container {
            max-width: 700px;
            margin: 0 auto;
            position: relative;
        }

        .search-input-wrapper {
            position: relative;
            display: flex;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            border-radius: 4px;
            overflow: hidden;
        }

        .search-input {
            flex: 1;
            padding: 16px 20px;
            border: none;
            font-size: 16px;
            outline: none;
        }

        .search-button {
            background-color: var(--accent-color);
            color: white;
            border: none;
            padding: 0 30px;
            font-weight: bold;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }

        .search-button:hover {
            background-color: #c0392b;
        }

        /* Main Content */
        .main-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px 40px;
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 40px;
        }

        /* Search Results Area */
        .results-area {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            min-height: 300px;
        }

        .results-header {
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 15px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .results-header h2 {
            margin: 0;
            font-size: 20px;
            color: var(--primary-color);
        }

        .result-card {
            padding: 20px 0;
            border-bottom: 1px solid #eee;
            transition: transform 0.2s;
        }

        .result-card:hover {
            transform: translateX(5px);
        }

        .result-card h3 {
            margin: 0 0 8px;
            font-size: 18px;
        }

        .result-card h3 a {
            color: #2980b9;
            text-decoration: none;
        }

        .result-card h3 a:hover {
            text-decoration: underline;
        }

        .result-meta {
            font-size: 12px;
            color: #999;
            margin-bottom: 8px;
        }

        .result-summary {
            color: #555;
            font-size: 14px;
        }

        /* Sidebar */
        .sidebar-section {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }

        .sidebar-title {
            font-size: 16px;
            font-weight: bold;
            color: var(--primary-color);
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
        }

        .trending-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .trending-list li {
            margin-bottom: 12px;
            padding-bottom: 12px;
            border-bottom: 1px solid #f9f9f9;
        }

        .trending-list li:last-child {
            border-bottom: none;
        }

        .trending-list a {
            color: #444;
            text-decoration: none;
            font-size: 14px;
            display: block;
        }

        .trending-list a:hover {
            color: var(--accent-color);
        }

        .loading-spinner {
            display: none;
            text-align: center;
            padding: 40px;
            color: #666;
        }

        .footer {
            background-color: #2c3e50;
            color: #ecf0f1;
            padding: 40px 20px;
            text-align: center;
            font-size: 14px;
            margin-top: auto;
        }

        .footer a {
            color: #bdc3c7;
            text-decoration: none;
        }
        
        .no-results-msg {
            text-align: center;
            padding: 40px;
            color: #888;
            font-style: italic;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <a href="#" class="navbar-brand">Tech<span>Wire</span></a>
        <div class="nav-items">
            <a href="#">Home</a>
            <a href="#">Business</a>
            <a href="#">Technology</a>
            <a href="#">Security</a>
            <a href="../index.php">Lab Home</a>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="hero">
        <h1>Discover the Future Today</h1>
        <p>Search millions of articles from the world's leading tech publications.</p>
        
        <div class="search-container">
            <div class="search-input-wrapper">
                <input type="text" id="searchInput" class="search-input" placeholder="Search for topics, keywords, or authors..." onkeypress="handleKeyPress(event)">
                <button class="search-button" onclick="search()">SEARCH</button>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Left Column: Results -->
        <div class="results-area">
            <div class="results-header">
                <h2>Search Results</h2>
                <span style="font-size: 12px; color: #888;">Live API Connected</span>
            </div>

            <div id="loading" class="loading-spinner">
                Searching database...
            </div>

            <div id="resultsContainer">
                <div class="no-results-msg">Enter a keyword above to start searching.</div>
            </div>
        </div>

        <!-- Right Column: Sidebar -->
        <div class="sidebar">
            <div class="sidebar-section">
                <div class="sidebar-title">Trending Now</div>
                <ul class="trending-list">
                    <li><a href="#">🔥 AI Regulation Summit 2024</a></li>
                    <li><a href="#">⚡ Quantum Computing Breakthroughs</a></li>
                    <li><a href="#">📱 Next-Gen Smartphone Leaks</a></li>
                    <li><a href="#">🚀 SpaceX Mars Mission Update</a></li>
                    <li><a href="#">💻 New Cybersecurity Frameworks</a></li>
                </ul>
            </div>

            <div class="sidebar-section">
                <div class="sidebar-title">Top Categories</div>
                <ul class="trending-list">
                    <li><a href="#">Artificial Intelligence</a></li>
                    <li><a href="#">Cyber Security</a></li>
                    <li><a href="#">Blockchain</a></li>
                    <li><a href="#">Cloud Computing</a></li>
                </ul>
            </div>
        </div>
    </div>

    <footer class="footer">
        <p>&copy; 2024 TechWire Media Group. All rights reserved.</p>
        <p>
            <a href="#">Privacy Policy</a> | 
            <a href="#">Terms of Service</a> | 
            <a href="#">Contact Us</a>
        </p>
    </footer>

    <script>
        function handleKeyPress(event) {
            if (event.key === 'Enter') {
                search();
            }
        }

        function search() {
            var query = document.getElementById('searchInput').value;
            var resultsDiv = document.getElementById('resultsContainer');
            var loadingDiv = document.getElementById('loading');
            
            if (!query) return;

            // Show loading, hide previous results
            loadingDiv.style.display = 'block';
            resultsDiv.style.opacity = '0.5';

            var xhr = new XMLHttpRequest();
            
            xhr.onreadystatechange = function() {
                if (this.readyState == 4 && this.status == 200) {
                    // Simulate network delay for realism
                    setTimeout(function() {
                        loadingDiv.style.display = 'none';
                        resultsDiv.style.opacity = '1';
                        
                        try {
                            // VULNERABLE CODE: Using eval to parse JSON response
                            // The server reflects input into the JSON string
                            // Vulnerability: Reflected DOM XSS via JSON eval injection
                            var searchResultsObj = eval('(' + xhr.responseText + ')');
                            
                            displayResults(searchResultsObj);
                        } catch (e) {
                            console.error("Error parsing results", e);
                            resultsDiv.innerHTML = '<div class="no-results-msg" style="color:red">System Error: Failed to parse server response.</div>';
                        }
                    }, 500);
                }
            };
            
            xhr.open("GET", "search.php?q=" + encodeURIComponent(query), true);
            xhr.send();
        }

        function displayResults(data) {
            var container = document.getElementById('resultsContainer');
            var html = '';
            
            if (data.searchTerm) {
                html += '<p style="margin-bottom: 20px; color: #666;">Showing results for: <strong style="color: #333;">' + escapeHtml(data.searchTerm) + '</strong></p>';
            }

            if (data.results && data.results.length > 0) {
                data.results.forEach(function(item) {
                    html += '<div class="result-card">';
                    html += '<h3><a href="#">' + escapeHtml(item.title) + '</a></h3>';
                    html += '<div class="result-meta">' + escapeHtml(item.date || 'Just now') + ' • ' + escapeHtml(item.category || 'News') + '</div>';
                    html += '<div class="result-summary">' + escapeHtml(item.summary) + '</div>';
                    html += '</div>';
                });
            } else {
                html += '<div class="no-results-msg">No articles found matching your query.</div>';
            }
            
            container.innerHTML = html;
        }

        function escapeHtml(text) {
            if (!text) return text;
            return text
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }
    </script>
</body>
</html>
