// Philippine Address Data from PSGC API
let regions = [];
let provinces = [];
let cities = [];
let barangays = [];

// Initialize use-postal-ph library
let postalPH;

// Load use-postal-ph library from CDN
async function loadPostalLibrary() {
    try {
        const module = await import('https://unpkg.com/use-postal-ph@1.1.13/dist/index.mjs');
        postalPH = module.default();
        console.log('use-postal-ph library loaded successfully');
        return true;
    } catch (error) {
        console.error('Error loading use-postal-ph library:', error);
        return false;
    }
}

// Load Philippine regions, provinces, cities, and barangays
async function loadPhilippineData() {
    try {
        // Load regions
        const regionsResponse = await fetch('https://psgc.gitlab.io/api/regions/');
        regions = await regionsResponse.json();
        populateRegions();
        
        // Load provinces
        const provincesResponse = await fetch('https://psgc.gitlab.io/api/provinces/');
        provinces = await provincesResponse.json();
        
        // Load cities/municipalities
        const citiesResponse = await fetch('https://psgc.gitlab.io/api/cities-municipalities/');
        cities = await citiesResponse.json();
        
        // Load barangays
        const barangaysResponse = await fetch('https://psgc.gitlab.io/api/barangays/');
        barangays = await barangaysResponse.json();
        
        console.log('Philippine address data loaded successfully');
    } catch (error) {
        console.error('Error loading Philippine data:', error);
    }
}

// Populate regions dropdown
function populateRegions() {
    const regionSelect = document.getElementById('region');
    regionSelect.innerHTML = '<option value="">-- Select Region --</option>';
    
    regions.forEach(region => {
        const option = document.createElement('option');
        option.value = region.code;
        option.textContent = region.name;
        regionSelect.appendChild(option);
    });
}

// Handle region selection
document.getElementById('region').addEventListener('change', function() {
    const selectedRegionCode = this.value;
    const provinceSelect = document.getElementById('province');
    const citySelect = document.getElementById('city');
    const barangaySelect = document.getElementById('barangay');
    const zipcodeInput = document.getElementById('zipcode');
    const streetInput = document.getElementById('street');
    
    // Reset all dependent dropdowns and fields
    provinceSelect.innerHTML = '<option value="">-- Select Province --</option>';
    citySelect.innerHTML = '<option value="">-- Select City First --</option>';
    barangaySelect.innerHTML = '<option value="">-- Select City First --</option>';
    citySelect.disabled = true;
    barangaySelect.disabled = true;
    zipcodeInput.value = '';
    streetInput.value = '';
    
    if (selectedRegionCode) {
        // Filter provinces by selected region
        const regionProvinces = provinces.filter(province => province.regionCode === selectedRegionCode);
        
        regionProvinces.forEach(province => {
            const option = document.createElement('option');
            option.value = province.code;
            option.textContent = province.name;
            provinceSelect.appendChild(option);
        });
        
        provinceSelect.disabled = false;
    } else {
        provinceSelect.disabled = true;
        provinceSelect.innerHTML = '<option value="">-- Select Region First --</option>';
    }
    
    updateAddressField();
});

// Handle province selection
document.getElementById('province').addEventListener('change', function() {
    const selectedProvinceCode = this.value;
    const citySelect = document.getElementById('city');
    const barangaySelect = document.getElementById('barangay');
    
    // Reset dependent dropdowns
    citySelect.innerHTML = '<option value="">-- Select City/Municipality --</option>';
    barangaySelect.innerHTML = '<option value="">-- Select City First --</option>';
    barangaySelect.disabled = true;
    document.getElementById('zipcode').value = '';
    
    if (selectedProvinceCode) {
        // Filter cities by selected province
        const provinceCities = cities.filter(city => city.provinceCode === selectedProvinceCode);
        
        provinceCities.forEach(city => {
            const option = document.createElement('option');
            option.value = city.code;
            option.textContent = city.name;
            citySelect.appendChild(option);
        });
        
        citySelect.disabled = false;
    } else {
        citySelect.disabled = true;
    }
    
    updateAddressField();
});

// Handle city selection and auto-fill ZIP code using use-postal-ph
document.getElementById('city').addEventListener('change', function() {
    const selectedCityCode = this.value;
    const barangaySelect = document.getElementById('barangay');
    const zipcodeInput = document.getElementById('zipcode');
    
    // Reset barangay dropdown
    barangaySelect.innerHTML = '<option value="">-- Select Barangay --</option>';
    zipcodeInput.value = '';
    
    if (selectedCityCode) {
        // Get selected city name
        const selectedCity = cities.find(city => city.code === selectedCityCode);
        
        if (selectedCity && postalPH) {
            // Find ZIP code using use-postal-ph library
            const zipCode = findZipCodeWithPostalPH(selectedCity.name);
            
            if (zipCode) {
                zipcodeInput.value = zipCode;
                zipcodeInput.title = `ZIP code for ${selectedCity.name}`;
            } else {
                zipcodeInput.value = '';
                zipcodeInput.placeholder = 'Not available';
                zipcodeInput.title = 'ZIP code not available for this city';
            }
        }
        
        // Filter barangays by selected city
        const cityBarangays = barangays.filter(barangay => barangay.cityCode === selectedCityCode);
        
        cityBarangays.forEach(barangay => {
            const option = document.createElement('option');
            option.value = barangay.code;
            option.textContent = barangay.name;
            barangaySelect.appendChild(option);
        });
        
        barangaySelect.disabled = false;
    } else {
        barangaySelect.disabled = true;
    }
    
    updateAddressField();
});

// Handle barangay selection
document.getElementById('barangay').addEventListener('change', function() {
    updateAddressField();
});

// Handle street input
document.getElementById('street').addEventListener('input', function() {
    updateAddressField();
});

// Find ZIP code using use-postal-ph library
function findZipCodeWithPostalPH(cityName) {
    if (!postalPH) {
        console.warn('use-postal-ph library not loaded');
        return null;
    }
    
    try {
        // Normalize city name - remove "City" or "Municipality" suffix
        let searchTerm = cityName
            .replace(/ City$/i, '')
            .replace(/ \(City\)$/i, '')
            .replace(/ Municipality$/i, '')
            .trim();
        
        // Try searching by municipality name
        let results = postalPH.fetchDataLists({ municipality: searchTerm });
        
        // If no results, try searching by location
        if (!results || results.length === 0) {
            results = postalPH.fetchDataLists({ location: searchTerm });
        }
        
        // If still no results, try a general search
        if (!results || results.length === 0) {
            results = postalPH.fetchDataLists({ search: searchTerm });
        }
        
        // Return the first valid ZIP code found
        if (results && results.length > 0) {
            const result = results[0];
            return result.post_code ? String(result.post_code) : null;
        }
        
        return null;
    } catch (error) {
        console.error('Error finding ZIP code:', error);
        return null;
    }
}

// Update hidden address field
function updateAddressField() {
    const region = document.getElementById('region');
    const province = document.getElementById('province');
    const city = document.getElementById('city');
    const barangay = document.getElementById('barangay');
    const street = document.getElementById('street').value.trim();
    const zipcode = document.getElementById('zipcode').value;
    
    let addressParts = [];
    
    // Add street if provided
    if (street) {
        addressParts.push(street);
    }
    
    // Add barangay
    if (barangay.value && barangay.options[barangay.selectedIndex]) {
        addressParts.push(barangay.options[barangay.selectedIndex].text);
    }
    
    // Add city
    if (city.value && city.options[city.selectedIndex]) {
        addressParts.push(city.options[city.selectedIndex].text);
    }
    
    // Add province
    if (province.value && province.options[province.selectedIndex]) {
        addressParts.push(province.options[province.selectedIndex].text);
    }
    
    // Add region
    if (region.value && region.options[region.selectedIndex]) {
        addressParts.push(region.options[region.selectedIndex].text);
    }
    
    // Add zipcode if available and valid
    if (zipcode && zipcode !== 'Not available' && zipcode.trim() !== '') {
        addressParts.push(zipcode);
    }
    
    // Update hidden field
    document.getElementById('address').value = addressParts.join(', ');
}

// Calculate age from birthdate
document.getElementById('birthdate').addEventListener('change', function() {
    const birthdate = new Date(this.value);
    const today = new Date();
    const birthdateError = document.getElementById('birthdateError');
    
    // Check if birthdate is in the future
    if (birthdate > today) {
        birthdateError.classList.remove('hidden');
        this.value = '';
        document.getElementById('age').value = '';
        return;
    } else {
        birthdateError.classList.add('hidden');
    }
    
    let age = today.getFullYear() - birthdate.getFullYear();
    const monthDiff = today.getMonth() - birthdate.getMonth();
    
    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthdate.getDate())) {
        age--;
    }
    
    document.getElementById('age').value = age;
});

// Validate contact number (Philippine format)
document.getElementById('contact_no').addEventListener('input', function() {
    const contactError = document.getElementById('contactError');
    const value = this.value;
    
    // Remove any non-digit characters for validation
    const digitsOnly = value.replace(/\D/g, '');
    
    // Check if contains any letters
    if (/[a-zA-Z]/.test(value)) {
        contactError.classList.remove('hidden');
        this.setCustomValidity('Contact number cannot contain letters');
    } else {
        contactError.classList.add('hidden');
        this.setCustomValidity('');
    }
    
    // Update value to digits only
    this.value = digitsOnly;
});

// Generate school years for dropdown
function populateSchoolYears() {
    const select = document.getElementById('last_school_year');
    const currentYear = new Date().getFullYear();
    
    // Generate last 20 school years
    for (let i = 0; i < 20; i++) {
        const startYear = currentYear - i;
        const endYear = startYear + 1;
        const schoolYear = `${startYear}-${endYear}`;
        
        const option = document.createElement('option');
        option.value = schoolYear;
        option.textContent = schoolYear;
        select.appendChild(option);
    }
}

// Form validation before submit
document.querySelector('form').addEventListener('submit', function(e) {
    const address = document.getElementById('address').value;
    
    if (!address || address.trim() === '') {
        e.preventDefault();
        alert('Please complete the address section before submitting.');
        return false;
    }
    
    // Validate contact number format
    const contactNo = document.getElementById('contact_no').value;
    if (!/^09\d{9}$/.test(contactNo)) {
        e.preventDefault();
        alert('Please enter a valid Philippine mobile number (11 digits starting with 09).');
        return false;
    }
});

// Initialize on page load
document.addEventListener('DOMContentLoaded', async function() {
    populateSchoolYears();
    
    // Load both libraries
    await loadPostalLibrary(); // Load use-postal-ph from CDN
    await loadPhilippineData(); // Load Philippine address data
    
    console.log('Enrollment form initialized with use-postal-ph library');
});