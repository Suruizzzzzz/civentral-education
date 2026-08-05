// Shared Toast Notification for Roles & Permissions

let rolesToastTimer = null;

function showToast(message, isError = false) {
  const toast = document.getElementById('toast');
  const toastMsg = document.getElementById('toastMsg');
  let iconBox = document.getElementById('toastIconBox') || (toast ? toast.querySelector('div') : null);
  let iconSymbol = document.getElementById('toastIconSymbol') || (iconBox ? iconBox.querySelector('i') : null);
  
  if (!toast || !toastMsg) return;

  const msgLower = (message || '').toString().toLowerCase();
  if (!isError && (
    msgLower.includes('error') || 
    msgLower.includes('failed') || 
    msgLower.includes('invalid') || 
    msgLower.includes('network') || 
    msgLower.includes('require') || 
    msgLower.includes('forbidden') || 
    msgLower.includes('denied') || 
    msgLower.includes('cannot') ||
    msgLower.includes('unable') ||
    msgLower.includes('please')
  )) {
    isError = true;
  }

  toastMsg.innerText = message;
  
  if (iconBox) {
    if (isError) {
      iconBox.className = "h-5 w-5 rounded-full bg-rose-500 flex items-center justify-center text-white text-[10px] shrink-0";
    } else {
      iconBox.className = "h-5 w-5 rounded-full bg-emerald-500 flex items-center justify-center text-white text-[10px] shrink-0";
    }
  }

  if (iconSymbol) {
    if (isError) {
      iconSymbol.className = "fa-solid fa-xmark";
    } else {
      iconSymbol.className = "fa-solid fa-check";
    }
  }

  toast.classList.remove('opacity-0', 'pointer-events-none', 'translate-y-4');
  toast.classList.add('opacity-100', 'translate-y-0');

  if (rolesToastTimer) clearTimeout(rolesToastTimer);
  
  rolesToastTimer = setTimeout(() => {
    toast.classList.remove('opacity-100', 'translate-y-0');
    toast.classList.add('opacity-0', 'pointer-events-none', 'translate-y-4');
  }, 3200);
}
