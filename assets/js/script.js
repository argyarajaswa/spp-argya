// Fungsi untuk menampilkan konfirmasi sebelum menghapus
document.addEventListener('DOMContentLoaded', function() {
    // Konfirmasi hapus
    const deleteButtons = document.querySelectorAll('.btn-delete');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirm('Apakah Anda yakin ingin menghapus data ini?')) {
                e.preventDefault();
            }
        });
    });
    
    // Toggle password visibility
    const togglePassword = document.querySelectorAll('.toggle-password');
    togglePassword.forEach(toggle => {
        toggle.addEventListener('click', function() {
            const input = this.previousElementSibling;
            const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
            input.setAttribute('type', type);
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });
    });
    
    // Auto format nominal uang
    const nominalInputs = document.querySelectorAll('.nominal-input');
    nominalInputs.forEach(input => {
        input.addEventListener('keyup', function(e) {
            let value = this.value.replace(/[^0-9]/g, '');
            this.value = formatRupiah(value);
        });
    });
});

// Format angka ke Rupiah
function formatRupiah(angka) {
    if (!angka) return '';
    return 'Rp ' + angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

// Fungsi untuk menampilkan loading
function showLoading() {
    const loading = document.createElement('div');
    loading.id = 'loading-overlay';
    loading.style.position = 'fixed';
    loading.style.top = '0';
    loading.style.left = '0';
    loading.style.width = '100%';
    loading.style.height = '100%';
    loading.style.backgroundColor = 'rgba(0,0,0,0.5)';
    loading.style.display = 'flex';
    loading.style.justifyContent = 'center';
    loading.style.alignItems = 'center';
    loading.style.zIndex = '9999';
    
    const spinner = document.createElement('div');
    spinner.className = 'spinner-border text-light';
    spinner.style.width = '3rem';
    spinner.style.height = '3rem';
    spinner.setAttribute('role', 'status');
    
    const srOnly = document.createElement('span');
    srOnly.className = 'visually-hidden';
    srOnly.textContent = 'Loading...';
    
    spinner.appendChild(srOnly);
    loading.appendChild(spinner);
    document.body.appendChild(loading);
}

// Fungsi untuk menyembunyikan loading
function hideLoading() {
    const loading = document.getElementById('loading-overlay');
    if (loading) {
        loading.remove();
    }
}

// AJAX untuk form submit
document.addEventListener('DOMContentLoaded', function() {
    const ajaxForms = document.querySelectorAll('.ajax-form');
    ajaxForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            showLoading();
            
            const formData = new FormData(this);
            const action = this.getAttribute('action');
            const method = this.getAttribute('method') || 'POST';
            
            fetch(action, {
                method: method,
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                hideLoading();
                
                if (data.success) {
                    if (data.redirect) {
                        window.location.href = data.redirect;
                    } else {
                        alert(data.message);
                    }
                } else {
                    alert(data.message || 'Terjadi kesalahan');
                }
            })
            .catch(error => {
                hideLoading();
                console.error('Error:', error);
                alert('Terjadi kesalahan saat memproses data');
            });
        });
    });
});