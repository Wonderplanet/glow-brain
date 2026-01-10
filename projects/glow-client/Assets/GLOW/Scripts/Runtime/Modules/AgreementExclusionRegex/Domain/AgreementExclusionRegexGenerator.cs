using System.Linq;
using System.Text.RegularExpressions;
using GLOW.Core.Constants;

namespace GLOW.Modules.AgreementExclusionRegex.Domain
{
    public class AgreementExclusionRegexGenerator
    {
        public static string GenerateExclusionRegex()
        {
            // フルパスで除外するURLのパターン
            string[] fullPathExclusions = new string[]
            {
                Credentials.FangageSummaryURL,
                Credentials.FangageAnalysisURL,
                Credentials.FangageAdURL,
                Credentials.FangageSummaryFgJumblePersdURL
            };

            // パスが含まれる場合に除外するURLのパターン
            string[] pathContainsExclusions = new string[]
            {
                Credentials.AgreementChannelURL,
                Credentials.SubAgreementChannelURL
            };

            var exclusionPatterns = new System.Collections.Generic.List<string>();

            // 1. フルパスで除外するURLのパターンを構築
            if (fullPathExclusions.Length > 0)
            {
                var escapedFullPaths = fullPathExclusions.Select(url =>
                    // URL全体をエスケープし、その後に$を付けて完全一致を要求
                    Regex.Escape(url) + "$"
                );
                exclusionPatterns.Add($"(?:{string.Join("|", escapedFullPaths)})");
            }

            // 2. パスが含まれる場合に除外するURLのパターンを構築
            if (pathContainsExclusions.Length > 0)
            {
                var escapedPathContains = pathContainsExclusions.Select(url =>
                {
                    // URL全体をエスケープ
                    string escaped = Regex.Escape(url);

                    // エスケープされたURLの末尾が '\/' (スラッシュ) でない場合、そのURL自体とそれに続くスラッシュからのパスを許可
                    if (escaped.EndsWith("\\/"))
                    {
                        return escaped + ".*";
                    }
                    else
                    {
                        return escaped + "(?:\\/.*)?";
                    }
                });
                exclusionPatterns.Add($"(?:{string.Join("|", escapedPathContains)})");
            }

            // 除外パターンが一つもない場合は、全てのHTTPS/HTTP URLにマッチする正規表現を返す
            if (exclusionPatterns.Count == 0)
            {
                return "^https?://.*$";
            }

            string combinedExclusions = string.Join("|", exclusionPatterns);
            return $"^(?!{combinedExclusions}).*$";
        }
    }
}
