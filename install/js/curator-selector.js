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
        BX.ajax.runComponentAction('bitrix:intranet.user.selector.new', 'load', {
            mode: 'class',
            data: {
                search: query
            }
        }).then(function(response) {
            if (response.status === 'success') {
                this.showDropdown(response.data.users || []);
            }
        }.bind(this));
    }

    showDropdown(users) {
        this.dropdown.innerHTML = '';
        this.dropdown.style.display = 'block';

        if (!users || users.length === 0) {
            this.dropdown.innerHTML = '<div class="curator-no-results">Пользователи не найдены</div>';
            return;
        }

        users.forEach(user => {
            if (this.selectedCurators.some(c => c.id == user.id)) {
                return;
            }

            const item = document.createElement('div');
            item.className = 'curator-dropdown-item';
            item.innerHTML = `
                <span class="curator-name">${user.name} ${user.lastName}</span>
                <span class="curator-email">${user.email || ''}</span>
            `;

            item.addEventListener('click', () => {
                this.selectCurator({
                    id: user.id,
                    name: user.name + ' ' + user.lastName,
                    email: user.email
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

        BX.ajax.runAction('custom.curator:curator.add', {
            data: {
                taskId: this.taskId,
                userId: userId
            }
        });
    }

    removeCuratorFromServer(userId) {
        if (!this.taskId) return;

        BX.ajax.runAction('custom.curator:curator.remove', {
            data: {
                taskId: this.taskId,
                userId: userId
            }
        });
    }

    loadCurators() {
        if (!this.taskId) return;

        BX.ajax.runAction('custom.curator:curator.list', {
            data: {
                taskId: this.taskId
            }
        }).then(function(response) {
            if (response.status === 'success' && response.data) {
                const userIds = response.data.map(c => c.USER_ID);
                this.fetchUserDetails(userIds);
            }
        }.bind(this));
    }

    fetchUserDetails(userIds) {
        // Загрузка данных пользователей
        userIds.forEach(userId => {
            this.selectedCurators.push({
                id: userId,
                name: 'User ' + userId,
                email: ''
            });
        });

        this.renderCurators();
    }
}

window.CuratorSelector = CuratorSelector;