using System.IO;
using System.Linq;
using System.Text;
using System.Text.RegularExpressions;
using UnityEditor;
using UnityEditor.Build;
using UnityEditor.Build.Reporting;
using UnityEditor.iOS.Xcode;
using UnityEngine;
using UnityEngine.Localization.Settings;

namespace GLOW.Editor.PostprocessBuild
{
    /// <summary>
    /// Localization-Localesにenを設定していないとき、
    /// XCodeのプロジェクト中にen.lproj/InfoPlist.stringsのファイルが存在していないにもかかわらず
    /// pbxprojファイルにen.lproj/InfoPlist.stringsへの参照が残っていて、
    /// アプリの対応言語に英語が含まれてしまう場合があるので
    /// en.lproj/InfoPlist.stringsのファイルが存在しないときは、それへの参照を削除する
    /// </summary>
    public class RemoveEnInfoPlistReferencePostprocess : IPostprocessBuildWithReport
    {
        public int callbackOrder => int.MaxValue;
        
        public void OnPostprocessBuild(BuildReport report)
        {
            if (report.summary.platform != BuildTarget.iOS) return;

            // 英語の言語設定があれば、なにもしない
            var locales = LocalizationSettings.AvailableLocales.Locales;
            var hasEnglishLocale = locales.Any(locale => locale.Identifier.Code == "en");
            if (hasEnglishLocale)
            {
                return;
            }
            
            // pbxprojファイルのパス
            var pbxPath = PBXProject.GetPBXProjectPath(report.summary.outputPath);
        
            var pbxProject = new PBXProject();
            pbxProject.ReadFromFile(pbxPath);
            
            // 存在しないはずのen.lproj/InfoPlist.stringsへの参照を削除
            RemoveEnInfoPlistReference(pbxProject);
            
            // 変更を保存
            pbxProject.WriteToFile(pbxPath);
        }

        static void RemoveEnInfoPlistReference(PBXProject pbxProject)
        {
            var pbxString = pbxProject.WriteToString();
            
            // 「name = en; path = en.lproj/InfoPlist.strings;」の記述がある行を検索してGUIDを取得
            var targetPattern = @"^\s*([A-F0-9]+)\s.*name = en; path = en\.lproj/InfoPlist\.strings;";
            var match = Regex.Match(pbxString, targetPattern, RegexOptions.Multiline);
            
            if (match.Success)
            {
                var guidToRemove = match.Groups[1].Value;
                Debug.Log($"RemoveEnInfoPlistReferencePostprocess: Found GUID to remove: {guidToRemove}");
                
                // 取得したGUIDが記載されている行をすべて削除
                var sb = new StringBuilder();
                using (var reader = new StringReader(pbxString))
                {
                    while (reader.ReadLine() is { } line)
                    {
                        if (line.Contains(guidToRemove)) continue;
                        sb.AppendLine(line);
                    }
                }
                
                pbxProject.ReadFromString(sb.ToString());
                Debug.Log($"RemoveEnInfoPlistReferencePostprocess: Removed all lines containing GUID: {guidToRemove}");
            }
            else
            {
                Debug.Log("RemoveEnInfoPlistReferencePostprocess: Target InfoPlist.strings entry not found");
            }
        }
    }
}
