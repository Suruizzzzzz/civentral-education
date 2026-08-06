// EVENTS & FORM SUBMISSION

async function handleSaveResource(event) {
  event.preventDefault();

  const formEl = document.getElementById('resourceForm');
  const submitBtn = formEl ? formEl.querySelector('button[type="submit"]') : null;
  if (submitBtn) {
    if (submitBtn.disabled || submitBtn.getAttribute('data-submitting') === 'true') return;
    submitBtn.setAttribute('data-submitting', 'true');
    submitBtn.disabled = true;
    submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
  }

  const idVal = document.getElementById('formResourceId').value;
  const moduleId = document.getElementById('resourceParentModule').value;
  const name = document.getElementById('resourceName').value.trim();
  const status = document.getElementById('resourceStatus').value;
  const route = document.getElementById('resourceRoute').value.trim();
  const desc = document.getElementById('resourceDesc').value.trim();

  if (!name || !moduleId) {
    if (typeof showToast === 'function') showToast('Please select a Parent Module and enter Resource Name.');
    if (submitBtn) {
      submitBtn.removeAttribute('data-submitting');
      submitBtn.disabled = false;
      submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
    }
    return;
  }

  const allMods = (typeof getCombinedSystemModules === 'function') 
    ? getCombinedSystemModules() 
    : ((typeof systemModulesList !== 'undefined' && Array.isArray(systemModulesList)) ? systemModulesList : []);
  const parentMod = allMods.find(m => String(m.module_id || m.id) === String(moduleId) || (m.module_name || m.name || '').trim().toLowerCase() === String(moduleId).trim().toLowerCase());
  const parentModuleName = parentMod ? (parentMod.module_name || parentMod.name) : (isNaN(moduleId) ? moduleId : 'Unassigned');

  const selectedActionIds = Array.from(document.querySelectorAll('.resource-action-checkbox:checked'))
    .map(cb => parseInt(cb.value))
    .filter(val => !isNaN(val));

  const payload = {
    module_id: parseInt(moduleId) || moduleId,
    resource_name: name,
    resource_route: route,
    description: desc,
    status: status,
    actions: selectedActionIds
  };

  if (idVal !== '') {
    payload.resource_id = parseInt(idVal);
  }

  const method = idVal === '' ? 'POST' : 'PUT';

  try {
    const response = await fetch('../../api/employee/resources.php', {
      method: method,
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
    const result = await response.json();

    const nowFormatted = new Date().toISOString().replace('T', ' ').substring(0, 19);
    const createdObj = result.data || result.resource || null;
    const resId = createdObj ? (createdObj.resource_id || createdObj.id || payload.resource_id) : (payload.resource_id || Date.now());

    const resObj = {
      id: resId,
      module_id: payload.module_id,
      module: parentModuleName,
      name: name,
      route: route,
      desc: desc,
      status: status,
      action_ids: selectedActionIds,
      created_at: nowFormatted,
      updated_at: nowFormatted
    };

    const cleanName = name.toLowerCase();
    const existingIndex = systemResources.findIndex(r => String(r.id) === String(resId) || (r.name || '').trim().toLowerCase() === cleanName);
    if (existingIndex >= 0) {
      systemResources[existingIndex] = { ...systemResources[existingIndex], ...resObj };
    } else {
      systemResources.unshift(resObj);
    }

    if (typeof showToast === 'function') showToast(result.message || 'Resource saved successfully.');
    if (typeof closeResourceModal === 'function') closeResourceModal();

    // Reset filters and search inputs to default so newly created resource shows on page 1
    const searchInput = document.getElementById('resourceSearchInput');
    if (searchInput) searchInput.value = '';

    const parentFilter = document.getElementById('parentModuleFilter');
    if (parentFilter) parentFilter.value = 'ALL';

    if (typeof currentPage !== 'undefined') currentPage = 1;

    if (typeof switchStatusTab === 'function') {
      switchStatusTab(status);
    } else if (typeof filterResources === 'function') {
      filterResources();
    }

    if (typeof fetchResources === 'function') {
      await fetchResources();
      if (typeof switchStatusTab === 'function') {
        switchStatusTab(status);
      }
    }
  } catch (err) {
    console.error('Error saving resource:', err);
    if (typeof showToast === 'function') showToast('Failed to save resource TO DATABASE.');
  } finally {
    if (submitBtn) {
      submitBtn.removeAttribute('data-submitting');
      submitBtn.disabled = false;
      submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
    }
  }
}

window.handleSaveResource = handleSaveResource;

// DISMISS MODALS ON BACKDROP CLICK & ESCAPE KEY
document.addEventListener('click', (e) => {
  if (e.target.id === 'resourceModal' && typeof closeResourceModal === 'function') closeResourceModal();
  if (e.target.id === 'archiveModal' && typeof closeArchiveModal === 'function') closeArchiveModal();
});

document.addEventListener('keydown', (e) => {
  if (e.key === 'Escape') {
    if (typeof closeResourceModal === 'function') closeResourceModal();
    if (typeof closeArchiveModal === 'function') closeArchiveModal();
  }
});
