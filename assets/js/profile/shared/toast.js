// Shared Toast
// Responsible for displaying toast popup notifications across the profile module

let sharedToastTimer = null;

function showToast(message, type = 'success', subtitle = '') {
  let toastId, msgId, subId, iconId;
  
  if (document.getElementById('settingsToast')) {
    toastId = 'settingsToast'; msgId = 'settingsToastMsg'; subId = 'settingsToastSub'; iconId = 'settingsToastIcon';
  } else if (document.getElementById('pwToast')) {
    toastId = 'pwToast'; msgId = 'pwToastMsg'; subId = 'pwToastSub'; iconId = 'pwToastIcon';
  } else if (document.getElementById('toast')) {
    toastId = 'toast'; msgId = 'toastMsg'; subId = 'toastSub'; iconId = 'toastIcon';
  } else {
    return;
  }

  const toast = document.getElementById(toastId);
  const toastMsg = document.getElementById(msgId);
  const toastSub = document.getElementById(subId);
  const toastIcon = document.getElementById(iconId);

  if (!toast || !toastMsg) return;

  if (typeof type === 'boolean') {
    type = type ? 'error' : 'success';
  }

  const msgLower = (message || '').toString().toLowerCase();
  if (type !== 'error' && (
    msgLower.includes('error') || 
    msgLower.includes('failed') || 
    msgLower.includes('invalid') || 
    msgLower.includes('network') || 
    msgLower.includes('require') || 
    msgLower.includes('forbidden') || 
    msgLower.includes('denied')
  )) {
    type = 'error';
  }

  const configs = {
    success: { iconClass: 'fa-check', iconBg: 'bg-emerald-500' },
    error:   { iconClass: 'fa-xmark', iconBg: 'bg-rose-500' },
    info:    { iconClass: 'fa-circle-info', iconBg: 'bg-blue-500' }
  };

  const cfg = configs[type] || configs.success;
  
  if (toastIcon) {
    toastIcon.className = 'h-6 w-6 rounded-full ' + cfg.iconBg + ' flex items-center justify-center text-white text-[11px] shrink-0';
    toastIcon.innerHTML = '<i class="fa-solid ' + cfg.iconClass + '"></i>';
  }

  toastMsg.textContent = message;
  if (toastSub) toastSub.textContent = subtitle;

  toast.classList.remove('translate-y-4', 'opacity-0', 'pointer-events-none');
  toast.classList.add('translate-y-0', 'opacity-100');

  sharedToastTimer = setTimeout(function() {
    toast.classList.remove('translate-y-0', 'opacity-100');
    toast.classList.add('translate-y-4', 'opacity-0', 'pointer-events-none');
  }, 3500);
}
