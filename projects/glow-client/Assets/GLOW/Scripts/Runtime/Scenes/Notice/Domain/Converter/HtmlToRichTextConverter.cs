using System.Text.RegularExpressions;

namespace GLOW.Scenes.Notice.Domain.Converter
{
    public class HtmlToRichTextConverter
    {
        public static string ConvertToRichText(string html)
        {
            var richText = html;
            
            // 太字にするタグを変更
            richText = Regex.Replace(richText, @"<strong>(.*?)<\/strong>", "<b>$1</b>");
            
            // 色タグを変更
            richText = Regex.Replace(richText, @"<span\s+style=""color:\s*(#[0-9A-Fa-f]{6});?"">(.*?)<\/span>", "<color=$1>$2</color>");
            
            // 改行タグの変更
            richText = Regex.Replace(richText, @"<\/?p>", "");
            richText = Regex.Replace(richText, @"<\/p>", "<br>");

            return richText;
        }
    }
}