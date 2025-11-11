class CuratorSelector {
    constructor(options) {
        this.options = options || {};
        this.el = document.getElementById(this.options.el);
        this.taskId = this.options.taskId;
        this.selectedCurators = [];
        this.userCache = {};

        this.init();
    }

    init() {
        if (!this.el) return;

        this.createUI();
        this.attachEvents();
        this.loadCurators();
    }

    createUI() {
        this.el.innerHTML = `
            <div class="curator-selector">
                <div class="curator-input-wrapper">
                    <input type="text" 
                           class="curator-search" 
                           placeholder="Начните вводить имя сотрудника...">
                    <div class="curator-dropdown" style="display:none;"></div>
                </div>
                <div class="curator-list"></div>
            </div>
        `;

        this.searchInput = this.el.querySelector('.curator-search');
        this.dropdown = this.el.querySelector('.curator-dropdown');
        this.curatorList = this.el.querySelector('.curator-list');
    }

    attachEvents() {
        this.searchInput.addEventListener('input', (e) => this.onSearch(e));
        this.searchInput.addEventListener('keydown', (e) => this.onKeyDown(e));
        document.addEventListener('click', (e) => this.onDocumentClick(e));
    }

    onSearch(e) {
        const query = e.target.value.trim();

        if (query.length < 1) {
            this.dropdown.style.display = 'none';
            return;
        }

        this.fetchUsers(query);
    }

    onKeyDown(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
        }
    }

    onDocumentClick(e) {
        if (!this.el.contains(e.target)) {
            this.dropdown.style.display = 'none';
        }
    }

    fetchUsers(query) {
        BX.rest.callMethod('user.get', {
            filter: {
                'SEARCH': query,
                'ACTIVE': 'Y'
            },
            select: ['ID', 'NAME', 'LAST_NAME', 'EMAIL', 'PERSONAL_PHOTO']
        }, (result) => {
            if (result.error()) {
                console.error('Error fetching users:', result.error());
                return;
            }

            this.showDropdown(result.data());
        });
    }

    showDropdown(users) {
        this.dropdown.innerHTML = '';
        this.dropdown.style.display = 'block';

        if (!users || users.length === 0) {
            this.dropdown.innerHTML = '<div class="curator-no-results">Пользователи не найдены</div>';
            return;
        }

        users.forEach(user => {
            if (this.selectedCurators.some(c => c.id == user.ID)) {
                return;
            }

            const item = document.createElement('div');
            item.className = 'curator-dropdown-item';
            item.innerHTML = `
                <span class="curator-name">${user.NAME} ${user.LAST_NAME}</span>
                <span class="curator-email">${user.EMAIL}</span>
            `;

            item.addEventListener('click', () => {
                this.selectCurator({
                    id: user.ID,
                    name: user.NAME + ' ' + user.LAST_NAME,
                    email: user.EMAIL
                });
            });

            this.dropdown.appendChild(item);
        });
    }

    selectCurator(user) {
        if (this.selectedCurators.some(c => c.id === user.id)) {
            return;
        }

        this.selectedCurators.push(user);
        this.renderCurators();
        this.searchInput.value = '';
        this.dropdown.style.display = 'none';

        if (this.options.onSelect) {
            this.options.onSelect(user);
        }

        if (this.taskId) {
            this.saveCurator(user.id);
        }
    }

    removeCurator(userId) {
        this.selectedCurators = this.selectedCurators.filter(c => c.id !== userId);
        this.renderCurators();

        if (this.taskId) {
            this.removeCuratorFromServer(userId);
        }
    }

    renderCurators() {
        this.curatorList.innerHTML = '';

        this.selectedCurators.forEach(curator => {
            const item = document.createElement('div');
            item.className = 'curator-tag';
            item.innerHTML = `
                <span>${curator.name}</span>
                <button type="button" class="curator-remove" data-user-id="${curator.id}">×</button>
            `;

            item.querySelector('.curator-remove').addEventListener('click', () => {
                this.removeCurator(curator.id);
            });

            this.curatorList.appendChild(item);
        });
    }

    saveCurator(userId) {
        if (!this.taskId) return;

        BX.rest.callMethod('curator.add', {
            taskId: this.taskId,
            userId: userId
        }, (result) => {
            if (result.error()) {
                console.error('Error saving curator:', result.error());
            }
        });
    }

    removeCuratorFromServer(userId) {
        if (!this.taskId) return;

        BX.rest.callMethod('curator.remove', {
            taskId: this.taskId,
            userId: userId
        }, (result) => {
            if (result.error()) {
                console.error('Error removing curator:', result.error());
            }
        });
    }

    loadCurators() {
        if (!this.taskId) return;

        BX.rest.callMethod('curator.list', {
            taskId: this.taskId
        }, (result) => {
            if (result.error()) {
                console.error('Error loading curators:', result.error());
                return;
            }

            const curators = result.data();
            if (curators && curators.length > 0) {
                const userIds = curators.map(c => c.USER_ID);
                this.fetchUserDetails(userIds);
            }
        });
    }

    fetchUserDetails(userIds) {
        BX.rest.callMethod('user.get', {
            filter: { 'ID': userIds }
        }, (result) => {
            if (result.error()) {
                console.error('Error fetching curator details:', result.error());
                return;
            }

            result.data().forEach(user => {
                this.selectedCurators.push({
                    id: user.ID,
                    name: user.NAME + ' ' + user.LAST_NAME,
                    email: user.EMAIL
                });
            });

            this.renderCurators();
        });
    }
}

window.CuratorSelector = CuratorSelector;