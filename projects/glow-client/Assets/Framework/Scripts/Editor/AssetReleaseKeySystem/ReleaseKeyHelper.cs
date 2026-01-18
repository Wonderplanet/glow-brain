using System.Text.RegularExpressions;

namespace WPFramework.AssetReleaseKeySystem
{
    /// <summary>
    /// リリースキー周りの情報をパスから取得等するヘルパークラス
    /// リリースキーの内訳
    /// 　202406010 (yyyy：mm：nn:x(yyyy:年 mm:月 nn:その月で何回目の適用か？ x:hotfix)
    /// フォルダの指定方法
    /// Prefabs/Characters/Characters_202406010/Chr_t00_AlchemyChef_equip00A
    ///
    /// ローカルディレクトリが存在する場合、そのパス以下のファイルを全てローカルとして扱う
    /// Prefabs/Characters/Characters_local/Chr_t00_AlchemyChef_equip00A
    /// </summary>
    public static class ReleaseKeyHelper
    {
        public const string ReleaseKeyDelimiter = "!";

        /// <summary>
        /// ファイルパスからリリースキーが入ったフォルダ名を削除する
        /// Prefabs/Characters/Characters_202406010/Chr_t00_AlchemyChef_equip00A
        /// /Characters_20240610/
        /// </summary>
        /// <param name="path">入力パス</param>
        /// <param name="releaseKeyPattern">抽出パターン</param>
        /// <returns></returns>
        public static string RemoveReleaseKeyDirectoryFromPath(string path, string releaseKeyPattern)
        {
            //                   /ReleaseKeyDirectory/
            // Prefabs/Characters/Characters_202406010/Chr_t00_AlchemyChef_equip00A
            // 「/Characters_20240610/」を取得する。ディレクトリからのみリリースキーを取得したいので両端「/」スラッシュが履いている文字列から取得する
            return Regex.Replace(path, releaseKeyPattern,"/");
        }

        /// <summary>
        /// ベースパスかリリースキーを含んだフォルダを取得する
        /// ルールに沿ってない場合は空を返す
        /// </summary>
        /// <param name="path">入力パス</param>
        /// <param name="releaseKeyPattern">抽出パターン</param>
        /// <returns>運用ルールに沿ってない場合は空を返す</returns>
        public static string ExtractDateOrBasePath(string path, string releaseKeyPattern)
        {
            // NOTE: 日付付きパターンにマッチ (先頭のスラッシュはオプション)
            var regexWithDate = new Regex(releaseKeyPattern);
            var matchWithDate = regexWithDate.Match(path);

            if (matchWithDate.Success)
            {
                return matchWithDate.Groups[1].Value;
            }

            // NOTE: 基礎パターンが繰り返されるパスにマッチ (先頭のスラッシュはオプション)
            var regexBaseRepeated = new Regex(@"(?:\/?)([\w]+)(?:\/)\1(?:\/)");
            var matchBaseRepeated = regexBaseRepeated.Match(path);

            return matchBaseRepeated.Success ? matchBaseRepeated.Groups[1].Value :
                // 該当なしの場合は Empty
                string.Empty;
        }

        /// <summary>
        /// ファイルパスからリリースキーを取得する
        /// </summary>
        /// <returns>リリースキー</returns>
        public static string FindReleaseKeyFromPath(string path, string releaseKeyExtractPattern)
        {
            var match = Regex.Match(path, releaseKeyExtractPattern);
            if (!match.Success)
            {
                return "";
            }

            var res = match.Value.Remove(0, 1); // 頭の(_)を削除
            var releaseKey = res.Remove(res.Length-1, 1); // 末の(/)を削除

            return releaseKey;
        }
    }
}
