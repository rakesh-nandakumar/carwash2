/**
 * Searchable Dropdown Component
 * A simple, reusable searchable dropdown that works as a drop-in replacement for datalist
 * 
 * Usage:
 * <div class="searchable-dropdown" data-input-name="field_name" data-placeholder="Search...">
 *     <input type="hidden" name="field_name" value="">
 *     <input type="text" class="searchable-dropdown-input" placeholder="Search...">
 *     <div class="searchable-dropdown-options"></div>
 * </div>
 * 
 * Initialize with:
 * const dropdown = new SearchableDropdown(element, options);
 * 
 * Options:
 * - data: Array of objects with {id, label} or {value, text}
 * - onSelect: Callback function when an option is selected
 * - searchThreshold: Minimum characters to trigger search (default: 0)
 */

class SearchableDropdown {
    constructor(container, options = {}) {
        this.container = container;
        this.options = {
            data: options.data || [],
            onSelect: options.onSelect || null,
            searchThreshold: options.searchThreshold || 0,
            ...options
        };
        
        this.hiddenInput = container.querySelector('input[type="hidden"]');
        this.textInput = container.querySelector('.searchable-dropdown-input');
        this.optionsContainer = container.querySelector('.searchable-dropdown-options');
        
        this.isOpen = false;
        this.selectedIndex = -1;
        this.filteredData = [...this.options.data];
        
        this.init();
    }
    
    init() {
        // Initialize with data
        this.renderOptions();

        // Event listeners
        this.textInput.addEventListener('focus', () => this.open());
        this.textInput.addEventListener('blur', () => {
            setTimeout(() => this.close(), 200);
        });
        this.textInput.addEventListener('input', (e) => this.handleInput(e));
        this.textInput.addEventListener('keydown', (e) => this.handleKeydown(e));

        // Click outside to close
        document.addEventListener('click', (e) => {
            if (!this.container.contains(e.target)) {
                this.close();
            }
        });

        // Reposition on window resize
        window.addEventListener('resize', () => {
            if (this.isOpen) {
                this.positionDropdown();
            }
        });

        // Reposition on scroll
        document.addEventListener('scroll', () => {
            if (this.isOpen) {
                this.positionDropdown();
            }
        }, true);
    }
    
    handleInput(e) {
        const query = e.target.value.toLowerCase();

        if (query.length < this.options.searchThreshold) {
            this.filteredData = [...this.options.data];
        } else {
            this.filteredData = this.options.data.filter(item => {
                const label = item.label || item.text || '';
                return label.toLowerCase().includes(query);
            });
        }

        this.selectedIndex = -1;
        this.renderOptions();
        this.open();
        this.positionDropdown();
    }
    
    handleKeydown(e) {
        if (!this.isOpen) {
            if (e.key === 'ArrowDown' || e.key === 'ArrowUp' || e.key === 'Enter') {
                e.preventDefault();
                this.open();
            }
            return;
        }
        
        const options = this.optionsContainer.querySelectorAll('.searchable-dropdown-option');
        
        switch (e.key) {
            case 'ArrowDown':
                e.preventDefault();
                this.selectedIndex = Math.min(this.selectedIndex + 1, options.length - 1);
                this.updateSelection();
                break;
            case 'ArrowUp':
                e.preventDefault();
                this.selectedIndex = Math.max(this.selectedIndex - 1, 0);
                this.updateSelection();
                break;
            case 'Enter':
                e.preventDefault();
                if (this.selectedIndex >= 0 && options[this.selectedIndex]) {
                    this.selectOption(this.filteredData[this.selectedIndex]);
                }
                break;
            case 'Escape':
                e.preventDefault();
                this.close();
                break;
        }
    }
    
    updateSelection() {
        const options = this.optionsContainer.querySelectorAll('.searchable-dropdown-option');
        options.forEach((opt, index) => {
            opt.classList.toggle('selected', index === this.selectedIndex);
        });
        
        if (this.selectedIndex >= 0 && options[this.selectedIndex]) {
            options[this.selectedIndex].scrollIntoView({ block: 'nearest' });
        }
    }
    
    renderOptions() {
        this.optionsContainer.innerHTML = '';
        
        if (this.filteredData.length === 0) {
            const noResults = document.createElement('div');
            noResults.className = 'searchable-dropdown-no-results';
            noResults.textContent = 'No results found';
            this.optionsContainer.appendChild(noResults);
            return;
        }
        
        this.filteredData.forEach((item, index) => {
            const option = document.createElement('div');
            option.className = 'searchable-dropdown-option';
            option.textContent = item.label || item.text || '';
            option.dataset.index = index;
            
            option.addEventListener('click', (e) => {
                e.preventDefault();
                this.selectOption(item);
            });
            
            this.optionsContainer.appendChild(option);
        });
    }
    
    selectOption(item) {
        const value = item.id || item.value || '';
        const label = item.label || item.text || '';
        
        this.hiddenInput.value = value;
        this.textInput.value = label;
        
        if (this.options.onSelect) {
            this.options.onSelect(item);
        }
        
        this.close();
    }
    
    open() {
        // Check if the input is visible before opening
        const rect = this.textInput.getBoundingClientRect();
        if (rect.width === 0 || rect.height === 0) {
            return; // Don't open if input is hidden
        }

        this.isOpen = true;
        this.container.classList.add('open');
        this.renderOptions();
        this.positionDropdown();
    }
    
    close() {
        this.isOpen = false;
        this.container.classList.remove('open');
    }

    positionDropdown() {
        // Reset to absolute positioning
        this.optionsContainer.style.position = 'absolute';
        this.optionsContainer.style.top = '100%';
        this.optionsContainer.style.left = '0';
        this.optionsContainer.style.right = '0';
        this.optionsContainer.style.marginTop = '4px';
    }
    
    setValue(value, label) {
        this.hiddenInput.value = value;
        this.textInput.value = label || '';
    }
    
    getValue() {
        return this.hiddenInput.value;
    }
    
    updateData(newData) {
        this.options.data = newData;
        this.filteredData = [...newData];
        this.renderOptions();
    }
}

// Auto-initialize searchable dropdowns on page load
document.addEventListener('DOMContentLoaded', function() {
    const dropdowns = document.querySelectorAll('.searchable-dropdown');
    dropdowns.forEach(container => {
        // Skip dropdowns that are inside hidden modals
        if (container.closest('.modal-overlay[style*="display:none"]')) {
            return;
        }

        const dataAttr = container.dataset.data;
        let data = [];

        if (dataAttr) {
            try {
                data = JSON.parse(dataAttr);
            } catch (e) {
                console.error('Failed to parse searchable dropdown data:', e);
            }
        }

        new SearchableDropdown(container, { data });
    });
});
