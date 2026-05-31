import sys
import json
import os
import docx
from docx import Document
import pdfplumber
from difflib import SequenceMatcher

docx_shared = docx.shared
Pt = docx.shared.Pt
Cm = docx.shared.Cm
Inches = docx.shared.Inches

def similar(a, b):
    return SequenceMatcher(None, a.lower().strip(), b.lower().strip()).ratio()

def build_pdf_text_index(pdf_path):
    index = []
    if not pdf_path or not os.path.exists(pdf_path):
        return index
    try:
        with pdfplumber.open(pdf_path) as pdf:
            for page_num, page in enumerate(pdf.pages, 1):
                lines = page.extract_text_lines(return_chars=False) or []
                for line in lines:
                    text = line.get("text", "").strip()
                    if not text:
                        continue
                    index.append({
                        "halaman": page_num,
                        "teks": text,
                        "x0": round(line.get("x0", 0), 2),
                        "y0": round(line.get("top", 0), 2),
                        "x1": round(line.get("x1", 0), 2),
                        "y1": round(line.get("bottom", 0), 2),
                    })
    except Exception:
        pass
    return index

def find_koordinat(pdf_index, teks_docx, used_indices):
    best_score = 0.0
    best_match = None
    best_idx   = -1
    needle     = teks_docx.lower().strip()[:80]

    for i, entry in enumerate(pdf_index):
        if i in used_indices:
            continue
        score = similar(needle, entry["teks"][:80])
        if score > best_score:
            best_score = score
            best_match = entry
            best_idx   = i

    if best_score >= 0.6 and best_match:
        used_indices.add(best_idx)
        return {
            "halaman": best_match["halaman"],
            "x0": best_match["x0"],
            "y0": best_match["y0"],
            "x1": best_match["x1"],
            "y1": best_match["y1"],
            "skor_kecocokan": round(best_score, 2)
        }
    return None

def get_actual_spacing(paragraph):
    p_format = paragraph.paragraph_format
    if p_format.line_spacing is not None:
        if isinstance(p_format.line_spacing, float):
            return round(p_format.line_spacing, 2)
        elif isinstance(p_format.line_spacing, Pt):
            return round(p_format.line_spacing.pt / 12.0, 2)
    if paragraph.style and paragraph.style.paragraph_format.line_spacing is not None:
        style_space = paragraph.style.paragraph_format.line_spacing
        if isinstance(style_space, float):
            return round(style_space, 2)
        elif isinstance(style_space, Pt):
            return round(style_space.pt / 12.0, 2)
    return 1.15

def get_text_element_format(paragraph, doc_default_font, doc_default_size):
    runs_data    = []
    p_style_font = paragraph.style.font.name if paragraph.style and paragraph.style.font.name else doc_default_font
    p_style_size = paragraph.style.font.size.pt if paragraph.style and paragraph.style.font.size else doc_default_size
    if not paragraph.runs:
        return [{"font": p_style_font, "size": p_style_size, "text": paragraph.text}]
    for run in paragraph.runs:
        if not run.text.strip():
            continue
        r_font = run.font.name if run.font.name else p_style_font
        r_size = run.font.size.pt if run.font.size else p_style_size
        runs_data.append({"font": r_font, "size": r_size, "text": run.text})
    return runs_data

def parse_rules(rules_json):
    """Parse rules JSON dari argumen ke-3, fallback ke default jika tidak ada."""
    defaults = {
        "required_margin_top_cm":    4.0,
        "required_margin_bottom_cm": 3.0,
        "required_margin_left_cm":   4.0,
        "required_margin_right_cm":  3.0,
        "required_line_spacing":     1.5,
        "required_font_name":        "Times New Roman",
        "required_font_size":        12.0,
    }
    if not rules_json:
        return defaults
    try:
        parsed = json.loads(rules_json)
        # Merge: pakai nilai dari rules jika ada, fallback ke default
        return {key: parsed.get(key, defaults[key]) for key in defaults}
    except Exception:
        return defaults

def validate_word(file_path, pdf_path=None, rules_json=None):
    if not os.path.exists(file_path):
        return {"is_valid": False, "logs": {"error": "Berkas fisik tidak ditemukan di container."}}
    try:
        doc   = Document(file_path)
        rules = parse_rules(rules_json)

        # Ambil rules dari argumen
        req_top    = float(rules["required_margin_top_cm"])
        req_bottom = float(rules["required_margin_bottom_cm"])
        req_left   = float(rules["required_margin_left_cm"])
        req_right  = float(rules["required_margin_right_cm"])
        req_space  = float(rules["required_line_spacing"])
        req_font   = str(rules["required_font_name"])
        req_size   = float(rules["required_font_size"])

        margin_tolerance = 0.15
        space_tolerance  = 0.08

        result = {
            "is_valid": True,
            "logs": {
                "masalah_global": [],
                "detail_pelanggaran": [],
                "laporan_lengkap": [],
                "rekomendasi": "Format laporan sudah sesuai standar."
            }
        }

        # --- 1. VALIDASI MARGIN ---
        for i, section in enumerate(doc.sections):
            sec_name = f"Bagian {i+1}"
            mT = round(section.top_margin.cm, 2) if section.top_margin else 0
            mB = round(section.bottom_margin.cm, 2) if section.bottom_margin else 0
            mL = round(section.left_margin.cm, 2) if section.left_margin else 0
            mR = round(section.right_margin.cm, 2) if section.right_margin else 0
            if abs(mT - req_top) > margin_tolerance:
                result["is_valid"] = False
                result["logs"]["masalah_global"].append(f"[{sec_name}] Margin Atas {mT} cm (Wajib: {req_top} cm)")
            if abs(mB - req_bottom) > margin_tolerance:
                result["is_valid"] = False
                result["logs"]["masalah_global"].append(f"[{sec_name}] Margin Bawah {mB} cm (Wajib: {req_bottom} cm)")
            if abs(mL - req_left) > margin_tolerance:
                result["is_valid"] = False
                result["logs"]["masalah_global"].append(f"[{sec_name}] Margin Kiri {mL} cm (Wajib: {req_left} cm)")
            if abs(mR - req_right) > margin_tolerance:
                result["is_valid"] = False
                result["logs"]["masalah_global"].append(f"[{sec_name}] Margin Kanan {mR} cm (Wajib: {req_right} cm)")

        # --- 2. PREPARASI DATA DEFAULT ---
        try:
            doc_default_font = doc.styles.element.xpath('w:docDefaults/w:rPrDefault/w:rPr/w:rFonts')[0].get('{http://schemas.openxmlformats.org/wordprocessingml/2006/main}ascii')
        except:
            doc_default_font = "Calibri"
        try:
            doc_default_size = int(doc.styles.element.xpath('w:docDefaults/w:rPrDefault/w:rPr/w:sz')[0].get('{http://schemas.openxmlformats.org/wordprocessingml/2006/main}val')) / 2.0
        except:
            doc_default_size = 11.0

        # --- 3. BUILD PDF INDEX ---
        pdf_index        = build_pdf_text_index(pdf_path)
        used_pdf_indices = set()

        # Variabel simulasi fallback
        sim_page          = 1
        sim_line_in_page  = 1
        sim_accumulated   = 0.0
        page_height_limit = 22.0

        current_page = 1
        line_in_page = 1

        # --- 4. DEEP SCANNING BARIS PER BARIS ---
        for idx, para in enumerate(doc.paragraphs):
            text_clean = para.text.strip()
            if not text_clean:
                continue

            actual_spacing   = get_actual_spacing(para)
            elements_format  = get_text_element_format(para, doc_default_font, doc_default_size)
            max_size_in_para = max([item['size'] for item in elements_format]) if elements_format else req_size
            dominan_font     = elements_format[0]['font'] if elements_format else req_font

            # Update simulasi
            line_height_pt   = max_size_in_para * actual_spacing
            sim_accumulated += (line_height_pt / 72.0) * 2.54
            if sim_accumulated > page_height_limit:
                sim_page        += 1
                sim_line_in_page = 1
                sim_accumulated  = (line_height_pt / 72.0) * 2.54

            # Cari koordinat PDF
            koordinat_pdf = find_koordinat(pdf_index, text_clean, used_pdf_indices)

            # Tentukan lokasi
            if koordinat_pdf:
                actual_page = koordinat_pdf["halaman"]
                if actual_page != current_page:
                    current_page = actual_page
                    line_in_page = 1
                location = f"Hlm. {actual_page}, Baris ~{line_in_page}"
            else:
                if sim_page != current_page:
                    current_page = sim_page
                    line_in_page = 1
                location = f"Hlm. {sim_page}, Baris ~{sim_line_in_page}"

            preview       = text_clean[:60] + "..." if len(text_clean) > 60 else text_clean
            is_line_valid = True
            catatan_baris = []

            # A. Cek Spasi
            if abs(actual_spacing - req_space) > space_tolerance:
                result["is_valid"] = False
                is_line_valid = False
                msg = f"Spasi {actual_spacing} salah (Wajib: {req_space})"
                catatan_baris.append(msg)
                pelanggaran_entry = {"message": f"[{location}] {msg} pada: \"{preview}\""}
                if koordinat_pdf:
                    pelanggaran_entry["koordinat_pdf"] = koordinat_pdf
                result["logs"]["detail_pelanggaran"].append(pelanggaran_entry)

            # B. Cek Font & Ukuran
            font_errors_logged = False
            size_errors_logged = False
            for elem in elements_format:
                if elem['font'] and elem['font'].lower() != req_font.lower() and not font_errors_logged:
                    result["is_valid"] = False
                    is_line_valid = False
                    msg = f"Font '{elem['font']}' salah (Wajib: {req_font})"
                    catatan_baris.append(msg)
                    pelanggaran_entry = {"message": f"[{location}] {msg} pada: \"{preview}\""}
                    if koordinat_pdf:
                        pelanggaran_entry["koordinat_pdf"] = koordinat_pdf
                    result["logs"]["detail_pelanggaran"].append(pelanggaran_entry)
                    font_errors_logged = True
                if elem['size'] and float(elem['size']) != float(req_size) and not size_errors_logged:
                    result["is_valid"] = False
                    is_line_valid = False
                    msg = f"Ukuran {elem['size']}pt salah (Wajib: {req_size}pt)"
                    catatan_baris.append(msg)
                    pelanggaran_entry = {"message": f"[{location}] {msg} pada: \"{preview}\""}
                    if koordinat_pdf:
                        pelanggaran_entry["koordinat_pdf"] = koordinat_pdf
                    result["logs"]["detail_pelanggaran"].append(pelanggaran_entry)
                    size_errors_logged = True

            # C. Log Laporan Lengkap
            laporan_entry = {
                "lokasi": location,
                "teks": preview,
                "font_terdeteksi": dominan_font,
                "ukuran_terdeteksi": max_size_in_para,
                "spasi_terdeteksi": actual_spacing,
                "status": "Valid" if is_line_valid else "Tidak Valid",
                "detail_catatan": catatan_baris,
            }
            if koordinat_pdf:
                laporan_entry["koordinat_pdf"] = koordinat_pdf
            result["logs"]["laporan_lengkap"].append(laporan_entry)

            line_in_page     += 1
            sim_line_in_page += 1

        if not result["is_valid"]:
            result["logs"]["rekomendasi"] = "Silakan perbaiki bagian format dokumen yang ditandai di atas lalu unggah kembali."

        return result

    except Exception as e:
        return {
            "is_valid": False,
            "logs": {
                "error": f"Python Parser Error: {str(e)}",
                "masalah_global": [],
                "detail_pelanggaran": [],
                "laporan_lengkap": []
            }
        }

if __name__ == "__main__":
    if len(sys.argv) < 2:
        print(json.dumps({"error": "Path file .docx tidak ditemukan."}))
        sys.exit(1)

    docx_path  = sys.argv[1]
    pdf_path   = sys.argv[2] if len(sys.argv) >= 3 else None
    rules_json = sys.argv[3] if len(sys.argv) >= 4 else None

    print(json.dumps(validate_word(docx_path, pdf_path, rules_json)))