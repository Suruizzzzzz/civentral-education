// RESOURCE MANAGEMENT API

var systemResources = [];
var systemModulesList = [];
var currentUserScope = null;
var archiveTargetResourceId = null;

// Clear legacy local storage keys
try {
  localStorage.removeItem('civentral_custom_resources');
} catch (e) {}

// FETCH RESOURCES AND MODULES FROM Database API
async function fetchResources() {
  try {
    const response = await fetch('../../api/employee/resources.php');
    if (!response.ok) {
      console.warn('Fetch resources response HTTP status:', response.status);
    }
    const result = await response.json();
    if (result.status === 'success' || result.success || response.ok) {
      currentUserScope = result.current_user || currentUserScope || null;
      systemModulesList = Array.isArray(result.modules) ? result.modules : [];
      populateModuleSelects();

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

// DYNAMICALLY POPULATE PARENT MODULE SELECT DROPDOWNS
function populateModuleSelects() {
  const filterSelect = document.getElementById('parentModuleFilter');
  const modalSelect = document.getElementById('resourceParentModule');

  if (filterSelect) {
    const curVal = filterSelect.value || 'ALL';
    let optionsHtml = '<option value="ALL">All Parent Modules</option>';
    systemModulesList.forEach(m => {
      const mId = m.module_id || m.id;
      const mName = m.module_name || m.name || 'Unassigned Module';
      optionsHtml += `<option value="${mId}">${mName}</option>`;
    });
    filterSelect.innerHTML = optionsHtml;
    filterSelect.value = curVal;
  }

  if (modalSelect) {
    const curVal = modalSelect.value;
    let optionsHtml = '';
    systemModulesList.forEach(m => {
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
