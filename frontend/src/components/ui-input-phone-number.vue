<template>
    <div class="phone-input-container">
        <div class="phone-input-wrapper">
            <div class="country-code-dropdown" ref="dropdown">
                <div class="country-code-selected" @click="toggleDropdown" :class="{'invalid': invalid, 'valid': valid}">
                    <span v-if="selectedCountry">
                        {{ selectedCountry.flag }} {{ selectedCountry.dialCode }}
                    </span>
                    <span v-else class="placeholder">Select country code</span>
                    <span class="dropdown-arrow">▼</span>
                </div>
                <div v-if="dropdownOpen" class="country-code-list">
                    <input 
                        ref="searchInput"
                        type="text" 
                        v-model="searchQuery" 
                        @input="filterCountries"
                        placeholder="Search country or code..."
                        class="country-search"
                        @keydown.esc="closeDropdown"
                        @keydown.enter.prevent="selectFirstFiltered"
                    >
                    <div class="country-options">
                        <div 
                            v-for="country in filteredCountries" 
                            :key="country.code"
                            @click="selectCountry(country)"
                            class="country-option"
                            :class="{'selected': selectedCountryCode === country.dialCode}"
                        >
                            {{ country.flag }} {{ country.dialCode }} - {{ country.name }}
                        </div>
                        <div v-if="filteredCountries.length === 0" class="no-results">
                            No countries found
                        </div>
                    </div>
                </div>
            </div>
            <input 
                type="tel" 
                v-model="phoneNumber" 
                @input="updatePhoneNumber"
                @blur="$emit('blur')"
                @keydown.enter="$emit('keydown-enter')"
                @keyup.esc="$emit('keyup-esc')"
                :placeholder="placeholder"
                :class="{'invalid': invalid, 'valid': valid}"
                class="form-control phone-input"
                :autofocus="autofocus"
            >
        </div>
        <div v-if="formattedValue && false" class="formatted-display">
            E.164: {{ formattedValue }}
        </div>
    </div>
</template>

<style scoped>
    .phone-input-container {
        position: relative;
        width: 100%;
    }

    .phone-input-wrapper {
        display: flex;
        gap: 8px;
    }

    .country-code-dropdown {
        position: relative;
        min-width: 200px;
    }

    .country-code-selected {
        padding: 8px 12px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 14px;
        background-color: white;
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        align-items: center;
        user-select: none;
    }

    .country-code-selected:hover {
        border-color: #999;
    }

    .country-code-selected .placeholder {
        color: #999;
    }

    .dropdown-arrow {
        margin-left: 8px;
        font-size: 10px;
        color: #666;
    }

    .country-code-list {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        margin-top: 4px;
        background: white;
        border: 1px solid #ddd;
        border-radius: 4px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        z-index: 1000;
        max-height: 300px;
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }

    .country-search {
        padding: 8px 12px;
        border: none;
        border-bottom: 1px solid #ddd;
        font-size: 14px;
        outline: none;
    }

    .country-search:focus {
        background-color: #f9f9f9;
    }

    .country-options {
        overflow-y: auto;
        max-height: 250px;
    }

    .country-option {
        padding: 8px 12px;
        cursor: pointer;
        font-size: 14px;
        transition: background-color 0.15s;
    }

    .country-option:hover {
        background-color: #f0f0f0;
    }

    .country-option.selected {
        background-color: #e3f2fd;
        font-weight: 500;
    }

    .no-results {
        padding: 12px;
        text-align: center;
        color: #999;
        font-size: 14px;
    }

    .phone-input {
        flex: 1;
    }

    .formatted-display {
        margin-top: 8px;
        font-size: 13px;
        color: #666;
        font-family: monospace;
    }

    .invalid {
        border-color: red;
    }

    .valid {
        background-color: var(--valid-background);
    }

    .valid:focus {
        background-color: var(--valid-background);
    }
</style>

<script>
export default {
    name: 'ui-input-phone-number',
    data() {
        return {
            selectedCountryCode: '',
            phoneNumber: '',
            dropdownOpen: false,
            searchQuery: '',
            filteredCountries: [],
            countries: [
                { code: 'US', dialCode: '+1', flag: '🇺🇸', name: 'United States' },
                { code: 'CA', dialCode: '+1', flag: '🇨🇦', name: 'Canada' },
                { code: 'EG', dialCode: '+20', flag: '🇪🇬', name: 'Egypt' },
                { code: 'ZA', dialCode: '+27', flag: '🇿🇦', name: 'South Africa' },
                { code: 'GR', dialCode: '+30', flag: '🇬🇷', name: 'Greece' },
                { code: 'NL', dialCode: '+31', flag: '🇳🇱', name: 'Netherlands' },
                { code: 'BE', dialCode: '+32', flag: '🇧🇪', name: 'Belgium' },
                { code: 'FR', dialCode: '+33', flag: '🇫🇷', name: 'France' },
                { code: 'ES', dialCode: '+34', flag: '🇪🇸', name: 'Spain' },
                { code: 'HU', dialCode: '+36', flag: '🇭🇺', name: 'Hungary' },
                { code: 'IT', dialCode: '+39', flag: '🇮🇹', name: 'Italy' },
                { code: 'RO', dialCode: '+40', flag: '🇷🇴', name: 'Romania' },
                { code: 'CH', dialCode: '+41', flag: '🇨🇭', name: 'Switzerland' },
                { code: 'AT', dialCode: '+43', flag: '🇦🇹', name: 'Austria' },
                { code: 'GB', dialCode: '+44', flag: '🇬🇧', name: 'United Kingdom' },
                { code: 'DK', dialCode: '+45', flag: '🇩🇰', name: 'Denmark' },
                { code: 'SE', dialCode: '+46', flag: '🇸🇪', name: 'Sweden' },
                { code: 'NO', dialCode: '+47', flag: '🇳🇴', name: 'Norway' },
                { code: 'PL', dialCode: '+48', flag: '🇵🇱', name: 'Poland' },
                { code: 'DE', dialCode: '+49', flag: '🇩🇪', name: 'Germany' },
                { code: 'PE', dialCode: '+51', flag: '🇵🇪', name: 'Peru' },
                { code: 'MX', dialCode: '+52', flag: '🇲🇽', name: 'Mexico' },
                { code: 'CU', dialCode: '+53', flag: '🇨🇺', name: 'Cuba' },
                { code: 'AR', dialCode: '+54', flag: '🇦🇷', name: 'Argentina' },
                { code: 'BR', dialCode: '+55', flag: '🇧🇷', name: 'Brazil' },
                { code: 'CL', dialCode: '+56', flag: '🇨🇱', name: 'Chile' },
                { code: 'CO', dialCode: '+57', flag: '🇨🇴', name: 'Colombia' },
                { code: 'VE', dialCode: '+58', flag: '🇻🇪', name: 'Venezuela' },
                { code: 'MY', dialCode: '+60', flag: '🇲🇾', name: 'Malaysia' },
                { code: 'AU', dialCode: '+61', flag: '🇦🇺', name: 'Australia' },
                { code: 'ID', dialCode: '+62', flag: '🇮🇩', name: 'Indonesia' },
                { code: 'PH', dialCode: '+63', flag: '🇵🇭', name: 'Philippines' },
                { code: 'NZ', dialCode: '+64', flag: '🇳🇿', name: 'New Zealand' },
                { code: 'SG', dialCode: '+65', flag: '🇸🇬', name: 'Singapore' },
                { code: 'TH', dialCode: '+66', flag: '🇹🇭', name: 'Thailand' },
                { code: 'JP', dialCode: '+81', flag: '🇯🇵', name: 'Japan' },
                { code: 'KR', dialCode: '+82', flag: '🇰🇷', name: 'South Korea' },
                { code: 'VN', dialCode: '+84', flag: '🇻🇳', name: 'Vietnam' },
                { code: 'CN', dialCode: '+86', flag: '🇨🇳', name: 'China' },
                { code: 'TR', dialCode: '+90', flag: '🇹🇷', name: 'Turkey' },
                { code: 'IN', dialCode: '+91', flag: '🇮🇳', name: 'India' },
                { code: 'PK', dialCode: '+92', flag: '🇵🇰', name: 'Pakistan' },
                { code: 'AF', dialCode: '+93', flag: '🇦🇫', name: 'Afghanistan' },
                { code: 'LK', dialCode: '+94', flag: '🇱🇰', name: 'Sri Lanka' },
                { code: 'MM', dialCode: '+95', flag: '🇲🇲', name: 'Myanmar' },
                { code: 'IR', dialCode: '+98', flag: '🇮🇷', name: 'Iran' },
                { code: 'MA', dialCode: '+212', flag: '🇲🇦', name: 'Morocco' },
                { code: 'DZ', dialCode: '+213', flag: '🇩🇿', name: 'Algeria' },
                { code: 'TN', dialCode: '+216', flag: '🇹🇳', name: 'Tunisia' },
                { code: 'LY', dialCode: '+218', flag: '🇱🇾', name: 'Libya' },
                { code: 'GM', dialCode: '+220', flag: '🇬🇲', name: 'Gambia' },
                { code: 'SN', dialCode: '+221', flag: '🇸🇳', name: 'Senegal' },
                { code: 'MR', dialCode: '+222', flag: '🇲🇷', name: 'Mauritania' },
                { code: 'ML', dialCode: '+223', flag: '🇲🇱', name: 'Mali' },
                { code: 'GN', dialCode: '+224', flag: '🇬🇳', name: 'Guinea' },
                { code: 'CI', dialCode: '+225', flag: '🇨🇮', name: 'Ivory Coast' },
                { code: 'BF', dialCode: '+226', flag: '🇧🇫', name: 'Burkina Faso' },
                { code: 'NE', dialCode: '+227', flag: '🇳🇪', name: 'Niger' },
                { code: 'TG', dialCode: '+228', flag: '🇹🇬', name: 'Togo' },
                { code: 'BJ', dialCode: '+229', flag: '🇧🇯', name: 'Benin' },
                { code: 'MU', dialCode: '+230', flag: '🇲🇺', name: 'Mauritius' },
                { code: 'LR', dialCode: '+231', flag: '🇱🇷', name: 'Liberia' },
                { code: 'SL', dialCode: '+232', flag: '🇸🇱', name: 'Sierra Leone' },
                { code: 'GH', dialCode: '+233', flag: '🇬🇭', name: 'Ghana' },
                { code: 'NG', dialCode: '+234', flag: '🇳🇬', name: 'Nigeria' },
                { code: 'TD', dialCode: '+235', flag: '🇹🇩', name: 'Chad' },
                { code: 'CF', dialCode: '+236', flag: '🇨🇫', name: 'Central African Republic' },
                { code: 'CM', dialCode: '+237', flag: '🇨🇲', name: 'Cameroon' },
                { code: 'CV', dialCode: '+238', flag: '🇨🇻', name: 'Cape Verde' },
                { code: 'ST', dialCode: '+239', flag: '🇸🇹', name: 'São Tomé and Príncipe' },
                { code: 'GQ', dialCode: '+240', flag: '🇬🇶', name: 'Equatorial Guinea' },
                { code: 'GA', dialCode: '+241', flag: '🇬🇦', name: 'Gabon' },
                { code: 'CG', dialCode: '+242', flag: '🇨🇬', name: 'Republic of the Congo' },
                { code: 'CD', dialCode: '+243', flag: '🇨🇩', name: 'Democratic Republic of the Congo' },
                { code: 'AO', dialCode: '+244', flag: '🇦🇴', name: 'Angola' },
                { code: 'GW', dialCode: '+245', flag: '🇬🇼', name: 'Guinea-Bissau' },
                { code: 'SC', dialCode: '+248', flag: '🇸🇨', name: 'Seychelles' },
                { code: 'SD', dialCode: '+249', flag: '🇸🇩', name: 'Sudan' },
                { code: 'RW', dialCode: '+250', flag: '🇷🇼', name: 'Rwanda' },
                { code: 'ET', dialCode: '+251', flag: '🇪🇹', name: 'Ethiopia' },
                { code: 'SO', dialCode: '+252', flag: '🇸🇴', name: 'Somalia' },
                { code: 'DJ', dialCode: '+253', flag: '🇩🇯', name: 'Djibouti' },
                { code: 'KE', dialCode: '+254', flag: '🇰🇪', name: 'Kenya' },
                { code: 'TZ', dialCode: '+255', flag: '🇹🇿', name: 'Tanzania' },
                { code: 'UG', dialCode: '+256', flag: '🇺🇬', name: 'Uganda' },
                { code: 'BI', dialCode: '+257', flag: '🇧🇮', name: 'Burundi' },
                { code: 'MZ', dialCode: '+258', flag: '🇲🇿', name: 'Mozambique' },
                { code: 'ZM', dialCode: '+260', flag: '🇿🇲', name: 'Zambia' },
                { code: 'MG', dialCode: '+261', flag: '🇲🇬', name: 'Madagascar' },
                { code: 'RE', dialCode: '+262', flag: '🇷🇪', name: 'Réunion' },
                { code: 'ZW', dialCode: '+263', flag: '🇿🇼', name: 'Zimbabwe' },
                { code: 'NA', dialCode: '+264', flag: '🇳🇦', name: 'Namibia' },
                { code: 'MW', dialCode: '+265', flag: '🇲🇼', name: 'Malawi' },
                { code: 'LS', dialCode: '+266', flag: '🇱🇸', name: 'Lesotho' },
                { code: 'BW', dialCode: '+267', flag: '🇧🇼', name: 'Botswana' },
                { code: 'SZ', dialCode: '+268', flag: '🇸🇿', name: 'Eswatini' },
                { code: 'KM', dialCode: '+269', flag: '🇰🇲', name: 'Comoros' },
                { code: 'SH', dialCode: '+290', flag: '🇸🇭', name: 'Saint Helena' },
                { code: 'ER', dialCode: '+291', flag: '🇪🇷', name: 'Eritrea' },
                { code: 'AW', dialCode: '+297', flag: '🇦🇼', name: 'Aruba' },
                { code: 'FO', dialCode: '+298', flag: '🇫🇴', name: 'Faroe Islands' },
                { code: 'GL', dialCode: '+299', flag: '🇬🇱', name: 'Greenland' },
                { code: 'GI', dialCode: '+350', flag: '🇬🇮', name: 'Gibraltar' },
                { code: 'PT', dialCode: '+351', flag: '🇵🇹', name: 'Portugal' },
                { code: 'LU', dialCode: '+352', flag: '🇱🇺', name: 'Luxembourg' },
                { code: 'IE', dialCode: '+353', flag: '🇮🇪', name: 'Ireland' },
                { code: 'IS', dialCode: '+354', flag: '🇮🇸', name: 'Iceland' },
                { code: 'AL', dialCode: '+355', flag: '🇦🇱', name: 'Albania' },
                { code: 'MT', dialCode: '+356', flag: '🇲🇹', name: 'Malta' },
                { code: 'CY', dialCode: '+357', flag: '🇨🇾', name: 'Cyprus' },
                { code: 'FI', dialCode: '+358', flag: '🇫🇮', name: 'Finland' },
                { code: 'BG', dialCode: '+359', flag: '🇧🇬', name: 'Bulgaria' },
                { code: 'LT', dialCode: '+370', flag: '🇱🇹', name: 'Lithuania' },
                { code: 'LV', dialCode: '+371', flag: '🇱🇻', name: 'Latvia' },
                { code: 'EE', dialCode: '+372', flag: '🇪🇪', name: 'Estonia' },
                { code: 'MD', dialCode: '+373', flag: '🇲🇩', name: 'Moldova' },
                { code: 'AM', dialCode: '+374', flag: '🇦🇲', name: 'Armenia' },
                { code: 'BY', dialCode: '+375', flag: '🇧🇾', name: 'Belarus' },
                { code: 'AD', dialCode: '+376', flag: '🇦🇩', name: 'Andorra' },
                { code: 'MC', dialCode: '+377', flag: '🇲🇨', name: 'Monaco' },
                { code: 'SM', dialCode: '+378', flag: '🇸🇲', name: 'San Marino' },
                { code: 'UA', dialCode: '+380', flag: '🇺🇦', name: 'Ukraine' },
                { code: 'RS', dialCode: '+381', flag: '🇷🇸', name: 'Serbia' },
                { code: 'ME', dialCode: '+382', flag: '🇲🇪', name: 'Montenegro' },
                { code: 'HR', dialCode: '+385', flag: '🇭🇷', name: 'Croatia' },
                { code: 'SI', dialCode: '+386', flag: '🇸🇮', name: 'Slovenia' },
                { code: 'BA', dialCode: '+387', flag: '🇧🇦', name: 'Bosnia and Herzegovina' },
                { code: 'MK', dialCode: '+389', flag: '🇲🇰', name: 'North Macedonia' },
                { code: 'CZ', dialCode: '+420', flag: '🇨🇿', name: 'Czech Republic' },
                { code: 'SK', dialCode: '+421', flag: '🇸🇰', name: 'Slovakia' },
                { code: 'LI', dialCode: '+423', flag: '🇱🇮', name: 'Liechtenstein' },
                { code: 'FK', dialCode: '+500', flag: '🇫🇰', name: 'Falkland Islands' },
                { code: 'BZ', dialCode: '+501', flag: '🇧🇿', name: 'Belize' },
                { code: 'GT', dialCode: '+502', flag: '🇬🇹', name: 'Guatemala' },
                { code: 'SV', dialCode: '+503', flag: '🇸🇻', name: 'El Salvador' },
                { code: 'HN', dialCode: '+504', flag: '🇭🇳', name: 'Honduras' },
                { code: 'NI', dialCode: '+505', flag: '🇳🇮', name: 'Nicaragua' },
                { code: 'CR', dialCode: '+506', flag: '🇨🇷', name: 'Costa Rica' },
                { code: 'PA', dialCode: '+507', flag: '🇵🇦', name: 'Panama' },
                { code: 'PM', dialCode: '+508', flag: '🇵🇲', name: 'Saint Pierre and Miquelon' },
                { code: 'HT', dialCode: '+509', flag: '🇭🇹', name: 'Haiti' },
                { code: 'BO', dialCode: '+591', flag: '🇧🇴', name: 'Bolivia' },
                { code: 'GY', dialCode: '+592', flag: '🇬🇾', name: 'Guyana' },
                { code: 'EC', dialCode: '+593', flag: '🇪🇨', name: 'Ecuador' },
                { code: 'PY', dialCode: '+595', flag: '🇵🇾', name: 'Paraguay' },
                { code: 'SR', dialCode: '+597', flag: '🇸🇷', name: 'Suriname' },
                { code: 'UY', dialCode: '+598', flag: '🇺🇾', name: 'Uruguay' },
                { code: 'TL', dialCode: '+670', flag: '🇹🇱', name: 'Timor-Leste' },
                { code: 'AQ', dialCode: '+672', flag: '🇦🇶', name: 'Antarctica' },
                { code: 'BN', dialCode: '+673', flag: '🇧🇳', name: 'Brunei' },
                { code: 'NR', dialCode: '+674', flag: '🇳🇷', name: 'Nauru' },
                { code: 'PG', dialCode: '+675', flag: '🇵🇬', name: 'Papua New Guinea' },
                { code: 'TO', dialCode: '+676', flag: '🇹🇴', name: 'Tonga' },
                { code: 'SB', dialCode: '+677', flag: '🇸🇧', name: 'Solomon Islands' },
                { code: 'VU', dialCode: '+678', flag: '🇻🇺', name: 'Vanuatu' },
                { code: 'FJ', dialCode: '+679', flag: '🇫🇯', name: 'Fiji' },
                { code: 'PW', dialCode: '+680', flag: '🇵🇼', name: 'Palau' },
                { code: 'WF', dialCode: '+681', flag: '🇼🇫', name: 'Wallis and Futuna' },
                { code: 'CK', dialCode: '+682', flag: '🇨🇰', name: 'Cook Islands' },
                { code: 'NU', dialCode: '+683', flag: '🇳🇺', name: 'Niue' },
                { code: 'WS', dialCode: '+685', flag: '🇼🇸', name: 'Samoa' },
                { code: 'KI', dialCode: '+686', flag: '🇰🇮', name: 'Kiribati' },
                { code: 'NC', dialCode: '+687', flag: '🇳🇨', name: 'New Caledonia' },
                { code: 'TV', dialCode: '+688', flag: '🇹🇻', name: 'Tuvalu' },
                { code: 'PF', dialCode: '+689', flag: '🇵🇫', name: 'French Polynesia' },
                { code: 'TK', dialCode: '+690', flag: '🇹🇰', name: 'Tokelau' },
                { code: 'FM', dialCode: '+691', flag: '🇫🇲', name: 'Micronesia' },
                { code: 'MH', dialCode: '+692', flag: '🇲🇭', name: 'Marshall Islands' },
                { code: 'KP', dialCode: '+850', flag: '🇰🇵', name: 'North Korea' },
                { code: 'HK', dialCode: '+852', flag: '🇭🇰', name: 'Hong Kong' },
                { code: 'MO', dialCode: '+853', flag: '🇲🇴', name: 'Macau' },
                { code: 'KH', dialCode: '+855', flag: '🇰🇭', name: 'Cambodia' },
                { code: 'LA', dialCode: '+856', flag: '🇱🇦', name: 'Laos' },
                { code: 'BD', dialCode: '+880', flag: '🇧🇩', name: 'Bangladesh' },
                { code: 'TW', dialCode: '+886', flag: '🇹🇼', name: 'Taiwan' },
                { code: 'MV', dialCode: '+960', flag: '🇲🇻', name: 'Maldives' },
                { code: 'LB', dialCode: '+961', flag: '🇱🇧', name: 'Lebanon' },
                { code: 'JO', dialCode: '+962', flag: '🇯🇴', name: 'Jordan' },
                { code: 'SY', dialCode: '+963', flag: '🇸🇾', name: 'Syria' },
                { code: 'IQ', dialCode: '+964', flag: '🇮🇶', name: 'Iraq' },
                { code: 'KW', dialCode: '+965', flag: '🇰🇼', name: 'Kuwait' },
                { code: 'SA', dialCode: '+966', flag: '🇸🇦', name: 'Saudi Arabia' },
                { code: 'YE', dialCode: '+967', flag: '🇾🇪', name: 'Yemen' },
                { code: 'OM', dialCode: '+968', flag: '🇴🇲', name: 'Oman' },
                { code: 'PS', dialCode: '+970', flag: '🇵🇸', name: 'Palestine' },
                { code: 'AE', dialCode: '+971', flag: '🇦🇪', name: 'United Arab Emirates' },
                { code: 'IL', dialCode: '+972', flag: '🇮🇱', name: 'Israel' },
                { code: 'BH', dialCode: '+973', flag: '🇧🇭', name: 'Bahrain' },
                { code: 'QA', dialCode: '+974', flag: '🇶🇦', name: 'Qatar' },
                { code: 'BT', dialCode: '+975', flag: '🇧🇹', name: 'Bhutan' },
                { code: 'MN', dialCode: '+976', flag: '🇲🇳', name: 'Mongolia' },
                { code: 'NP', dialCode: '+977', flag: '🇳🇵', name: 'Nepal' },
                { code: 'TJ', dialCode: '+992', flag: '🇹🇯', name: 'Tajikistan' },
                { code: 'TM', dialCode: '+993', flag: '🇹🇲', name: 'Turkmenistan' },
                { code: 'AZ', dialCode: '+994', flag: '🇦🇿', name: 'Azerbaijan' },
                { code: 'GE', dialCode: '+995', flag: '🇬🇪', name: 'Georgia' },
                { code: 'KG', dialCode: '+996', flag: '🇰🇬', name: 'Kyrgyzstan' },
                { code: 'UZ', dialCode: '+998', flag: '🇺🇿', name: 'Uzbekistan' },
            ]
        }
    },
    computed: {
        selectedCountry() {
            return this.countries.find(c => c.dialCode === this.selectedCountryCode)
        },
        formattedValue() {
            if (!this.phoneNumber) return ''
            
            const digits = this.phoneNumber.replace(/\D/g, '')
            
            if (!digits) return ''
            
            return `${this.selectedCountryCode} ${digits}`
        },
        value: {
            get() {
                return this.modelValue
            },
            set(value) {
                this.$emit('update:modelValue', value)
            }
        },
        invalid() {
            if (!this.phoneNumber) return false
            
            const digits = this.phoneNumber.replace(/\D/g, '')
            return digits.length < 5
        },
        valid() {
            if (!this.phoneNumber || !this.selectedCountryCode) return false
            
            const digits = this.phoneNumber.replace(/\D/g, '')
            return digits.length >= 5 && digits.length <= 15
        }
    },
    methods: {
        toggleDropdown() {
            this.dropdownOpen = !this.dropdownOpen
            if (this.dropdownOpen) {
                this.searchQuery = ''
                this.filteredCountries = [...this.countries]
                this.$nextTick(() => {
                    if (this.$refs.searchInput) {
                        this.$refs.searchInput.focus()
                    }
                })
            }
        },
        closeDropdown() {
            this.dropdownOpen = false
            this.searchQuery = ''
        },
        selectCountry(country) {
            this.selectedCountryCode = country.dialCode
            this.closeDropdown()
            this.updatePhoneNumber()
        },
        selectFirstFiltered() {
            if (this.filteredCountries.length > 0) {
                this.selectCountry(this.filteredCountries[0])
            }
        },
        filterCountries() {
            const query = this.searchQuery.toLowerCase().trim()
            
            if (!query) {
                this.filteredCountries = [...this.countries]
                return
            }
            
            this.filteredCountries = this.countries.filter(country => {
                return country.name.toLowerCase().includes(query) ||
                       country.dialCode.includes(query) ||
                       country.code.toLowerCase().includes(query)
            })
        },
        updatePhoneNumber() {
            const formattedNumber = this.formattedValue
            if (!this.validOnly || this.valid) {
                this.$emit('update:modelValue', formattedNumber)
            } else {
                this.$emit('update:modelValue', null)
            }
        },
        parseInitialValue(value) {
            if (!value) return
            
            const match = value.match(/^(\+\d{1,4})\s?(.*)$/)
            if (match) {
                const [, countryCode, number] = match
                this.selectedCountryCode = countryCode
                this.phoneNumber = number.replace(/\D/g, '')
            }
        },
        handleClickOutside(event) {
            if (this.$refs.dropdown && !this.$refs.dropdown.contains(event.target)) {
                this.closeDropdown()
            }
        }
    },
    watch: {
        modelValue: {
            immediate: true,
            handler(newValue) {
                if (newValue && newValue !== this.formattedValue) {
                    this.parseInitialValue(newValue)
                }
            }
        }
    },
    props: {
        validOnly: {
            required: false,
            default: false,
            type: Boolean
        },
        autofocus: {
            required: false,
            default: false,
            type: Boolean
        },
        modelValue: {
            required: false,
            default: ''
        },
        placeholder: {
            required: false,
            default: 'Phone number',
            type: String
        }
    },
    mounted() {
        this.filteredCountries = [...this.countries]
        
        document.addEventListener('click', this.handleClickOutside)
        
        if (this.autofocus) {
            this.$nextTick(() => {
                const input = this.$el.querySelector('.phone-input')
                if (input) input.focus()
            })
        }
    },
    beforeUnmount() {
        document.removeEventListener('click', this.handleClickOutside)
    }
}
</script>