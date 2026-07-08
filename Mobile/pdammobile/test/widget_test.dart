// Basic smoke test for PDAM Mobile app.

import 'package:flutter_test/flutter_test.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:shared_preferences/shared_preferences.dart';

import 'package:pdammobile/main.dart';
import 'package:pdammobile/core/providers/technician_provider.dart';

void main() {
  testWidgets('App launches and shows IdentityPage when no name stored',
      (WidgetTester tester) async {
    // Setup mock SharedPreferences with empty data
    SharedPreferences.setMockInitialValues({});
    final prefs = await SharedPreferences.getInstance();

    await tester.pumpWidget(
      ProviderScope(
        overrides: [
          sharedPreferencesProvider.overrideWithValue(prefs),
        ],
        child: const PdamMobileApp(),
      ),
    );

    // IdentityPage should show "Mulai" button
    expect(find.text('Mulai'), findsOneWidget);
    expect(find.text('PDAM Mobile'), findsOneWidget);
  });
}
