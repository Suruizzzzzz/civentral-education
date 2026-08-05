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
    name: name,
    description: desc,
    desc: desc,
    status: status
  };

  if (idVal !== '') {
    payload.module_id = parseInt(idVal) || idVal;
    payload.id = payload.module_id;
  }

  const method = idVal === '' ? 'POST' : 'PUT';

  const normStatus = typeof normalizeStatus === 'function' ? normalizeStatus(status) : status;
  const modId = idVal !== '' ? (parseInt(idVal) || idVal) : Date.now();
  const nowFormatted = new Date().toISOString().replace('T', ' ').substring(0, 19);

  const modObj = {
    id: modId,
    name: name,
    desc: desc,
    status: normStatus,
    created_at: nowFormatted,
    updated_at: nowFormatted
  };

  // 1. Immediately display and persist created/edited module in UI
  if (typeof saveLocalCustomModule === 'function') {
    saveLocalCustomModule(modObj);
  }

  const cleanName = name.toLowerCase();
  const existingIndex = systemModules.findIndex(m => String(m.id) === String(modId) || (m.name || '').trim().toLowerCase() === cleanName);
  if (existingIndex >= 0) {
    systemModules[existingIndex] = { ...systemModules[existingIndex], ...modObj };
  } else {
    systemModules.unshift(modObj);
  }

  // Clear search filter so created module is visible
  const searchInput = document.getElementById('moduleSearchInput');
  if (searchInput) searchInput.value = '';

  if (typeof closeModuleModal === 'function') closeModuleModal();

  if (typeof switchStatusTab === 'function') {
    switchStatusTab(normStatus);
  } else if (typeof filterModules === 'function') {
    filterModules();
  }

  if (typeof showToast === 'function') showToast(`Module "${name}" saved successfully.`);

  // 2. Fire API request in background if supported
  try {
    const response = await fetch('../../api/employee/modules.php', {
      method: method,
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
    const result = await response.json();
    if (result && (result.module_id || result.id || (result.data && (result.data.module_id || result.data.id)))) {
      const serverId = (result.data ? (result.data.module_id || result.data.id) : (result.module_id || result.id));
      if (serverId && serverId !== modId) {
        modObj.id = serverId;
        saveLocalCustomModule(modObj);
        const idx = systemModules.findIndex(m => String(m.id) === String(modId));
        if (idx >= 0) systemModules[idx].id = serverId;
        if (typeof filterModules === 'function') filterModules();
      }
    }
  } catch (err) {
    console.warn('API sync notice (running in API display mode):', err);
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
