using System.Text.RegularExpressions;

namespace GLOW.Core.Domain.Encoder
{
    public static class UserInputEncoder
    {
        static readonly Regex HtmlTagRegex = new Regex("<.*?>", RegexOptions.Compiled);
        static readonly Regex EscapeSequenceRegex = new Regex(@"\\[abfnrtv'""\\]|[\r\n\t]", RegexOptions.Compiled);

        // 入力された文字列からHTMLタグとエスケープシーケンスを除去するメソッド
        public static string Sanitize(string input)
        {
            if (string.IsNullOrEmpty(input))
            {
                return string.Empty;
            }

            string sanitized = HtmlTagRegex.Replace(input, string.Empty);
            sanitized = EscapeSequenceRegex.Replace(sanitized, string.Empty);

            return sanitized.Trim();
        }
    }
}
