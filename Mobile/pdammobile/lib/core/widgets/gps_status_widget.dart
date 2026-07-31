import 'package:flutter/material.dart';
import '../theme/app_colors.dart';
import '../services/gps_service.dart';

/// Widget reusable yang menampilkan status GPS di form.
///
/// 3 state visual:
/// - **Loading**: Spinner + "Mencari lokasi..."
/// - **Sukses**: Ikon hijau + lat/lng (6 digit desimal)
/// - **Gagal**: Ikon merah + pesan error + tombol "Coba Lagi"
class GpsStatusWidget extends StatelessWidget {
  final GpsState gpsState;
  final VoidCallback onRetry;

  const GpsStatusWidget({
    super.key,
    required this.gpsState,
    required this.onRetry,
  });

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);

    return AnimatedSwitcher(
      duration: const Duration(milliseconds: 300),
      child: Container(
        key: ValueKey(gpsState.runtimeType),
        width: double.infinity,
        padding: const EdgeInsets.all(14),
        decoration: BoxDecoration(
          color: _backgroundColor,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: _borderColor, width: 0.8),
        ),
        child: switch (gpsState) {
          GpsLoading() => Row(
              children: [
                const SizedBox(
                  width: 18,
                  height: 18,
                  child: CircularProgressIndicator(
                    strokeWidth: 2,
                    color: AppColors.primary,
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Text(
                    'Mencari lokasi...',
                    style: theme.textTheme.bodyMedium?.copyWith(
                      color: AppColors.primary,
                      fontWeight: FontWeight.w500,
                    ),
                  ),
                ),
              ],
            ),
          GpsSuccess(latitude: final lat, longitude: final lng) => Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(4),
                  decoration: BoxDecoration(
                    color: AppColors.statusNormal.withValues(alpha: 0.15),
                    shape: BoxShape.circle,
                  ),
                  child: const Icon(
                    Icons.check_circle_rounded,
                    color: AppColors.statusNormal,
                    size: 18,
                  ),
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'Lokasi berhasil ditangkap',
                        style: theme.textTheme.bodyMedium?.copyWith(
                          color: AppColors.statusNormal,
                          fontWeight: FontWeight.w600,
                          fontSize: 13,
                        ),
                      ),
                      const SizedBox(height: 2),
                      Text(
                        '${lat.toStringAsFixed(6)}, ${lng.toStringAsFixed(6)}',
                        style: theme.textTheme.bodyMedium?.copyWith(
                          color: AppColors.textSecondary,
                          fontSize: 12,
                          fontFamily: 'monospace',
                        ),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        '⚠️ WAJIB: Refresh koordinat jika berpindah lokasi!',
                        style: theme.textTheme.bodySmall?.copyWith(
                          color: Colors.orange[800],
                          fontWeight: FontWeight.bold,
                          fontSize: 11,
                        ),
                      ),
                    ],
                  ),
                ),
                IconButton(
                  onPressed: onRetry,
                  icon: const Icon(Icons.refresh_rounded),
                  color: AppColors.statusNormal,
                  tooltip: 'Ulangi / Refresh Lokasi',
                  iconSize: 20,
                  splashRadius: 24,
                ),
              ],
            ),
          GpsError(message: final msg) => Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.all(4),
                      decoration: BoxDecoration(
                        color: AppColors.statusKritis.withValues(alpha: 0.15),
                        shape: BoxShape.circle,
                      ),
                      child: const Icon(
                        Icons.error_outline_rounded,
                        color: AppColors.statusKritis,
                        size: 18,
                      ),
                    ),
                    const SizedBox(width: 10),
                    Expanded(
                      child: Text(
                        msg,
                        style: theme.textTheme.bodyMedium?.copyWith(
                          color: AppColors.statusKritis,
                          fontSize: 13,
                        ),
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 10),
                SizedBox(
                  height: 36,
                  child: OutlinedButton.icon(
                    onPressed: onRetry,
                    icon: const Icon(Icons.refresh_rounded, size: 16),
                    label: const Text('Coba Lagi'),
                    style: OutlinedButton.styleFrom(
                      foregroundColor: AppColors.statusKritis,
                      side: const BorderSide(color: AppColors.statusKritis),
                      padding: const EdgeInsets.symmetric(horizontal: 14),
                      textStyle: theme.textTheme.bodyMedium?.copyWith(
                        fontSize: 13,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                  ),
                ),
              ],
            ),
        },
      ),
    );
  }

  Color get _backgroundColor => switch (gpsState) {
        GpsLoading() => AppColors.primary.withValues(alpha: 0.04),
        GpsSuccess() => AppColors.statusNormal.withValues(alpha: 0.04),
        GpsError() => AppColors.statusKritis.withValues(alpha: 0.04),
      };

  Color get _borderColor => switch (gpsState) {
        GpsLoading() => AppColors.primary.withValues(alpha: 0.15),
        GpsSuccess() => AppColors.statusNormal.withValues(alpha: 0.2),
        GpsError() => AppColors.statusKritis.withValues(alpha: 0.2),
      };
}
