import 'package:flutter/widgets.dart';

/// Helpers for presenting numbers the way the site does: Persian (Eastern
/// Arabic) digits with a `٬` thousands separator when the active locale is
/// Persian, and plain Latin digits otherwise.
class PersianNumbers {
  const PersianNumbers._();

  static const List<String> _faDigits = <String>[
    '۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹',
  ];

  /// Convert every ASCII digit in [input] to its Persian glyph.
  static String toFa(String input) {
    final StringBuffer buffer = StringBuffer();
    for (final int code in input.runes) {
      if (code >= 0x30 && code <= 0x39) {
        buffer.write(_faDigits[code - 0x30]);
      } else {
        buffer.write(String.fromCharCode(code));
      }
    }
    return buffer.toString();
  }

  /// Group the integer part of [value] with the `٬` separator (e.g. `7٬240`).
  static String _group(int value) {
    final String digits = value.abs().toString();
    final StringBuffer out = StringBuffer(value < 0 ? '-' : '');
    final int firstGroup = digits.length % 3 == 0 ? 3 : digits.length % 3;
    for (int i = 0; i < digits.length; i++) {
      if (i != 0 && (i - firstGroup) % 3 == 0) out.write('٬');
      out.write(digits[i]);
    }
    return out.toString();
  }

  /// Format [value] with grouping, converting to Persian digits when [fa].
  static String format(int value, {bool fa = true}) {
    final String grouped = _group(value);
    return fa ? toFa(grouped) : grouped;
  }

  /// Convert an arbitrary string (already containing Latin digits) to the
  /// active locale's digit set.
  static String localizeDigits(String input, {required bool fa}) {
    return fa ? toFa(input) : input;
  }
}

/// Convenience extensions available from any widget with a [BuildContext].
extension PersianNumbersContext on BuildContext {
  bool get isFa => Localizations.localeOf(this).languageCode == 'fa';

  /// Locale-aware grouped number, e.g. `7٬240` in fa or `7,240` in en.
  String faNum(int value) {
    if (isFa) return PersianNumbers.format(value, fa: true);
    // Latin grouping with comma.
    final String s = value.abs().toString();
    final StringBuffer out = StringBuffer(value < 0 ? '-' : '');
    final int firstGroup = s.length % 3 == 0 ? 3 : s.length % 3;
    for (int i = 0; i < s.length; i++) {
      if (i != 0 && (i - firstGroup) % 3 == 0) out.write(',');
      out.write(s[i]);
    }
    return out.toString();
  }

  /// Locale-aware digit substitution for pre-formatted strings (dates, etc.).
  String faDigits(String input) =>
      PersianNumbers.localizeDigits(input, fa: isFa);
}
