class GeoUtils {
  /// Memvalidasi nilai Latitude.
  /// Harus berada di rentang -90.0 hingga 90.0.
  static bool isValidLatitude(double lat) {
    return lat >= -90.0 && lat <= 90.0;
  }

  /// Memvalidasi nilai Longitude.
  /// Harus berada di rentang -180.0 hingga 180.0.
  static bool isValidLongitude(double lng) {
    return lng >= -180.0 && lng <= 180.0;
  }
}
