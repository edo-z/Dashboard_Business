<?php
require_once "auth.php"; // validasi JWT
$user = $_SESSION['user'];
$username = $user['name'];
$role     = $user['role'];
// kirim JWT & API key ke frontend JS
$jwt     = $_SESSION['jwt'];
$apiKey  = "1ee34e9824617bb465cc92c7ccdcdb04ad2303f16560b8ee68cf0609517cbafd51828c39a45ad57f3bb4e3532a1da6a11fc30bd571528a161d9f1b8e2bceec8d"; 
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard - Mokko Project</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="icon" href="https://www.mokko.co.id/images/fevicon/icon.png" type="image/gif">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="https://unpkg.com/aos@2.3.1/dist/aos.css" />
  <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            'jordy-blue': {
              DEFAULT: '#8BB4E4',
              100: '#0e233b',
              200: '#1c4677',
              300: '#2a6ab2',
              400: '#508ed6',
              500: '#8bb4e4',
              600: '#a2c4e9',
              700: '#bad2ef',
              800: '#d1e1f4',
              900: '#e8f0fa'
            },
            'un-blue': {
              DEFAULT: '#5592D6',
              100: '#0c1d30',
              200: '#173a61',
              300: '#235791',
              400: '#2f73c2',
              500: '#5592d6',
              600: '#78a8de',
              700: '#9abee7',
              800: '#bcd3ef',
              900: '#dde9f7'
            },
            'un-blue-2': {
              DEFAULT: '#5B91D1',
              100: '#0d1c2f',
              200: '#1a395e',
              300: '#28558d',
              400: '#3572bc',
              500: '#5b91d1',
              600: '#7da7da',
              700: '#9dbde3',
              800: '#bed3ed',
              900: '#dee9f6'
            },
            'bronze': {
              DEFAULT: '#DA8235',
              100: '#2e1a08',
              200: '#5b3411',
              300: '#894d19',
              400: '#b76722',
              500: '#da8235',
              600: '#e19b5d',
              700: '#e9b485',
              800: '#f0cdae',
              900: '#f8e6d6'
            }
          },
          animation: {
            'fade-in': 'fadeIn 0.6s ease-out',
            'slide-up': 'slideUp 0.8s ease-out',
            'slide-in': 'slideIn 0.3s ease-out',
          },
          keyframes: {
            fadeIn: {
              '0%': { opacity: '0' },
              '100%': { opacity: '1' },
            },
            slideUp: {
              '0%': {
                opacity: '0',
                transform: 'translateY(20px)'
              },
              '100%': {
                opacity: '1',
                transform: 'translateY(0)'
              }
            },
            slideIn: {
              '0%': {
                opacity: '0',
                transform: 'translateX(-10px)'
              },
              '100%': {
                opacity: '1',
                transform: 'translateX(0)'
              }
            }
          }
        }
      }
    }
  </script>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

    body {
      font-family: 'Inter', sans-serif;
    }

    .sidebar-item {
      transition: all 0.2s ease;
    }

    .sidebar-item:hover {
      transform: translateX(5px);
    }

    .sidebar-item.active {
      border-left: 4px solid #5B91D1;
      background-color: rgba(91, 145, 209, 0.1);
    }

    .card {
      transition: all 0.3s ease;
    }

    .card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
    }

    .stat-card {
      background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
      border-left: 4px solid #5B91D1;
      transition: all 0.3s ease;
    }

    .stat-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 20px rgba(0, 0, 0, 0.05);
    }

    .table-row {
      transition: all 0.2s ease;
    }

    .table-row:hover {
      background-color: rgba(91, 145, 209, 0.05);
    }

    .btn-primary {
      background: #5B91D1;
      transition: all 0.3s ease;
    }

    .btn-primary:hover {
      background: #5592D6;
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(91, 145, 209, 0.3);
    }

    .btn-danger {
      background: #ef4444;
      transition: all 0.3s ease;
    }

    .btn-danger:hover {
      background: #dc2626;
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(239, 68, 68, 0.3);
    }

    .modal-backdrop {
      backdrop-filter: blur(5px);
    }

    .loading-spinner {
      border: 3px solid rgba(91, 145, 209, 0.2);
      border-radius: 50%;
      border-top: 3px solid #5B91D1;
      width: 20px;
      height: 20px;
      animation: spin 1s linear infinite;
    }

    @keyframes spin {
      0% {
        transform: rotate(0deg);
      }

      100% {
        transform: rotate(360deg);
      }
    }

    .toast {
      position: fixed;
      bottom: 20px;
      right: 20px;
      padding: 16px;
      border-radius: 8px;
      color: white;
      font-weight: 500;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
      z-index: 1000;
      animation: slideInRight 0.3s ease-out, slideOutRight 0.3s ease-out 2.7s;
      animation-fill-mode: forwards;
    }

    @keyframes slideInRight {
      from {
        transform: translateX(100%);
        opacity: 0;
      }

      to {
        transform: translateX(0);
        opacity: 1;
      }
    }

    @keyframes slideOutRight {
      from {
        transform: translateX(0);
        opacity: 1;
      }

      to {
        transform: translateX(100%);
        opacity: 0;
      }
    }

    .sidebar-collapsed {
      width: 80px;
    }

    .sidebar-collapsed .sidebar-text {
      display: none;
    }

    .sidebar-collapsed .sidebar-logo {
      justify-content: center;
    }

    @media (max-width: 768px) {
      .sidebar {
        position: fixed;
        left: -100%;
        z-index: 50;
        transition: left 0.3s ease;
      }

      .sidebar.active {
        left: 0;
      }

      .sidebar-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        z-index: 40;
        display: none;
      }

      .sidebar-overlay.active {
        display: block;
      }
    }

    /* Chart container fixes */
    .chart-container {
      position: relative;
      height: 300px;
      width: 100%;
    }

    .chart-container canvas {
      position: absolute;
      top: 0;
      left: 0;
      width: 100% !important;
      height: 100% !important;
    }
  </style>
</head>

<body class="bg-gray-50">
  <!-- Sidebar Overlay for Mobile -->
  <div class="sidebar-overlay" onclick="toggleSidebar()"></div>

  <div class="flex h-screen">
    <!-- Sidebar -->
    <aside id="sidebar" class="sidebar w-64 bg-white shadow-md min-h-screen flex flex-col">
      <div class="p-4 flex items-center justify-between sidebar-logo">
        <div class="flex items-center space-x-2">
          <img src="https://www.mokko.co.id/images/logos/mokkologo.png" alt="Mokko Project">
        </div>
        <button onclick="toggleSidebar()" class="md:hidden text-gray-500">
          <i class="fas fa-times"></i>
        </button>
      </div>

      <div class="flex-1 overflow-y-auto">
        <nav class="mt-6 space-y-1 px-2">
          <?php if ($role === "admin"): ?>
          <a href="#" onclick="loadSummary()"
            class="sidebar-item active flex items-center px-4 py-3 text-gray-700 rounded-lg">
            <i class="fas fa-home w-5 mr-3"></i>
            <span class="sidebar-text">Dashboard</span>
          </a>
          <a href="#" onclick="fetchData('customers')"
            class="sidebar-item flex items-center px-4 py-3 text-gray-700 rounded-lg">
            <i class="fas fa-users w-5 mr-3"></i>
            <span class="sidebar-text">Customers</span>
          </a>
          <a href="#" onclick="fetchData('quotations')"
            class="sidebar-item flex items-center px-4 py-3 text-gray-700 rounded-lg">
            <i class="fas fa-file-invoice w-5 mr-3"></i>
            <span class="sidebar-text">Quotations</span>
          </a>
          <a href="#" onclick="fetchData('invoices')"
            class="sidebar-item flex items-center px-4 py-3 text-gray-700 rounded-lg">
            <i class="fas fa-file-invoice-dollar w-5 mr-3"></i>
            <span class="sidebar-text">Invoices</span>
          </a>
          <a href="#" onclick="fetchData('deliveryorders')"
            class="sidebar-item flex items-center px-4 py-3 text-gray-700 rounded-lg">
            <i class="fas fa-truck w-5 mr-3"></i>
            <span class="sidebar-text">Delivery Orders</span>
          </a>
          <?php else: ?>
          <a href="#" onclick="fetchData('profile')"
            class="sidebar-item flex items-center px-4 py-3 text-gray-700 rounded-lg">
            <i class="fas fa-user w-5 mr-3"></i>
            <span class="sidebar-text">My Profile</span>
          </a>
          <a href="#" onclick="fetchData('quotations')"
            class="sidebar-item flex items-center px-4 py-3 text-gray-700 rounded-lg">
            <i class="fas fa-file-invoice w-5 mr-3"></i>
            <span class="sidebar-text">My Quotations</span>
          </a>
          <?php endif; ?>
        </nav>
      </div>

      <div class="p-4 border-t">
        <a href="logout.php" class="sidebar-item flex items-center px-4 py-3 text-red-600 rounded-lg">
          <i class="fas fa-sign-out-alt w-5 mr-3"></i>
          <span class="sidebar-text">Logout</span>
        </a>
      </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col overflow-hidden">
      <!-- Top Bar -->
      <header class="bg-white shadow-sm z-10">
        <div class="flex items-center justify-between p-4">
          <div class="flex items-center">
            <button onclick="toggleSidebar()" class="mr-4 text-gray-500 md:hidden">
              <i class="fas fa-bars"></i>
            </button>
            <h1 class="text-xl font-semibold text-gray-800">Dashboard</h1>
          </div>

          <div class="flex items-center space-x-4">
            <div class="relative">
              <button class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-bell"></i>
              </button>
              <span class="absolute top-0 right-0 w-2 h-2 bg-red-500 rounded-full"></span>
            </div>

            <div class="flex items-center space-x-2">
              <div class="w-8 h-8 rounded-full bg-un-blue-2 flex items-center justify-center text-white">
                <?= strtoupper(substr($username, 0, 1)) ?>
              </div>
              <span class="hidden md:block text-gray-700">
                <?= htmlspecialchars($username) ?>
              </span>
            </div>
          </div>
        </div>
      </header>

      <!-- Page Content -->
      <div class="flex-1 overflow-y-auto p-6" id="main-content">
        <div class="mb-6">
          <h1 class="text-2xl font-bold text-gray-800">Selamat datang,
            <?= htmlspecialchars($username) ?> 👋
          </h1>
          <p class="mt-1 text-gray-600">Anda login sebagai <span class="font-semibold">
              <?= htmlspecialchars($role) ?>
            </span>.</p>
        </div>

        <!-- Konten akan dimuat di sini -->
        <div id="content" class="mt-4"></div>
      </div>
    </main>
  </div>

  <!-- Modal -->
  <div id="modal"
    class="fixed inset-0 modal-backdrop bg-black bg-opacity-50 hidden z-50 flex justify-center items-center p-4">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-lg animate-slide-up">
      <div class="flex justify-between items-center border-b p-4">
        <h3 id="modalTitle" class="text-lg font-bold text-gray-800">Form</h3>
        <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700">
          <i class="fas fa-times"></i>
        </button>
      </div>
      <div class="p-4">
        <form id="modalForm" class="space-y-4"></form>
      </div>
      <div class="flex justify-end border-t p-4 space-x-2">
        <button onclick="closeModal()"
          class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition">
          Batal
        </button>
        <button type="submit" form="modalForm" class="px-4 py-2 btn-primary text-white rounded-lg">
          Simpan
        </button>
      </div>
    </div>
  </div>

  <!-- Loading Overlay -->
  <div id="loadingOverlay" class="fixed inset-0 bg-black bg-opacity-30 hidden z-50 flex justify-center items-center">
    <div class="bg-white p-4 rounded-lg shadow-lg flex items-center space-x-3">
      <div class="loading-spinner"></div>
      <span class="text-gray-700">Loading...</span>
    </div>
  </div>
  <script>
    const jwt = "<?= $jwt ?>";
    const apiKey = "<?= $apiKey ?>";
    const baseUrl = "http://mokkoproject.biz.id/Mokko_Businness/src/api"; // Diubah ke HTTPS
    const user = <?= json_encode($user) ?>;
    const role = "<?= $role ?>";

    // Initialize AOS
    AOS.init({
      duration: 600,
      once: true,
      offset: 50
    });

    // Toggle sidebar for mobile
    function toggleSidebar() {
      const sidebar = document.getElementById('sidebar');
      const sidebarOverlay = document.querySelector('.sidebar-overlay');

      sidebar.classList.toggle('active');
      sidebarOverlay.classList.toggle('active');
    }

    // Set active sidebar item
    function setActiveSidebarItem() {
      const sidebarItems = document.querySelectorAll('.sidebar-item');
      sidebarItems.forEach(item => {
        item.addEventListener('click', function () {
          sidebarItems.forEach(i => i.classList.remove('active'));
          this.classList.add('active');
        });
      });
    }

    document.addEventListener("DOMContentLoaded", function () {
      loadSummary();
      setActiveSidebarItem();
    });

    // Show loading overlay
    function showLoading() {
      document.getElementById('loadingOverlay').classList.remove('hidden');
    }

    // Hide loading overlay
    function hideLoading() {
      document.getElementById('loadingOverlay').classList.add('hidden');
    }

    // Show toast notification
    function showToast(message, type = 'success') {
      const toast = document.createElement('div');
      toast.className = `toast ${type === 'success' ? 'bg-green-500' : 'bg-red-500'}`;
      toast.textContent = message;
      document.body.appendChild(toast);

      setTimeout(() => {
        toast.remove();
      }, 3000);
    }

    // Fungsi untuk mengambil data dropdown dari API
    async function fetchDropdownData(endpoint) {
      try {
        const res = await fetch(`${baseUrl}/${endpoint}.php`, {
          method: 'GET',
          headers: {
            "Authorization": "Bearer " + jwt,
            "X-API-KEY": apiKey,
            "Content-Type": "application/json"
          },
          mode: 'cors'
        });

        if (!res.ok) {
          throw new Error(`HTTP error! status: ${res.status}`);
        }

        const data = await res.json();
        console.log(`Fetched ${endpoint} data:`, data);

        // Handle different response formats
        let dataArray = data;
        if (data && !Array.isArray(data)) {
          if (data.data && Array.isArray(data.data)) {
            dataArray = data.data;
          } else if (data.results && Array.isArray(data.results)) {
            dataArray = data.results;
          } else if (data.items && Array.isArray(data.items)) {
            dataArray = data.items;
          } else {
            console.warn(`${endpoint} data is not in expected format:`, data);
            dataArray = [];
          }
        }

        return dataArray;
      } catch (error) {
        console.error(`Error fetching ${endpoint}:`, error);
        showToast(`Failed to load ${endpoint} data`, 'error');
        return [];
      }
    }

    // Load dashboard summary
    async function loadSummary() {
      showLoading();
      try {
        console.log('Loading summary from:', `${baseUrl}/summary.php`);
        const res = await fetch(`${baseUrl}/summary.php`, {
          method: 'GET',
          headers: {
            "Authorization": "Bearer " + jwt,
            "X-API-KEY": apiKey,
            "Content-Type": "application/json"
          },
          mode: 'cors'
        });
        console.log('Response status:', res.status);
        console.log('Response ok:', res.ok);

        if (!res.ok) {
          const errorText = await res.text();
          console.error('Error response:', errorText);
          throw new Error(`Failed to load summary: HTTP ${res.status} - ${errorText}`);
        }

        const data = await res.json();
        console.log('Summary data:', data);
        console.log('Full API response:', data);

        // Store data globally for charts
        window.dashboardData = data;

        // Jika tidak ada data counts, gunakan data default
        const counts = data.counts || {
          customers: 0,
          quotations: 0,
          invoices: 0,
          deliveryorders: 0
        };

        // Generate recent activities HTML from API data
        const recentActivities = generateRecentActivities(data);

        let html = `
          <!-- Stats Cards -->
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="stat-card p-6 rounded-xl shadow" data-aos="fade-up" data-aos-delay="100">
              <div class="flex items-center">
                <div class="p-3 rounded-lg bg-un-blue-2/10">
                  <i class="fas fa-users text-un-blue-2 text-xl"></i>
                </div>
                <div class="ml-4">
                  <h3 class="text-sm font-medium text-gray-500">Customers</h3>
                  <p class="text-2xl font-bold text-gray-900">${counts.customers}</p>
                </div>
              </div>
            </div>
            
            <div class="stat-card p-6 rounded-xl shadow" data-aos="fade-up" data-aos-delay="200">
              <div class="flex items-center">
                <div class="p-3 rounded-lg bg-jordy-blue/10">
                  <i class="fas fa-file-invoice text-jordy-blue text-xl"></i>
                </div>
                <div class="ml-4">
                  <h3 class="text-sm font-medium text-gray-500">Quotations</h3>
                  <p class="text-2xl font-bold text-gray-900">${counts.quotations}</p>
                </div>
              </div>
            </div>
            
            <div class="stat-card p-6 rounded-xl shadow" data-aos="fade-up" data-aos-delay="300">
              <div class="flex items-center">
                <div class="p-3 rounded-lg bg-bronze/10">
                  <i class="fas fa-file-invoice-dollar text-bronze text-xl"></i>
                </div>
                <div class="ml-4">
                  <h3 class="text-sm font-medium text-gray-500">Invoices</h3>
                  <p class="text-2xl font-bold text-gray-900">${counts.invoices}</p>
                </div>
              </div>
            </div>
            
            <div class="stat-card p-6 rounded-xl shadow" data-aos="fade-up" data-aos-delay="400">
              <div class="flex items-center">
                <div class="p-3 rounded-lg bg-un-blue/10">
                  <i class="fas fa-truck text-un-blue text-xl"></i>
                </div>
                <div class="ml-4">
                  <h3 class="text-sm font-medium text-gray-500">Delivery Orders</h3>
                  <p class="text-2xl font-bold text-gray-900">${counts.deliveryorders}</p>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Charts Row -->
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <div class="bg-white p-6 rounded-xl shadow" data-aos="fade-up" data-aos-delay="500">
              <h3 class="text-lg font-semibold text-gray-800 mb-4">Monthly Statistics</h3>
              <div class="chart-container">
                <canvas id="monthlyChart"></canvas>
              </div>
            </div>
            
            <div class="bg-white p-6 rounded-xl shadow" data-aos="fade-up" data-aos-delay="600">
              <h3 class="text-lg font-semibold text-gray-800 mb-4">Status Distribution</h3>
              <div class="chart-container">
                <canvas id="statusChart"></canvas>
              </div>
            </div>
          </div>
          
          <!-- Recent Activities -->
          <div class="bg-white p-6 rounded-xl shadow" data-aos="fade-up" data-aos-delay="700">
            <div class="flex justify-between items-center mb-4">
              <h3 class="text-lg font-semibold text-gray-800">Recent Activities</h3>
              <a href="#" onclick="fetchData('quotations')" class="text-sm text-un-blue-2 hover:underline">View All</a>
            </div>
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reference</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  ${recentActivities}
                </tbody>
              </table>
            </div>
          </div>
        `;

        document.getElementById("content").innerHTML = html;

        // Initialize charts after DOM is updated
        setTimeout(() => {
          initializeCharts();
        }, 300);

      } catch (error) {
        console.error('Error loading summary:', error);
        showToast(`Failed to load dashboard data: ${error.message}`, 'error');

        document.getElementById("content").innerHTML = `
          <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded" data-aos="fade-up">
            <div class="flex">
              <div class="flex-shrink-0">
                <i class="fas fa-exclamation-triangle text-red-500"></i>
              </div>
              <div class="ml-3">
                <h3 class="text-sm font-medium text-red-800">Error Loading Dashboard</h3>
                <div class="mt-2 text-sm text-red-700">
                  <p>${error.message}</p>
                  <p class="mt-2">Please check your internet connection or server configuration and try again.</p>
                </div>
                <div class="mt-4">
                  <button onclick="loadSummary()" class="btn-primary text-white px-4 py-2 rounded-lg">
                    <i class="fas fa-redo mr-2"></i> Retry
                  </button>
                </div>
              </div>
            </div>
          </div>
        `;
      } finally {
        hideLoading();
      }
    }

    // Helper function to generate recent activities HTML
    function generateRecentActivities(data) {
      let activities = [];

      if (data.latest_quotations && Array.isArray(data.latest_quotations)) {
        data.latest_quotations.forEach(item => {
          activities.push({
            type: 'Quotation',
            icon: 'fas fa-file-invoice',
            iconColor: 'jordy-blue',
            reference: item.QuotationID || item.QuotationCode || `QTN-${item.QuotationID}`,
            date: formatDate(item.QuotationDate || item.Date),
            status: item.QuotationStatus || item.Status || 'Unknown',
            statusClass: getStatusClass(item.QuotationStatus || item.Status)
          });
        });
      }

      if (data.latest_invoices && Array.isArray(data.latest_invoices)) {
        data.latest_invoices.forEach(item => {
          activities.push({
            type: 'Invoice',
            icon: 'fas fa-file-invoice-dollar',
            iconColor: 'bronze',
            reference: item.InvoiceID || item.InvoiceCode || `INV-${item.InvoiceID}`,
            date: formatDate(item.InvoiceDate || item.Date),
            status: item.InvoiceStatus || item.Status || 'Unknown',
            statusClass: getStatusClass(item.InvoiceStatus || item.Status)
          });
        });
      }

      if (data.latest_deliveries && Array.isArray(data.latest_deliveries)) {
        data.latest_deliveries.forEach(item => {
          activities.push({
            type: 'Delivery',
            icon: 'fas fa-truck',
            iconColor: 'un-blue',
            reference: item.DeliveryID || item.DOID || `DLV-${item.DeliveryID}`,
            date: formatDate(item.DODate || item.Date),
            status: item.DOStatus || item.Status || 'Unknown',
            statusClass: getStatusClass(item.DOStatus || item.Status)
          });
        });
      }

      activities.sort((a, b) => new Date(b.date) - new Date(a.date));
      activities = activities.slice(0, 5);

      return activities.map(activity => `
        <tr class="table-row">
          <td class="px-6 py-4 whitespace-nowrap">
            <div class="flex items-center">
              <div class="p-2 rounded-full bg-${activity.iconColor}/10">
                <i class="fas ${activity.icon} text-${activity.iconColor}"></i>
              </div>
              <span class="ml-2">${activity.type}</span>
            </div>
          </td>
          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${activity.reference}</td>
          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${activity.date}</td>
          <td class="px-6 py-4 whitespace-nowrap">
            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${activity.statusClass}">
              ${activity.status}
            </span>
          </td>
        </tr>
      `).join('');
    }

    function formatDate(dateString) {
      if (!dateString) return 'No date';

      const date = new Date(dateString);
      const now = new Date();
      const diffTime = Math.abs(now - date);
      const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24));

      if (diffDays === 0) {
        return 'Today, ' + date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
      } else if (diffDays === 1) {
        return 'Yesterday, ' + date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
      } else if (diffDays < 7) {
        return diffDays + ' days ago';
      } else {
        return date.toLocaleDateString('id-ID', {
          year: 'numeric',
          month: 'short',
          day: 'numeric'
        });
      }
    }

    function getStatusClass(status) {
      const statusLower = (status || '').toLowerCase();

      switch (statusLower) {
        case 'approved':
        case 'paid':
        case 'completed':
        case 'delivered':
          return 'bg-green-100 text-green-800';
        case 'pending':
        case 'in progress':
        case 'in transit':
        case 'sent':
        case 'shipped':
          return 'bg-yellow-100 text-yellow-800';
        case 'rejected':
        case 'cancelled':
          return 'bg-red-100 text-red-800';
        case 'draft':
        case 'unpaid':
        case 'not shipped':
          return 'bg-blue-100 text-blue-800';
        default:
          return 'bg-gray-100 text-gray-800';
      }
    }

    function initializeCharts() {
      console.log('Initializing charts...');
      console.log('Dashboard data:', window.dashboardData);

      const monthlyCtx = document.getElementById('monthlyChart');
      if (monthlyCtx) {
        console.log('Found monthly chart canvas');

        const existingChart = Chart.getChart(monthlyCtx);
        if (existingChart) {
          existingChart.destroy();
        }

        const monthlyData = window.dashboardData?.monthly_statistics || {
          labels: ['No Data'],
          quotations: [0],
          invoices: [0]
        };

        console.log('Monthly data for chart:', monthlyData);

        const dateRange = monthlyData.date_range
          ? `${monthlyData.date_range.first_date} - ${monthlyData.date_range.last_date}`
          : 'No date range info';

        console.log('Date range:', dateRange);

        new Chart(monthlyCtx, {
          type: 'line',
          data: {
            labels: monthlyData.labels,
            datasets: [{
              label: 'Quotations',
              data: monthlyData.quotations,
              borderColor: '#8BB4E4',
              backgroundColor: 'rgba(139, 180, 228, 0.1)',
              tension: 0.3,
              fill: true
            }, {
              label: 'Invoices',
              data: monthlyData.invoices,
              borderColor: '#DA8235',
              backgroundColor: 'rgba(218, 130, 53, 0.1)',
              tension: 0.3,
              fill: true
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              title: {
                display: true,
                text: `Monthly Statistics (${dateRange})`,
                font: {
                  size: 16
                }
              },
              legend: {
                position: 'top',
              },
              tooltip: {
                mode: 'index',
                intersect: false,
              }
            },
            scales: {
              y: {
                beginAtZero: true,
                grid: {
                  color: 'rgba(0, 0, 0, 0.05)'
                },
                title: {
                  display: true,
                  text: 'Jumlah Transaksi'
                }
              },
              x: {
                grid: {
                  display: false
                },
                title: {
                  display: true,
                  text: 'Periode'
                }
              }
            }
          }
        });
      } else {
        console.error('Monthly chart canvas not found');
      }

      const statusCtx = document.getElementById('statusChart');
      if (statusCtx) {
        console.log('Found status chart canvas');

        const existingStatusChart = Chart.getChart(statusCtx);
        if (existingStatusChart) {
          existingStatusChart.destroy();
        }

        const statusData = window.dashboardData?.status_distribution || {
          labels: ['No Data'],
          values: [0]
        };

        console.log('Status data for chart:', statusData);

        new Chart(statusCtx, {
          type: 'doughnut',
          data: {
            labels: statusData.labels,
            datasets: [{
              data: statusData.values,
              backgroundColor: [
                '#10B981',
                '#F59E0B',
                '#3B82F6',
                '#EF4444'
              ],
              borderWidth: 0
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              title: {
                display: true,
                text: 'Status Distribution',
                font: {
                  size: 16
                }
              },
              legend: {
                position: 'right',
                labels: {
                  padding: 20,
                  usePointStyle: true,
                  pointStyle: 'circle'
                }
              },
              tooltip: {
                callbacks: {
                  label: function (context) {
                    let label = context.label || '';
                    if (label) {
                      label += ': ';
                    }
                    label += context.parsed;
                    return label;
                  }
                }
              }
            }
          }
        });
      } else {
        console.error('Status chart canvas not found');
      }
    }

    async function fetchData(endpoint) {
      showLoading();
      try {
        console.log('Fetching data for:', endpoint);

        let url = `${baseUrl}/${endpoint}.php`;

        const res = await fetch(url, {
          method: 'GET',
          headers: {
            "Authorization": "Bearer " + jwt,
            "X-API-KEY": apiKey,
            "Content-Type": "application/json"
          },
          mode: 'cors'
        });

        console.log('Response status:', res.status);

        if (!res.ok) {
          const errorText = await res.text();
          console.error('Error response:', errorText);

          try {
            const errorJson = JSON.parse(errorText);
            throw new Error(`HTTP error! status: ${res.status}, message: ${errorJson.error || errorText}`);
          } catch (e) {
            throw new Error(`HTTP error! status: ${res.status}, message: ${errorText}`);
          }
        }

        const data = await res.json();
        console.log('Fetched data:', data);
        console.log('Data type:', typeof data);
        console.log('Is array:', Array.isArray(data));

        let dataArray = data;
        if (data && !Array.isArray(data)) {
          if (data.data && Array.isArray(data.data)) {
            dataArray = data.data;
          } else if (data.results && Array.isArray(data.results)) {
            dataArray = data.results;
          } else if (data.items && Array.isArray(data.items)) {
            dataArray = data.items;
          } else {
            console.warn('Data is not in expected format:', data);
            dataArray = [];
          }
        }

        switch (endpoint) {
          case "customers":
            renderTable("Customers", dataArray, ["CustomerID", "CustomerCode", "Name", "Email", "Phone", "Address"]);
            break;
          case "quotations":
            renderTable("Quotations", dataArray, ["QuotationID", "QuotationCode", "CustomerID", "QuotationDate", "Status", "Notes"]);
            break;
          case "invoices":
            renderTable("Invoices", dataArray, ["InvoiceID", "InvoiceCode", "QuotationID", "InvoiceDate", "Status", "TotalAmount"]);
            break;
          case "deliveryorders":
            renderTable("Delivery Orders", dataArray, ["DOID", "DOCode", "InvoiceID", "DODate", "Status", "Notes"]);
            break;
          case "profile":
            renderProfile(data);
            break;
          default:
            document.getElementById("content").innerHTML = `
              <div class="bg-white p-6 rounded-xl shadow" data-aos="fade-up">
                <h2 class="text-xl font-bold mb-4 capitalize">${endpoint}</h2>
                <pre class="bg-gray-900 text-green-400 p-4 rounded overflow-x-auto">${JSON.stringify(data, null, 2)}</pre>
              </div>
            `;
        }
      } catch (error) {
        console.error('Error fetching data:', error);
        showToast('Failed to load data: ' + error.message, 'error');

        document.getElementById("content").innerHTML = `
          <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded" data-aos="fade-up">
            <div class="flex">
              <div class="flex-shrink-0">
                <i class="fas fa-exclamation-triangle text-red-500"></i>
              </div>
              <div class="ml-3">
                <h3 class="text-sm font-medium text-red-800">Error Loading Data</h3>
                <div class="mt-2 text-sm text-red-700">
                  <p>${error.message}</p>
                  <p class="mt-2">Please check your internet connection and try again.</p>
                </div>
                <div class="mt-4">
                  <button onclick="fetchData('${endpoint}')" class="btn-primary text-white px-4 py-2 rounded-lg">
                    <i class="fas fa-redo mr-2"></i> Retry
                  </button>
                </div>
              </div>
            </div>
          </div>
        `;
      } finally {
        hideLoading();
      }
    }

    function renderProfile(data) {
      const quotations = Array.isArray(data.quotations) ? data.quotations : [];
      const invoices = Array.isArray(data.invoices) ? data.invoices : [];
      const deliveries = Array.isArray(data.deliveries) ? data.deliveries : [];
      const stats = data.stats || {};

      let html = `
        <div class="bg-white p-6 rounded-xl shadow mb-6" data-aos="fade-up">
          <div class="flex flex-col md:flex-row">
            <div class="md:w-1/3 mb-6 md:mb-0 flex justify-center">
              <div class="w-32 h-32 rounded-full bg-un-blue-2 flex items-center justify-center text-white text-4xl">
                ${data.Name ? data.Name.charAt(0).toUpperCase() : 'U'}
              </div>
            </div>
            <div class="md:w-2/3">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-500">Name</label>
                  <p class="text-lg font-medium">${data.Name || '-'}</p>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-500">Email</label>
                  <p class="text-lg font-medium">${data.Email || '-'}</p>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-500">Phone</label>
                  <p class="text-lg font-medium">${data.Phone || '-'}</p>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-500">Address</label>
                  <p class="text-lg font-medium">${data.Address || '-'}</p>
                </div>
              </div>
              <div class="mt-6">
                <button onclick='openModal("customers", "edit", ${JSON.stringify(data)})' class="btn-primary text-white px-4 py-2 rounded-lg">
                  <i class="fas fa-edit mr-2"></i> Edit Profile
                </button>
              </div>
            </div>
          </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
          <div class="stat-card p-6 rounded-xl shadow" data-aos="fade-up" data-aos-delay="100">
            <div class="flex items-center">
              <div class="p-3 rounded-lg bg-jordy-blue/10">
                <i class="fas fa-file-invoice text-jordy-blue text-xl"></i>
              </div>
              <div class="ml-4">
                <h3 class="text-sm font-medium text-gray-500">Quotations</h3>
                <p class="text-2xl font-bold text-gray-900">${stats.quotations?.total_quotations || 0}</p>
                <p class="text-xs text-gray-500">${stats.quotations?.approved_quotations || 0} Approved</p>
              </div>
            </div>
          </div>
          
          <div class="stat-card p-6 rounded-xl shadow" data-aos="fade-up" data-aos-delay="200">
            <div class="flex items-center">
              <div class="p-3 rounded-lg bg-bronze/10">
                <i class="fas fa-file-invoice-dollar text-bronze text-xl"></i>
              </div>
              <div class="ml-4">
                <h3 class="text-sm font-medium text-gray-500">Invoices</h3>
                <p class="text-2xl font-bold text-gray-900">${stats.invoices?.total_invoices || 0}</p>
                <p class="text-xs text-gray-500">${stats.invoices?.paid_invoices || 0} Paid</p>
              </div>
            </div>
          </div>
          
          <div class="stat-card p-6 rounded-xl shadow" data-aos="fade-up" data-aos-delay="300">
            <div class="flex items-center">
              <div class="p-3 rounded-lg bg-un-blue/10">
                <i class="fas fa-truck text-un-blue text-xl"></i>
              </div>
              <div class="ml-4">
                <h3 class="text-sm font-medium text-gray-500">Delivery Orders</h3>
                <p class="text-2xl font-bold text-gray-900">${stats.deliveries?.total_deliveries || 0}</p>
                <p class="text-xs text-gray-500">${stats.deliveries?.delivered_orders || 0} Delivered</p>
              </div>
            </div>
          </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div class="bg-white p-6 rounded-xl shadow" data-aos="fade-up" data-aos-delay="100">
            <div class="flex justify-between items-center mb-4">
              <h3 class="text-lg font-semibold">Recent Quotations</h3>
              <a href="#" onclick="fetchData('quotations')" class="text-sm text-un-blue-2 hover:underline">View All</a>
            </div>
            <div class="space-y-3">
              ${quotations.length > 0 ?
          quotations.map(q => `
                  <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div>
                      <p class="font-medium">${q.QuotationCode}</p>
                      <p class="text-sm text-gray-500">${formatDate(q.QuotationDate)}</p>
                    </div>
                    <span class="px-2 py-1 text-xs rounded-full ${getStatusClass(q.QuotationStatus || q.Status)}">${q.QuotationStatus || q.Status}</span>
                  </div>
                `).join('') :
          '<p class="text-gray-500 text-center py-4">No quotations found</p>'
        }
            </div>
          </div>
          
          <div class="bg-white p-6 rounded-xl shadow" data-aos="fade-up" data-aos-delay="200">
            <div class="flex justify-between items-center mb-4">
              <h3 class="text-lg font-semibold">Recent Invoices</h3>
              <a href="#" onclick="fetchData('invoices')" class="text-sm text-un-blue-2 hover:underline">View All</a>
            </div>
            <div class="space-y-3">
              ${invoices.length > 0 ?
          invoices.map(i => `
                  <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div>
                      <p class="font-medium">${i.InvoiceCode}</p>
                      <p class="text-sm text-gray-500">${formatDate(i.InvoiceDate)}</p>
                    </div>
                    <span class="px-2 py-1 text-xs rounded-full ${getStatusClass(i.InvoiceStatus || i.Status)}">${i.InvoiceStatus || i.Status}</span>
                  </div>
                `).join('') :
          '<p class="text-gray-500 text-center py-4">No invoices found</p>'
        }
            </div>
          </div>
          
          <div class="bg-white p-6 rounded-xl shadow" data-aos="fade-up" data-aos-delay="300">
            <div class="flex justify-between items-center mb-4">
              <h3 class="text-lg font-semibold">Recent Delivery Orders</h3>
              <a href="#" onclick="fetchData('deliveryorders')" class="text-sm text-un-blue-2 hover:underline">View All</a>
            </div>
            <div class="space-y-3">
              ${deliveries.length > 0 ?
          deliveries.map(d => `
                  <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div>
                      <p class="font-medium">${d.DOCode}</p>
                      <p class="text-sm text-gray-500">${formatDate(d.DODate)}</p>
                    </div>
                    <span class="px-2 py-1 text-xs rounded-full ${getStatusClass(d.DOStatus || d.Status)}">${d.DOStatus || d.Status}</span>
                  </div>
                `).join('') :
          '<p class="text-gray-500 text-center py-4">No delivery orders found</p>'
        }
            </div>
          </div>
        </div>
      `;

      document.getElementById("content").innerHTML = html;
    }

    function renderTable(title, rows, fields) {
  // Pastikan rows adalah array
  if (!Array.isArray(rows)) {
    console.error('renderTable: rows is not an array', rows);
    rows = [];
  }
  
  // Fungsi untuk melihat detail
function viewDetail(type, id) {
  showLoading();
  
  let url = `${baseUrl}/${type}.php?id=${id}`;
  
  fetch(url, {
    method: 'GET',
    headers: {
      "Authorization": "Bearer " + jwt,
      "X-API-KEY": apiKey,
      "Content-Type": "application/json"
    },
    mode: 'cors'
  })
  .then(res => {
    if (!res.ok) {
      throw new Error(`HTTP error! status: ${res.status}`);
    }
    return res.json();
  })
  .then(data => {
    // Tampilkan detail view
    let title = type.charAt(0).toUpperCase() + type.slice(1);
    if (type === "deliveryorders") title = "Delivery Orders";
    
    renderTable(title, [data], getFieldsForType(type));
  })
  .catch(error => {
    console.error('Error fetching detail:', error);
    showToast('Failed to load detail: ' + error.message, 'error');
  })
  .finally(() => {
    hideLoading();
  });
}

// Helper function untuk mendapatkan fields berdasarkan type
function getFields(type) {
  const fieldsMap = {
    quotations: ["QuotationID", "QuotationCode", "CustomerID", "QuotationDate", "Notes", "Status"],
    invoices: ["InvoiceID", "InvoiceCode", "QuotationID", "InvoiceDate", "TotalAmount", "Status"],
    deliveryorders: ["DOID", "DOCode", "InvoiceID", "DODate", "Notes", "Status"]
  };
  
  return fieldsMap[type] || [];
}
  
  // Jika hanya ada satu row, tampilkan detail view dengan items
  if (rows.length === 1 && (title === "Quotations" || title === "Invoices" || title === "Delivery Orders")) {
    const row = rows[0];
    let type = title.toLowerCase().replace(" orders", "");
    
    let detailHtml = `
      <div class="bg-white p-6 rounded-xl shadow" data-aos="fade-up">
        <div class="flex justify-between items-center mb-6">
          <h2 class="text-xl font-bold text-gray-800">${title} Detail</h2>
          <button onclick="fetchData('${type}')" class="text-sm text-un-blue-2 hover:underline">
            <i class="fas fa-arrow-left mr-1"></i> Back to List
          </button>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
    `;
    
    // Tampilkan informasi utama
    fields.forEach(field => {
      if (field !== 'Actions') {
        let value = row[field] || '-';
        if (field === 'Status') {
          value = `<span class="px-2 py-1 text-xs rounded-full ${getStatusClass(row[field + 'Status'] || row[field])}">${row[field + 'Status'] || row[field]}</span>`;
        } else if (field.includes('Date')) {
          value = formatDate(value);
        } else if (field.includes('Amount') || field.includes('Price')) {
          value = `Rp ${parseFloat(value).toLocaleString('id-ID')}`;
        }
        
        detailHtml += `
          <div>
            <label class="block text-sm font-medium text-gray-500">${field}</label>
            <p class="text-lg font-medium">${value}</p>
          </div>
        `;
      }
    });
    
    detailHtml += `
        </div>
        
        ${renderItemsTable(type, row)}
        
        ${role === "admin" ? `
          <div class="mt-6 flex justify-end space-x-2">
            <button onclick='openModal("${type}", "edit", ${JSON.stringify(row)})' class="btn-primary text-white px-4 py-2 rounded-lg">
              <i class="fas fa-edit mr-2"></i> Edit ${title}
            </button>
            <button onclick='handleDelete("${type}", ${row[fields[0]]})' class="btn-danger text-white px-4 py-2 rounded-lg">
              <i class="fas fa-trash-alt mr-2"></i> Delete
            </button>
          </div>
        ` : ""}
      </div>
    `;
    
    document.getElementById("content").innerHTML = detailHtml;
    return;
  }
  
  // Jika banyak row, tampilkan tabel biasa
  let thead = fields.map(field => `<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">${field}</th>`).join("") + `<th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>`;
  
  let tbody = rows.map(row => {
    let cells = fields.map(field => {
      let cellValue = row[field] || '-';
      if (field === 'Status') {
        cellValue = row[field + 'Status'] || row[field] || '-';
      } else if (field.includes('Date')) {
        cellValue = formatDate(cellValue);
      } else if (field.includes('Amount') || field.includes('Price')) {
        cellValue = `Rp ${parseFloat(cellValue).toLocaleString('id-ID')}`;
      }
      return `<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${cellValue}</td>`;
    }).join("");
    
    let actionButtons = '';
    if (role === "admin" || !fields.includes("Status")) {
      actionButtons += `<button onclick='openModal("${title.toLowerCase()}", "edit", ${JSON.stringify(row)})' class="text-un-blue-2 hover:text-un-blue mr-3">
        <i class="fas fa-edit"></i>
      </button>`;
    }
    
    if (role === "admin") {
      actionButtons += `<button onclick='handleDelete("${title.toLowerCase()}", ${row[fields[0]]})' class="text-red-600 hover:text-red-800">
        <i class="fas fa-trash-alt"></i>
      </button>`;
    }
    
    // Tambahkan tombol view detail
    actionButtons += `<button onclick='viewDetail("${title.toLowerCase()}", ${row[fields[0]]})' class="text-green-600 hover:text-green-800 ml-2">
      <i class="fas fa-eye"></i>
    </button>`;
    
    return `<tr class="table-row">
      ${cells}
      <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
        ${actionButtons}
      </td>
    </tr>`;
  }).join("");
  
  document.getElementById("content").innerHTML = `
    <div class="bg-white p-6 rounded-xl shadow" data-aos="fade-up">
      <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">
        <h2 class="text-xl font-bold text-gray-800">${title}</h2>
        ${role === "admin" || (title !== "Invoices" && title !== "Delivery Orders") ? `
          <div class="mt-4 md:mt-0">
            <button onclick='openModal("${title.toLowerCase()}", "add")' class="btn-primary text-white px-4 py-2 rounded-lg">
              <i class="fas fa-plus mr-2"></i> Add New
            </button>
          </div>
        ` : ''}
      </div>
      
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>${thead}</tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            ${tbody}
          </tbody>
        </table>
      </div>
      
      ${rows.length === 0 ? `
        <div class="text-center py-8">
          <i class="fas fa-inbox text-gray-300 text-4xl mb-2"></i>
          <p class="text-gray-500">No data available</p>
        </div>
      ` : ''}
    </div>
  `;
}
    
    // Fungsi untuk menampilkan tabel items
function renderItemsTable(type, parentData) {
    let items = parentData.items || [];
    let parentIdField = '';
    let itemIdField = '';
    
    switch (type) {
        case 'quotation':
            parentIdField = 'QuotationID';
            itemIdField = 'ItemID';
            break;
        case 'invoice':
            parentIdField = 'InvoiceID';
            itemIdField = 'InvoiceItemID';
            break;
        case 'delivery':
            parentIdField = 'DOID';
            itemIdField = 'DOItemID';
            break;
    }
    
    let thead = `
        <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item Name</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subtotal</th>
            ${role === "admin" ? `<th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>` : ""}
        </tr>
    `;
    
    let tbody = items.map(item => `
        <tr class="table-row">
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${item.ItemName}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${item.Quantity}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Rp ${parseFloat(item.Price).toLocaleString('id-ID')}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Rp ${parseFloat(item.Subtotal || (item.Quantity * item.Price)).toLocaleString('id-ID')}</td>
            ${role === "admin" ? `
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <button onclick='openItemModal("${type}", "edit", ${JSON.stringify(item)}, ${parentData[parentIdField]})' class="text-un-blue-2 hover:text-un-blue mr-3">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button onclick='deleteItem("${type}", ${item[itemIdField]}, ${parentData[parentIdField]})' class="text-red-600 hover:text-red-800">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </td>
            ` : ""}
        </tr>
    `).join("");
    
    // Hitung total
    let total = items.reduce((sum, item) => sum + parseFloat(item.Subtotal || (item.Quantity * item.Price)), 0);
    
    return `
        <div class="bg-white p-6 rounded-xl shadow mt-6" data-aos="fade-up">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Items</h3>
                ${role === "admin" ? `
                    <button onclick='openItemModal("${type}", "add", null, ${parentData[parentIdField]})' class="btn-primary text-white px-4 py-2 rounded-lg">
                        <i class="fas fa-plus mr-2"></i> Add Item
                    </button>
                ` : ""}
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        ${thead}
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        ${tbody}
                    </tbody>
                </table>
            </div>
            
            ${items.length === 0 ? `
                <div class="text-center py-8">
                    <i class="fas fa-inbox text-gray-300 text-4xl mb-2"></i>
                    <p class="text-gray-500">No items found</p>
                </div>
            ` : `
                <div class="mt-4 flex justify-end">
                    <div class="text-lg font-semibold">
                        Total: Rp ${total.toLocaleString('id-ID')}
                    </div>
                </div>
            `}
        </div>
    `;
}

// Modal untuk items
function openItemModal(type, mode, item = null, parentId = null) {
    const form = document.getElementById("modalForm");
    form.innerHTML = "";
    
    // Tambahkan hidden field untuk parent ID
    form.innerHTML += `<input type="hidden" name="${getParentIdField(type)}" value="${parentId}">`;
    
    // Tambahkan hidden field untuk item ID jika mode edit
    if (mode === "edit" && item) {
        form.innerHTML += `<input type="hidden" name="${getItemIdField(type)}" value="${item[getItemIdField(type)]}">`;
    }
    
    // Form fields
    form.innerHTML += `
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Item Name</label>
            <input type="text" name="ItemName" value="${item ? item.ItemName : ""}" 
                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-un-blue-2 focus:border-transparent" required />
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Quantity</label>
            <input type="number" name="Quantity" value="${item ? item.Quantity : ""}" min="1"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-un-blue-2 focus:border-transparent" required />
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Price</label>
            <input type="number" name="Price" value="${item ? item.Price : ""}" min="0" step="0.01"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-un-blue-2 focus:border-transparent" required />
        </div>
    `;
    
    // Attach submit handler
    form.onsubmit = async (e) => {
        e.preventDefault();
        showLoading();
        
        try {
            const formData = Object.fromEntries(new FormData(form).entries());
            let url = `${baseUrl}/${type}_items.php`;
            let method = mode === "add" ? "POST" : "PUT";
            
            if (mode === "edit") {
                url += `?id=${item[getItemIdField(type)]}`;
            }
            
            const res = await fetch(url, {
                method,
                headers: {
                    "Authorization": "Bearer " + jwt,
                    "X-API-KEY": apiKey,
                    "Content-Type": "application/json"
                },
                body: JSON.stringify(formData)
            });
            
            const result = await res.json();
            
            if (result.message) {
                showToast(result.message);
                closeModal();
                // Refresh data
                if (type === 'quotation') {
                    fetchData('quotations');
                } else if (type === 'invoice') {
                    fetchData('invoices');
                } else if (type === 'delivery') {
                    fetchData('deliveryorders');
                }
            } else if (result.error) {
                showToast(result.error, 'error');
            }
        } catch (error) {
            console.error('Error submitting form:', error);
            showToast('Failed to save data: ' + error.message, 'error');
        } finally {
            hideLoading();
        }
    };
    
    document.getElementById("modalTitle").innerText = (mode === "add" ? "Tambah " : "Edit ") + "Item";
    document.getElementById("modal").classList.remove("hidden");
}

// Helper functions
function getParentIdField(type) {
    switch (type) {
        case 'quotation': return 'QuotationID';
        case 'invoice': return 'InvoiceID';
        case 'delivery': return 'DOID';
        default: return '';
    }
}

function getItemIdField(type) {
    switch (type) {
        case 'quotation': return 'ItemID';
        case 'invoice': return 'InvoiceItemID';
        case 'delivery': return 'DOItemID';
        default: return '';
    }
}

// Delete item
async function deleteItem(type, itemId, parentId) {
    if (!confirm("Are you sure you want to delete this item?")) return;
    
    showLoading();
    
    try {
        const res = await fetch(`${baseUrl}/${type}_items.php?id=${itemId}`, {
            method: "DELETE",
            headers: {
                "Authorization": "Bearer " + jwt,
                "X-API-KEY": apiKey
            }
        });
        
        const result = await res.json();
        
        if (result.message) {
            showToast(result.message);
            // Refresh data
            if (type === 'quotation') {
                fetchData('quotations');
            } else if (type === 'invoice') {
                fetchData('invoices');
            } else if (type === 'delivery') {
                fetchData('deliveryorders');
            }
        } else if (result.error) {
            showToast(result.error, 'error');
        }
    } catch (error) {
        console.error('Error deleting item:', error);
        showToast('Failed to delete item: ' + error.message, 'error');
    } finally {
        hideLoading();
    }
}

    async function openModal(endpoint, mode, row = {}) {
      const form = document.getElementById("modalForm");
      form.innerHTML = "";

      let fields = {
        customers: ["Name", "Email", "Phone", "Address"],
        quotations: ["CustomerID", "QuotationDate", "Notes", "Status"],
        invoices: ["QuotationID", "InvoiceDate", "TotalAmount", "Status"],
        deliveryorders: ["InvoiceID", "DODate", "Notes", "Status"]
      }[endpoint];

      for (let f of fields) {
        if (f === "CustomerID") {
          if (role === "customer") {
            form.innerHTML += `
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Customer</label>
                <input type="hidden" name="CustomerID" value="${user.id}" />
                <div class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-gray-100">
                  ${user.name} (${user.email})
                </div>
              </div>`;
          } else {
            let customers = await fetchDropdownData('customers');

            let options = customers.map(c =>
              `<option value="${c.CustomerID}" ${c.CustomerID == row[f] ? "selected" : ""}>
                ${c.Name} (${c.Phone})
              </option>`).join("");

            form.innerHTML += `
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Customer</label>
                <select name="CustomerID" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-un-blue-2 focus:border-transparent">
                  ${options}
                </select>
              </div>`;
          }
        }
        else if (f === "QuotationID") {
          let quotations = await fetchDropdownData(role === "customer" ? `quotations?customer=${user.id}` : 'quotations');

          let options = quotations.map(q =>
            `<option value="${q.QuotationID}" ${q.QuotationID == row[f] ? "selected" : ""}>
              #${q.QuotationCode} - ${q.Notes || "No Notes"}
            </option>`).join("");

          form.innerHTML += `
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Quotation</label>
              <select name="QuotationID" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-un-blue-2 focus:border-transparent">
                ${options}
              </select>
            </div>`;
        }
        else if (f === "InvoiceID") {
          let invoices = await fetchDropdownData(role === "customer" ? `invoices?customer=${user.id}` : 'invoices');

          let options = invoices.map(i =>
            `<option value="${i.InvoiceID}" ${i.InvoiceID == row[f] ? "selected" : ""}>
              #${i.InvoiceCode} - ${i.Status}
            </option>`).join("");

          form.innerHTML += `
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Invoice</label>
              <select name="InvoiceID" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-un-blue-2 focus:border-transparent">
                ${options}
              </select>
            </div>`;
        }
        else if (f === "Status") {
          let statusOptions = [];

          if (endpoint === "quotations") {
            statusOptions = [
              { id: 'Draft', name: 'Draft' },
              { id: 'Sent', name: 'Sent' },
              { id: 'Approved', name: 'Approved' },
              { id: 'Rejected', name: 'Rejected' }
            ];
          }
          else if (endpoint === "invoices") {
            statusOptions = [
              { id: 'Unpaid', name: 'Unpaid' },
              { id: 'Paid', name: 'Paid' },
              { id: 'Partial', name: 'Partial' }
            ];
          }
          else if (endpoint === "deliveryorders") {
            statusOptions = [
              { id: 'Not Shipped', name: 'Not Shipped' },
              { id: 'Shipped', name: 'Shipped' },
              { id: 'Delivered', name: 'Delivered' }
            ];
          }

          let options = statusOptions.map(s =>
            `<option value="${s.id}" ${s.id == row[f] ? "selected" : ""}>
              ${s.name}
            </option>`).join("");

          form.innerHTML += `
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
              <select name="Status" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-un-blue-2 focus:border-transparent">
                ${options}
              </select>
            </div>`;
        }
        else {
          let inputType = f.toLowerCase().includes("date") ? "date" : "text";
          if (f.toLowerCase().includes("amount")) inputType = "number";

          form.innerHTML += `
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">${f}</label>
              <input type="${inputType}" name="${f}" value="${row[f] ?? ""}" 
                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-un-blue-2 focus:border-transparent" />
            </div>`;
        }
      }

      form.onsubmit = async (e) => {
        e.preventDefault();
        showLoading();

        try {
          const formData = Object.fromEntries(new FormData(form).entries());
          let url = `${baseUrl}/${endpoint}.php`;
          let method = mode === "add" ? "POST" : "PUT";

          if (role === "customer") {
            url += `?customer=${user.id}`;
          }

          if (mode === "edit") {
            url += `&id=${row[Object.keys(row)[0]]}`;
          }

          const res = await fetch(url, {
            method,
            headers: {
              "Authorization": "Bearer " + jwt,
              "X-API-KEY": apiKey,
              "Content-Type": "application/json"
            },
            body: JSON.stringify(formData)
          });

          const result = await res.json();

          if (result.message) {
            showToast(result.message);
            closeModal();
            fetchData(endpoint);
          } else if (result.error) {
            showToast(result.error, 'error');
          }
        } catch (error) {
          console.error('Error submitting form:', error);
          showToast('Failed to save data: ' + error.message, 'error');
        } finally {
          hideLoading();
        }
      };

      document.getElementById("modalTitle").innerText = (mode === "add" ? "Tambah " : "Edit ") + endpoint.charAt(0).toUpperCase() + endpoint.slice(1);
      document.getElementById("modal").classList.remove("hidden");
    }

    function closeModal() {
      document.getElementById("modal").classList.add("hidden");
    }

    async function handleDelete(endpoint, id) {
      if (!confirm("Are you sure you want to delete this item?")) return;

      showLoading();

      try {
        let url = `${baseUrl}/${endpoint}.php?id=${id}`;

        if (role === "customer") {
          url += `&customer=${user.id}`;
        }

        const res = await fetch(url, {
          method: "DELETE",
          headers: {
            "Authorization": "Bearer " + jwt,
            "X-API-KEY": apiKey
          }
        });

        const result = await res.json();

        if (result.message) {
          showToast(result.message);
          fetchData(endpoint);
        } else if (result.error) {
          showToast(result.error, 'error');
        }
      } catch (error) {
        console.error('Error deleting item:', error);
        showToast('Failed to delete item: ' + error.message, 'error');
      } finally {
        hideLoading();
      }
    }
  </script>
</body>

</html>