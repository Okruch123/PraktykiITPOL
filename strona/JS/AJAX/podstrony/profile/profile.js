import { state } from "../../../state.js";

export function renderProfil(){
  const p = state.profile;
  const editing = state.editingProfile;
  const field = (label, key, value) => `
    <div class="profile-row">
      <div class="profile-label">${label}</div>
      <div class="profile-value">
        ${editing ? `<input data-field="${key}" value="${value}">` : value}
      </div>
    </div>
  `;
  return `
    <div class="profile-card">
      ${field('Imię i nazwisko','name',p.name)}
      ${field('E-mail','email',p.email)}
      ${field('Telefon','phone',p.phone)}
      <div class="profile-row">
        <div class="profile-label">Nr karty klubowej</div>
        <div class="profile-value">${p.cardNo}</div>
      </div>
      <div class="profile-row">
        <div class="profile-label">Członkostwo</div>
        <div class="profile-value">${p.member}</div>
      </div>
    </div>
    <div class="profile-actions">
      ${editing
        ? `<button class="save-btn" data-action="save-profile">Zapisz zmiany</button>`
        : `<button class="edit-btn" data-action="edit-profile">Edytuj profil</button>`}
    </div>
  `;
}