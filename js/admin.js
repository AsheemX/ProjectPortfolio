// js/admin.js
class ProjectManager {
    constructor() {
        this.projects = [];
        this.currentUser = null;
        this.init();
    }

    init() {
        this.loadProjects();
        this.setupEventListeners();
        this.checkLogin();
    }

    // نظام الدخول
    checkLogin() {
        const savedUser = localStorage.getItem('adminUser');
        if (savedUser) {
            this.currentUser = JSON.parse(savedUser);
            this.showAdminPanel();
        }
    }

    login(username, password) {
        // في الواقع، يجب التحقق من خادم آمن
        if (username === 'admin' && password === '123456') {
            this.currentUser = { username, loginTime: new Date() };
            localStorage.setItem('adminUser', JSON.stringify(this.currentUser));
            this.showAdminPanel();
            return true;
        }
        return false;
    }

    logout() {
        this.currentUser = null;
        localStorage.removeItem('adminUser');
        this.showLoginScreen();
    }

    showLoginScreen() {
        document.getElementById('loginScreen').style.display = 'block';
        document.getElementById('adminPanel').style.display = 'none';
    }

    showAdminPanel() {
        document.getElementById('loginScreen').style.display = 'none';
        document.getElementById('adminPanel').style.display = 'block';
        this.showTab('projects');
    }

    // إدارة التبويبات
    showTab(tabName) {
        // إخفاء جميع التبويبات
        document.querySelectorAll('.tab-content').forEach(tab => {
            tab.style.display = 'none';
        });

        // إزالة النشط من جميع الأزرار
        document.querySelectorAll('.nav-link').forEach(link => {
            link.classList.remove('active');
        });

        // إظهار التبويب المطلوب
        document.getElementById(tabName + 'Tab').style.display = 'block';
        
        // تفعيل الزر النشط
        document.querySelector(`[data-tab="${tabName}"]`).classList.add('active');

        // تحديث العنوان
        const titles = {
            'projects': 'إدارة المشاريع',
            'add-project': 'إضافة مشروع جديد',
            'settings': 'الإعدادات'
        };
        document.getElementById('pageTitle').textContent = titles[tabName];

        // إذا كان تبويب المشاريع، قم بتحديث القائمة
        if (tabName === 'projects') {
            this.displayProjects();
        }
    }

    // إدارة المشاريع
    loadProjects() {
        try {
            const saved = localStorage.getItem('projects');
            this.projects = saved ? JSON.parse(saved) : this.getSampleProjects();
        } catch (error) {
            console.error('Error loading projects:', error);
            this.projects = this.getSampleProjects();
        }
    }

    saveProjects() {
        localStorage.setItem('projects', JSON.stringify(this.projects));
        this.updatePortfolioPage();
    }

    getSampleProjects() {
        return [
            {
                id: 1,
                title: "عزل خزانات مياه",
                category: "insulation",
                image: "https://images.unsplash.com/photo-1581094794329-cdce34d41d33?ixlib=rb-4.0.3&auto=format&fit=crop&w=300&q=80",
                description: "عزل كامل لخزانات المياه في مجمع سكني باستخدام أحدث مواد العزل المائي",
                location: "الرياض",
                duration: "10 أيام"
            }
        ];
    }

    addProject(projectData) {
        const newProject = {
            id: Date.now(),
            title: projectData.title,
            category: projectData.category,
            image: projectData.image,
            description: projectData.description,
            location: projectData.location,
            duration: projectData.duration,
            date: new Date().toLocaleDateString('ar-EG')
        };

        this.projects.unshift(newProject);
        this.saveProjects();
        this.showTab('projects');
        this.resetForm();
    }

    deleteProject(projectId) {
        if (confirm('هل أنت متأكد من حذف هذا المشروع؟')) {
            this.projects = this.projects.filter(project => project.id !== projectId);
            this.saveProjects();
            this.displayProjects();
        }
    }

    displayProjects(filterCategory = 'all') {
        const container = document.getElementById('projectsList');
        let filteredProjects = this.projects;

        if (filterCategory !== 'all') {
            filteredProjects = this.projects.filter(project => project.category === filterCategory);
        }

        if (filteredProjects.length === 0) {
            container.innerHTML = `
                <div class="col-12 text-center py-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">لا توجد مشاريع</h5>
                    <p class="text-muted">قم بإضافة مشروع جديد لبدء العرض</p>
                </div>
            `;
            return;
        }

        container.innerHTML = filteredProjects.map(project => `
            <div class="col-lg-4 col-md-6">
                <div class="card project-card">
                    <img src="${project.image}" class="card-img-top project-image" alt="${project.title}">
                    <div class="card-body">
                        <h6 class="card-title">${project.title}</h6>
                        <p class="card-text small text-muted">${project.description.substring(0, 80)}...</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="badge bg-primary">${this.getCategoryName(project.category)}</span>
                            <small class="text-muted">${project.date}</small>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button class="btn btn-sm btn-outline-danger" onclick="projectManager.deleteProject(${project.id})">
                            <i class="fas fa-trash"></i> حذف
                        </button>
                    </div>
                </div>
            </div>
        `).join('');
    }

    getCategoryName(category) {
        const categories = {
            'insulation': 'العوازل',
            'decoration': 'الديكور',
            'furniture': 'النجارة والأثاث',
            'marble': 'الرخام'
        };
        return categories[category] || category;
    }

    // معالجة الصور
    handleImageUpload(file) {
        return new Promise((resolve, reject) => {
            if (!file.type.match('image.*')) {
                reject('الملف يجب أن يكون صورة');
                return;
            }

            const reader = new FileReader();
            reader.onload = (e) => {
                // في الواقع، هنا يجب رفع الصورة للخادم
                // لكن حالياً سنستخدم Data URL للتخزين المحلي
                resolve(e.target.result);
            };
            reader.onerror = () => reject('خطأ في قراءة الملف');
            reader.readAsDataURL(file);
        });
    }

    // تحديث صفحة portfolio تلقائياً
    updatePortfolioPage() {
        // هذا الكود سيعمل عندما تكون صفحة portfolio مفتوحة
        if (window.updatePortfolioProjects) {
            window.updatePortfolioProjects(this.projects);
        }
    }

    resetForm() {
        document.getElementById('addProjectForm').reset();
        document.getElementById('imagePreview').style.display = 'none';
        document.getElementById('uploadArea').style.display = 'block';
    }

    setupEventListeners() {
        // نظام الدخول
        document.getElementById('loginForm').addEventListener('submit', (e) => {
            e.preventDefault();
            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;
            
            if (this.login(username, password)) {
                alert('تم الدخول بنجاح!');
            } else {
                alert('اسم المستخدم أو كلمة المرور غير صحيحة!');
            }
        });

        // تسجيل الخروج
        document.getElementById('logoutBtn').addEventListener('click', (e) => {
            e.preventDefault();
            this.logout();
        });

        // التنقل بين التبويبات
        document.querySelectorAll('.nav-link[data-tab]').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const tabName = link.getAttribute('data-tab');
                this.showTab(tabName);
            });
        });

        // تصفية المشاريع
        document.getElementById('categoryFilter').addEventListener('change', (e) => {
            this.displayProjects(e.target.value);
        });

        // رفع الصور
        const uploadArea = document.getElementById('uploadArea');
        const fileInput = document.getElementById('projectImage');

        uploadArea.addEventListener('click', () => fileInput.click());

        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.classList.add('dragover');
        });

        uploadArea.addEventListener('dragleave', () => {
            uploadArea.classList.remove('dragover');
        });

        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.classList.remove('dragover');
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                this.previewImage(files[0]);
            }
        });

        fileInput.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                this.previewImage(e.target.files[0]);
            }
        });

        // إضافة مشروع جديد
        document.getElementById('addProjectForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const formData = {
                title: document.getElementById('projectTitle').value,
                category: document.getElementById('projectCategory').value,
                description: document.getElementById('projectDescription').value,
                location: document.getElementById('projectLocation').value,
                duration: document.getElementById('projectDuration').value,
                image: document.getElementById('previewImg')?.src || ''
            };

            if (!formData.image) {
                alert('يرجى اختيار صورة للمشروع');
                return;
            }

            try {
                this.addProject(formData);
                alert('تم إضافة المشروع بنجاح!');
            } catch (error) {
                alert('حدث خطأ أثناء إضافة المشروع: ' + error.message);
            }
        });
    }

    async previewImage(file) {
        try {
            const imageUrl = await this.handleImageUpload(file);
            document.getElementById('previewImg').src = imageUrl;
            document.getElementById('imagePreview').style.display = 'block';
            document.getElementById('uploadArea').style.display = 'none';
        } catch (error) {
            alert(error);
        }
    }
}

// تهيئة النظام
const projectManager = new ProjectManager();