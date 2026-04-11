let counter = 0;

const cities    = ['Jayapura', 'Sentani', 'Abepura', 'Waena', 'Kotaraja'];
const religions = ['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha'];
const schools   = ['SMP Negeri 1 Jayapura', 'SMP Negeri 2 Jayapura', 'SMP Negeri 3 Sentani'];
const streets   = ['Jl. Raya Abepura', 'Jl. Sentani Raya', 'Jl. Kotaraja', 'Jl. Hamadi'];

export function generateStudentData(context, events, done) {
    counter++;
    const ts         = Date.now().toString().slice(-8);
    const uid        = String(counter).padStart(6, '0') + ts;
    const nik        = ('1111111111111111' + uid).slice(-16);
    const yearsAgo   = 15 + (counter % 6);
    const birthYear  = new Date().getFullYear() - yearsAgo;
    const birthMonth = String((counter % 12) + 1).padStart(2, '0');
    const birthDay   = String((counter % 28) + 1).padStart(2, '0');
    const phone      = '08' + (uid + '0000000000').slice(0, 10);
    const pPhone     = '08' + String(counter + 10000000).slice(0, 10);

    context.vars['full_name']      = 'Calon Siswa ' + counter;
    context.vars['nik']            = nik;
    context.vars['nisn']           = uid.slice(0, 10);
    context.vars['place_of_birth'] = cities[counter % cities.length];
    context.vars['date_of_birth']  = birthYear + '-' + birthMonth + '-' + birthDay;
    context.vars['gender']         = counter % 2 === 0 ? 'male' : 'female';
    context.vars['religion']       = religions[counter % religions.length];
    context.vars['street']         = streets[counter % streets.length] + ' No.' + counter;
    context.vars['rt']             = String((counter % 9) + 1).padStart(3, '0');
    context.vars['rw']             = String((counter % 5) + 1).padStart(3, '0');
    context.vars['phone']          = phone;
    context.vars['email']          = 'siswa' + counter + '.' + ts + '@loadtest.com';
    context.vars['parent_name']    = 'Ayah ' + counter;
    context.vars['mother_name']    = 'Ibu ' + counter;
    context.vars['parent_phone']   = pPhone;
    context.vars['school_name']    = schools[counter % schools.length];
    context.vars['major_1']        = '1';
    context.vars['major_2']        = '2';

    return done();
}
