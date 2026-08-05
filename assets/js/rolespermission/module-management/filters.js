// FILTER MODULES REAL TIME
var currentTabStatus = 'Active';
var currentPage = 1;
var pageSize = 10;

function switchStatusTab(targetStatus) {
  currentTabStatus = targetStatus;
  currentPage = 1;

  document.querySelectorAll('.status-tab-btn').forEach(btn => {
    const tabVal = btn.getAttribute('data-status-tab') || '';
    if (tabVal.toLowerCase() === String(targetStatus).toLowerCase()) {
      btn.className = "status-tab-btn px-4 py-2.5 rounded-t-xl text-xs font-black transition-all cursor-pointer bg-white text-[#176B87] border-t-2 border-[#86B6F6] border-x border-slate-200/80 shadow-2xs";
    } else {
      btn.className = "status-tab-btn px-4 py-2.5 rounded-t-xl text-xs font-bold transition-all cursor-pointer text-slate-500 hover:text-slate-800 hover:bg-slate-100/60";
    }
  });

  filterModules();
}

function filterModules() {
  const searchInput = document.getElementById('moduleSearchInput');
  const query = searchInput ? searchInput.value.toLowerCase().trim() : '';

  const filtered = systemModules.filter(mod => {
    const nameStr = (mod.name || '').toLowerCase();
    const descStr = (mod.desc || '').toLowerCase();
    const matchesQuery = !query || nameStr.includes(query) || descStr.includes(query);

    const mStatus = (mod.status || 'Active').trim();
    const tabTarget = (currentTabStatus || 'Active').trim();
    const matchesStatus = tabTarget.toUpperCase() === 'ALL' || mStatus.toLowerCase() === tabTarget.toLowerCase();

    return matchesQuery && matchesStatus;
  });

  if (typeof renderModulesTable === 'function') renderModulesTable(filtered);
}

window.switchStatusTab = switchStatusTab;
window.filterModules = filterModules;
