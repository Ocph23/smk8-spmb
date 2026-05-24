import { reactive } from 'vue';

const isBlank = (value) => value === null || value === undefined || String(value).trim() === '';

const asText = (value) => String(value ?? '').trim();

const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
const nikPattern = /^\d{16}$/;
const phonePattern = /^08[0-9]{8,}$/;
const postalPattern = /^\d{4,10}$/;

export function useStudentFormValidation(form) {
    const getMessage = (field) => {
        const value = form[field];

        switch (field) {
            case 'full_name':
                return isBlank(value) ? 'Nama lengkap wajib diisi.' : '';
            case 'nik':
                if (isBlank(value)) return 'NIK wajib diisi.';
                return nikPattern.test(asText(value)) ? '' : 'NIK harus 16 digit angka.';
            case 'place_of_birth':
                return isBlank(value) ? 'Tempat lahir wajib diisi.' : '';
            case 'date_of_birth':
                if (isBlank(value)) return 'Tanggal lahir wajib diisi.';
                {
                    const dob = new Date(value);
                    if (Number.isNaN(dob.getTime())) return 'Tanggal lahir tidak valid.';
                    const today = new Date();
                    const age = today.getFullYear() - dob.getFullYear()
                        - (today < new Date(today.getFullYear(), dob.getMonth(), dob.getDate()) ? 1 : 0);
                    if (age < 14) return 'Usia pendaftar minimal 14 tahun.';
                    if (age > 21) return 'Usia pendaftar maksimal 21 tahun.';
                    return '';
                }
            case 'gender':
                return isBlank(value) ? 'Jenis kelamin wajib dipilih.' : '';
            case 'street':
                return isBlank(value) ? 'Jalan/Gang wajib diisi.' : '';
            case 'district':
                return isBlank(value) ? 'Kecamatan/Distrik wajib diisi.' : '';
            case 'postal_code':
                if (isBlank(value)) return '';
                return postalPattern.test(asText(value)) ? '' : 'Kode pos harus 4-10 digit angka.';
            case 'phone':
                if (isBlank(value)) return 'No. telepon wajib diisi.';
                return phonePattern.test(asText(value)) ? '' : 'Nomor telepon harus diawali 08 dan minimal 10 digit.';
            case 'email':
                if (isBlank(value)) return 'Email wajib diisi.';
                return emailPattern.test(asText(value)) ? '' : 'Format email tidak valid.';
            case 'parent_name':
                return isBlank(value) ? 'Nama ayah/wali wajib diisi.' : '';
            case 'mother_name':
                return isBlank(value) ? 'Nama ibu wajib diisi.' : '';
            case 'parent_phone':
                if (isBlank(value)) return 'No. telepon orang tua/wali wajib diisi.';
                return phonePattern.test(asText(value)) ? '' : 'Nomor telepon orang tua/wali harus diawali 08 dan minimal 10 digit.';
            case 'school_name':
                return isBlank(value) ? 'Nama sekolah wajib diisi.' : '';
            case 'school_city':
                return isBlank(value) ? 'Kota/Kabupaten sekolah wajib diisi.' : '';
            case 'school_province':
                return isBlank(value) ? 'Provinsi sekolah wajib diisi.' : '';
            case 'major_1':
                return isBlank(value) ? 'Pilihan jurusan 1 wajib dipilih.' : '';
            case 'major_2':
                if (isBlank(value)) return 'Pilihan jurusan 2 wajib dipilih.';
                return asText(value) === asText(form.major_1)
                    ? 'Pilihan jurusan 2 harus berbeda dari pilihan 1.'
                    : '';
            case 'major_3':
                if (isBlank(value)) return '';
                if (asText(value) === asText(form.major_1) || asText(value) === asText(form.major_2)) {
                    return 'Pilihan jurusan 3 harus berbeda dari pilihan 1 dan 2.';
                }
                return '';
            default:
                return '';
        }
    };

    const setError = (field, message, errors) => {
        if (message) {
            errors[field] = message;
        } else {
            delete errors[field];
        }
    };

    const clientErrors = reactive({});

    const validateField = (field) => {
        const message = getMessage(field);
        setError(field, message, clientErrors);
        return !message;
    };

    const isFieldValid = (field) => !getMessage(field);

    const validateFields = (fields) => fields.every((field) => validateField(field));

    const isFieldsValid = (fields) => fields.every((field) => isFieldValid(field));

    const step1Fields = [
        'full_name',
        'nik',
        'place_of_birth',
        'date_of_birth',
        'gender',
        'street',
        'district',
        'postal_code',
        'phone',
        'email',
        'parent_name',
        'mother_name',
        'parent_phone',
        'school_name',
        'school_city',
        'school_province',
    ];

    const step2Fields = ['major_1', 'major_2', 'major_3'];

    const validateStep1 = () => validateFields(step1Fields);
    const validateStep2 = () => validateFields(step2Fields);
    const isStep1Valid = () => isFieldsValid(step1Fields);
    const isStep2Valid = () => isFieldsValid(step2Fields);
    const validateAll = () => validateStep1() && validateStep2();

    const clearError = (field) => {
        delete clientErrors[field];
    };

    const fieldError = (field, serverErrors = {}) => clientErrors[field] || serverErrors[field] || '';

    return {
        clientErrors,
        clearError,
        fieldError,
        isFieldValid,
        isStep1Valid,
        isStep2Valid,
        validateAll,
        validateField,
        validateStep1,
        validateStep2,
    };
}
