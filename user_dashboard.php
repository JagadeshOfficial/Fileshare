<?php
session_start();
include("db.php");

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

// Fetch user data
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT name, email, mobile, aadhaar, address, profile_image FROM users WHERE id = ?");
if (!$stmt) {
    error_log("Error preparing statement: " . $conn->error);
    echo "An error occurred while fetching user data.";
    exit();
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->bind_result($name, $email, $mobile, $aadhaar, $address, $profile_image);
    $stmt->fetch();
    $user = [
        'name' => $name ?? '',
        'email' => $email ?? '',
        'mobile' => $mobile ?? '',
        'aadhaar' => $aadhaar ?? '',
        'address' => $address ?? '',
        'profile_image' => $profile_image ?? 'default.jpg'  // default image if not set
    ];
} else {
    error_log("User not found for user_id: $user_id");
    echo "User not found!";
    exit();
}
$stmt->close();

// Fetch dashboard stats
$stmt = $conn->prepare("SELECT COUNT(*) FROM documents WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($totalFiles);
$stmt->fetch();
$stmt->close();

$stmt = $conn->prepare("SELECT COUNT(*) FROM user_activities WHERE user_id = ? AND action = 'Downloaded'");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($totalDownloads);
$stmt->fetch();
$stmt->close();

// Example: Premium status
$premiumPlan = "Active";

$conn->close();
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - File System</title>
    <link rel="stylesheet" href="user_dashboard.css">
    <style>
        .container {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 250px;
            background: #2c3e50;
            color: white;
            padding: 20px;
        }

        .sidebar-header h2 {
            margin: 0 0 20px;
            font-size: 24px;
        }

        .nav-link {
            display: block;
            color: white;
            padding: 10px;
            text-decoration: none;
            margin: 5px 0;
        }

        .nav-link:hover,
        .nav-link.active {
            background: #34495e;
            border-radius: 4px;
        }

        .logout {
            margin-top: auto;
        }

        .main {
            flex: 1;
            padding: 20px;
            background: #f5f5f5;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .header-left h1 {
            font-size: 28px;
            margin: 0;
        }

        .header-left p {
            margin: 5px 0;
        }

        .dashboard-summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .card h3 {
            margin: 0 0 10px;
            font-size: 18px;
        }

        .card p {
            margin: 0;
            font-size: 24px;
            color: #007bff;
        }

        .recent-activity {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .recent-activity h3 {
            font-size: 18px;
            margin: 0 0 10px;
        }

        .recent-activity ul {
            list-style: none;
            padding: 0;
        }

        .recent-activity li {
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }

        .recent-activity li:last-child {
            border-bottom: none;
        }

        .recent-activity .timestamp {
            float: right;
            color: #666;
            font-size: 12px;
        }

        .recent-activity .error {
            color: #d9534f;
        }

        .documents-section {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .section-title {
            font-size: 24px;
            margin-bottom: 20px;
        }

        .search-container {
            margin-bottom: 20px;
        }

        .search-bar {
            width: 100%;
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .documents-container {
            display: flex;
            gap: 15px;
        }

        .document-box {
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 8px;
            background: #f9f9f9;
        }

        .document-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
        }

        .download-icon {
            width: 24px;
            height: 24px;
        }

        .file-name {
            font-weight: bold;
        }

        .document-details {
            font-size: 14px;
        }

        .download-btn {
            display: inline-block;
            padding: 8px 16px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 10px;
        }

        .download-btn:hover {
            background: #0056b3;
        }

        .no-documents,
        .loading {
            text-align: center;
            color: #666;
            font-size: 16px;
        }

        .error {
            text-align: center;
            color: #d9534f;
            font-size: 16px;
        }

        .downloads-container {
            display: grid;
            gap: 15px;
        }

        .download-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background: #f9f9f9;
        }

        .download-info p {
            margin: 5px 0;
        }

        .profile-wrapper {
            display: flex;
            gap: 20px;
        }

        .profile-left img {
            border-radius: 8px;
            object-fit: cover;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .form-group input,
        .form-group textarea {
            width: 350px;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .btn-group button {
            padding: 10px 20px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .btn-group button:hover {
            background: #0056b3;
        }

        /* Default: Sidebar visible */
        .sidebar {
            width: 371px;
            background-color: #2d2d2d;
            color: white;
        }

        .nav {
            display: flex;
            flex-direction: column;
        }

        /* Hide toggle button on large screens */
        .menu-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 24px;
            color: white;
            cursor: pointer;
        }

        /* Responsive - for tablets and mobiles */
        @media (max-width: 768px) {
            .menu-toggle {
                display: block;
            }

            #upload {
                width: 270px;
            }

            .nav {
                display: none;
                flex-direction: column;
                background-color: #2d2d2d;
            }

            .nav.show {
                display: flex;
            }

            .main {
                width: 366px;
            }
        }
    </style>
</head>

<body>
    <script>
        window.currentUserId = <?php echo json_encode($_SESSION['user_id'] ?? null); ?>;
        if (!window.currentUserId) {
            window.location.href = 'login.html';
        }
    </script>

    <div class="container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2>FileShare</h2>
                <!-- Toggle Button (Visible only on mobile/tablet) -->
                <button id="menu-toggle" class="menu-toggle">☰</button>
            </div>
            <nav class="nav" id="mobileMenu">
                <a href="#" class="nav-link active" data-section="dashboard">Dashboard</a>
                <a href="#" class="nav-link" data-section="documents">Documents</a>
                <a href="#" class="nav-link" data-section="downloads">My Downloads</a>
                <a href="#" class="nav-link" data-section="profile">My Profile</a>
                <a href="logout.php" class="nav-link logout">Logout</a>
            </nav>
        </aside>


        <main class="main">
            <div class="header">
                <div class="header-left">
                    <h1>Welcome, <span id="userName"><?php echo htmlspecialchars($user['name'] ?? ''); ?></span></h1>
                    <p>Email: <span id="userEmail"><?php echo htmlspecialchars($user['email']); ?></span></p>
                </div>
                <div class="header-right"></div>
            </div>

            <div id="content">
                <div id="dashboard" style="display: block;">
                    <h2>Dashboard</h2>
                    <div class="dashboard-summary">
                        <div class="card">
                            <h3>Total Files</h3>
                            <p><?php echo $totalFiles; ?></p>
                        </div>
                        <div class="card">
                            <h3>Downloads</h3>
                            <p><?php echo $totalDownloads; ?></p>
                        </div>
                        <div class="card">
                            <h3>Premium Plan</h3>
                            <p><?php echo $premiumPlan; ?></p>
                        </div>
                    </div>
                    <div class="recent-activity">
                        <h3>Recent Activity</h3>
                        <ul id="activityList">
                            <li>Loading activities...</li>
                        </ul>
                    </div>
                </div>

                <div id="documents" class="documents-section" style="display: none;">
                    <h2 class="section-title">My Documents</h2>
                    <div class="search-container">
                        <input type="text" id="searchInput" placeholder="Search documents..." class="search-bar" />
                    </div>
                    <div class="documents-container" id="documentsContainer">
                        <p class="loading">Loading documents...</p>
                    </div>
                </div>

                <div id="downloads" style="display: none;">
                    <h2>My Downloads</h2>
                    <p>Files you've downloaded recently.</p>
                    <div class="downloads-container" id="downloadsContainer">
                        <p class="loading">Loading downloads...</p>
                    </div>
                </div>

                <div id="profile" class="section" style="display: none;">
                    <h2>My Profile</h2>
                    <form action="update_user_profile.php" method="POST" enctype="multipart/form-data" id="profileForm">
                        <div class="profile-wrapper" style="display: flex; gap: 100px;">
                            <!-- Left: Profile Image -->
                            <div class="profile-left" style="flex: 1;">
                                <img src="profile_images/<?php echo htmlspecialchars($user['profile_image'] ?? 'default.png'); ?>"
                                    alt="Profile Picture" id="profilePic" width="150" height="150">
                                <div class="form-group" style="margin-top: 10px;">
                                    <label for="upload">Change Image</label>
                                    <input type="file" id="upload" accept="image/*" name="profile_image"
                                        onchange="previewImage(event)">
                                </div>
                            </div>

                            <!-- Right: Profile Info -->
                            <div class="profile-right" style="flex: 2;">
                                <div class="form-group">
                                    <label for="name">Full Name</label>
                                    <input type="text" id="name" name="name"
                                        value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="aadhaar">Aadhaar Number</label>
                                    <input type="text" id="aadhaar" name="aadhaar"
                                        value="<?php echo htmlspecialchars($user['aadhaar'] ?? ''); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="mobile">Mobile</label>
                                    <input type="text" id="mobile" name="mobile"
                                        value="<?php echo htmlspecialchars($user['mobile'] ?? ''); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="email">Email</label>
                                    <input type="email" id="email" name="email"
                                        value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="address">Address</label>
                                    <textarea id="address" name="address"
                                        required><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                                </div>
                                <div class="btn-group">
                                    <button type="submit">Update Profile</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <script>
                    function previewImage(event) {
                        const reader = new FileReader();
                        reader.onload = function () {
                            document.getElementById('profilePic').src = reader.result;
                        };
                        reader.readAsDataURL(event.target.files[0]);
                    }
                </script>


            </div>
        </main>
    </div>
    <script>
        const toggleBtn = document.getElementById('menu-toggle');
        const navMenu = document.getElementById('mobileMenu');

        toggleBtn.addEventListener('click', () => {
            navMenu.classList.toggle('show');
        });
    </script>



    <script>

        document.addEventListener('DOMContentLoaded', () => {
            const userId = window.currentUserId;
            const searchInput = document.getElementById('searchInput');
            const documentsContainer = document.getElementById('documentsContainer');
            const downloadsContainer = document.getElementById('downloadsContainer');
            const activityList = document.getElementById('activityList');
            let allDocuments = [];

            if (!userId) {
                documentsContainer.innerHTML = '<p class="error">Please log in to view documents</p>';
                activityList.innerHTML = '<li class="error">Please log in to view activities</li>';
                downloadsContainer.innerHTML = '<p class="error">Please log in to view downloads</p>';
                return;
            }

            async function loadDocuments() {
                documentsContainer.innerHTML = '<p class="loading">Loading documents...</p>';
                try {
                    const response = await fetch(`api.php?action=get_assigned_documents&userId=${encodeURIComponent(userId)}`);
                    if (!response.ok) {
                        const text = await response.text();
                        throw new Error(`Failed to load documents: ${text}`);
                    }
                    const documents = await response.json();
                    allDocuments = documents;
                    renderDocuments(documents);
                } catch (error) {
                    console.error('Error loading documents:', error);
                    documentsContainer.innerHTML = `<p class="error">Error loading documents: ${error.message}</p>`;
                }
            }

            function renderDocuments(documents) {
                documentsContainer.innerHTML = '';
                if (documents.length === 0) {
                    documentsContainer.innerHTML = '<p class="no-documents">No documents assigned</p>';
                    return;
                }
                documents.forEach(doc => {
                    const fileExtension = doc.file_name.split('.').pop().toLowerCase();
                    const iconMap = {
                        pdf: 'icons/pdf-icon.png',
                        doc: 'icons/docx-icon.png',
                        docx: 'icons/docx-icon.png',
                        txt: 'icons/txt-icon.png',
                        default: 'icons/file-icon.png'
                    };
                    const iconSrc = iconMap[fileExtension] || iconMap.default;
                    const docBox = document.createElement('div');
                    docBox.className = 'document-box';
                    docBox.innerHTML = `
                <div class="document-header">
                    <img src="${iconSrc}" alt="${fileExtension.toUpperCase()}" class="download-icon" />
                    <span class="file-name">${sanitizeHTML(doc.file_name)}</span>
                </div>
                <div class="document-details">
                    <p>Uploaded on: <span>${new Date(doc.uploaded_at).toLocaleDateString()}</span></p>
                    <p>Assigned by: <span>${sanitizeHTML(doc.admin_name)}</span></p>
                    <a href="#" class="download-btn" data-doc-id="${doc.id}">Download</a>
                </div>
            `;
                    documentsContainer.appendChild(docBox);
                });
                attachDownloadListeners();
            }

            async function loadRecentActivities() {
                if (!activityList) return;
                activityList.innerHTML = '<li>Loading activities...</li>';
                try {
                    const response = await fetch(`api.php?action=get_recent_activities&userId=${encodeURIComponent(userId)}`);
                    if (!response.ok) {
                        const text = await response.text();
                        throw new Error(`Failed to load activities: ${text}`);
                    }
                    const activities = await response.json();
                    activityList.innerHTML = '';
                    if (activities.length === 0) {
                        activityList.innerHTML = '<li>No recent activities</li>';
                        return;
                    }
                    activities.forEach(activity => {
                        const li = document.createElement('li');
                        const timeAgo = getTimeAgo(new Date(activity.created_at));
                        li.innerHTML = `<strong>${sanitizeHTML(activity.action)}:</strong> ${sanitizeHTML(activity.file_name)} <span class="timestamp">${timeAgo}</span>`;
                        activityList.appendChild(li);
                    });
                } catch (error) {
                    console.error('Error loading activities:', error);
                    activityList.innerHTML = `<li class="error">Error loading activities: ${error.message}</li>`;
                }
            }

            async function loadDownloads() {
                downloadsContainer.innerHTML = '<p class="loading">Loading downloads...</p>';
                try {
                    const response = await fetch(`api.php?action=get_recent_activities&userId=${encodeURIComponent(userId)}&filterAction=Downloaded`);
                    if (!response.ok) {
                        const text = await response.text();
                        throw new Error(`Failed to load downloads: ${text}`);
                    }
                    const downloads = await response.json();
                    downloadsContainer.innerHTML = '';
                    if (downloads.length === 0) {
                        downloadsContainer.innerHTML = '<p class="no-documents">No downloads found</p>';
                        return;
                    }
                    downloads.forEach(download => {
                        const item = document.createElement('div');
                        item.className = 'download-item';
                        item.innerHTML = `
                    <div class="download-info">
                        <p class="file-name">${sanitizeHTML(download.file_name)}</p>
                        <p class="download-date">Downloaded on: ${new Date(download.created_at).toLocaleDateString()}</p>
                    </div>
                    <a href="#" class="download-btn" data-file-name="${sanitizeHTML(download.file_name)}">Download Again</a>
                `;
                        downloadsContainer.appendChild(item);
                    });
                    attachDownloadListeners();
                } catch (error) {
                    console.error('Error loading downloads:', error);
                    downloadsContainer.innerHTML = `<p class="error">Error loading downloads: ${error.message}</p>`;
                }
            }

            function attachDownloadListeners() {
                document.querySelectorAll('.download-btn').forEach(btn => {
                    btn.removeEventListener('click', handleDownload);
                    btn.addEventListener('click', handleDownload);
                });
            }

            async function handleDownload(e) {
                e.preventDefault();
                const btn = e.target;
                const docId = btn.dataset.docId;
                const fileName = btn.dataset.fileName;
                console.log('Download clicked', { docId, fileName }); // Debug log
                try {
                    let response;
                    if (docId) {
                        response = await fetch(`api.php?action=download_document&docId=${docId}`);
                    } else if (fileName) {
                        const docsResponse = await fetch(`api.php?action=get_assigned_documents&userId=${encodeURIComponent(userId)}`);
                        if (!docsResponse.ok) throw new Error('Failed to fetch documents');
                        const documents = await docsResponse.json();
                        const doc = documents.find(d => d.file_name === fileName);
                        if (!doc) throw new Error('Document not found');
                        response = await fetch(`api.php?action=download_document&docId=${doc.id}`);
                    }
                    if (!response.ok) {
                        const text = await response.text();
                        throw new Error(`HTTP Error: ${response.status} - ${text}`);
                    }
                    const data = await response.json();
                    console.log('API Response:', data); // Debug log
                    if (data.success) {
                        const link = document.createElement('a');
                        link.href = window.location.origin + '/' + data.file_path; // Ensure full URL
                        link.download = data.file_name;
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);
                        loadRecentActivities();
                        if (document.getElementById('downloads').style.display === 'block') {
                            loadDownloads();
                        }
                    } else {
                        throw new Error(data.error || 'Download failed');
                    }
                } catch (error) {
                    console.error('Download Error:', error);
                    alert(`Error: ${error.message}`);
                }
            }

            function getTimeAgo(date) {
                const now = new Date();
                const diffMs = now - date;
                const diffMins = Math.round(diffMs / 60000);
                if (diffMins < 1) return 'just now';
                if (diffMins < 60) return `${diffMins} mins ago`;
                const diffHours = Math.round(diffMins / 60);
                if (diffHours < 24) return `${diffHours} hour${diffHours > 1 ? 's' : ''} ago`;
                return `${Math.round(diffHours / 24)} day${diffHours > 24 ? 's' : ''} ago`;
            }

            function sanitizeHTML(str) {
                const div = document.createElement('div');
                div.textContent = str;
                return div.innerHTML;
            }

            function sanitizeURL(url) {
                try {
                    const parsedUrl = new URL(url, window.location.origin);
                    return parsedUrl.href;
                } catch {
                    return '#';
                }
            }

            searchInput.addEventListener('input', () => {
                const query = searchInput.value.toLowerCase().trim();
                const filteredDocuments = allDocuments.filter(doc =>
                    doc.file_name.toLowerCase().includes(query) ||
                    doc.admin_name.toLowerCase().includes(query)
                );
                renderDocuments(filteredDocuments);
            });

            const navLinks = document.querySelectorAll('.nav-link[data-section]');
            const sections = document.querySelectorAll('#content > div');
            function hideAllSections() {
                sections.forEach(section => section.style.display = 'none');
                navLinks.forEach(link => link.classList.remove('active'));
            }
            navLinks.forEach(link => {
                link.addEventListener('click', e => {
                    e.preventDefault();
                    hideAllSections();
                    const sectionId = link.dataset.section;
                    const activeSection = document.getElementById(sectionId);
                    if (activeSection) {
                        activeSection.style.display = 'block';
                        link.classList.add('active');
                        if (sectionId === 'documents') loadDocuments();
                        if (sectionId === 'dashboard') loadRecentActivities();
                        if (sectionId === 'downloads') loadDownloads();
                    }
                });
            });

            function previewImage(event) {
                const reader = new FileReader();
                reader.onload = function () {
                    document.getElementById('profilePic').src = reader.result;
                };
                reader.readAsDataURL(event.target.files[0]);
            }

            document.getElementById('dashboard').style.display = 'block';
            loadRecentActivities();
        });
    </script>
</body>

</html>