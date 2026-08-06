// RESOURCE MANAGEMENT API

var systemResources = [];
var systemModulesList = [];
var systemActionVerbsList = [];
var currentUserScope = null;
var archiveTargetResourceId = null;

window.systemActionVerbsList = systemActionVerbsList;

// Clear legacy local storage keys
try {
  localStorage.removeItem('civentral_custom_resources');
} catch (e) {}

// FETCH ACTION VERBS FROM ACTION MANAGEMENT API
async function fetchActionVerbsList() {
  try {
    const res = await fetch('../../api/employee/actions.php');
    const resData = await res.json();
    if ((resData.status === 'success' || resData.success || res.ok) && Array.isArray(resData.data) && resData.data.length > 0) {
      systemActionVerbsList = resData.data.filter(a => (a.status || '').toLowerCase() !== 'archived');
      window.systemActionVerbsList = systemActionVerbsList;
    }
  } catch (err) {
    console.error('Error fetching action verbs:', err);
  }
}
window.fetchActionVerbsList = fetchActionVerbsList;

// FETCH RESOURCES AND MODULES FROM Database API
async function fetchResources() {
  try {
    await fetchActionVerbsList();
    const response = await fetch('../../api/employee/resources.php');
    if (!response.ok) {
      console.warn('Fetch resources response HTTP status:', response.status);
    }
    const result = await response.json();
    if (result.status === 'success' || result.success || response.ok) {
      currentUserScope = result.current_user || currentUserScope || null;
      systemModulesList = Array.isArray(result.modules) ? result.modules : [];
      populateModuleSelects();

      if (Array.isArray(result.actions) && result.actions.length > 0) {
        systemActionVerbsList = result.actions;
        window.systemActionVerbsList = systemActionVerbsList;
      }

      if (Array.isArray(result.data)) {
        systemResources = result.data.map(r => {
          const modObj = systemModulesList.find(m => String(m.module_id || m.id) === String(r.module_id));
          return {
            id: r.resource_id,
            module_id: r.module_id,
            module: r.modules ? r.modules.module_name : (modObj ? (modObj.module_name || modObj.name) : 'Unassigned'),
            name: r.resource_name,
            route: r.resource_route || '',
            desc: r.description || '',
            status: r.status || 'Active',
            action_ids: Array.isArray(r.action_ids) ? r.action_ids : [],
            created_at: r.created_at ? r.created_at.replace('T', ' ').substring(0, 19) : '',
            updated_at: r.updated_at ? r.updated_at.replace('T', ' ').substring(0, 19) : ''
          };
        });

        systemResources.sort((a, b) => {
          const timeA = a.updated_at || a.created_at || '';
          const timeB = b.updated_at || b.created_at || '';
          if (timeA && timeB && timeA !== timeB) {
            return timeB.localeCompare(timeA);
          }
          return String(b.id).localeCompare(String(a.id), undefined, { numeric: true });
        });

        if (typeof filterResources === 'function') filterResources();
      }
    } else {
      console.warn('Resources fetch notice:', result.message);
    }
  } catch (err) {
    console.error('Error fetching resources FROM DATABASE:', err);
    if (typeof showToast === 'function') showToast('Network error connecting to Database.');
  } finally {
    if (typeof hideResourceSkeleton === 'function') {
      hideResourceSkeleton();
    }
  }
}

function getCombinedSystemModules() {
  const modMap = new Map();

  // 1. Local custom modules from localStorage
  try {
    const stored = localStorage.getItem('civentral_custom_modules');
    if (stored) {
      const parsed = JSON.parse(stored);
      if (Array.isArray(parsed)) {
        parsed.forEach(m => {
          const mId = m.module_id || m.id;
          const mName = m.module_name || m.name;
          const mStatus = m.status || 'Active';
          if (mName) {
            const modObj = { module_id: mId, id: mId, module_name: mName, name: mName, status: mStatus };
            modMap.set(String(mId), modObj);
            modMap.set(mName.trim().toLowerCase(), modObj);
          }
        });
      }
    }
  } catch (e) {}

  // 2. Global systemModules array if available
  if (typeof systemModules !== 'undefined' && Array.isArray(systemModules)) {
    systemModules.forEach(m => {
      const mId = m.module_id || m.id;
      const mName = m.module_name || m.name;
      const mStatus = m.status || 'Active';
      if (mName) {
        const modObj = { module_id: mId, id: mId, module_name: mName, name: mName, status: mStatus };
        modMap.set(String(mId), modObj);
        modMap.set(mName.trim().toLowerCase(), modObj);
      }
    });
  }

  // 3. API systemModulesList
  if (Array.isArray(systemModulesList)) {
    systemModulesList.forEach(m => {
      const mId = m.module_id || m.id;
      const mName = m.module_name || m.name;
      const mStatus = m.status || 'Active';
      if (mName) {
        const modObj = { module_id: mId, id: mId, module_name: mName, name: mName, status: mStatus };
        const existing = modMap.get(String(mId)) || modMap.get(mName.trim().toLowerCase());
        if (!existing) {
          modMap.set(String(mId), modObj);
          modMap.set(mName.trim().toLowerCase(), modObj);
        }
      }
    });
  }

  return Array.from(new Set(modMap.values()));
}
window.getCombinedSystemModules = getCombinedSystemModules;

// DYNAMICALLY POPULATE PARENT MODULE SELECT DROPDOWNS
function populateModuleSelects() {
  const filterSelect = document.getElementById('parentModuleFilter');
  const modalSelect = document.getElementById('resourceParentModule');

  const combinedModules = getCombinedSystemModules();

  if (filterSelect) {
    const curVal = filterSelect.value || 'ALL';
    let optionsHtml = '<option value="ALL">All Parent Modules</option>';
    combinedModules.forEach(m => {
      const mId = m.module_id || m.id;
      const mName = m.module_name || m.name || 'Unassigned Module';
      optionsHtml += `<option value="${mId}">${mName}</option>`;
    });
    filterSelect.innerHTML = optionsHtml;
    filterSelect.value = curVal;
  }

  if (modalSelect) {
    const curVal = modalSelect.value;
    let optionsHtml = '<option value="" disabled selected>Select Parent Module...</option>';
    combinedModules.forEach(m => {
      const isArchived = (m.status || '').toString().trim().toLowerCase() === 'archived';
      if (isArchived) return; // Exclude archived modules from parent module selection modal

      const mId = m.module_id || m.id;
      const mName = m.module_name || m.name || 'Unassigned Module';
      optionsHtml += `<option value="${mId}">${mName}</option>`;
    });
    modalSelect.innerHTML = optionsHtml;
    if (curVal) modalSelect.value = curVal;
  }
}

// UPDATE RESOURCE STATUS IN DATABASE
async function updateResourceStatusInDb(resourceId, newStatus) {
  try {
    const response = await fetch('../../api/employee/resources.php', {
      method: 'PATCH',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ resource_id: resourceId, status: newStatus })
    });
    const result = await response.json();
    if (result.status === 'success') {
      if (typeof showToast === 'function') showToast(`Resource status updated to ${newStatus}.`);
      await fetchResources();
      if (typeof switchStatusTab === 'function') switchStatusTab(newStatus);
    } else {
      if (typeof showToast === 'function') showToast(result.message || 'Failed to update resource status.');
    }
  } catch (err) {
    console.error('Error updating status:', err);
    if (typeof showToast === 'function') showToast('Error updating status IN DATABASE.');
  }
}
