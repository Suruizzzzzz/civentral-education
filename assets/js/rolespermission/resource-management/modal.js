// MODAL CONTROLS - CREATE / EDIT / ARCHIVE
function showModalOverlay(modalId, cardId) {
  const modal = document.getElementById(modalId);
  const card = document.getElementById(cardId);
  if (!modal) return;

  modal.classList.remove('opacity-0', 'pointer-events-none');
  modal.classList.add('opacity-100', 'pointer-events-auto');

  if (card) {
    card.classList.remove('scale-95', 'opacity-0');
    card.classList.add('scale-100', 'opacity-100');
  }
}

function hideModalOverlay(modalId, cardId) {
  const modal = document.getElementById(modalId);
  const card = document.getElementById(cardId);
  if (!modal) return;

  modal.classList.remove('opacity-100', 'pointer-events-auto');
  modal.classList.add('opacity-0', 'pointer-events-none');

  if (card) {
    card.classList.remove('scale-100', 'opacity-100');
    card.classList.add('scale-95', 'opacity-0');
  }
}

function openCreateResourceModal() {
  const isSuperAdmin = currentUserScope ? !!currentUserScope.is_superadmin : false;
  const grantedActions = currentUserScope ? (currentUserScope.granted_actions || []) : [];
  const canCreate = isSuperAdmin || grantedActions.includes('CREATE');

  if (!canCreate) {
    if (typeof showToast === 'function') showToast('Forbidden. View-only access level cannot create system resources.');
    return;
  }

  const formResourceId = document.getElementById('formResourceId');
  const resourceForm = document.getElementById('resourceForm');
  const modalHeaderTitle = document.getElementById('modalHeaderTitle');
  const resourceStatus = document.getElementById('resourceStatus');
  const resourceCreatedAt = document.getElementById('resourceCreatedAt');
  const resourceName = document.getElementById('resourceName');
  const resourceRoute = document.getElementById('resourceRoute');
  const resourceDesc = document.getElementById('resourceDesc');

  if (formResourceId) formResourceId.value = '';
  if (resourceForm) resourceForm.reset();
  if (resourceName) resourceName.value = '';
  if (resourceRoute) resourceRoute.value = '';
  if (resourceDesc) resourceDesc.value = '';
  if (modalHeaderTitle) modalHeaderTitle.textContent = 'Add New System Resource';
  if (resourceStatus) resourceStatus.value = 'Active';
  if (resourceCreatedAt) resourceCreatedAt.value = 'Auto-generated on save';
  
  if (typeof populateModuleSelects === 'function') populateModuleSelects();

  showModalOverlay('resourceModal', 'resourceModalCard');
}

function openEditResourceModal(id) {
  const isSuperAdmin = currentUserScope ? !!currentUserScope.is_superadmin : false;
  const grantedActions = currentUserScope ? (currentUserScope.granted_actions || []) : [];
  const canEdit = isSuperAdmin || grantedActions.includes('EDIT');

  if (!canEdit) {
    if (typeof showToast === 'function') showToast('Forbidden. View-only access level cannot modify system resources.');
    return;
  }

  const res = systemResources.find(r => r.id === id);
  if (!res) return;

  const formResourceId = document.getElementById('formResourceId');
  const resourceParentModule = document.getElementById('resourceParentModule');
  const resourceName = document.getElementById('resourceName');
  const resourceStatus = document.getElementById('resourceStatus');
  const resourceCreatedAt = document.getElementById('resourceCreatedAt');
  const resourceRoute = document.getElementById('resourceRoute');
  const resourceDesc = document.getElementById('resourceDesc');
  const modalHeaderTitle = document.getElementById('modalHeaderTitle');

  if (typeof populateModuleSelects === 'function') populateModuleSelects();

  if (formResourceId) formResourceId.value = res.id;
  if (resourceParentModule) resourceParentModule.value = res.module_id || '';
  if (resourceName) resourceName.value = res.name || '';
  if (resourceStatus) resourceStatus.value = res.status || 'Active';
  if (resourceCreatedAt) resourceCreatedAt.value = res.created_at || '';
  if (resourceRoute) resourceRoute.value = res.route || '';
  if (resourceDesc) resourceDesc.value = res.desc || '';

  if (modalHeaderTitle) modalHeaderTitle.textContent = `Edit Resource: ${res.name}`;

  showModalOverlay('resourceModal', 'resourceModalCard');
}

function closeResourceModal() {
  hideModalOverlay('resourceModal', 'resourceModalCard');
}

function openArchiveResourceModal(id) {
  const res = systemResources.find(r => r.id === id);
  if (!res) return;

  archiveTargetResourceId = id;
  const targetNameEl = document.getElementById('archiveTargetName');
  if (targetNameEl) targetNameEl.textContent = `Resource: ${res.name}`;

  showModalOverlay('archiveModal', 'archiveModalCard');
}

function closeArchiveModal() {
  archiveTargetResourceId = null;
  hideModalOverlay('archiveModal', 'archiveModalCard');
}

async function confirmArchiveResource() {
  if (!archiveTargetResourceId) return;

  const targetId = archiveTargetResourceId;
  closeArchiveModal();
  if (typeof updateResourceStatusInDb === 'function') await updateResourceStatusInDb(targetId, 'Archived');
}

async function toggleResourceStatus(id) {
  const res = systemResources.find(r => r.id === id);
  if (!res) return;

  const nextStatus = res.status === 'Active' ? 'Inactive' : 'Active';
  if (typeof updateResourceStatusInDb === 'function') await updateResourceStatusInDb(id, nextStatus);
}

window.openCreateResourceModal = openCreateResourceModal;
window.openEditResourceModal = openEditResourceModal;
window.closeResourceModal = closeResourceModal;
window.openArchiveResourceModal = openArchiveResourceModal;
window.closeArchiveModal = closeArchiveModal;
window.confirmArchiveResource = confirmArchiveResource;
window.toggleResourceStatus = toggleResourceStatus;
