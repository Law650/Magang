import 'package:flutter/material.dart';

/// Konstanta warna aplikasi sesuai PRD §5.2
/// Palet warna status selaras dengan Web Dashboard (Tailwind equivalents).
class AppColors {
  AppColors._();

  // ── Brand / Primary ──────────────────────────────────────────────
  static const Color primary = Color(0xFF2563EB); // Vibrant Blue untuk tombol aktif
  static const Color primaryDark = Color(0xFF0F2933); // Dark Teal/Blue-Grey untuk AppBar / Header
  static const Color accentGreen = Color(0xFF14B8A6); // Teal 500 untuk Form Tekanan Air

  // ── Status — sesuai PRD §5.2 (selaras Tailwind Web) ──────────────
  /// Normal / Sukses — green-600 (#16A34A)
  static const Color statusNormal = Color(0xFF16A34A);

  /// Rendah / Peringatan — yellow-600 (#CA8A04)
  static const Color statusRendah = Color(0xFFCA8A04);

  /// Kritis / Bahaya — red-600 (#DC2626)
  static const Color statusKritis = Color(0xFFDC2626);

  /// Netral / Info / Pending Sync — slate-600 (#475569)
  static const Color statusNetral = Color(0xFF475569);

  // ── Surface & Background ─────────────────────────────────────────
  static const Color background = Color(0xFFF8FAFC); // slate-50
  static const Color surface = Color(0xFFFFFFFF);
  static const Color surfaceVariant = Color(0xFFF1F5F9); // slate-100
  static const Color cardBorder = Color(0xFFE2E8F0); // slate-200

  // ── Text ──────────────────────────────────────────────────────────
  static const Color textPrimary = Color(0xFF0F172A); // slate-900
  static const Color textSecondary = Color(0xFF475569); // slate-600
  static const Color textHint = Color(0xFF94A3B8); // slate-400
  static const Color textOnPrimary = Color(0xFFFFFFFF);

  // ── Misc ──────────────────────────────────────────────────────────
  static const Color divider = Color(0xFFE2E8F0); // slate-200
  static const Color disabled = Color(0xFFCBD5E1); // slate-300
}
