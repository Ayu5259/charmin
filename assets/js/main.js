// 📦 کلاس مدیریت سبد خرید (localStorage برای مهمان‌ها)
class Cart {
    constructor() {
        this.items = [];
        this.loadFromStorage();
        this.updateCartDisplay();
    }

    loadFromStorage() {
        const saved = localStorage.getItem('charmin_cart');
        if (saved) {
            this.items = JSON.parse(saved);
        }
    }

    saveToStorage() {
        localStorage.setItem('charmin_cart', JSON.stringify(this.items));
    }

    addItem(productId, name, price, image, quantity = 1) {
        const existing = this.items.find(item => item.id === productId);
        if (existing) {
            existing.quantity += quantity;
        } else {
            this.items.push({ id: productId, name, price, image, quantity });
        }
        this.saveToStorage();
        this.updateCartDisplay();
        this.showNotification('✅ محصول به سبد اضافه شد', 'success');
    }

    removeItem(productId) {
        this.items = this.items.filter(item => item.id !== productId);
        this.saveToStorage();
        this.updateCartDisplay();
        this.showNotification('🗑️ محصول حذف شد', 'info');
    }

    updateQuantity(productId, quantity) {
        const item = this.items.find(i => i.id === productId);
        if (item) {
            if (quantity <= 0) this.removeItem(productId);
            else {
                item.quantity = quantity;
                this.saveToStorage();
                this.updateCartDisplay();
            }
        }
    }

    getTotalItems() {
        return this.items.reduce((sum, item) => sum + item.quantity, 0);
    }

    getTotalPrice() {
        return this.items.reduce((sum, item) => sum + item.quantity * item.price, 0);
    }

    clear() {
        this.items = [];
        this.saveToStorage();
        this.updateCartDisplay();
    }

    updateCartDisplay() {
        const badge = document.querySelector('.cart-badge');
        if (!badge) return;

        // فقط اگر کاربر مهمان است badge را از localStorage بروز کن
        if (!document.body.classList.contains('logged-in')) {
            const count = this.getTotalItems();
            badge.textContent = count;
            badge.style.display = count > 0 ? 'inline-block' : 'none';
        }
    }

    showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `alert alert-${type} position-fixed`;
        notification.style.cssText = `
            top: 20px;
            right: 20px;
            z-index: 9999;
            min-width: 300px;
            animation: slideIn 0.3s ease;
        `;
        notification.innerHTML = `
            <strong>${message}</strong>
            <button type="button" class="btn-close float-start" onclick="this.parentElement.remove()"></button>
        `;
        document.body.appendChild(notification);
        setTimeout(() => notification.remove(), 3000);
    }
}

// 🧠 ابزارهای کمکی
function validateForm(formId) {
    const form = document.getElementById(formId);
    const inputs = form.querySelectorAll('input[required]');
    let isValid = true;
    inputs.forEach(input => {
        if (!input.value.trim()) {
            input.classList.add('is-invalid');
            isValid = false;
        } else input.classList.remove('is-invalid');
    });
    return isValid;
}

function checkPasswordStrength(password) {
    let strength = 0;
    if (password.length >= 8) strength++;
    if (/[A-Z]/.test(password)) strength++;
    if (/[a-z]/.test(password)) strength++;
    if (/\d/.test(password)) strength++;
    if (/[!@#$%^&*(),.?":{}|<>]/.test(password)) strength++;
    return strength;
}

function previewImage(input, previewId) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            document.getElementById(previewId).src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function formatCurrency(amount) {
    return new Intl.NumberFormat('fa-IR', {
        style: 'currency',
        currency: 'IRR',
        minimumFractionDigits: 0
    }).format(amount);
}

function scrollToTop() {
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function searchProducts(query) {
    const term = query.toLowerCase();
    document.querySelectorAll('.product-card').forEach(card => {
        const title = card.querySelector('.product-title').textContent.toLowerCase();
        const desc = card.querySelector('.product-description').textContent.toLowerCase();
        card.style.display = (title.includes(term) || desc.includes(term)) ? 'block' : 'none';
    });
}

// 🚀 اجرای کد پس از لود کامل DOM
document.addEventListener('DOMContentLoaded', () => {
    const cart = new Cart();

    // دکمه‌های افزودن به سبد برای مهمان‌ها
    document.querySelectorAll('.add-to-cart').forEach(btn => {
        btn.addEventListener('click', () => {
            const id = btn.dataset.productId;
            const name = btn.dataset.productName;
            const price = parseFloat(btn.dataset.productPrice);
            const image = btn.dataset.productImage;
            cart.addItem(id, name, price, image);
        });
    });

    // جستجو در محصولات
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', () => {
            searchProducts(searchInput.value);
        });
    }

    // فرم‌ها: بررسی فیلدها
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', e => {
            if (!validateForm(form.id)) {
                e.preventDefault();
                cart.showNotification('❗ لطفاً تمام فیلدهای الزامی را پر کنید', 'danger');
            }
        });
    });

    // بررسی رمز عبور
    const passwordInput = document.getElementById('password');
    const indicator = document.getElementById('passwordStrength');
    if (passwordInput && indicator) {
        passwordInput.addEventListener('input', () => {
            const strength = checkPasswordStrength(passwordInput.value);
            const levels = ['ضعیف', 'متوسط', 'خوب', 'عالی'];
            const colors = ['red', 'orange', 'gold', 'green'];
            indicator.textContent = levels[strength - 1] || '';
            indicator.style.color = colors[strength - 1] || '';
        });
    }

    // کنترل کم/زیاد کردن تعداد
    document.querySelectorAll('.quantity-decrease').forEach(btn => {
        btn.addEventListener('click', () => {
            const input = btn.nextElementSibling;
            const value = parseInt(input.value);
            if (value > 1) {
                input.value = value - 1;
                input.dispatchEvent(new Event('change'));
            }
        });
    });

    document.querySelectorAll('.quantity-increase').forEach(btn => {
        btn.addEventListener('click', () => {
            const input = btn.previousElementSibling;
            const value = parseInt(input.value);
            input.value = value + 1;
            input.dispatchEvent(new Event('change'));
        });
    });
});

// 📜 انیمیشن نوتیفیکیشن
const style = document.createElement('style');
style.textContent = `
@keyframes slideIn {
    from { transform: translateX(100%); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}`;
document.head.appendChild(style);
