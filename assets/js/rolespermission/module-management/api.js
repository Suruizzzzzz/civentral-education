// MODULE MANAGEMENT API

var systemModules = [];
var currentUserScope = null;
var archiveTargetId = null;

function normalizeStatus(str) {
  if (!str) return 'Active';
  const s = String(str).trim().toLowerCase();
  if (s === 'active') return 'Active';
  if (s === 'inactive') return 'Inactive';
  if (s === 'archived') return 'Archived';
  return str.charAt(0).toUpperCase() + str.slice(1);
}
window.normalizeStatus = normalizeStatus;

function getLocalCustomModules() {
  try {
    const stored = localStorage.getItem('civentral_custom_modules');
    return stored ? JSON.parse(stored) : [];
  } catch (e) {
    return [];
  }
}
window.getLocalCustomModules = getLocalCustomModules;

function saveLocalCustomModule(modObj) {
  try {
    const list = getLocalCustomModules();
    const cleanName = (modObj.name || '').trim().toLowerCase();
    const idx = list.findIndex(m => String(m.id) === String(modObj.id) || (m.name || '').trim().toLowerCase() === cleanName);
    if (idx >= 0) {
      list[idx] = { ...list[idx], ...modObj };
    } else {
      list.unshift(modObj);
    }
    localStorage.setItem('civentral_custom_modules', JSON.stringify(list));
  } catch (e) {
    console.error('Failed to save module to localStorage', e);
  }
}
window.saveLocalCustomModule = saveLocalCustomModule;

// FETCH MODULES FROM Database REST API
async function fetchModules() {
  try {
    const response = await fetch('../../api/employee/modules.php');
    if (!response.ok) {
      console.warn('Fetch modules response HTTP status:', response.status);
    }
    const result = await response.json();
    
    let rawList = null;
    if (Array.isArray(result)) {
      rawList = result;
    } else if (Array.isArray(result.data)) {
      rawList = result.data;
    } else if (Array.isArray(result.modules)) {
      rawList = result.modules;
    } else if (result.data && Array.isArray(result.data.modules)) {
      rawList = result.data.modules;
    } else if (result.data && Array.isArray(result.data.data)) {
      rawList = result.data.data;
    }
    
    const dataList = Array.isArray(rawList) ? rawList : [];

    const serverModules = dataList.map(m => ({
      id: m.module_id || m.id,
      name: m.module_name || m.name || 'Unassigned Module',
      desc: m.description || m.desc || '',
      status: normalizeStatus(m.status),
      created_at: m.created_at ? String(m.created_at).replace('T', ' ').substring(0, 19) : '',
      updated_at: m.updated_at ? String(m.updated_at).replace('T', ' ').substring(0, 19) : ''
    }));

    // Merge local custom modules from localStorage so they persist across page reloads
    const localModules = getLocalCustomModules();
    const modMap = new Map();

    serverModules.forEach(m => {
      modMap.set(String(m.id), m);
      if (m.name) modMap.set(m.name.trim().toLowerCase(), m);
    });

    localModules.forEach(m => {
      const idKey = String(m.id);
      const nameKey = (m.name || '').trim().toLowerCase();
      const existing = modMap.get(idKey) || (nameKey ? modMap.get(nameKey) : null);
      if (existing) {
        const merged = { ...existing, ...m };
        modMap.set(idKey, merged);
        if (nameKey) modMap.set(nameKey, merged);
      } else {
        modMap.set(idKey, m);
        if (nameKey) modMap.set(nameKey, m);
      }
    });

    let combined = Array.from(new Set(modMap.values()));

    combined.sort((a, b) => {
      const timeA = a.updated_at || a.created_at || '';
      const timeB = b.updated_at || b.created_at || '';
      if (timeA && timeB && timeA !== timeB) {
        return timeB.localeCompare(timeA);
      }
      return String(b.id).localeCompare(String(a.id), undefined, { numeric: true });
    });

    systemModules = combined;
    currentUserScope = result.current_user || currentUserScope || null;
    if (typeof filterModules === 'function') filterModules();
  } catch (err) {
    console.error('Error fetching modules FROM DATABASE:', err);
    const localModules = getLocalCustomModules();
    if (localModules.length > 0) {
      systemModules = localModules;
      if (typeof filterModules === 'function') filterModules();
    }
    if (typeof showToast === 'function') showToast('Network error connecting to Database.');
  } finally {
    if (typeof hideModuleSkeleton === 'function') {
      hideModuleSkeleton();
    }
  }
}

// UPDATE MODULE STATUS IN DATABASE
async function updateModuleStatusInDb(moduleId, newStatus) {
  const normStatus = normalizeStatus(newStatus);
  const modIndex = systemModules.findIndex(m => String(m.id) === String(moduleId));
  if (modIndex >= 0) {
    systemModules[modIndex].status = normStatus;
    saveLocalCustomModule(systemModules[modIndex]);
    if (typeof filterModules === 'function') filterModules();
  }

  try {
    const response = await fetch('../../api/employee/modules.php', {
      method: 'PATCH',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ module_id: moduleId, status: normStatus })
    });
    const result = await response.json();
    const isSuccess = response.ok && (
      result.status === 'success' || 
      result.success === true || 
      result.code === 200 || 
      !result.status
    );

    if (isSuccess) {
      if (typeof showToast === 'function') showToast(`Module status updated to ${normStatus}.`);
      await fetchModules();
    } else {
      if (typeof showToast === 'function') showToast(result.message || 'Failed to update module status.');
      await fetchModules();
    }
  } catch (err) {
    console.error('Error updating module status:', err);
    if (typeof showToast === 'function') showToast('Error updating status IN DATABASE.');
    await fetchModules();
  }
}
