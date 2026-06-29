<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    currentAcademicYear: {
        type: Object,
        default: null,
    },
    openWave: {
        type: Object,
        default: null,
    },
    majors: {
        type: Array,
        default: () => [],
    },
});

const form = useForm({
    full_name: '',
    nik: '',
    nisn: '',
    place_of_birth: '',
    date_of_birth: '',
    religion: '',
    school_name: '',
    school_city: '',
    school_province: '',
    parent_name: '',
    mother_name: '',
    parent_phone: '',
    major_1: '',
    major_2: '',
    major_3: '',
    email: '',
    phone: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('admin.students.store'));
};

const majorSelectDisabled = (value, currentField) => {
    const normalizedValue = String(value || '');
    if (!normalizedValue) {
        return false;
    }

    const selected = [form.major_1, form.major_2, form.major_3].map((item) => String(item || ''));
    return selected.some((item, index) => item && item === normalizedValue && `major_${index + 1}` !== currentField);
};
</script>

<template>
    <Head title="Tambah Pendaftar - Admin" />

    <AdminLayout>
        <div class="mx-auto max-w-4xl space-y-6">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Tambah Pendaftar Baru</h1>
                    <p class="mt-1 text-sm text-gray-500">
                        Buat akun pendaftar draft dari panel admin.
                    </p>
                </div>
                <Link
                    :href="route('admin.students')"
                    class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                >
                    Kembali
                </Link>
            </div>

            <div class="rounded-lg border border-blue-100 bg-blue-50 p-4 text-sm text-blue-800">
                Data yang dibuat dari halaman ini akan disimpan sebagai <strong>Draft</strong>, sehingga bisa dilengkapi
                lagi dari menu pendaftar.
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <div class="rounded-lg bg-white p-5 shadow-sm">
                    <div class="text-sm font-semibold text-gray-900">Konteks Saat Ini</div>
                    <div class="mt-3 space-y-2 text-sm text-gray-600">
                        <div>
                            <span class="font-medium text-gray-700">Tahun Ajaran:</span>
                            {{ currentAcademicYear?.name || '-' }}
                        </div>
                        <div>
                            <span class="font-medium text-gray-700">Gelombang Aktif:</span>
                            {{ openWave?.name || 'Tidak ada' }}
                        </div>
                    </div>
                </div>

                <div class="rounded-lg bg-white p-5 shadow-sm">
                    <div class="text-sm font-semibold text-gray-900">Catatan</div>
                    <ul class="mt-3 list-disc space-y-2 pl-5 text-sm text-gray-600">
                        <li>NIK wajib 16 digit.</li>
                        <li>NISN bersifat opsional.</li>
                        <li>Usia siswa harus antara 14 sampai 21 tahun.</li>
                        <li>Email dan nomor telepon akan dipakai untuk login siswa.</li>
                        <li>Password akan dikirim ke inbox sistem dan email siswa.</li>
                        <li>Admin bisa melengkapi biodata setelah akun dibuat.</li>
                    </ul>
                </div>
            </div>

            <div class="rounded-lg bg-white p-6 shadow-sm">
                <form @submit.prevent="submit" class="space-y-5">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">Data Pribadi</h2>
                        <p class="mt-1 text-sm text-gray-500">Isi identitas dasar calon siswa.</p>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">
                            Nama Lengkap <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.full_name"
                            type="text"
                            class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                            placeholder="Nama lengkap siswa"
                        />
                        <p v-if="form.errors.full_name" class="mt-1 text-xs text-red-600">
                            {{ form.errors.full_name }}
                        </p>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">
                                NIK <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="form.nik"
                                type="text"
                                inputmode="numeric"
                                maxlength="16"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                                placeholder="16 digit NIK"
                            />
                            <p class="mt-1 text-xs text-gray-500">Wajib 16 digit.</p>
                            <p v-if="form.errors.nik" class="mt-1 text-xs text-red-600">
                                {{ form.errors.nik }}
                            </p>
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">
                                NISN <span class="text-gray-400">(opsional)</span>
                            </label>
                            <input
                                v-model="form.nisn"
                                type="text"
                                inputmode="numeric"
                                maxlength="10"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                                placeholder="10 digit NISN"
                            />
                            <p class="mt-1 text-xs text-gray-500">Boleh dikosongkan.</p>
                            <p v-if="form.errors.nisn" class="mt-1 text-xs text-red-600">
                                {{ form.errors.nisn }}
                            </p>
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">
                                Tempat Lahir <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="form.place_of_birth"
                                type="text"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                                placeholder="Kota / Kabupaten"
                            />
                            <p v-if="form.errors.place_of_birth" class="mt-1 text-xs text-red-600">
                                {{ form.errors.place_of_birth }}
                            </p>
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">
                                Tanggal Lahir <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="form.date_of_birth"
                                type="date"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                            />
                            <p class="mt-1 text-xs text-gray-500">Usia minimal 14 tahun dan maksimal 21 tahun.</p>
                            <p v-if="form.errors.date_of_birth" class="mt-1 text-xs text-red-600">
                                {{ form.errors.date_of_birth }}
                            </p>
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">
                                Agama <span class="text-gray-400">(opsional)</span>
                            </label>
                            <select
                                v-model="form.religion"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                            >
                                <option value="">Pilih Agama</option>
                                <option value="Islam">Islam</option>
                                <option value="Kristen">Kristen</option>
                                <option value="Katolik">Katolik</option>
                                <option value="Hindu">Hindu</option>
                                <option value="Buddha">Buddha</option>
                                <option value="Konghucu">Konghucu</option>
                            </select>
                            <p v-if="form.errors.religion" class="mt-1 text-xs text-red-600">
                                {{ form.errors.religion }}
                            </p>
                        </div>
                    </div>

                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">Asal Sekolah</h2>
                        <p class="mt-1 text-sm text-gray-500">Data sekolah asal calon siswa.</p>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">
                                Asal Sekolah <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="form.school_name"
                                type="text"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                                placeholder="Nama sekolah asal"
                            />
                            <p v-if="form.errors.school_name" class="mt-1 text-xs text-red-600">
                                {{ form.errors.school_name }}
                            </p>
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">
                                Kabupaten/Kota Sekolah <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="form.school_city"
                                type="text"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                                placeholder="Kabupaten / Kota"
                            />
                            <p v-if="form.errors.school_city" class="mt-1 text-xs text-red-600">
                                {{ form.errors.school_city }}
                            </p>
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">
                                Provinsi Sekolah <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="form.school_province"
                                type="text"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                                placeholder="Provinsi"
                            />
                            <p v-if="form.errors.school_province" class="mt-1 text-xs text-red-600">
                                {{ form.errors.school_province }}
                            </p>
                        </div>
                    </div>

                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">Data Orang Tua</h2>
                        <p class="mt-1 text-sm text-gray-500">Isi identitas wali/pengurus utama siswa.</p>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">
                                Nama Orang Tua / Wali <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="form.parent_name"
                                type="text"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                                placeholder="Nama ayah / wali"
                            />
                            <p v-if="form.errors.parent_name" class="mt-1 text-xs text-red-600">
                                {{ form.errors.parent_name }}
                            </p>
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">
                                Nama Ibu <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="form.mother_name"
                                type="text"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                                placeholder="Nama ibu kandung"
                            />
                            <p v-if="form.errors.mother_name" class="mt-1 text-xs text-red-600">
                                {{ form.errors.mother_name }}
                            </p>
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">
                                Nomor Telepon Orang Tua <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="form.parent_phone"
                                type="tel"
                                inputmode="numeric"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                                placeholder="08xxxxxxxxxx"
                            />
                            <p class="mt-1 text-xs text-gray-500">Wajib diawali 08 dan minimal 10 digit.</p>
                            <p v-if="form.errors.parent_phone" class="mt-1 text-xs text-red-600">
                                {{ form.errors.parent_phone }}
                            </p>
                        </div>
                    </div>

                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">Pilihan Jurusan</h2>
                        <p class="mt-1 text-sm text-gray-500">Pilih jurusan sesuai urutan prioritas.</p>
                    </div>

                    <div class="grid gap-4 md:grid-cols-3">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">
                                Pilihan 1 <span class="text-red-500">*</span>
                            </label>
                            <select
                                v-model="form.major_1"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                            >
                                <option value="">Pilih Jurusan</option>
                                <option v-for="major in majors" :key="major.id" :value="major.id">
                                    {{ major.name }}
                                </option>
                            </select>
                            <p v-if="form.errors.major_1" class="mt-1 text-xs text-red-600">
                                {{ form.errors.major_1 }}
                            </p>
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">
                                Pilihan 2 <span class="text-red-500">*</span>
                            </label>
                            <select
                                v-model="form.major_2"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                            >
                                <option value="">Pilih Jurusan</option>
                                <option
                                    v-for="major in majors"
                                    :key="major.id"
                                    :value="major.id"
                                    :disabled="majorSelectDisabled(String(major.id), 'major_2')"
                                >
                                    {{ major.name }}
                                </option>
                            </select>
                            <p v-if="form.errors.major_2" class="mt-1 text-xs text-red-600">
                                {{ form.errors.major_2 }}
                            </p>
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">
                                Pilihan 3 <span class="text-gray-400">(opsional)</span>
                            </label>
                            <select
                                v-model="form.major_3"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                            >
                                <option value="">Pilih Jurusan</option>
                                <option
                                    v-for="major in majors"
                                    :key="major.id"
                                    :value="major.id"
                                    :disabled="majorSelectDisabled(String(major.id), 'major_3')"
                                >
                                    {{ major.name }}
                                </option>
                            </select>
                            <p v-if="form.errors.major_3" class="mt-1 text-xs text-red-600">
                                {{ form.errors.major_3 }}
                            </p>
                        </div>
                    </div>

                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">Akun Login</h2>
                        <p class="mt-1 text-sm text-gray-500">Informasi untuk login siswa.</p>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">
                                Email <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="form.email"
                                type="email"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                                placeholder="nama@email.com"
                            />
                            <p v-if="form.errors.email" class="mt-1 text-xs text-red-600">
                                {{ form.errors.email }}
                            </p>
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">
                                Nomor Telepon <span class="text-gray-400">(opsional)</span>
                            </label>
                            <input
                                v-model="form.phone"
                                type="tel"
                                inputmode="numeric"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                                placeholder="08xxxxxxxxxx"
                            />
                            <p class="mt-1 text-xs text-gray-500">Jika diisi, harus diawali 08 dan minimal 10 digit.</p>
                            <p v-if="form.errors.phone" class="mt-1 text-xs text-red-600">
                                {{ form.errors.phone }}
                            </p>
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">
                                Password <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="form.password"
                                type="password"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                                placeholder="Minimal 6 karakter"
                            />
                            <p v-if="form.errors.password" class="mt-1 text-xs text-red-600">
                                {{ form.errors.password }}
                            </p>
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">
                                Konfirmasi Password <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="form.password_confirmation"
                                type="password"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                                placeholder="Ulangi password"
                            />
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 border-t border-gray-100 pt-4">
                        <Link
                            :href="route('admin.students')"
                            class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                        >
                            Batal
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-60"
                        >
                            {{ form.processing ? 'Menyimpan...' : 'Simpan Pendaftar' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>
