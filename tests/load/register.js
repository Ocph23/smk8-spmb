import http from 'k6/http';
import { check, sleep } from 'k6';
import { Counter } from 'k6/metrics';

// Metrik custom
const successCount = new Counter('registration_success');
const failCount    = new Counter('registration_failed');

// Konfigurasi load test
export const options = {
    stages: [
        { duration: '10s', target: 5  },  // Warm-up: naik ke 5 user
        { duration: '30s', target: 20 },  // Normal: 20 concurrent user
        { duration: '20s', target: 50 },  // Peak: 50 concurrent user
        { duration: '10s', target: 0  },  // Cool-down
    ],
    thresholds: {
        http_req_duration: ['p(95)<3000'], // 95% request < 3 detik
        http_req_failed:   ['rate<0.1'],   // Error rate < 10%
    },
};

// Generate data unik per VU (virtual user) per iterasi
function generateData(vuId, iter) {
    const uid        = String(vuId).padStart(4, '0') + String(iter).padStart(6, '0');
    const nik        = ('1111111111111111' + uid).slice(-16);
    const yearsAgo   = 15 + (iter % 6);
    const birthYear  = 2026 - yearsAgo;
    const birthMonth = String((iter % 12) + 1).padStart(2, '0');
    const birthDay   = String((iter % 28) + 1).padStart(2, '0');
    const phone      = '08' + (uid + '00000000').slice(0, 10);
    const pPhone     = '08' + String(vuId * 1000 + iter + 10000000).toString().slice(0, 10);

    return {
        full_name:      `Calon Siswa VU${vuId} Iter${iter}`,
        nik:            nik,
        nisn:           uid.slice(0, 10),
        place_of_birth: 'Jayapura',
        date_of_birth:  `${birthYear}-${birthMonth}-${birthDay}`,
        gender:         iter % 2 === 0 ? 'male' : 'female',
        religion:       'Kristen',
        street:         `Jl. Test No.${vuId}-${iter}`,
        rt:             '001',
        rw:             '001',
        district:       'Abepura',
        postal_code:    '99111',
        phone:          phone,
        email:          `siswa${vuId}.${iter}.${Date.now()}@loadtest.com`,
        parent_name:    `Ayah VU${vuId}`,
        mother_name:    `Ibu VU${vuId}`,
        parent_phone:   pPhone,
        school_name:    'SMP Negeri 1 Jayapura',
        school_city:    'Jayapura',
        school_province: 'Papua',
        major_1:        '1',
        major_2:        '2',
    };
}

export default function () {
    const data = generateData(__VU, __ITER);

    const res = http.post(
        'https://spmb.smkn8tikjayapura.sch.id/students/pendaftaran/daftar',
        JSON.stringify(data),
        {
            headers: {
                'Content-Type':     'application/json',
                'Accept':           'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            timeout: '30s',
        }
    );

    const ok = check(res, {
        'status 200 atau 302': (r) => r.status === 200 || r.status === 302,
        'status 422 (validasi)': (r) => r.status === 422,
        'bukan 500': (r) => r.status !== 500,
    });

    if (res.status === 200 || res.status === 302) {
        successCount.add(1);
    } else if (res.status === 500) {
        failCount.add(1);
        console.log(`ERROR VU${__VU} iter${__ITER}: ${res.status} - ${res.body.substring(0, 200)}`);
    }

    sleep(0.5); // jeda 0.5 detik antar request per user
}
