# DESIGN GUIDELINES & SPECIFICATION (DESIGN.md)

## 1. Product Identity & Direction
- **Product Name:** Univ IAM (University Identity & Access Management)
- **Role in Ecosystem:** Central Identity Provider (IdP) & SSO Portal for university sivitas akademika (Dosen, Mahasiswa, Karyawan, Admin SDM).
- **Design Philosophy:** Clean, authoritative, trustworthy, and modern enterprise academic portal.
- **Base UI Framework:** Vuexy (Bootstrap 5 HTML/CSS/JS Assets).
- **Antislop Dials:**
  - **ENERGY 2 (Balanced):** Professional, crisp enterprise interface with clear visual hierarchy.
  - **RHYTHM 2 (Structured):** Predictable, orderly grid compositions with distinct focal app cards.
  - **MOTION 1 (Subtle):** Micro-interactions on button hover, card lift, and dropdown transitions; no distracting animations.

---

## 2. Color Palette & Design Tokens

All colors must meet WCAG AA contrast standards (minimum 4.5:1 for normal text against its background).

| Token Name | Hex Code | Purpose / Application | WCAG Contrast |
| :--- | :--- | :--- | :--- |
| **Primary (Royal Indigo)** | `#7367F0` | Main CTA buttons, active states, brand logos | 4.6:1 on White |
| **Primary Hover / Focus** | `#5E50EE` | Hover and keyboard focus indicator state | 5.8:1 on White |
| **Background (Body)** | `#F8F7FA` | Global page canvas background | Neutral light |
| **Surface (Card / Topbar)**| `#FFFFFF` | Form cards, application launcher cards, navbar | Neutral surface |
| **Heading Text** | `#4B465C` | H1-H6, card titles, app names | 9.2:1 (AAA Pass) |
| **Body Text** | `#6F6B7D` | Descriptive labels, subtitles, inputs | 4.8:1 (AA Pass) |
| **Border & Divider** | `#DBDADE` | Subtle card borders, input strokes, dividers | Structural |
| **Role: Super Admin / SDM** | `#7367F0` | Badge color for Administrator roles | Solid & subtle pill |
| **Role: Dosen** | `#00CFE8` | Badge color for Lecturer sivitas | Solid & subtle pill |
| **Role: Mahasiswa** | `#28C76F` | Badge color for Student sivitas | Solid & subtle pill |
| **Role: Karyawan** | `#FF9F43` | Badge color for Staff / Tendik | Solid & subtle pill |

---

## 3. Typography & Iconography

* **Font Family:** `Public Sans, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif`
* **Font Weights:** `400` (Regular), `500` (Medium), `600` (Semi-bold for Titles), `700` (Bold for Brand H1).
* **Icons:** Tabler Icons (provided via Vuexy `public/assets/fonts/iconify-icons.css` using class `icon-base ti tabler-[icon-name]`).

---

## 4. Component & Screen Specifications

### A. Authentication Page (`/login`)
* **Layout:** Centered single card on `#F8F7FA` background.
* **Header:** University IAM SVG logo + "Univ IAM Single Sign-On" title.
* **Form Controls:**
  * Email input with clear label and placeholder `dosen@univ.ac.id`.
  * Password input with interactive eye toggle (`tabler-eye` / `tabler-eye-off`).
  * Remember Me checkbox.
  * Submit button: `btn btn-primary d-grid w-100` with text "Masuk ke Portal".
* **Interactive Demo Helper:** A subtle quick-fill pill bar (e.g., `[Dosen]`, `[Mahasiswa]`, `[Admin SDM]`) to easily populate login inputs during recruitment demonstrations without typing manually.
* **Feedback States:** Dedicated alert box for validation errors (e.g., credentials mismatch) using accessible alert banners.

### B. SSO Portal Dashboard (`/` or `/portal`)
* **Top Navigation Bar:**
  * Left: Brand logo & "Univ IAM - Portal Sivitas".
  * Right: User demographic pill badge (Nama Lengkap, Nomor Induk NIDN/NIM, Role badge) + Avatar dropdown with "Keluar / Logout" action.
* **Welcome Header:** Personalized greeting ("Selamat Datang, [Nama Lengkap]") with academic status indicator badge (`aktif`, `studi_lanjut`, `cuti`).
* **Academic App Grid (Launcher):**
  * Responsive Grid: 1 col (Mobile), 2 cols (Tablet), 3-4 cols (Desktop).
  * Application Card Elements:
    * Distinct Category Icon inside a rounded container with role-specific accent color.
    * Application Name (e.g., **"Knowledge Hub"**, **"Sistem Informasi Akademik"**, **"Perpustakaan Digital"**).
    * Target Audience tag & one-line description.
    * Action CTA: "Buka Aplikasi" (for Knowledge Hub: triggers OAuth2 Authorization Code flow).
* **Empty State:** If a user has no active applications for their role, display a clean, friendly empty state with an informative explanation.

---

## 5. Anti-Slop Strict Quality Gate (R-01 to R-38 Compliance)

1. **No Em Dashes (`—`):** Never use em dash in copy. Use commas, colons, or parentheses.
2. **No Dead Links:** Every button and card must have a valid `href`, route, or modal toggle.
3. **No Fake Claims / Buzzwords:** Avoid "AI-Powered", "Next-Gen", or placeholder statistics.
4. **Resilience & Mobile:** Tap targets >= 44px, zero horizontal overflow on mobile screens.
5. **Form Accessibility:** All inputs must have associated `<label>` tags and visible focus rings.
