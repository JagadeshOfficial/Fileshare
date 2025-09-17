<?php
session_start();
include("db.php"); // Include your DB connection file

// Check if the user is logged in (i.e., session variables are set)
if (!isset($_SESSION['user_id'])) {
  header("Location: Admin_login.html"); // Redirect to login page if not logged in
  exit();
}

// Fetch user data from the database using the user_id stored in the session
$user_id = $_SESSION['user_id'];

// Prepare and execute the query to fetch user details
$stmt = $conn->prepare("SELECT email, password, profile_picture FROM admins WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
  $stmt->bind_result($email, $password, $profile_picture);
  $stmt->fetch();

  // Create a user array to hold the fetched data
  $user = [
    'email' => $email,
    'password' => $password,
    'profile_picture' => $profile_picture
  ];
} else {
  // Handle the case where the user is not found in the database
  header("Location: Admin_login.html"); // Redirect to login page if no user is found
  exit();
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin Dashboard</title>
  <link rel="stylesheet" href="admin_dashboard.css" />
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
    integrity="sha512-dO3CyN4Wb05++V+sUut8AfLP0sD6kl9IXZ9cMHkN4e6R37p78qv1F0gLfGeF9LrkMcEjh4AKlZL3ChHghfBaOg=="
    crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <style>
    /* Hide toggle on desktop */
    .menu-btn {
      display: none;
      background: none;
      border: none;
      color: white;
      font-size: 24px;
      cursor: pointer;
    }

    /* Responsive for mobile and tablet */
    @media (max-width: 768px) {
      .sidebar {
        width: 100%;
        height: auto;
        position: relative;
        padding-bottom: 0;
      }

      .sidebar-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
      }

      .menu-btn {
        display: block;
        margin-right: 10px;
      }

      .sidebar-menu {
        display: none;
        flex-direction: column;
        background-color: #2C3E50;
        padding: 10px 0;
      }

      .sidebar-menu.show {
        display: flex;
      }

      .content {
        margin-left: -15px;
      }

      .dashboard-overview {
        gap: 3px;
      }

      .section.active {
        display: block;
        width: 350px;
      }

      #uploadForm {
        width: 200px;
      }

      .upload-btn {
        margin-left: -50px;
      }

      #userSelect {
        width: 250px;
      }
    }
  </style>
</head>

<body>
  <div class="admin-dashboard">
    <!-- Sidebar -->
    <div class="sidebar">
      <div class="sidebar-header">
        <h2>FileShare</h2>
        <button id="menu-toggle" class="menu-btn">☰</button>
      </div>
      <ul class="sidebar-menu" id="sidebarMenu">
        <li><a href="#" data-section="dashboard">Dashboard</a></li>
        <li><a href="#" data-section="admins">Admins</a></li>
        <li><a href="#" data-section="document-attach">Document Attach</a></li>
        <li><a href="#" data-section="files">Files</a></li>
        <li><a href="#" data-section="Profile">Profile</a></li>
        <li><a href="index.html">Logout</a></li>
      </ul>
    </div>
  </div>

  <!-- Right Content -->
  <div class="content">
    <!-- Dashboard Section -->
    <div id="dashboard" class="section active">
      <!-- Top Nav -->
      <div class="top-nav">
        <div class="user-info">
          <span class="user-name">Admin</span>
          <img
            src="<?php echo $user['profile_picture'] ? htmlspecialchars($user['profile_picture']) : 'https://via.placeholder.com/120'; ?>"
            alt="Admin Avatar" class="user-avatar" onclick="showSection('profile')" title="View Profile" />
        </div>
      </div>

      <!-- Dashboard Cards -->
      <?php
      include("db.php");

      // Initialize variables with fallback values
      $total_admins = 0;
      $total_users = 0;
      $total_files = 0;

      // Query for total admins
      $query_admins = "SELECT COUNT(*) as count FROM admins";
      $result_admins = $conn->query($query_admins);
      if ($result_admins && $row_admins = $result_admins->fetch_assoc()) {
        $total_admins = $row_admins['count'];
      }

      // Query for total users
      $query_users = "SELECT COUNT(*) as count FROM users";
      $result_users = $conn->query($query_users);
      if ($result_users && $row_users = $result_users->fetch_assoc()) {
        $total_users = $row_users['count'];
      }

      $query_files = "SELECT COUNT(*) as count FROM files WHERE is_deleted = 0";
      $result_files = $conn->query($query_files);
      if ($result_files && $row_files = $result_files->fetch_assoc()) {
        $total_files = $row_files['count'];
      }

      // Close connection
      $conn->close();
      ?>

      <div class="dashboard-overview">
        <div class="card total-admins">
          <div class="card-icon">👤</div>
          <h3>Total Admins</h3>
          <p class="value"><?php echo htmlspecialchars($total_admins); ?></p>
        </div>
        <div class="card total-users">
          <div class="card-icon">👥</div>
          <h3>Total Users</h3>
          <p class="value"><?php echo htmlspecialchars($total_users); ?></p>
        </div>
        <div class="card total-files">
          <div class="card-icon">📁</div>
          <h3>Total Files Uploaded</h3>
          <p class="value"><?php echo htmlspecialchars($total_files); ?></p>
        </div>
      </div>
    </div>

    <!-- Admins Section -->
    <div id="admins" class="section">
      <div class="table-header">
        <h2>Admins</h2>
        <button onclick="openAddAdminForm()" class="add-admin-btn">Add Admin</button>
      </div>
      <div class="user-table-container">
        <table class="user-table">
          <thead>
            <tr>
              <th>#</th>
              <th>Full Name</th>
              <th>Email</th>
              <th>Aadhar Number</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php include('fetch_admins.php'); ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Add Admin Modal -->
    <div id="add-admin-modal" class="modal">
      <div class="modal-content">
        <span class="close-btn" onclick="closeAddAdminForm()">×</span>
        <h2 id="modal-title">Add Admin</h2>
        <form id="add-admin-form">
          <div class="form-group">
            <label for="full-name">Full Name</label>
            <input type="text" id="full-name" name="fullName" required />
          </div>
          <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" required />
          </div>
          <div class="form-group">
            <label for="mobile">Mobile Number</label>
            <input type="tel" id="mobile" name="mobile" pattern="[0-9]{10}" maxlength="10" required />
          </div>
          <div class="form-group">
            <label for="aadhar">Aadhar Number</label>
            <input type="text" id="aadhar" name="aadhar" pattern="[0-9]{12}" maxlength="12" required />
          </div>
          <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required />
          </div>
          <div class="form-actions">
            <button type="submit" class="submit-btn">Save</button>
          </div>
        </form>
      </div>
    </div>

    <div id="files" class="section">
      <h2>File Management</h2>
      <form id="uploadForm" enctype="multipart/form-data">
        <input type="file" id="fileInput" name="file" required />
        <button type="submit" class="upload-btn">Upload</button>
      </form>

      <!-- Uploaded Files Grid -->
      <div id="fileGrid" class="file-grid"></div>
    </div>

    <!-- Profile Section (containing the Edit Profile button) -->
    <?php
    include("db.php");

    if (!isset($_SESSION['user_id'])) {
      header("Location: Admin_login.html");
      exit();
    }

    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT name, email, mobile, aadhaar, profile_picture FROM admins WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
      $user = $result->fetch_assoc();
    } else {
      header("Location: Admin_login.html");
      exit();
    }

    $stmt->close();
    $conn->close();
    ?>

    <div id="Profile" class="section profile-section">
      <h2 class="section-title">Admin Profile</h2>
      <div class="profile-card">
        <div class="profile-left">
          <img
            src="<?php echo $user['profile_picture'] ? htmlspecialchars($user['profile_picture']) : 'https://via.placeholder.com/120'; ?>"
            alt="Profile Picture" class="profile-image" />
        </div>
        <div class="profile-right" id="profile-info">
          <div class="profile-details">
            <p><strong>Full Name:</strong> <span id="full-name"><?php echo htmlspecialchars($user['name']); ?></span>
            </p>
            <p><strong>Email:</strong> <span id="email"><?php echo htmlspecialchars($user['email']); ?></span></p>
            <p><strong>Mobile:</strong> <span id="mobile"><?php echo htmlspecialchars($user['mobile']); ?></span></p>
            <p><strong>Aadhaar:</strong> <span id="aadhaar"><?php echo htmlspecialchars($user['aadhaar']); ?></span>
            </p>
          </div>
          <button class="edit-profile-btn" onclick="openEditAdminForm(<?php echo htmlspecialchars($user_id); ?>)">Edit
            Profile</button>
        </div>
      </div>
    </div>

    <!-- Edit Admin Modal -->
    <div id="editAdminModal" class="modal">
      <div class="modal-content">
        <span class="close" onclick="closeEditAdminForm()">×</span>
        <h2>Edit Admin</h2>
        <form id="editAdminForm" enctype="multipart/form-data">
          <input type="hidden" id="editAdminIndex" name="editAdminIndex">
          <div class="form-group">
            <label for="editAdminName">Full Name:</label>
            <input type="text" id="editAdminName" name="editAdminName" required>
          </div>
          <div class="form-group">
            <label for="editAdminEmail">Email:</label>
            <input type="email" id="editAdminEmail" name="editAdminEmail" required>
          </div>
          <div class="form-group">
            <label for="editAdminMobile">Mobile:</label>
            <input type="text" id="editAdminMobile" name="editAdminMobile" required>
          </div>
          <div class="form-group">
            <label for="editAdminAadhar">Aadhaar:</label>
            <input type="text" id="editAdminAadhar" name="editAdminAadhar" required>
          </div>
          <div class="form-group">
            <label for="editAdminProfilePicture">Profile Picture:</label>
            <input type="file" id="editAdminProfilePicture" name="profile_picture" accept="image/*">
            <img id="editAdminProfilePreview" src="https://via.placeholder.com/120" alt="Profile Preview"
              style="width: 100px; height: 100px; object-fit: cover; margin-top: 10px;" />
          </div>
          <button type="submit" class="submit-btn">Update Admin</button>
        </form>
      </div>
    </div>
    <script>
      const toggleButton = document.getElementById('menu-toggle');
      const sidebarMenu = document.getElementById('sidebarMenu');

      toggleButton.addEventListener('click', () => {
        sidebarMenu.classList.toggle('show');
      });
    </script>

    <script>
      // Edit Admin Modal Functions
      function openEditAdminForm(adminId) {
        console.log('Opening edit form for admin ID:', adminId);
        const modal = document.getElementById('editAdminModal');

        if (!modal) {
          console.error('Edit modal element not found in DOM');
          showNotification('Error: Edit modal not found in the DOM', 'error');
          return;
        }

        fetch(`get_admin.php?id=${adminId}`)
          .then(response => {
            console.log('Fetch response status:', response.status);
            if (!response.ok) {
              throw new Error(`HTTP error! Status: ${response.status}`);
            }
            return response.text();
          })
          .then(text => {
            console.log('Get Admin Response:', text);
            try {
              const admin = JSON.parse(text);
              if (admin && admin.id) {
                console.log('Admin data loaded:', admin);
                document.getElementById('editAdminIndex').value = admin.id || '';
                document.getElementById('editAdminName').value = admin.name || '';
                document.getElementById('editAdminEmail').value = admin.email || '';
                document.getElementById('editAdminMobile').value = admin.mobile || '';
                document.getElementById('editAdminAadhar').value = admin.aadhaar || '';
                const profilePreview = document.getElementById('editAdminProfilePreview');
                if (profilePreview) {
                  profilePreview.src = admin.profile_picture || 'https://via.placeholder.com/120';
                }
                modal.style.display = 'block';
                console.log('Modal display set to block');
              } else {
                console.log('Admin data not found in response');
                showNotification('Admin not found.', 'error');
              }
            } catch (e) {
              console.error('Invalid JSON from get_admin.php:', text);
              showNotification('Error: Invalid response from get_admin.php. Check console for details.', 'error');
            }
          })
          .catch(error => {
            console.error('Fetch error for get_admin.php:', error);
            showNotification('Error: ' + error.message, 'error');
          });
      }

      function closeEditAdminForm() {
        const modal = document.getElementById('editAdminModal');
        if (modal) {
          modal.style.display = 'none';
          console.log('Modal closed');
        } else {
          console.error('Edit modal element not found when trying to close');
        }
      }

      // Notification function (required for error handling)
      function showNotification(message, type) {
        alert(`${type.charAt(0).toUpperCase() + type.slice(1)}: ${message}`);
      }
    </script>

    <!-- Document Attach Section -->
    <div id="document-attach" class="section document-attach-section">
      <h2 class="section-title">Assign Documents to User</h2>
      <div class="notification" id="notification"></div>

      <form id="documentForm">
        <div class="form-group">
          <label for="userSelect">Select User</label>
          <select id="userSelect" required>
            <option value="">Select a user</option>
            <!-- Users will be populated dynamically -->
          </select>
        </div>

        <div class="form-group">
          <label for="documentSelect">Select Documents (Hold Ctrl/Cmd to select multiple)</label>
          <select id="documentSelect" multiple class="document-select" required>
            <!-- Documents will be populated dynamically -->
          </select>
        </div>

        <button type="submit">Assign Documents</button>
      </form>

      <div class="document-list" id="documentList">
        <h3 class="text-2xl font-bold mb-4 text-gray-800">Assigned Documents</h3>
        <div id="userDocumentList">
          <!-- User list will be populated dynamically -->
          <!-- Example structure:
            <div class="user-item bg-white shadow-md rounded-lg mb-2">
                <button class="user-toggle w-full text-left p-4 font-semibold text-gray-700 hover:bg-gray-100">
                    User Name
                </button>
                <div class="user-documents hidden p-4 border-t border-gray-200">
                    <ul class="space-y-2">
                        <li class="flex justify-between items-center p-2 bg-gray-50 rounded">
                            <div>
                                <span class="font-medium">document.pdf</span>
                                <p class="text-sm text-gray-600">Assigned by Admin Name on 2025-04-29 10:00:00</p>
                            </div>
                            <button class="delete-btn text-red-500 hover:text-red-700" data-doc-id="1">Delete</button>
                        </li>
                    </ul>
                </div>
            </div>
            -->
        </div>
      </div>
    </div>
  </div>

  <script>
    // Combined DOMContentLoaded listener for all sections
    document.addEventListener('DOMContentLoaded', () => {
      // Document Attach Section
      const userSelect = document.getElementById('userSelect');
      const documentSelect = document.getElementById('documentSelect');
      const documentForm = document.getElementById('documentForm');
      const documentList = document.getElementById('userDocumentList');
      let currentUserId = null; // Track the currently selected userId

      // Fetch users
      fetch('api.php?action=get_users')
        .then(response => response.json())
        .then(users => {
          console.log('Fetched users:', users);
          users.forEach(user => {
            const option = document.createElement('option');
            option.value = user.id;
            option.textContent = user.name;
            userSelect.appendChild(option);
          });
        })
        .catch(error => {
          showNotification('Error fetching users: ' + error, 'error');
        });

      // Fetch documents
      fetch('api.php?action=get_documents')
        .then(response => response.json())
        .then(documents => {
          console.log('Fetched documents:', documents);
          documents.forEach(doc => {
            const option = document.createElement('option');
            option.value = doc.id;
            option.textContent = doc.file_name;
            documentSelect.appendChild(option);
          });
        })
        .catch(error => {
          showNotification('Error fetching documents: ' + error, 'error');
        });

      // Handle form submission
      documentForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        const userId = userSelect.value;
        const documentIds = Array.from(documentSelect.selectedOptions).map(option => parseInt(option.value));

        if (!userId || documentIds.length === 0) {
          showNotification('Please select a user and at least one document', 'error');
          return;
        }

        try {
          const response = await fetch('api.php?action=assign_documents', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ userId: parseInt(userId), documentIds })
          });

          const result = await response.json();
          console.log('Assign API Response:', result);
          if (response.ok) {
            showNotification(result.message, 'success');
            loadAssignedDocuments(userId);
            documentForm.reset();
          } else {
            showNotification(result.error || 'Failed to assign documents', 'error');
          }
        } catch (error) {
          console.error('Assign Fetch Error:', error);
          showNotification('Error assigning documents: ' + error.message, 'error');
        }
      });

      // Load assigned documents when user is selected
      userSelect.addEventListener('change', () => {
        currentUserId = userSelect.value || null;
        console.log('Selected userId:', currentUserId);
        loadAssignedDocuments(currentUserId);
      });

      // Load assigned documents
      async function loadAssignedDocuments(userId = null) {
        try {
          const url = userId
            ? `api.php?action=get_assigned_documents&userId=${userId}`
            : 'api.php?action=get_all_assigned_documents';
          const response = await fetch(url);
          if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
          }
          const documents = await response.json();
          console.log('Assigned documents:', documents);

          const documentsByUser = documents.reduce((acc, doc) => {
            if (!acc[doc.user_id]) {
              acc[doc.user_id] = { user_name: doc.user_name, documents: [] };
            }
            acc[doc.user_id].documents.push(doc);
            return acc;
          }, {});

          documentList.innerHTML = '';
          if (Object.keys(documentsByUser).length === 0) {
            documentList.innerHTML = '<p class="text-gray-500">No documents assigned</p>';
            return;
          }

          Object.entries(documentsByUser).forEach(([userId, { user_name, documents }]) => {
            const userItem = document.createElement('div');
            userItem.className = 'user-item bg-white shadow-md rounded-lg mb-2';
            userItem.innerHTML = `
                    <button class="user-toggle w-full text-left p-4 font-semibold text-gray-700 hover:bg-gray-100">
                        ${user_name}
                    </button>
                    <div class="user-documents hidden p-4 border-t border-gray-200">
                        <ul class="space-y-2">
                            ${documents.map(doc => `
                                <li class="flex justify-between items-center p-2 bg-gray-50 rounded">
                                    <div>
                                        <span class="font-medium">${doc.file_name}</span>
                                        <p class="text-sm text-gray-600">
                                            Assigned by ${doc.admin_name} on ${new Date(doc.uploaded_at).toLocaleString()}
                                        </p>
                                    </div>
                                    <button class="delete-btn text-red-500 hover:text-red-700" data-doc-id="${doc.id}">Delete</button>
                                </li>
                            `).join('')}
                        </ul>
                    </div>
                `;
            documentList.appendChild(userItem);
          });

          // Toggle document visibility
          document.querySelectorAll('.user-toggle').forEach(button => {
            button.addEventListener('click', () => {
              const docsDiv = button.nextElementSibling;
              docsDiv.classList.toggle('hidden');
              docsDiv.classList.toggle('show');
            });
          });

          document.querySelectorAll('.delete-btn').forEach(button => {
            button.addEventListener('click', async () => {
              const docId = parseInt(button.dataset.docId);
              console.log('Deleting document with ID:', docId);
              if (confirm('Are you sure you want to delete this document assignment?')) {
                try {
                  const response = await fetch('api.php?action=delete_document', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ docId })
                  });

                  const result = await response.json();
                  console.log('Delete API Response:', result);
                  if (response.ok) {
                    showNotification(result.message, 'success');
                    loadAssignedDocuments(currentUserId); // Reload the current user's documents
                  } else {
                    showNotification(result.error || 'Failed to delete document', 'error');
                  }
                } catch (error) {
                  console.error('Delete Fetch Error:', error);
                  showNotification('Error deleting document: ' + error.message, 'error');
                }
              }
            });
          });
        } catch (error) {
          console.error('Load Documents Error:', error);
          showNotification('Error loading assigned documents: ' + error, 'error');
        }
      }

      // Show notification using alert
      function showNotification(message, type) {
        alert(`${type.charAt(0).toUpperCase() + type.slice(1)}: ${message}`);
      }

      // Initial load of all assigned documents
      loadAssignedDocuments();

      // Section Navigation
      document.querySelectorAll('.sidebar-menu a[data-section]').forEach(link => {
        link.addEventListener('click', function (e) {
          e.preventDefault();
          document.querySelectorAll('.sidebar-menu a').forEach(a => a.classList.remove('active'));
          this.classList.add('active');
          document.querySelectorAll('.section').forEach(section => section.classList.remove('active'));
          const sectionId = this.getAttribute('data-section');
          document.getElementById(sectionId).classList.add('active');
        });
      });

      // Show Section
      function showSection(sectionId) {
        document.querySelectorAll('.section').forEach(section => section.classList.remove('active'));
        const targetSection = document.getElementById(sectionId);
        if (targetSection) {
          targetSection.classList.add('active');
        }
      }

      // Add Admin Modal
      function openAddAdminForm() {
        document.getElementById('add-admin-modal').style.display = 'block';
      }

      function closeAddAdminForm() {
        document.getElementById('add-admin-modal').style.display = 'none';
      }

      document.getElementById('add-admin-form').addEventListener('submit', function (e) {
        e.preventDefault();
        const formData = new FormData(this);
        fetch('add_admin.php', {
          method: 'POST',
          body: formData
        })
          .then(response => {
            if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);
            return response.text();
          })
          .then(text => {
            console.log('Add Admin Response:', text);
            try {
              const data = JSON.parse(text);
              showNotification(data.message || 'Admin added successfully', data.status);
              if (data.status === 'success') {
                closeAddAdminForm();
                location.reload();
              }
            } catch (e) {
              console.error('Invalid JSON from add_admin.php:', text);
              showNotification('Error: Invalid response from add_admin.php. Check console for details.', 'error');
            }
          })
          .catch(error => {
            console.error('Fetch error for add_admin.php:', error);
            showNotification('Error: ' + error.message, 'error');
          });
      });

      // Edit Admin Modal
      function openEditAdminForm(adminId) {
        console.log('Opening edit form for admin ID:', adminId);
        fetch(`get_admin.php?id=${adminId}`)
          .then(response => {
            console.log('Fetch response status:', response.status);
            if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);
            return response.text();
          })
          .then(text => {
            console.log('Get Admin Response:', text);
            try {
              const admin = JSON.parse(text);
              if (admin && admin.id) {
                console.log('Admin data loaded:', admin);
                document.getElementById('editAdminIndex').value = admin.id;
                document.getElementById('editAdminName').value = admin.name || '';
                document.getElementById('editAdminEmail').value = admin.email || '';
                document.getElementById('editAdminMobile').value = admin.mobile || '';
                document.getElementById('editAdminAadhar').value = admin.aadhaar || '';
                const profilePreview = document.getElementById('editAdminProfilePreview');
                if (profilePreview) {
                  profilePreview.src = admin.profile_picture || 'https://via.placeholder.com/120';
                }
                const modal = document.getElementById('editAdminModal');
                if (modal) {
                  modal.style.display = 'block';
                  console.log('Modal should now be visible');
                } else {
                  console.error('Modal element not found');
                  showNotification('Error: Edit modal not found in the DOM', 'error');
                }
              } else {
                console.log('Admin data not found in response');
                showNotification('Admin not found.', 'error');
              }
            } catch (e) {
              console.error('Invalid JSON from get_admin.php:', text);
              showNotification('Error: Invalid response from get_admin.php. Check console for details.', 'error');
            }
          })
          .catch(error => {
            console.error('Fetch error for get_admin.php:', error);
            showNotification('Error: ' + error.message, 'error');
          });
      }

      function closeEditAdminForm() {
        const modal = document.getElementById('editAdminModal');
        if (modal) {
          modal.style.display = 'none';
        }
      }

      document.getElementById('editAdminForm').addEventListener('submit', function (e) {
        e.preventDefault();
        const formData = new FormData(this);
        fetch('update_admin.php', {
          method: 'POST',
          body: formData
        })
          .then(response => {
            if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);
            return response.text();
          })
          .then(text => {
            console.log('Update Admin Response:', text);
            try {
              const data = JSON.parse(text);
              showNotification(data.message || 'Admin updated successfully', data.status);
              if (data.status === 'success') {
                closeEditAdminForm();
                location.reload();
              }
            } catch (e) {
              console.error('Invalid JSON from update_admin.php:', text);
              showNotification('Error: Invalid response from update_admin.php. Check console for details.', 'error');
            }
          })
          .catch(error => {
            console.error('Fetch error for update_admin.php:', error);
            showNotification('Error: ' + error.message, 'error');
          });
      });

      // Delete Admin
      function deleteAdmin(adminId) {
        if (confirm('Are you sure you want to delete this admin?')) {
          fetch('delete_admin.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'id=' + adminId
          })
            .then(response => {
              if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);
              return response.text();
            })
            .then(text => {
              console.log('Delete Admin Response:', text);
              try {
                const data = JSON.parse(text);
                showNotification(data.message || 'Admin deleted successfully', data.status);
                if (data.status === 'success') {
                  location.reload();
                }
              } catch (e) {
                console.error('Invalid JSON from delete_admin.php:', text);
                showNotification('Error: Invalid response from delete_admin.php. Check console for details.', 'error');
              }
            })
            .catch(error => {
              console.error('Fetch error for delete_admin.php:', error);
              showNotification('Error: ' + error.message, 'error');
            });
        }
      }

      // File Management
      document.getElementById('uploadForm').addEventListener('submit', function (e) {
        e.preventDefault();
        const formData = new FormData(this);
        fetch('upload.php', {
          method: 'POST',
          body: formData
        })
          .then(response => {
            if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);
            return response.text();
          })
          .then(text => {
            console.log('Upload File Response:', text);
            try {
              const data = JSON.parse(text);
              showNotification(data.message || 'File uploaded successfully', data.status);
              if (data.status === 'success') {
                fetchFiles();
              }
            } catch (e) {
              console.error('Invalid JSON from upload.php:', text);
              showNotification('Error: Invalid response from upload.php. Check console for details.', 'error');
            }
          })
          .catch(error => {
            console.error('Fetch error for upload.php:', error);
            showNotification('Error: ' + error.message, 'error');
          });
      });

      function fetchFiles() {
        fetch('fetch_files.php')
          .then(response => {
            if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);
            return response.text();
          })
          .then(text => {
            console.log('Fetch Files Response:', text);
            try {
              const data = JSON.parse(text);
              const fileGrid = document.getElementById('fileGrid');
              fileGrid.innerHTML = '';
              if (data.status === 'success') {
                console.log('Files to display:', data.files);
                if (data.files.length === 0) {
                  fileGrid.innerHTML = '<p>No files found.</p>';
                  return;
                }
                data.files.forEach(file => {
                  const fileCard = document.createElement('div');
                  fileCard.classList.add('file-card');
                  fileCard.innerHTML = `
                            <div class="file-card-content">
                                <h4>${file.file_name}</h4>
                                <p>Size: ${file.file_size}</p>
                                <p>Type: ${file.file_type}</p>
                                <a href="Uploads/${file.file_name}" target="_blank" class="download-btn">
                                    <i class="fas fa-download"></i>
                                </a>
                                <button class="delete-btn" data-id="${file.id}">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        `;
                  fileGrid.appendChild(fileCard);
                });
                document.querySelectorAll('.delete-btn').forEach(button => {
                  button.addEventListener('click', function () {
                    const fileId = this.getAttribute('data-id');
                    deleteFile(fileId);
                  });
                });
              } else {
                fileGrid.innerHTML = '<p>No files found.</p>';
              }
            } catch (e) {
              console.error('Invalid JSON from fetch_files.php:', text);
              showNotification('Error: Invalid response from fetch_files.php. Check console for details.', 'error');
            }
          })
          .catch(error => {
            console.error('Fetch error for fetch_files.php:', error);
            document.getElementById('fileGrid').innerHTML = '<p>Error loading files.</p>';
          });
      }

      function deleteFile(fileId) {
        if (confirm('Are you sure you want to delete this file?')) {
          fetch('remove_file.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: fileId })
          })
            .then(response => {
              if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);
              return response.text();
            })
            .then(text => {
              console.log('Delete File Response:', text);
              try {
                const data = JSON.parse(text);
                showNotification(data.message || 'File deleted successfully', data.status);
                if (data.status === 'success') {
                  fetchFiles();
                }
              } catch (e) {
                console.error('Invalid JSON from remove_file.php:', text);
                showNotification('Error: Invalid response from remove_file.php. Check console for details.', 'error');
              }
            })
            .catch(error => {
              console.error('Fetch error for remove_file.php:', error);
              showNotification('Error: ' + error.message, 'error');
            });
        }
      }

      // Initialize File Management and Section Navigation
      fetchFiles();
      document.querySelector('.sidebar-menu a[data-section="dashboard"]').classList.add('active');

      // Modal Close on Outside Click
      window.onclick = function (event) {
        const addModal = document.getElementById('add-admin-modal');
        const editModal = document.getElementById('editAdminModal');
        if (event.target === addModal) closeAddAdminForm();
        if (event.target === editModal) closeEditAdminForm();
      };
    });
  </script>
</body>

</html>