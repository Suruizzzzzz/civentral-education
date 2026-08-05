// RENDER MODULES TABLE AND METRICS
function renderModulesTable(dataToRender = systemModules) {
  const tableBody = document.getElementById('moduleTableBody');
  const emptyState = document.getElementById('emptyTableState');
  if (!tableBody) return;

  tableBody.innerHTML = '';

  const totalFiltered = dataToRender.length;
  const totalPages = Math.ceil(totalFiltered / pageSize) || 1;
  if (currentPage > totalPages) currentPage = totalPages;
  if (currentPage < 1) currentPage = 1;

  const startIndex = (currentPage - 1) * pageSize;
  const endIndex = Math.min(startIndex + pageSize, totalFiltered);
  const pageData = dataToRender.slice(startIndex, endIndex);

  if (totalFiltered === 0) {
    if (emptyState) emptyState.classList.remove('hidden');
    tableBody.innerHTML = `
      <tr>
        <td colspan="6" class="px-6 py-10 text-center text-xs text-slate-400 font-medium bg-slate-50/30">
          <div class="flex flex-col items-center justify-center space-y-2">
            <i class="fa-solid fa-folder-open text-2xl text-slate-300"></i>
            <span>No system modules found for this tab or search filter.</span>
          </div>
        </td>
      </tr>
    `;
    renderPaginationUI(0, 0, 0, 1);
    updateMetrics();
    return;
  } else {
    if (emptyState) emptyState.classList.add('hidden');
  }

  const isSuperAdmin = currentUserScope ? (!!currentUserScope.is_superadmin || !!currentUserScope.is_global_access) : false;
  const grantedActions = currentUserScope ? (currentUserScope.granted_actions || []) : [];
  const hasActionList = Array.isArray(grantedActions) && grantedActions.length > 0;
  const canEdit = isSuperAdmin || !hasActionList || grantedActions.includes('EDIT');

  pageData.forEach(mod => {
    const tr = document.createElement('tr');
    tr.className = 'hover:bg-slate-50/60 transition group';

    // Status Badge HTML
    let statusBadgeHtml = '';
    const mStatus = typeof normalizeStatus === 'function' ? normalizeStatus(mod.status) : (mod.status || 'Active');
    if (mStatus === 'Active') {
      statusBadgeHtml = `
        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-black bg-emerald-50 text-emerald-700 border border-emerald-200">
          <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
          Active
        </span>
      `;
    } else if (mStatus === 'Inactive') {
      statusBadgeHtml = `
        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-black bg-slate-100 text-slate-600 border border-slate-200">
          <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>
          Inactive
        </span>
      `;
    } else {
      statusBadgeHtml = `
        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-black bg-amber-50 text-amber-700 border border-amber-200">
          <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
          Archived
        </span>
      `;
    }

    // iOS Style Status Toggle Switch
    const isChecked = mStatus === 'Active';
    const isArchived = mStatus === 'Archived';

    tr.innerHTML = `
      <td class="px-6 py-4">
        <span class="font-extrabold text-slate-900 tracking-tight block text-xs">${mod.name}</span>
      </td>

      <td class="px-6 py-4 max-w-xs">
        <p class="text-xs text-slate-600 font-medium leading-relaxed">${mod.desc || '<span class="text-slate-400 italic">No description</span>'}</p>
      </td>

      <td class="px-6 py-4 text-center">
        ${statusBadgeHtml}
      </td>

      <td class="px-6 py-4 text-slate-500 font-mono text-[10px] font-bold">
        ${mod.created_at || 'N/A'}
      </td>

      <td class="px-6 py-4 text-slate-500 font-mono text-[10px] font-bold">
        ${mod.updated_at || 'N/A'}
      </td>

      <td class="px-6 py-4 text-right">
        <div class="flex items-center justify-end gap-3">
          ${canEdit ? `
          <!-- iOS Toggle Switch -->
          <label class="relative inline-flex items-center cursor-pointer ${isArchived ? 'opacity-50 pointer-events-none' : ''}" title="Activate/Deactivate Module">
            <input 
              type="checkbox" 
              ${isChecked ? 'checked' : ''} 
              onchange="toggleModuleStatus(${mod.id})" 
              class="sr-only peer"
            >
            <div class="w-8 h-4.5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-3.5 after:w-3.5 after:transition-all peer-checked:bg-emerald-600"></div>
          </label>

          <!-- Edit Button -->
          <button 
            type="button" 
            onclick="openEditModal(${mod.id})" 
            class="h-8 w-8 rounded-lg border border-slate-200 hover:bg-slate-100 text-slate-600 hover:text-brand-dark flex items-center justify-center transition cursor-pointer"
            title="Edit Module Parameters"
          >
            <i class="fa-solid fa-pen-to-square text-xs"></i>
          </button>

          <!-- Archive Button -->
          <button 
            type="button" 
            onclick="openArchiveModal(${mod.id})" 
            class="h-8 w-8 rounded-lg border border-slate-200 hover:bg-amber-50 text-slate-400 hover:text-amber-600 flex items-center justify-center transition cursor-pointer ${isArchived ? 'opacity-40 cursor-not-allowed' : ''}"
            ${isArchived ? 'disabled' : ''}
            title="Archive Module"
          >
            <i class="fa-solid fa-box-archive text-xs"></i>
          </button>` : `<span class="text-[10px] text-slate-400 font-bold italic">Read-only</span>`}
        </div>
      </td>
    `;

    tableBody.appendChild(tr);
  });

  renderPaginationUI(startIndex, endIndex, totalFiltered, totalPages);
  updateMetrics();
}

function renderPaginationUI(startIndex, endIndex, totalFiltered, totalPages) {
  const paginationEl = document.getElementById('paginationText');
  const controlsEl = document.getElementById('paginationControls');

  if (paginationEl) {
    if (totalFiltered === 0) {
      paginationEl.innerText = "Showing 0 to 0 of 0 modules";
    } else {
      paginationEl.innerText = `Showing ${startIndex + 1} to ${endIndex} of ${totalFiltered} modules`;
    }
  }

  if (!controlsEl) return;
  controlsEl.innerHTML = '';

  if (totalPages <= 1) return;

  // Prev Button
  const prevBtn = document.createElement('button');
  prevBtn.className = `px-3 py-1.5 rounded-lg border text-xs font-bold transition flex items-center justify-center ${currentPage === 1 ? 'border-slate-200 bg-white text-slate-300 cursor-not-allowed' : 'border-slate-200 bg-white hover:bg-slate-50 text-slate-600 cursor-pointer'}`;
  prevBtn.disabled = currentPage === 1;
  prevBtn.innerHTML = '<i class="fa-solid fa-chevron-left text-[9px]"></i>';
  prevBtn.onclick = () => { if (currentPage > 1) { currentPage--; filterModules(); } };
  controlsEl.appendChild(prevBtn);

  // Page Numbers
  for (let i = 1; i <= totalPages; i++) {
    const pBtn = document.createElement('button');
    if (i === currentPage) {
      pBtn.className = "px-3 py-1.5 rounded-lg bg-[#86B6F6] border border-[#72a6eb] text-slate-900 font-black shadow-2xs text-xs";
    } else {
      pBtn.className = "px-3 py-1.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 font-bold transition text-xs cursor-pointer";
    }
    pBtn.innerText = i;
    pBtn.onclick = () => { currentPage = i; filterModules(); };
    controlsEl.appendChild(pBtn);
  }

  // Next Button
  const nextBtn = document.createElement('button');
  nextBtn.className = `px-3 py-1.5 rounded-lg border text-xs font-bold transition flex items-center justify-center ${currentPage === totalPages ? 'border-slate-200 bg-white text-slate-300 cursor-not-allowed' : 'border-slate-200 bg-white hover:bg-slate-50 text-slate-600 cursor-pointer'}`;
  nextBtn.disabled = currentPage === totalPages;
  nextBtn.innerHTML = '<i class="fa-solid fa-chevron-right text-[9px]"></i>';
  nextBtn.onclick = () => { if (currentPage < totalPages) { currentPage++; filterModules(); } };
  controlsEl.appendChild(nextBtn);
}

// UPDATE TOP METRIC CARDS
function updateMetrics() {
  const totalEl = document.getElementById('metricTotalModules');
  const activeEl = document.getElementById('metricActiveModules');
  const inactiveEl = document.getElementById('metricInactiveModules');

  if (totalEl) totalEl.textContent = systemModules.length;
  
  const getNormStatus = m => (typeof normalizeStatus === 'function' ? normalizeStatus(m.status) : (m.status || 'Active'));
  const activeCount = systemModules.filter(m => getNormStatus(m) === 'Active').length;
  const inactiveCount = systemModules.filter(m => getNormStatus(m) === 'Inactive').length;
  const archivedCount = systemModules.filter(m => getNormStatus(m) === 'Archived').length;

  if (activeEl) activeEl.textContent = activeCount;
  if (inactiveEl) inactiveEl.textContent = inactiveCount + archivedCount;

  // Update tab counts
  const tabActive = document.getElementById('tabCountActive');
  const tabInactive = document.getElementById('tabCountInactive');
  const tabArchived = document.getElementById('tabCountArchived');
  const tabAll = document.getElementById('tabCountAll');

  if (tabActive) tabActive.innerText = activeCount;
  if (tabInactive) tabInactive.innerText = inactiveCount;
  if (tabArchived) tabArchived.innerText = archivedCount;
  if (tabAll) tabAll.innerText = systemModules.length;

  hideModuleSkeleton();
}

function hideModuleSkeleton() {
  const skel = document.getElementById('moduleSkeleton');
  const real = document.getElementById('moduleRealContent');
  if (real) {
    real.classList.remove('hidden', 'opacity-0', 'translate-y-2');
    real.classList.add('opacity-100', 'translate-y-0');
  }
  if (skel) {
    skel.classList.add('opacity-0', 'pointer-events-none', 'hidden');
  }
}
