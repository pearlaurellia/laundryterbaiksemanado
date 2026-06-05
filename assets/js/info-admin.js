/**
 * ============================================================
 * info-admin.js — CleanCo Laundry
 * Digunakan di: admin/edit-info.php
 * Murni Native JavaScript (Tanpa Library/Framework)
 * ============================================================
 */

'use strict';

// Objek mapper untuk mematikan dan menghidupkan form secara instan
const _seksiMap = {
    kontak:    { form: 'formKontak',    view: 'viewKontak',    btn: 'btnEditKontak'    },
    jam:       { form: 'formJam',       view: 'viewJam',       btn: 'btnEditJam'       },
    alamat:    { form: 'formAlamat',    view: 'viewAlamat',    btn: 'btnEditAlamat'    },
    kecamatan: { form: 'formKecamatan', view: 'viewKecamatan', btn: 'btnEditKecamatan' },
};

/**
 * Menyembunyikan tampilan teks biasa dan memunculkan form input edit
 */
function bukaEdit(seksi) {
    const s = _seksiMap[seksi];
    if (s) {
        document.getElementById(s.form).style.display = 'block';
        document.getElementById(s.view).style.display = 'none';
        document.getElementById(s.btn).style.display  = 'none';
    }
}

/**
 * Menyembunyikan form input edit dan mengembalikan ke tampilan teks biasa
 */
function tutupEdit(seksi) {
    const s = _seksiMap[seksi];
    if (s) {
        document.getElementById(s.form).style.display = 'none';
        document.getElementById(s.view).style.display = '';
        document.getElementById(s.btn).style.display  = '';
    }
}