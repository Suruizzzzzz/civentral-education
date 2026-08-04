// EVENTS & FORM SUBMISSION

async function handleSaveModule(event) {
  event.preventDefault();

  const submitBtn = event.target ? event.target.querySelector('button[type="submit"]') : null;
  if (submitBtn) {
    if (submitBtn.disabled) return;
    submitBtn.disabled = true;
    submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
  }

  const idVal = document.getElementById('formModuleId').value;
  const name = document.getElementById('moduleName').value.trim();
  const status = document.getElementById('moduleStatus').value;
  const desc = document.getElementById('moduleDesc').value.trim();

  if (!name) {
    if (typeof showToast === 'function') showToast('Module name is required.');
    if (submitBtn) {
      submitBtn.disabled = false;
      submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
    }
    return;
  }

  const payload = {
    module_name: name,
    description: desc,
    status: status
  };

  if (idVal !== '') {
    payload.module_id = parseInt(idVal);
  }

  const method = idVal === '' ? 'POST' : 'PUT';

  try {
    const response = await fetch('../../api/employee/modules.php', {
      method: method,
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
    const result = await response.json();

    const isSuccess = response.ok && (
      result.status === 'success' || 
      result.success === true || 
      result.code === 200 || 
      result.code === 201 ||
      result.module_id || 
      result.id ||
      (result.data && (result.data.module_id || result.data.id))
    );

    if (isSuccess) {
      if (typeof showToast === 'function') showToast(result.message || 'Module saved successfully.');
      if (typeof closeModuleModal === 'function') closeModuleModal();

      // Ensure newly created or updated module appears immediately in local state
      const createdObj = result.data || result.module || null;
      const modId = createdObj ? (createdObj.module_id || createdObj.id || payload.module_id) : (payload.module_id || Date.now());
      const nowFormatted = new Date().toISOString().replace('T', ' ').substring(0, 19);

      const modObj = {
        id: modId,
        name: name,
        desc: desc,
        status: status,
        created_at: nowFormatted,
        updated_at: nowFormatted
      };

      const cleanName = name.toLowerCase();
      const existingIndex = systemModules.findIndex(m => String(m.id) === String(modId) || (m.name || '').trim().toLowerCase() === cleanName);
      if (existingIndex >= 0) {
        systemModules[existingIndex] = { ...systemModules[existingIndex], ...modObj };
      } else {
        systemModules.unshift(modObj);
      }

      // Clear search filter so newly created/edited module is not hidden by query
      const searchInput = document.getElementById('moduleSearchInput');
      if (searchInput) searchInput.value = '';

      // Switch to matching tab status so user immediately sees the module
      if (typeof switchStatusTab === 'function') {
        switchStatusTab(status);
      } else if (typeof filterModules === 'function') {
        filterModules();
      }

      // Fetch fresh data in background to sync database auto-generated IDs
      if (typeof fetchModules === 'function') {
        await fetchModules();
        if (typeof switchStatusTab === 'function') {
          switchStatusTab(status);
        }
      }
    } else {
      if (typeof showToast === 'function') showToast(result.message || 'Failed to save module.');
    }
  } catch (err) {
    console.error('Error saving module:', err);
    if (typeof showToast === 'function') showToast('Failed to save module TO DATABASE.');
  } finally {
    if (submitBtn) {
      submitBtn.disabled = false;
      submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
    }
  }
}

window.handleSaveModule = handleSaveModule;

// DISMISS MODALS ON BACKDROP CLICK & ESCAPE KEY
document.addEventListener('click', (e) => {
  if (e.target.id === 'moduleModal' && typeof closeModuleModal === 'function') closeModuleModal();
  if (e.target.id === 'archiveModal' && typeof closeArchiveModal === 'function') closeArchiveModal();
});

document.addEventListener('keydown', (e) => {
  if (e.key === 'Escape') {
    if (typeof closeModuleModal === 'function') closeModuleModal();
    if (typeof closeArchiveModal === 'function') closeArchiveModal();
  }
});
