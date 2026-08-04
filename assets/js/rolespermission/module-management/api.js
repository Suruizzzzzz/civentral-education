// MODULE MANAGEMENT API

var systemModules = [];
var currentUserScope = null;
var archiveTargetId = null;

// Clear legacy local storage keys
try {
  localStorage.removeItem('civentral_custom_modules');
  localStorage.removeItem('civentral_module_status_overrides');
} catch (e) {}

// FETCH MODULES FROM Database REST API
async function fetchModules() {
  try {
    const response = await fetch('../../api/employee/modules.php');
    if (!response.ok) {
      console.warn('Fetch modules response HTTP status:', response.status);
    }
    const result = await response.json();
    const dataList = Array.isArray(result.data) ? result.data : (Array.isArray(result) ? result : (result.modules || null));
    
    const serverModules = (Array.isArray(dataList) ? dataList : []).map(m => ({
      id: m.module_id || m.id,
      name: m.module_name || m.name || 'Unassigned Module',
      desc: m.description || m.desc || '',
      status: (m.status || 'Active').trim(),
      created_at: m.created_at ? String(m.created_at).replace('T', ' ').substring(0, 19) : '',
      updated_at: m.updated_at ? String(m.updated_at).replace('T', ' ').substring(0, 19) : ''
    }));

    serverModules.sort((a, b) => {
      const timeA = a.updated_at || a.created_at || '';
      const timeB = b.updated_at || b.created_at || '';
      if (timeA && timeB && timeA !== timeB) {
        return timeB.localeCompare(timeA);
      }
      return String(b.id).localeCompare(String(a.id), undefined, { numeric: true });
    });

    systemModules = serverModules;
    currentUserScope = result.current_user || currentUserScope || null;
    if (typeof filterModules === 'function') filterModules();
  } catch (err) {
    console.error('Error fetching modules FROM DATABASE:', err);
    if (typeof showToast === 'function') showToast('Network error connecting to Database.');
  } finally {
    if (typeof hideModuleSkeleton === 'function') {
      hideModuleSkeleton();
    }
  }
}

// UPDATE MODULE STATUS IN DATABASE
async function updateModuleStatusInDb(moduleId, newStatus) {
  const modIndex = systemModules.findIndex(m => String(m.id) === String(moduleId));
  if (modIndex >= 0) {
    systemModules[modIndex].status = newStatus;
    if (typeof filterModules === 'function') filterModules();
  }

  try {
    const response = await fetch('../../api/employee/modules.php', {
      method: 'PATCH',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ module_id: moduleId, status: newStatus })
    });
    const result = await response.json();
    const isSuccess = response.ok && (
      result.status === 'success' || 
      result.success === true || 
      result.code === 200 || 
      !result.status
    );

    if (isSuccess) {
      if (typeof showToast === 'function') showToast(`Module status updated to ${newStatus}.`);
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
