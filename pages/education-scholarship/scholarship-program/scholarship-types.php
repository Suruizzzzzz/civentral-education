<?php
$basePath = '../../../';
include '../../../includes/header.php';
include '../../../includes/sidebar.php';

// PERMISSION CHECKS FOR SCHOLARSHIP TYPES MANAGEMENT
$userDeptName = strtolower($headerUser['department_name'] ?? '');
$userDeptCode = strtoupper($headerUser['department_code'] ?? '');
$userRoleName = strtolower($headerUser['role'] ?? '');
$grantedActions = $headerUser['granted_actions'] ?? [];

$isScholarshipDept = (
    strpos($userDeptName, 'scholarship') !== false || 
    strpos($userDeptName, 'education') !== false ||
    in_array($userDeptCode, ['EDSCH', 'SCH', 'SCHOLAR', 'EDUC'])
);

$hasScholarshipRole = (
    strpos($userRoleName, 'scholarship') !== false ||
    strpos($userRoleName, 'education') !== false
);

$isSuperAdmin = !empty($headerUser['is_superadmin']) || !empty($headerUser['is_global_access']);

// View / Read Access
$canAccess = $isSuperAdmin || $isScholarshipDept || $hasScholarshipRole || in_array('VIEW', $grantedActions) || in_array('READ', $grantedActions) || (isset($_SESSION['permissions']) && in_array('scholarship_access', $_SESSION['permissions']));

// Create Access
$canCreate = $isSuperAdmin || in_array('CREATE', $grantedActions) || in_array('ADD', $grantedActions) || (isset($_SESSION['permissions']) && in_array('scholarship_create', $_SESSION['permissions']));

// Edit Access
$canEdit = $isSuperAdmin || in_array('EDIT', $grantedActions) || in_array('UPDATE', $grantedActions) || (isset($_SESSION['permissions']) && in_array('scholarship_edit', $_SESSION['permissions']));

// Delete / Archive Access
$canDelete = $isSuperAdmin || in_array('DELETE', $grantedActions) || in_array('REMOVE', $grantedActions) || (isset($_SESSION['permissions']) && in_array('scholarship_delete', $_SESSION['permissions']));
?>

<main class="flex-1 p-6 md:p-8 w-full space-y-6 overflow-y-auto">

  <!-- Breadcrumb & Page Header -->
  <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-200/60 dark:border-slate-800 pb-5">
    <div class="space-y-1">
      <div class="flex items-center space-x-2 text-xs font-bold uppercase tracking-wider text-slate-400">
        <span>Education & Scholarship</span>
        <i class="fa-solid fa-chevron-right text-[8px] opacity-60"></i>
        <span>Scholarship Program</span>
        <i class="fa-solid fa-chevron-right text-[8px] opacity-60"></i>
        <span class="text-brand-dark dark:text-cyan-400">Scholarship Types</span>
      </div>
      <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight flex items-center gap-2.5 mt-4">
        <i class="fa-solid fa-graduation-cap text-brand-dark dark:text-cyan-400"></i>
        Scholarship Category Registry
      </h1>
      <p class="text-xs text-slate-500 dark:text-slate-400 max-w-2xl leading-relaxed">
        Manage the master inventory of municipal scholarship programs referenced across applicant requirements, benefit allocations, and period schedules.
      </p>
    </div>

    <!-- Primary Action Button (Permission Controlled) -->
    <?php if (!empty($canCreate)): ?>
    <div class="shrink-0">
      <button 
        type="button"
        onclick="openCreateModal()" 
        class="bg-brand-dark hover:bg-slate-800 dark:bg-slate-800 dark:hover:bg-slate-700 text-white font-bold px-4.5 py-2.5 rounded-xl text-xs transition duration-200 shadow-xs flex items-center gap-2 cursor-pointer focus:outline-none focus:ring-2 focus:ring-brand-dark/20"
      >
        <i class="fa-solid fa-plus text-xs"></i>
        <span>Create Scholarship Type</span>
      </button>
    </div>
    <?php endif; ?>
  </div>

  <!-- Master Overview Metric Cards (3 Columns) -->
  <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
    
    <!-- Total Programs Registered Card -->
    <div 
      onclick="filterByCard('ALL')" 
      id="cardTotalPrograms"
      class="role-metric-card active-card bg-white dark:bg-slate-900/90 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-5 shadow-xs hover:shadow-xl hover:-translate-y-1 transition-all duration-300 group cursor-pointer relative overflow-hidden flex flex-col justify-between"
      title="Click to view all registered scholarship programs"
    >
      <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-cyan-500 via-teal-500 to-brand-dark opacity-90 group-hover:h-1.5 transition-all duration-300"></div>
      <div class="flex items-start justify-between">
        <div class="space-y-1">
          <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 group-hover:text-brand-dark dark:group-hover:text-cyan-400 transition-colors block">Total Programs Registered</span>
          <h3 id="statTotalPrograms" class="text-3xl font-black text-slate-900 dark:text-white tracking-tight">5</h3>
        </div>
        <div class="h-12 w-12 rounded-2xl bg-cyan-50 dark:bg-cyan-950/60 border border-cyan-200/80 dark:border-cyan-800/50 flex items-center justify-center text-brand-dark dark:text-cyan-400 group-hover:scale-110 group-hover:rotate-3 transition-transform duration-300 shrink-0 shadow-xs">
          <i class="fa-solid fa-book-bookmark text-lg"></i>
        </div>
      </div>
      <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between">
        <p class="text-[10px] text-slate-500 dark:text-slate-400 font-bold flex items-center gap-1.5">
          <span class="h-1.5 w-1.5 rounded-full bg-brand-dark animate-pulse"></span>
          Master System Inventory
        </p>
        <span class="text-[10px] font-black text-brand-dark dark:text-cyan-400 flex items-center gap-1 opacity-80 group-hover:opacity-100 transition-all transform group-hover:translate-x-0.5">
          View All <i class="fa-solid fa-arrow-right text-[9px]"></i>
        </span>
      </div>
    </div>

    <!-- Active Offerings Card -->
    <div 
      onclick="filterByCard('ACTIVE')" 
      id="cardActiveOfferings"
      class="role-metric-card bg-white dark:bg-slate-900/90 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-5 shadow-xs hover:shadow-xl hover:-translate-y-1 transition-all duration-300 group cursor-pointer relative overflow-hidden flex flex-col justify-between"
      title="Click to filter active offerings"
    >
      <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-emerald-400 via-teal-500 to-emerald-600 opacity-90 group-hover:h-1.5 transition-all duration-300"></div>
      <div class="flex items-start justify-between">
        <div class="space-y-1">
          <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors block">Active Offerings</span>
          <h3 id="statActiveOfferings" class="text-3xl font-black text-emerald-600 dark:text-emerald-400 tracking-tight">4</h3>
        </div>
        <div class="h-12 w-12 rounded-2xl bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200/80 dark:border-emerald-800/50 flex items-center justify-center text-emerald-600 dark:text-emerald-400 group-hover:scale-110 group-hover:rotate-3 transition-transform duration-300 shrink-0 shadow-xs">
          <i class="fa-solid fa-circle-check text-lg"></i>
        </div>
      </div>
      <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between">
        <p class="text-[10px] text-slate-500 dark:text-slate-400 font-bold flex items-center gap-1.5">
          <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
          Operational Grants Enabled
        </p>
        <span class="text-[10px] font-black text-emerald-600 dark:text-emerald-400 flex items-center gap-1 opacity-80 group-hover:opacity-100 transition-all transform group-hover:translate-x-0.5">
          Filter Active <i class="fa-solid fa-filter text-[9px]"></i>
        </span>
      </div>
    </div>

    <!-- Program Categories Card -->
    <div 
      onclick="filterByCard('CATEGORIES')" 
      id="cardCategories"
      class="role-metric-card bg-white dark:bg-slate-900/90 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-5 shadow-xs hover:shadow-xl hover:-translate-y-1 transition-all duration-300 group cursor-pointer relative overflow-hidden flex flex-col justify-between"
      title="Click to view category breakdown"
    >
      <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-blue-500 via-indigo-500 to-purple-600 opacity-90 group-hover:h-1.5 transition-all duration-300"></div>
      <div class="flex items-start justify-between">
        <div class="space-y-1">
          <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors block">Program Categories</span>
          <h3 id="statCategories" class="text-3xl font-black text-blue-600 dark:text-blue-400 tracking-tight">5</h3>
        </div>
        <div class="h-12 w-12 rounded-2xl bg-blue-50 dark:bg-blue-950/60 border border-blue-200/80 dark:border-blue-800/50 flex items-center justify-center text-blue-600 dark:text-blue-400 group-hover:scale-110 group-hover:rotate-3 transition-transform duration-300 shrink-0 shadow-xs">
          <i class="fa-solid fa-shapes text-lg"></i>
        </div>
      </div>
      <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between">
        <p class="text-[10px] text-slate-500 dark:text-slate-400 font-bold flex items-center gap-1.5">
          <span class="h-1.5 w-1.5 rounded-full bg-blue-500"></span>
          Distinct Classification Tracks
        </p>
        <span class="text-[10px] font-black text-blue-600 dark:text-blue-400 flex items-center gap-1 opacity-80 group-hover:opacity-100 transition-all transform group-hover:translate-x-0.5">
          Categories <i class="fa-solid fa-layer-group text-[9px]"></i>
        </span>
      </div>
    </div>

  </div>

  <!-- Control Panel & Filters Toolbar Datatable Container -->
  <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl shadow-xs overflow-hidden space-y-4">
    
    <!-- Control Panel & Filters Toolbar -->
    <div class="p-4 sm:p-5 border-b border-slate-100 dark:border-slate-800 flex flex-col md:flex-row md:items-center justify-between gap-4 bg-slate-50/50 dark:bg-slate-900">
      
      <!-- Search Input -->
      <div class="relative flex-1 max-w-md">
        <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
        <input 
          type="text" 
          id="searchInput" 
          oninput="filterPrograms()" 
          placeholder="Search by Code (e.g. QC-ACAD-2026) or Program Title..." 
          class="pl-9 pr-3 py-2 border border-slate-200 dark:border-slate-700 rounded-xl text-xs w-full bg-white dark:bg-slate-950 text-slate-800 dark:text-white focus:outline-none focus:border-brand-medium focus:ring-2 focus:ring-brand-medium/10 transition"
        >
      </div>

      <!-- Filters Group -->
      <div class="flex flex-wrap items-center gap-3">
        <!-- Category Filter -->
        <select 
          id="categoryFilterSelect" 
          onchange="filterPrograms()" 
          class="px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-xl text-xs bg-white dark:bg-slate-950 text-slate-700 dark:text-slate-200 focus:outline-none focus:border-brand-medium transition cursor-pointer font-medium"
        >
          <option value="ALL">All Categories</option>
          <option value="Academic">Academic</option>
          <option value="Financial Assistance">Financial Assistance</option>
          <option value="Educational Assistance">Educational Assistance</option>
          <option value="Sports">Sports</option>
          <option value="Special Sector">Special Sector</option>
          <option value="Cultural & Arts">Cultural & Arts</option>
        </select>

        <!-- Status Filter -->
        <select 
          id="statusFilterSelect" 
          onchange="filterPrograms()" 
          class="px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-xl text-xs bg-white dark:bg-slate-950 text-slate-700 dark:text-slate-200 focus:outline-none focus:border-brand-medium transition cursor-pointer font-medium"
        >
          <option value="ALL">All Statuses</option>
          <option value="Active">Active</option>
          <option value="Inactive">Inactive</option>
          <option value="Archived">Archived</option>
        </select>
      </div>
    </div>

    <!-- Active Scholarship Inventory Datatable -->
    <div class="overflow-x-auto">
      <table class="w-full text-left border-collapse">
        <thead>
          <tr class="bg-slate-50/80 dark:bg-slate-950/60 border-b border-slate-200/80 dark:border-slate-800 text-[10px] font-black uppercase tracking-wider text-slate-400">
            <th class="px-6 py-3.5">Program Code</th>
            <th class="px-6 py-3.5">Scholarship Title & Summary</th>
            <th class="px-6 py-3.5">Category</th>
            <th class="px-6 py-3.5 text-center">Status</th>
            <th class="px-6 py-3.5 text-right">Actions</th>
          </tr>
        </thead>
        <tbody id="scholarshipTableBody" class="divide-y divide-slate-100 dark:divide-slate-800 text-xs font-medium">
          <!-- Dynamically Populated by Vanilla JS -->
        </tbody>
      </table>
    </div>

    <!-- Table Footer / Pagination -->
    <div class="bg-slate-50/80 dark:bg-slate-950/60 px-6 py-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-[11px] font-bold text-slate-500 dark:text-slate-400">
      <div id="paginationText">
        Showing 1 to 5 of 5 scholarship programs
      </div>
      <div class="flex items-center space-x-1">
        <button class="px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-400 cursor-not-allowed transition" disabled>
          <i class="fa-solid fa-chevron-left text-[9px]"></i>
        </button>
        <button class="px-3 py-1.5 rounded-lg bg-brand-light dark:bg-slate-800 border border-brand-border dark:border-slate-700 text-brand-dark dark:text-cyan-400 font-extrabold shadow-2xs">1</button>
        <button class="px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-400 cursor-not-allowed transition" disabled>
          <i class="fa-solid fa-chevron-right text-[9px]"></i>
        </button>
      </div>
    </div>

  </div>

</main>

<!-- MODALS SECTION -->

<!-- 1. CREATE / EDIT SCHOLARSHIP TYPE MODAL -->
<div id="scholarshipModal" class="fixed inset-0 z-50 flex items-center justify-center opacity-0 pointer-events-none transition-all duration-300 bg-slate-900/60 backdrop-blur-xs">
  <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl w-full max-w-lg mx-4 overflow-hidden transform scale-95 transition-all duration-300 border border-slate-200 dark:border-slate-800">
    
    <!-- Modal Header -->
    <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-950/60">
      <div class="flex items-center gap-2.5">
        <div class="h-8 w-8 rounded-xl bg-brand-light dark:bg-slate-800 border border-brand-border dark:border-slate-700 flex items-center justify-center text-brand-dark dark:text-cyan-400 font-bold text-xs">
          <i id="modalHeaderIcon" class="fa-solid fa-graduation-cap"></i>
        </div>
        <div>
          <h3 id="modalTitle" class="text-sm font-black text-slate-900 dark:text-white tracking-tight">Create Scholarship Program Type</h3>
          <p class="text-[10px] text-slate-400 font-medium">Configure municipal program metadata and classification track.</p>
        </div>
      </div>
      <button type="button" onclick="closeModal('scholarshipModal')" class="h-8 w-8 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 flex items-center justify-center transition cursor-pointer">
        <i class="fa-solid fa-xmark text-sm"></i>
      </button>
    </div>

    <!-- Form -->
    <form id="scholarshipForm" onsubmit="handleSaveProgram(event)" class="p-6 space-y-4">
      <input type="hidden" id="programIdRef">

      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <!-- Scholarship Code -->
        <div class="space-y-1.5">
          <label for="programCode" class="text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider block">Program Code</label>
          <input 
            type="text" 
            id="programCode" 
            required 
            placeholder="QC-ACAD-2026" 
            class="w-full px-3 py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-mono font-bold text-brand-dark dark:text-cyan-400 bg-slate-50/50 dark:bg-slate-950 uppercase focus:bg-white focus:outline-none focus:border-brand-medium focus:ring-2 focus:ring-brand-medium/10 transition"
          >
        </div>

        <!-- Scholarship Title -->
        <div class="sm:col-span-2 space-y-1.5">
          <label for="programTitle" class="text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider block">Scholarship Program Title</label>
          <input 
            type="text" 
            id="programTitle" 
            required 
            placeholder="e.g. QC Tertiary Academic Excellence Scholarship" 
            class="w-full px-3.5 py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-800 dark:text-white bg-white dark:bg-slate-950 focus:outline-none focus:border-brand-medium focus:ring-2 focus:ring-brand-medium/10 transition font-medium"
          >
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <!-- Category Dropdown -->
        <div class="space-y-1.5">
          <label for="programCategory" class="text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider block">Program Category</label>
          <select 
            id="programCategory" 
            required 
            class="w-full px-3 py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl text-xs bg-white dark:bg-slate-950 text-slate-800 dark:text-white focus:outline-none focus:border-brand-medium transition cursor-pointer font-bold"
          >
            <option value="Academic">Academic</option>
            <option value="Financial Assistance">Financial Assistance</option>
            <option value="Educational Assistance">Educational Assistance</option>
            <option value="Sports">Sports</option>
            <option value="Special Sector">Special Sector</option>
            <option value="Cultural & Arts">Cultural & Arts</option>
          </select>
        </div>

        <!-- Status Dropdown -->
        <div class="space-y-1.5">
          <label for="programStatus" class="text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider block">Initial Status</label>
          <select 
            id="programStatus" 
            required 
            class="w-full px-3 py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl text-xs bg-white dark:bg-slate-950 text-slate-800 dark:text-white focus:outline-none focus:border-brand-medium transition cursor-pointer font-bold"
          >
            <option value="Active">Active</option>
            <option value="Inactive">Inactive</option>
          </select>
        </div>
      </div>

      <!-- Description Textarea -->
      <div class="space-y-1.5">
        <label for="programDesc" class="text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider block">Operational Summary & Description</label>
        <textarea 
          id="programDesc" 
          required 
          rows="3" 
          placeholder="Describe target beneficiary criteria, grant purpose, and municipal coverage..." 
          class="w-full px-3.5 py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-800 dark:text-white bg-white dark:bg-slate-950 focus:outline-none focus:border-brand-medium focus:ring-2 focus:ring-brand-medium/10 transition font-medium"
        ></textarea>
      </div>

      <!-- Modal Footer Buttons -->
      <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-2.5">
        <button 
          type="button" 
          onclick="closeModal('scholarshipModal')" 
          class="px-4 py-2 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-300 font-bold rounded-xl text-xs transition cursor-pointer"
        >
          Cancel
        </button>
        <button 
          type="submit" 
          class="px-5 py-2 bg-brand-dark hover:bg-slate-800 text-white font-bold rounded-xl text-xs transition shadow-xs cursor-pointer flex items-center gap-1.5"
        >
          <i class="fa-solid fa-floppy-disk text-xs"></i>
          <span>Save Program</span>
        </button>
      </div>

    </form>

  </div>
</div>

<!-- 2. VIEW SCHOLARSHIP DETAILS MODAL -->
<div id="viewModal" class="fixed inset-0 z-50 flex items-center justify-center opacity-0 pointer-events-none transition-all duration-300 bg-slate-900/60 backdrop-blur-xs">
  <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl w-full max-w-lg mx-4 overflow-hidden transform scale-95 transition-all duration-300 border border-slate-200 dark:border-slate-800 space-y-4">
    
    <!-- Modal Header -->
    <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-950/60">
      <div class="flex items-center gap-2.5">
        <div class="h-8 w-8 rounded-xl bg-blue-50 dark:bg-blue-950/60 border border-blue-100 dark:border-blue-800/50 flex items-center justify-center text-blue-600 dark:text-blue-400 font-bold text-xs">
          <i class="fa-solid fa-circle-info"></i>
        </div>
        <div>
          <h3 class="text-sm font-black text-slate-900 dark:text-white tracking-tight">Program Metadata Overview</h3>
          <p class="text-[10px] text-slate-400 font-medium">Detailed municipal scholarship profile & system linkages.</p>
        </div>
      </div>
      <button type="button" onclick="closeModal('viewModal')" class="h-8 w-8 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 flex items-center justify-center transition cursor-pointer">
        <i class="fa-solid fa-xmark text-sm"></i>
      </button>
    </div>

    <!-- Content Details -->
    <div class="p-6 space-y-4 text-xs">
      <div class="flex items-center justify-between bg-slate-50 dark:bg-slate-950/80 p-3.5 rounded-xl border border-slate-200/80 dark:border-slate-800">
        <div>
          <span class="text-[10px] font-black uppercase text-slate-400 tracking-wider block">Registry Code</span>
          <span id="viewCode" class="font-mono font-black text-sm text-brand-dark dark:text-cyan-400">QC-ACAD-2026</span>
        </div>
        <div id="viewStatusBadge">
          <!-- Populated by JS -->
        </div>
      </div>

      <div class="space-y-1">
        <span class="text-[10px] font-black uppercase text-slate-400 tracking-wider block">Full Scholarship Title</span>
        <h4 id="viewTitle" class="text-sm font-extrabold text-slate-900 dark:text-white">QC Tertiary Academic Excellence Scholarship</h4>
      </div>

      <div class="space-y-1">
        <span class="text-[10px] font-black uppercase text-slate-400 tracking-wider block">Classification Category</span>
        <div id="viewCategoryBadge">
          <!-- Populated by JS -->
        </div>
      </div>

      <div class="space-y-1">
        <span class="text-[10px] font-black uppercase text-slate-400 tracking-wider block">Description & Grant Scope</span>
        <p id="viewDesc" class="text-slate-600 dark:text-slate-300 leading-relaxed bg-slate-50/50 dark:bg-slate-950/40 p-3 rounded-xl border border-slate-100 dark:border-slate-800/60">
          Merit-based financial grant awarded to top-performing college students residing in Quezon City.
        </p>
      </div>

      <div class="grid grid-cols-2 gap-3 pt-2">
        <div class="bg-slate-50 dark:bg-slate-950/60 p-3 rounded-xl border border-slate-100 dark:border-slate-800/60">
          <span class="text-[9px] font-black uppercase text-slate-400 block">Creation History</span>
          <span class="font-mono text-[10px] font-bold text-slate-700 dark:text-slate-300">2026-01-15 08:30:00</span>
        </div>
        <div class="bg-slate-50 dark:bg-slate-950/60 p-3 rounded-xl border border-slate-100 dark:border-slate-800/60">
          <span class="text-[9px] font-black uppercase text-slate-400 block">System Linkages</span>
          <span class="text-[10px] font-extrabold text-emerald-600 dark:text-emerald-400 flex items-center gap-1 mt-0.5">
            <i class="fa-solid fa-link text-[8px]"></i> Active Allocations
          </span>
        </div>
      </div>
    </div>

    <!-- Modal Footer -->
    <div class="bg-slate-50 dark:bg-slate-950/60 px-6 py-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end">
      <button type="button" onclick="closeModal('viewModal')" class="border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-300 px-4 py-2 rounded-xl text-xs font-bold transition cursor-pointer">
        Close Overview
      </button>
    </div>

  </div>
</div>

<!-- 3. ARCHIVE CONFIRMATION MODAL -->
<div id="archiveConfirmModal" class="fixed inset-0 z-50 flex items-center justify-center opacity-0 pointer-events-none transition-all duration-300 bg-slate-900/60 backdrop-blur-xs">
  <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl w-full max-w-md mx-4 overflow-hidden transform scale-95 transition-all duration-300 border border-slate-200 dark:border-slate-800">
    
    <div class="bg-slate-900 text-white px-6 py-4 flex items-center justify-between">
      <div class="flex items-center space-x-2">
        <i class="fa-solid fa-box-archive text-amber-400"></i>
        <h3 class="font-extrabold text-sm tracking-tight uppercase">Confirm Program Archival</h3>
      </div>
      <button type="button" onclick="closeModal('archiveConfirmModal')" class="text-slate-400 hover:text-white transition cursor-pointer text-sm">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>

    <div class="p-6 space-y-4">
      <div class="flex items-start gap-3.5">
        <div class="h-10 w-10 rounded-2xl bg-amber-50 dark:bg-amber-950/60 border border-amber-100 dark:border-amber-800/50 flex items-center justify-center text-amber-600 dark:text-amber-400 shrink-0 shadow-2xs">
          <i class="fa-solid fa-triangle-exclamation text-base"></i>
        </div>
        <div class="space-y-1">
          <h4 class="font-bold text-slate-900 dark:text-white text-xs">Archive Program Registry?</h4>
          <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
            Archiving this QC scholarship program will prevent new application periods from referencing it. Existing records will be retained for audit compliance.
          </p>
        </div>
      </div>
    </div>

    <div class="bg-slate-50 dark:bg-slate-950/60 border-t border-slate-100 dark:border-slate-800 px-6 py-4 flex items-center justify-end space-x-2">
      <button type="button" onclick="closeModal('archiveConfirmModal')" class="border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-300 px-4 py-2 rounded-xl text-xs font-bold transition cursor-pointer">Cancel</button>
      <button type="button" onclick="executeArchive()" class="bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded-xl text-xs font-bold transition cursor-pointer shadow-xs">Confirm Archive</button>
    </div>

  </div>
</div>

<!-- TOAST POPUP NOTIFICATION CONTAINER -->
<div id="toast" class="fixed bottom-4 right-4 z-50 bg-slate-900 text-white text-xs font-bold px-4 py-3.5 rounded-xl shadow-lg flex items-center gap-3 transform translate-y-4 opacity-0 pointer-events-none transition-all duration-300">
  <div class="h-5 w-5 rounded-full bg-emerald-500 flex items-center justify-center text-white text-[10px]">
    <i class="fa-solid fa-check"></i>
  </div>
  <span id="toastMsg" class="tracking-wide">Action executed successfully.</span>
</div>

<!-- INTERACTIVE FRONTEND BEHAVIOR (VANILLA JAVASCRIPT) -->
<script>
// PRE-POPULATED QUEZON CITY SCHOLARSHIP PROGRAMS DATASTORE
let scholarshipPrograms = [
  {
    id: 1,
    code: 'QC-ACAD-2026',
    title: 'QC Tertiary Academic Excellence Scholarship',
    category: 'Academic',
    description: 'Merit-based financial grant awarded to top-performing college students residing in Quezon City.',
    status: 'Active',
    createdAt: '2026-01-15 08:30:00'
  },
  {
    id: 2,
    code: 'QC-FIN-2026',
    title: 'QC Educational Financial Assistance Program',
    category: 'Financial Assistance',
    description: 'Need-based stipend designed to support low-income families with basic schooling expenses.',
    status: 'Active',
    createdAt: '2026-01-18 10:15:00'
  },
  {
    id: 3,
    code: 'QC-SPRT-2026',
    title: 'QC Sports & Athletic Achievers Grant',
    category: 'Sports',
    description: 'Special assistance program for student-athletes representing the city in local and national competitions.',
    status: 'Active',
    createdAt: '2026-02-01 14:00:00'
  },
  {
    id: 4,
    code: 'QC-PWD-2026',
    title: 'QC PWD & Solo Parent Educational Support',
    category: 'Special Sector',
    description: 'Targeted educational assistance reserved for students with disabilities and children of solo parents.',
    status: 'Active',
    createdAt: '2026-02-10 11:45:00'
  },
  {
    id: 5,
    code: 'QC-ARTS-2026',
    title: 'QC Cultural & Creative Arts Fellowship',
    category: 'Cultural & Arts',
    description: 'Incentive grant supporting outstanding youth involved in local heritage, theater, and fine arts.',
    status: 'Inactive',
    createdAt: '2026-03-05 09:20:00'
  }
];

window.pendingArchiveId = null;

// DOM ELEMENT REFERENCES
const tableBody = document.getElementById('scholarshipTableBody');
const searchInput = document.getElementById('searchInput');
const categoryFilter = document.getElementById('categoryFilterSelect');
const statusFilter = document.getElementById('statusFilterSelect');

// CATEGORY BADGE GENERATOR
function getCategoryBadge(category) {
  switch (category) {
    case 'Academic':
      return `<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-[10px] font-bold bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-400 border border-blue-200 dark:border-blue-800/50"><i class="fa-solid fa-graduation-cap text-[9px]"></i> Academic</span>`;
    case 'Financial Assistance':
      return `<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-[10px] font-bold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/50"><i class="fa-solid fa-coins text-[9px]"></i> Financial Assistance</span>`;
    case 'Educational Assistance':
      return `<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-[10px] font-bold bg-cyan-50 dark:bg-cyan-950/60 text-cyan-700 dark:text-cyan-400 border border-cyan-200 dark:border-cyan-800/50"><i class="fa-solid fa-book-open text-[9px]"></i> Educational Assistance</span>`;
    case 'Sports':
      return `<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-[10px] font-bold bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-400 border border-purple-200 dark:border-purple-800/50"><i class="fa-solid fa-trophy text-[9px]"></i> Sports</span>`;
    case 'Special Sector':
      return `<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-[10px] font-bold bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-800/50"><i class="fa-solid fa-wheelchair text-[9px]"></i> Special Sector</span>`;
    case 'Cultural & Arts':
      return `<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-[10px] font-bold bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-400 border border-rose-200 dark:border-rose-800/50"><i class="fa-solid fa-palette text-[9px]"></i> Cultural & Arts</span>`;
    default:
      return `<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700">${category}</span>`;
  }
}

// PASS PERMISSIONS FROM PHP TO JS
const userPermissions = {
  canCreate: <?php echo json_encode(!empty($canCreate)); ?>,
  canEdit: <?php echo json_encode(!empty($canEdit)); ?>,
  canDelete: <?php echo json_encode(!empty($canDelete)); ?>
};

// RENDER SCHOLARSHIP TABLE
function renderTable(data = scholarshipPrograms) {
  if (!tableBody) return;
  tableBody.innerHTML = '';

  if (data.length === 0) {
    tableBody.innerHTML = `
      <tr>
        <td colspan="5" class="px-6 py-12 text-center text-slate-400 font-semibold">
          <i class="fa-solid fa-folder-open text-3xl mb-3 block opacity-60"></i>
          No scholarship programs match your search or filter criteria.
        </td>
      </tr>
    `;
    const pagEl = document.getElementById('paginationText');
    if (pagEl) pagEl.innerText = "Showing 0 to 0 of 0 scholarship programs";
    updateMetrics();
    return;
  }

  data.forEach(item => {
    // Status Badge & Pulse
    let statusStyles = 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border-emerald-200 dark:border-emerald-800/50';
    let dotPulse = '<span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>';
    
    if (item.status === 'Inactive') {
      statusStyles = 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border-slate-200 dark:border-slate-700';
      dotPulse = '<span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>';
    } else if (item.status === 'Archived') {
      statusStyles = 'bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400 border-amber-200 dark:border-amber-800/50';
      dotPulse = '<span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>';
    }

    const isChecked = item.status === 'Active' ? 'checked' : '';

    const tr = document.createElement('tr');
    tr.className = 'hover:bg-slate-50/50 dark:hover:bg-slate-800/50 transition';
    tr.innerHTML = `
      <!-- Code Badge -->
      <td class="px-6 py-4 whitespace-nowrap">
        <div class="h-9 min-w-[5.5rem] px-3 rounded-xl bg-gradient-to-br from-brand-light to-blue-50 dark:from-slate-800 dark:to-slate-900 border border-brand-border/80 dark:border-slate-700 flex items-center justify-center text-brand-dark dark:text-cyan-400 font-mono font-black text-xs tracking-wider shadow-2xs">
          ${item.code}
        </div>
      </td>

      <!-- Scholarship Title & Summary -->
      <td class="px-6 py-4">
        <div class="space-y-1 max-w-md">
          <span class="font-extrabold text-slate-900 dark:text-white tracking-tight text-xs block">${item.title}</span>
          <p class="text-[11px] text-slate-500 dark:text-slate-400 font-medium leading-relaxed line-clamp-2">${item.description}</p>
        </div>
      </td>

      <!-- Category -->
      <td class="px-6 py-4 whitespace-nowrap">
        ${getCategoryBadge(item.category)}
      </td>

      <!-- Status Pill -->
      <td class="px-6 py-4 text-center whitespace-nowrap">
        <span class="text-[10px] font-black uppercase px-2.5 py-1 rounded-full border ${statusStyles} inline-flex items-center gap-1.5">
          ${dotPulse}
          <span>${item.status}</span>
        </span>
      </td>

      <!-- Action Controls -->
      <td class="px-6 py-4 text-right whitespace-nowrap">
        <div class="inline-flex items-center space-x-2.5">
          
          <!-- iOS Status Switch Toggle (Permission Controlled) -->
          ${userPermissions.canEdit ? `
          <label class="relative inline-flex items-center cursor-pointer select-none" title="Toggle Program Active Status">
            <input type="checkbox" ${isChecked} onchange="toggleStatus(${item.id}, this)" class="sr-only peer">
            <div class="w-8 h-4.5 bg-slate-200 dark:bg-slate-700 rounded-full peer peer-focus:ring-2 peer-focus:ring-brand-medium/20 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-3.5 after:w-3.5 after:transition-all peer-checked:bg-emerald-600"></div>
          </label>
          ` : ''}

          <!-- View Details Icon Button -->
          <button type="button" onclick="openViewModal(${item.id})" class="text-slate-400 hover:text-brand-dark dark:hover:text-cyan-400 hover:bg-slate-100 dark:hover:bg-slate-800 p-1.5 rounded-lg border border-slate-200 dark:border-slate-700 transition cursor-pointer" title="View Program Details">
            <i class="fa-solid fa-eye text-xs"></i>
          </button>

          <!-- Edit Icon Button (Permission Controlled) -->
          ${userPermissions.canEdit ? `
          <button type="button" onclick="openEditModal(${item.id})" class="text-slate-400 hover:text-brand-dark dark:hover:text-cyan-400 hover:bg-slate-100 dark:hover:bg-slate-800 p-1.5 rounded-lg border border-slate-200 dark:border-slate-700 transition cursor-pointer" title="Edit Program Parameters">
            <i class="fa-solid fa-pen-to-square text-xs"></i>
          </button>
          ` : ''}

          <!-- Archive / Delete Button (Permission Controlled) -->
          ${userPermissions.canDelete ? `
          <button type="button" onclick="openArchiveModal(${item.id})" class="text-slate-400 hover:text-amber-600 dark:hover:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-950/40 p-1.5 rounded-lg border border-slate-200 dark:border-slate-700 transition cursor-pointer" title="Archive Program">
            <i class="fa-solid fa-box-archive text-xs"></i>
          </button>
          ` : ''}

        </div>
      </td>
    `;
    tableBody.appendChild(tr);
  });

  const pagEl = document.getElementById('paginationText');
  if (pagEl) {
    pagEl.innerText = `Showing 1 to ${data.length} of ${scholarshipPrograms.length} scholarship programs`;
  }

  updateMetrics();
}

// REAL-TIME DYNAMIC FILTERING
function filterPrograms() {
  const query = searchInput ? searchInput.value.toLowerCase().trim() : '';
  const catVal = categoryFilter ? categoryFilter.value : 'ALL';
  const statusVal = statusFilter ? statusFilter.value : 'ALL';

  const filtered = scholarshipPrograms.filter(item => {
    const matchesQuery = !query || 
                         (item.code || '').toLowerCase().includes(query) || 
                         (item.title || '').toLowerCase().includes(query) || 
                         (item.description || '').toLowerCase().includes(query);

    const matchesCategory = catVal === 'ALL' || item.category === catVal;
    const matchesStatus = statusVal === 'ALL' || item.status === statusVal;

    return matchesQuery && matchesCategory && matchesStatus;
  });

  renderTable(filtered);
}

// UPDATE SUMMARY METRIC CARDS
function updateMetrics() {
  const total = scholarshipPrograms.length;
  const activeCount = scholarshipPrograms.filter(p => p.status === 'Active').length;
  const categoriesSet = new Set(scholarshipPrograms.map(p => p.category));

  const totalEl = document.getElementById('statTotalPrograms');
  const activeEl = document.getElementById('statActiveOfferings');
  const categoriesEl = document.getElementById('statCategories');

  if (totalEl) totalEl.innerText = total;
  if (activeEl) activeEl.innerText = activeCount;
  if (categoriesEl) categoriesEl.innerText = categoriesSet.size;
}

// INTERACTIVE CARD FILTER HANDLER
function filterByCard(type) {
  document.querySelectorAll('.role-metric-card').forEach(card => {
    card.classList.remove('ring-2', 'ring-cyan-500', 'ring-emerald-500', 'ring-blue-500');
  });

  if (type === 'ALL') {
    if (categoryFilter) categoryFilter.value = 'ALL';
    if (statusFilter) statusFilter.value = 'ALL';
    if (searchInput) searchInput.value = '';
    const card = document.getElementById('cardTotalPrograms');
    if (card) card.classList.add('ring-2', 'ring-cyan-500');
  } else if (type === 'ACTIVE') {
    if (statusFilter) statusFilter.value = 'Active';
    if (categoryFilter) categoryFilter.value = 'ALL';
    if (searchInput) searchInput.value = '';
    const card = document.getElementById('cardActiveOfferings');
    if (card) card.classList.add('ring-2', 'ring-emerald-500');
  } else if (type === 'CATEGORIES') {
    if (categoryFilter) categoryFilter.value = 'Academic';
    if (statusFilter) statusFilter.value = 'ALL';
    if (searchInput) searchInput.value = '';
    const card = document.getElementById('cardCategories');
    if (card) card.classList.add('ring-2', 'ring-blue-500');
  }

  filterPrograms();

  if (searchInput) {
    searchInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
  }
}

// MODAL CONTROL HELPERS
function openModal(id) {
  const modal = document.getElementById(id);
  if (!modal) return;
  modal.classList.remove('opacity-0', 'pointer-events-none');
  modal.classList.add('opacity-100', 'pointer-events-auto');
  const innerCard = modal.querySelector('.transform');
  if (innerCard) {
    innerCard.classList.remove('scale-95');
    innerCard.classList.add('scale-100');
  }
}

function closeModal(id) {
  const modal = document.getElementById(id);
  if (!modal) return;
  modal.classList.remove('opacity-100', 'pointer-events-auto');
  modal.classList.add('opacity-0', 'pointer-events-none');
  const innerCard = modal.querySelector('.transform');
  if (innerCard) {
    innerCard.classList.remove('scale-100');
    innerCard.classList.add('scale-95');
  }
}

// OPEN CREATE SCHOLARSHIP MODAL
function openCreateModal() {
  document.getElementById('scholarshipForm').reset();
  document.getElementById('programIdRef').value = '';
  document.getElementById('modalTitle').innerText = 'Create Scholarship Program Type';
  document.getElementById('modalHeaderIcon').className = 'fa-solid fa-graduation-cap';
  document.getElementById('programCode').readOnly = false;
  openModal('scholarshipModal');
}

// OPEN EDIT SCHOLARSHIP MODAL
function openEditModal(id) {
  const program = scholarshipPrograms.find(p => p.id === id);
  if (!program) return;

  document.getElementById('programIdRef').value = program.id;
  document.getElementById('programCode').value = program.code;
  document.getElementById('programTitle').value = program.title;
  document.getElementById('programCategory').value = program.category;
  document.getElementById('programStatus').value = program.status;
  document.getElementById('programDesc').value = program.description;

  document.getElementById('modalTitle').innerText = `Edit Program: ${program.code}`;
  document.getElementById('modalHeaderIcon').className = 'fa-solid fa-pen-to-square';
  openModal('scholarshipModal');
}

// OPEN VIEW DETAILS MODAL
function openViewModal(id) {
  const program = scholarshipPrograms.find(p => p.id === id);
  if (!program) return;

  document.getElementById('viewCode').innerText = program.code;
  document.getElementById('viewTitle').innerText = program.title;
  document.getElementById('viewDesc').innerText = program.description;
  document.getElementById('viewCategoryBadge').innerHTML = getCategoryBadge(program.category);

  let statusBadge = `<span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase bg-emerald-50 text-emerald-700 border border-emerald-200"><i class="fa-solid fa-circle-check"></i> Active</span>`;
  if (program.status === 'Inactive') {
    statusBadge = `<span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase bg-slate-100 text-slate-600 border border-slate-200">Inactive</span>`;
  } else if (program.status === 'Archived') {
    statusBadge = `<span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase bg-amber-50 text-amber-700 border border-amber-200">Archived</span>`;
  }
  document.getElementById('viewStatusBadge').innerHTML = statusBadge;

  openModal('viewModal');
}

// TOGGLE STATUS DIRECTLY FROM DATATABLE
function toggleStatus(id, checkbox) {
  const program = scholarshipPrograms.find(p => p.id === id);
  if (program) {
    program.status = checkbox.checked ? 'Active' : 'Inactive';
    showToast(`Program ${program.code} set to ${program.status}.`);
    updateMetrics();
  }
}

// OPEN ARCHIVE CONFIRMATION MODAL
function openArchiveModal(id) {
  window.pendingArchiveId = id;
  openModal('archiveConfirmModal');
}

// EXECUTE ARCHIVE ACTION
function executeArchive() {
  if (!window.pendingArchiveId) return;
  const index = scholarshipPrograms.findIndex(p => p.id === window.pendingArchiveId);
  if (index !== -1) {
    const code = scholarshipPrograms[index].code;
    scholarshipPrograms[index].status = 'Archived';
    showToast(`Program ${code} archived successfully.`);
    filterPrograms();
  }
  closeModal('archiveConfirmModal');
  window.pendingArchiveId = null;
}

// SAVE PROGRAM (CREATE OR EDIT)
function handleSaveProgram(event) {
  event.preventDefault();
  const refId = document.getElementById('programIdRef').value;
  const code = document.getElementById('programCode').value.trim().toUpperCase();
  const title = document.getElementById('programTitle').value.trim();
  const category = document.getElementById('programCategory').value;
  const status = document.getElementById('programStatus').value;
  const desc = document.getElementById('programDesc').value.trim();

  if (refId) {
    // Edit existing
    const program = scholarshipPrograms.find(p => p.id == refId);
    if (program) {
      program.code = code;
      program.title = title;
      program.category = category;
      program.status = status;
      program.description = desc;
      showToast(`Program ${code} updated successfully.`);
    }
  } else {
    // Create new
    const newProg = {
      id: Date.now(),
      code,
      title,
      category,
      description: desc,
      status,
      createdAt: new Date().toISOString().replace('T', ' ').substring(0, 19)
    };
    scholarshipPrograms.unshift(newProg);
    showToast(`New scholarship program ${code} registered.`);
  }

  closeModal('scholarshipModal');
  filterPrograms();
}

// TOAST NOTIFICATIONS
let scholToastTimer = null;
function showToast(message, isError = false) {
  const toast = document.getElementById('toast');
  const toastMsg = document.getElementById('toastMsg');
  let iconBox = document.getElementById('toastIconBox') || (toast ? toast.querySelector('div') : null);
  let iconSymbol = document.getElementById('toastIconSymbol') || (iconBox ? iconBox.querySelector('i') : null);

  if (!toast || !toastMsg) return;

  const msgLower = (message || '').toString().toLowerCase();
  if (!isError && (
    msgLower.includes('error') || 
    msgLower.includes('failed') || 
    msgLower.includes('invalid') || 
    msgLower.includes('network') || 
    msgLower.includes('require') || 
    msgLower.includes('forbidden') || 
    msgLower.includes('denied') || 
    msgLower.includes('cannot') ||
    msgLower.includes('unable') ||
    msgLower.includes('please')
  )) {
    isError = true;
  }

  toastMsg.innerText = message;

  if (iconBox) {
    if (isError) {
      iconBox.className = "h-5 w-5 rounded-full bg-rose-500 flex items-center justify-center text-white text-[10px] shrink-0";
    } else {
      iconBox.className = "h-5 w-5 rounded-full bg-emerald-500 flex items-center justify-center text-white text-[10px] shrink-0";
    }
  }

  if (iconSymbol) {
    if (isError) {
      iconSymbol.className = "fa-solid fa-xmark";
    } else {
      iconSymbol.className = "fa-solid fa-check";
    }
  }

  toast.classList.remove('opacity-0', 'pointer-events-none', 'translate-y-4');
  toast.classList.add('opacity-100', 'pointer-events-auto', 'translate-y-0');

  if (scholToastTimer) clearTimeout(scholToastTimer);
  scholToastTimer = setTimeout(() => {
    toast.classList.remove('opacity-100', 'pointer-events-auto', 'translate-y-0');
    toast.classList.add('opacity-0', 'pointer-events-none', 'translate-y-4');
  }, 3000);
}

// BACKDROP & ESCAPE KEY MODAL DISMISS
document.querySelectorAll('#scholarshipModal, #viewModal, #archiveConfirmModal').forEach(modal => {
  modal.addEventListener('click', (e) => {
    if (e.target === modal) {
      closeModal(modal.id);
    }
  });
});

document.addEventListener('keydown', (e) => {
  if (e.key === 'Escape') {
    ['scholarshipModal', 'viewModal', 'archiveConfirmModal'].forEach(id => closeModal(id));
  }
});

// INITIAL RENDER ON PAGE LOAD
document.addEventListener('DOMContentLoaded', () => {
  renderTable();
});
</script>

<?php include '../../../includes/footer.php'; ?>
