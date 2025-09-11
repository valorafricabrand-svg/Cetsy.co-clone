class CurrencyUtils {
  static String symbolFor(String code) {
    switch (code.toUpperCase()) {
      case 'USD':
        return '\$';
      case 'EUR':
        return '\u20AC';
      case 'GBP':
        return '\u00A3';
      case 'KES':
        return 'KES';
      case 'JPY':
        return '\u00A5';
      case 'INR':
        return '\u20B9';
      case 'NGN':
        return '\u20A6';
      case 'AUD':
        return 'A\$';
      case 'CAD':
        return 'C\$';
      default:
        return code.toUpperCase();
    }
  }
}
