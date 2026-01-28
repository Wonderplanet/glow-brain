using System;
using System.Globalization;
using System.Linq;
using UnityEditor.AddressableAssets.Settings;

namespace WPFramework.AssetReleaseKeySystem
{
    public static class ReleaseKeyAssetGroupHelper
    {
        const string FolderJoinDelimiter = "-";
        const string FolderPascalCaseDelimiter = "_";

        public static AddressableAssetGroup CreateAddressableGroup(AddressableAssetSettings settings, string assetGroupName, AddressableAssetGroupTemplate groupTemplate)
        {
            if (!groupTemplate)
            {
                throw new ArgumentNullException(nameof(groupTemplate));
            }

            var group = settings.CreateGroup(assetGroupName, false, false, true, null, groupTemplate.GetTypes());
            groupTemplate.ApplyToAddressableAssetGroup(group);
            return group;
        }

        public static void UpdateAddressableGroup(AddressableAssetSettings settings, string assetGroupName, AddressableAssetGroupTemplate groupTemplate)
        {
            var group = settings.FindGroup(assetGroupName);
            if (!group)
            {
                return;
            }

            if (!groupTemplate)
            {
                throw new ArgumentNullException(nameof(groupTemplate));
            }

            groupTemplate.ApplyToAddressableAssetGroup(group);
        }

        public static string GetGroupNameFromPath(string path, NameConversionWordInfo[] conversionWordInfos)
        {
            var index = path.IndexOf("/", StringComparison.Ordinal);
            if (index < 0)
            {
                return string.Empty;
            }

            var assetGroupName = ConvertToPascalCaseWithHyphen(path.Substring(0, index), conversionWordInfos);
            return assetGroupName;
        }

        static string ConvertToPascalCaseWithHyphen(string input, NameConversionWordInfo[] conversionWordInfos)
        {
            // NOTE: 文字列を分割
            var words = input.Split(FolderPascalCaseDelimiter);

            // NOTE: 各単語の最初の文字を大文字にし、残りを小文字にする
            for (var i = 0; i < words.Length; i++)
            {
                var wordLower = words[i].ToLower();

                // NOTE ConversionWordInfo に基づいて変換
                var conversion = conversionWordInfos.FirstOrDefault(x => x.Word == wordLower);
                if (conversion != null)
                {
                    words[i] = conversion.ReplaceWord;
                }
                else
                {
                    words[i] = CultureInfo.CurrentCulture.TextInfo.ToTitleCase(wordLower);
                }
            }

            // NOTE: 単語を結合用デミリタで結合して返す
            return string.Join(FolderJoinDelimiter, words);
        }

        public static string CreateAssetGroupNameFromReleaseKeyIfNeeds(string assetGroupName, string releaseKey)
        {
            if (!string.IsNullOrEmpty(releaseKey))
            {
                assetGroupName = $"{assetGroupName}{ReleaseKeyHelper.ReleaseKeyDelimiter}{releaseKey}";
            }

            return assetGroupName;
        }
    }
}
