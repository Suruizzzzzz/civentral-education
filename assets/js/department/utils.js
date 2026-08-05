// DOM Elements
const deptsTbody = document.getElementById('deptsTableBody');
const searchInput = document.getElementById('deptSearchInput');

// Toast Popup
let deptToastTimer = null;
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

  if (deptToastTimer) clearTimeout(deptToastTimer);
  deptToastTimer = setTimeout(() => {
    toast.classList.remove('opacity-100', 'translate-y-0');
    toast.classList.add('opacity-0', 'pointer-events-none', 'translate-y-4');
  }, 3200);
}

// Export CSV
function exportDeptsCsv() {
  const query = searchInput.value.toLowerCase().trim();

  let csvContent = "data:text/csv;charset=utf-8,";
  csvContent += "Dept Code,Department Name,Department Administrator,Status,Created At\r\n";

  const filtered = departmentsData.filter(dept => {
    const adminObj = dept.users || null;
    const adminName = adminObj ? `${adminObj.first_name} ${adminObj.last_name}`.toLowerCase() : '';
    return dept.department_name.toLowerCase().includes(query) ||
           dept.department_code.toLowerCase().includes(query) ||
           adminName.includes(query);
  });

  filtered.forEach(dept => {
    const adminObj = dept.users || null;
    const adminName = adminObj ? `${adminObj.first_name} ${adminObj.last_name}` : 'Unassigned';
    const createdAt = dept.created_at ? dept.created_at.replace('T', ' ').substring(0, 19) : '';
    csvContent += `"${dept.department_code}","${dept.department_name}","${adminName}","${dept.status}","${createdAt}"\r\n`;
  });

  const encodedUri = encodeURI(csvContent);
  const link = document.createElement("a");
  link.setAttribute("href", encodedUri);
  link.setAttribute("download", `CIVENTRAL_Department_Directory_${new Date().toISOString().split('T')[0]}.csv`);
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);

  showToast(`Successfully exported ${filtered.length} department records.`);
}
