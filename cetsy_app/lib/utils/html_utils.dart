import 'package:html/parser.dart';

String stripHtmlTags(String? htmlString) {
  if (htmlString == null) return '';
  final document = parse(htmlString);
  return parse(document.body?.text).documentElement?.text ?? '';
}
