import 'package:flutter/services.dart';

/// TextInputFormatter kustom yang membatasi input desimal
/// maksimal [decimalPlaces] digit di belakang koma/titik.
///
/// Memaksa pemisah desimal berupa titik (`.`), sesuai PRD §5.3
/// dan konsisten dengan format backend `DECIMAL` (PRD §3.2).
///
/// Regex backend: `regex:/^\d+(\.\d{1,2})?$/`
class DecimalInputFormatter extends TextInputFormatter {
  final int decimalPlaces;

  DecimalInputFormatter({this.decimalPlaces = 2});

  @override
  TextEditingValue formatEditUpdate(
    TextEditingValue oldValue,
    TextEditingValue newValue,
  ) {
    final text = newValue.text;

    // Izinkan kosong
    if (text.isEmpty) return newValue;

    // Ganti koma dengan titik (locale Indonesia default koma)
    final normalized = text.replaceAll(',', '.');

    // Regex: angka dengan opsional titik desimal, maks [decimalPlaces] digit
    final regExp = RegExp(r'^\d*\.?\d{0,' + decimalPlaces.toString() + r'}$');

    if (regExp.hasMatch(normalized)) {
      if (normalized != text) {
        // Jika ada penggantian koma → titik, update teks
        return TextEditingValue(
          text: normalized,
          selection: TextSelection.collapsed(offset: normalized.length),
        );
      }
      return newValue;
    }

    // Jika tidak valid, kembalikan nilai lama
    return oldValue;
  }
}
