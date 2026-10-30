'use strict';

/* ===================================================================
 * StudentHub - AJAX CRUD using Fetch API + PHP JSON API
 * =================================================================== */

const API_URL = 'api.php';

/* ---------- DOM references ---------- */
const $ = (id) => document.getElementById(id);

const searchInput  = $('searchInput');
const courseFilter = $('courseFilter');
const addBtn       = $('addBtn');
const tbody        = $('studentsBody');
const countText    = $('countText');
const errorBanner  = $('errorBanner');
const errorText    = $('errorText');
const retryBtn     = $('retryBtn');

const formDialog   = $('formDialog');
const studentForm  = $('studentForm');
const formTitle    = $('formTitle');
const formError    = $('formError');
const saveBtn      = $('saveBtn');
const cancelBtn    = $('cancelBtn');

const deleteDialog     = $('deleteDialog');
const deleteName       = $('deleteName');
const confirmDeleteBtn = $('confirmDeleteBtn');
const cancelDeleteBtn  = $('cancelDeleteBtn');

const toastEl = $('toast');

const FIELDS = ['name', 'email', 'mobile', 'course', 'year'];

let pendingDeleteId = null;
let latestRequestId = 0;   // ignore out-of-order responses
let toastTimer = null;

/* ===================================================================
 * Centralized API helper (single place for fetch + error handling)
 * =================================================================== */

class ApiError extends Error {
  constructor(message, status = 0, errors = {}) {
    super(message);
    this.status = status;
    this.errors = errors || {};
  }
}

async function apiRequest(method, params = {}, body = null) {
  const url = new URL(API_URL, window.location.href);
  Object.entries(params).forEach(([key, value]) => {
    if (value !== '' && value !== null && value !== undefined) {
      url.searchParams.set(key, value);
    }
  });

  const options = { method, headers: { Accept: 'application/json' } };
  if (body !== null) {
    options.headers['Content-Type'] = 'application/json';
    options.body = JSON.stringify(body);
  }

  let response;
  try {
    response = await fetch(url, options);
  } catch (err) {
    throw new ApiError('Cannot reach the server. Make sure Apache and MySQL are running.', 0);
  }

  let data;
  try {
    data = await response.json();
  } catch (err) {
    throw new ApiError('Server returned an invalid (non-JSON) response.', response.status);
  }

  if (!response.ok || data.success === false) {
    throw new ApiError(data.message || 'Request failed.', response.status, data.errors);
  }
  return data;
}

/* ===================================================================
 * UI state helpers
 * =================================================================== */

function setTableMessage(html, withSpinner = false) {
  tbody.innerHTML = '';
  const tr = document.createElement('tr');
  const td = document.createElement('td');
  td.colSpan = 7;
  td.className = 'state-cell';
  if (withSpinner) {
    const spinner = document.createElement('span');
    spinner.className = 'spinner';
    td.appendChild(spinner);
  }
  td.appendChild(document.createTextNode(html));
  tr.appendChild(td);
  tbody.appendChild(tr);
}

function showError(message) {
  errorText.textContent = message;
  errorBanner.hidden = false;
}

function hideError() {
  errorBanner.hidden = true;
}

function showToast(message, type = 'success') {
  toastEl.textContent = message;
  toastEl.className = 'toast show ' + type;
  clearTimeout(toastTimer);
  toastTimer = setTimeout(() => { toastEl.className = 'toast'; }, 3000);
}

function debounce(fn, delay = 400) {
  let timer;
  return (...args) => {
    clearTimeout(timer);
    timer = setTimeout(() => fn(...args), delay);
  };
}

/* ===================================================================
 * LIST (GET) - with loading, empty and error states
 * =================================================================== */

async function loadStudents() {
  const requestId = ++latestRequestId;

  hideError();
  countText.textContent = '';
  setTableMessage('Loading students...', true);

  try {
    const result = await apiRequest('GET', {
      search: searchInput.value.trim(),
      course: courseFilter.value
    });

    if (requestId !== latestRequestId) return;   // a newer request is in flight

    renderStudents(result.data);
    countText.textContent = result.count + (result.count === 1 ? ' student found' : ' students found');
  } catch (err) {
    if (requestId !== latestRequestId) return;
    setTableMessage('Could not load students.');
    showError(err.message);
  }
}

function renderStudents(students) {
  tbody.innerHTML = '';

  if (students.length === 0) {
    setTableMessage('No students found.');
    return;
  }

  students.forEach((s) => {
    const tr = document.createElement('tr');

    [s.id, s.name, s.email, s.mobile, s.course, s.year].forEach((value) => {
      const td = document.createElement('td');
      td.textContent = value;            // textContent prevents XSS
      tr.appendChild(td);
    });

    const actions = document.createElement('td');
    actions.className = 'actions';

    const editBtn = document.createElement('button');
    editBtn.type = 'button';
    editBtn.className = 'small';
    editBtn.textContent = 'Edit';
    editBtn.addEventListener('click', () => openEditForm(s.id));

    const delBtn = document.createElement('button');
    delBtn.type = 'button';
    delBtn.className = 'small danger';
    delBtn.textContent = 'Delete';
    delBtn.addEventListener('click', () => openDeleteDialog(s));

    actions.append(editBtn, delBtn);
    tr.appendChild(actions);
    tbody.appendChild(tr);
  });
}

/* ===================================================================
 * ADD / EDIT form
 * =================================================================== */

function clearFormErrors() {
  formError.textContent = '';
  document.querySelectorAll('[data-error-for]').forEach((el) => { el.textContent = ''; });
  FIELDS.forEach((f) => $(f).classList.remove('invalid'));
}

function showFieldErrors(errors) {
  Object.entries(errors).forEach(([field, message]) => {
    const slot = document.querySelector('[data-error-for="' + field + '"]');
    if (slot) slot.textContent = message;
    if ($(field)) $(field).classList.add('invalid');
  });
}

function openAddForm() {
  studentForm.reset();
  clearFormErrors();
  $('studentId').value = '';
  formTitle.textContent = 'Add Student';
  saveBtn.textContent = 'Save';
  formDialog.showModal();
  $('name').focus();
}

async function openEditForm(id) {
  studentForm.reset();
  clearFormErrors();
  formTitle.textContent = 'Edit Student';
  saveBtn.textContent = 'Update';

  try {
    const result = await apiRequest('GET', { id });
    const s = result.data;
    $('studentId').value = s.id;
    FIELDS.forEach((f) => { $(f).value = s[f]; });
    formDialog.showModal();
    $('name').focus();
  } catch (err) {
    showToast(err.message, 'error');
    if (err.status === 404) loadStudents();
  }
}

/** Client-side validation (the server validates again). */
function validateForm(values) {
  const errors = {};

  if (!/^[A-Za-z][A-Za-z .'-]{1,59}$/.test(values.name)) {
    errors.name = "Name must be 2-60 characters (letters, spaces, . ' - only).";
  }
  if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(values.email)) {
    errors.email = 'Enter a valid email address.';
  }
  if (!/^[6-9][0-9]{9}$/.test(values.mobile)) {
    errors.mobile = 'Mobile must be 10 digits starting with 6-9.';
  }
  if (!values.course) {
    errors.course = 'Select a course.';
  }
  if (!values.year) {
    errors.year = 'Select a year.';
  }
  return errors;
}

async function handleSubmit(event) {
  event.preventDefault();
  clearFormErrors();

  const values = {
    name:   $('name').value.trim(),
    email:  $('email').value.trim(),
    mobile: $('mobile').value.trim(),
    course: $('course').value,
    year:   $('year').value
  };

  const clientErrors = validateForm(values);
  if (Object.keys(clientErrors).length > 0) {
    showFieldErrors(clientErrors);
    return;
  }

  const id = $('studentId').value;
  const isEdit = id !== '';

  saveBtn.disabled = true;
  const originalLabel = saveBtn.textContent;
  saveBtn.textContent = 'Saving...';

  try {
    const result = isEdit
      ? await apiRequest('PUT', { id }, values)
      : await apiRequest('POST', {}, values);

    formDialog.close();
    showToast(result.message || 'Saved successfully.');
    await loadStudents();                       // refresh list - no page reload
  } catch (err) {
    if (Object.keys(err.errors).length > 0) {
      showFieldErrors(err.errors);              // 409 / 422 field errors from server
    }
    formError.textContent = err.message;
  } finally {
    saveBtn.disabled = false;
    saveBtn.textContent = originalLabel;
  }
}

/* ===================================================================
 * DELETE
 * =================================================================== */

function openDeleteDialog(student) {
  pendingDeleteId = student.id;
  deleteName.textContent = student.name;
  deleteDialog.showModal();
}

async function handleDelete() {
  if (pendingDeleteId === null) return;

  confirmDeleteBtn.disabled = true;
  confirmDeleteBtn.textContent = 'Deleting...';

  try {
    const result = await apiRequest('DELETE', { id: pendingDeleteId });
    deleteDialog.close();
    showToast(result.message || 'Student deleted.');
    await loadStudents();
  } catch (err) {
    deleteDialog.close();
    showToast(err.message, 'error');
    if (err.status === 404) loadStudents();
  } finally {
    pendingDeleteId = null;
    confirmDeleteBtn.disabled = false;
    confirmDeleteBtn.textContent = 'Delete';
  }
}

/* ===================================================================
 * Event listeners
 * =================================================================== */

addBtn.addEventListener('click', openAddForm);
cancelBtn.addEventListener('click', () => formDialog.close());
studentForm.addEventListener('submit', handleSubmit);

cancelDeleteBtn.addEventListener('click', () => deleteDialog.close());
confirmDeleteBtn.addEventListener('click', handleDelete);

retryBtn.addEventListener('click', loadStudents);
courseFilter.addEventListener('change', loadStudents);
searchInput.addEventListener('input', debounce(loadStudents, 400));   // AJAX search with debounce

// Initial load
document.addEventListener('DOMContentLoaded', loadStudents);
