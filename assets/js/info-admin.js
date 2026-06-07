'use strict';

const _seksiMap = {
    kontak:    { form: 'formKontak',    view: 'viewKontak',    btn: 'btnEditKontak'    },
    jam:       { form: 'formJam',       view: 'viewJam',       btn: 'btnEditJam'       },
    alamat:    { form: 'formAlamat',    view: 'viewAlamat',    btn: 'btnEditAlamat'    },
    kecamatan: { form: 'formKecamatan', view: 'viewKecamatan', btn: 'btnEditKecamatan' },
};

function bukaEdit(seksi) {
    const s = _seksiMap[seksi];
    if (s) {
        document.getElementById(s.form).style.display = 'block';
        document.getElementById(s.view).style.display = 'none';
        document.getElementById(s.btn).style.display  = 'none';
    }
}

function tutupEdit(seksi) {
    const s = _seksiMap[seksi];
    if (s) {
        document.getElementById(s.form).style.display = 'none';
        document.getElementById(s.view).style.display = '';
        document.getElementById(s.btn).style.display  = '';
    }
}