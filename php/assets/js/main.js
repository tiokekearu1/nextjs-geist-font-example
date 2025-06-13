// Main JavaScript for School Attendance System

// Global Variables
let locationValid = false;
let currentPosition = null;

// Location Handling
class LocationManager {
    constructor(allowedRadius) {
        this.allowedRadius = allowedRadius || 100; // Default 100 meters
        this.schoolPosition = {
            lat: parseFloat(document.querySelector('meta[name="school-latitude"]')?.content || 0),
            lng: parseFloat(document.querySelector('meta[name="school-longitude"]')?.content || 0)
        };
    }

    // Initialize location tracking
    init() {
        if (!navigator.geolocation) {
            this.showLocationError('Geolocation is not supported by your browser');
            return;
        }

        this.watchPosition();
    }

    // Start watching position
    watchPosition() {
        navigator.geolocation.watchPosition(
            position => this.handlePositionUpdate(position),
            error => this.handlePositionError(error),
            {
                enableHighAccuracy: true,
                timeout: 5000,
                maximumAge: 0
            }
        );
    }

    // Handle successful position update
    handlePositionUpdate(position) {
        currentPosition = {
            lat: position.coords.latitude,
            lng: position.coords.longitude
        };

        const distance = this.calculateDistance(
            currentPosition.lat,
            currentPosition.lng,
            this.schoolPosition.lat,
            this.schoolPosition.lng
        );

        locationValid = distance <= this.allowedRadius;
        this.updateLocationStatus(locationValid);
        this.updateFormStates(locationValid);
    }

    // Handle position error
    handlePositionError(error) {
        let message;
        switch(error.code) {
            case error.PERMISSION_DENIED:
                message = "Location access denied. Please enable location services.";
                break;
            case error.POSITION_UNAVAILABLE:
                message = "Location information unavailable.";
                break;
            case error.TIMEOUT:
                message = "Location request timed out.";
                break;
            default:
                message = "An unknown error occurred.";
        }
        this.showLocationError(message);
    }

    // Calculate distance between two points using Haversine formula
    calculateDistance(lat1, lon1, lat2, lon2) {
        const R = 6371e3; // Earth's radius in meters
        const φ1 = lat1 * Math.PI/180;
        const φ2 = lat2 * Math.PI/180;
        const Δφ = (lat2-lat1) * Math.PI/180;
        const Δλ = (lon2-lon1) * Math.PI/180;

        const a = Math.sin(Δφ/2) * Math.sin(Δφ/2) +
                Math.cos(φ1) * Math.cos(φ2) *
                Math.sin(Δλ/2) * Math.sin(Δλ/2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));

        return R * c; // Distance in meters
    }

    // Update location status display
    updateLocationStatus(isValid) {
        const toast = document.getElementById('locationToast');
        const message = document.getElementById('locationMessage');
        
        if (toast && message) {
            message.textContent = isValid ? 
                'Location verified - You can mark attendance' : 
                'You must be within school premises';
            message.className = 'toast-body ' + (isValid ? 'text-success' : 'text-danger');
            bootstrap.Toast.getInstance(toast)?.show();
        }
    }

    // Update form states based on location validity
    updateFormStates(isValid) {
        const submitButtons = document.querySelectorAll('[data-requires-location]');
        submitButtons.forEach(button => {
            button.disabled = !isValid;
            button.title = isValid ? '' : 'You must be within school premises';
        });
    }

    // Show location error
    showLocationError(message) {
        const toast = document.getElementById('locationToast');
        const messageElement = document.getElementById('locationMessage');
        
        if (toast && messageElement) {
            messageElement.textContent = message;
            messageElement.className = 'toast-body text-danger';
            bootstrap.Toast.getInstance(toast)?.show();
        }
    }
}

// Form Handling
class FormManager {
    constructor() {
        this.setupFormValidation();
        this.setupImagePreviews();
    }

    // Setup form validation
    setupFormValidation() {
        const forms = document.querySelectorAll('.needs-validation');
        
        forms.forEach(form => {
            form.addEventListener('submit', event => {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            });
        });
    }

    // Setup image previews
    setupImagePreviews() {
        const imageInputs = document.querySelectorAll('input[type="file"][data-preview]');
        
        imageInputs.forEach(input => {
            input.addEventListener('change', event => {
                const file = event.target.files[0];
                const previewElement = document.getElementById(input.dataset.preview);
                
                if (file && previewElement) {
                    const reader = new FileReader();
                    reader.onload = e => {
                        previewElement.src = e.target.result;
                        previewElement.classList.remove('d-none');
                    };
                    reader.readAsDataURL(file);
                }
            });
        });
    }

    // Handle form submission with location data
    static attachLocationData(form) {
        if (!currentPosition) return false;
        
        const latInput = form.querySelector('input[name="location_lat"]');
        const lngInput = form.querySelector('input[name="location_lng"]');
        
        if (latInput && lngInput) {
            latInput.value = currentPosition.lat;
            lngInput.value = currentPosition.lng;
            return true;
        }
        return false;
    }
}

// Table Management
class TableManager {
    constructor(tableId, options = {}) {
        this.table = document.getElementById(tableId);
        this.options = {
            pageSize: options.pageSize || 10,
            searchable: options.searchable !== false,
            sortable: options.sortable !== false,
            ...options
        };
        
        if (this.table) {
            this.init();
        }
    }

    // Initialize table features
    init() {
        if (this.options.searchable) {
            this.setupSearch();
        }
        if (this.options.sortable) {
            this.setupSorting();
        }
        this.setupPagination();
    }

    // Setup search functionality
    setupSearch() {
        const wrapper = this.table.parentElement;
        const searchInput = document.createElement('input');
        searchInput.type = 'text';
        searchInput.className = 'form-control mb-3';
        searchInput.placeholder = 'Search...';
        
        searchInput.addEventListener('input', e => this.handleSearch(e.target.value));
        wrapper.insertBefore(searchInput, this.table);
    }

    // Handle search
    handleSearch(query) {
        const rows = this.table.querySelectorAll('tbody tr');
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(query.toLowerCase()) ? '' : 'none';
        });
    }

    // Setup sorting
    setupSorting() {
        const headers = this.table.querySelectorAll('th[data-sortable]');
        headers.forEach(header => {
            header.style.cursor = 'pointer';
            header.addEventListener('click', () => this.sortTable(header));
        });
    }

    // Sort table
    sortTable(header) {
        const column = header.cellIndex;
        const rows = Array.from(this.table.querySelectorAll('tbody tr'));
        const isAsc = header.classList.contains('asc');
        
        rows.sort((a, b) => {
            const aVal = a.cells[column].textContent;
            const bVal = b.cells[column].textContent;
            return isAsc ? bVal.localeCompare(aVal) : aVal.localeCompare(bVal);
        });
        
        header.classList.toggle('asc');
        rows.forEach(row => this.table.querySelector('tbody').appendChild(row));
    }

    // Setup pagination
    setupPagination() {
        // Pagination implementation
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    // Initialize location manager
    const locationManager = new LocationManager();
    locationManager.init();

    // Initialize form manager
    const formManager = new FormManager();

    // Initialize tables
    const tables = document.querySelectorAll('[data-table]');
    tables.forEach(table => {
        new TableManager(table.id, {
            pageSize: parseInt(table.dataset.pageSize) || 10,
            searchable: table.dataset.searchable !== 'false',
            sortable: table.dataset.sortable !== 'false'
        });
    });

    // Setup form submissions
    document.querySelectorAll('form[data-location-required]').forEach(form => {
        form.addEventListener('submit', event => {
            if (!locationValid) {
                event.preventDefault();
                alert('You must be within school premises to submit this form');
                return;
            }
            FormManager.attachLocationData(form);
        });
    });
});

// Export for module usage if needed
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        LocationManager,
        FormManager,
        TableManager
    };
}
