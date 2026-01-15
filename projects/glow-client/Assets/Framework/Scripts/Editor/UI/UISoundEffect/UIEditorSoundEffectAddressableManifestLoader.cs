using System.Collections.Generic;
using System.Linq;
using UnityEditor.AddressableAssets;
using UnityEditor.AddressableAssets.Settings;
using WPFramework.Domain.Constants;

namespace WPFramework.UIEditor
{
    public sealed class UIEditorSoundEffectAddressableManifestLoader : IUIEditorSoundEffectManifestLoader
    {
        string[] CreateFromSettings(AddressableAssetSettings settings)
        {
            // NOTE: 現状は抽出ルールがアドレス名のみになっている
            //       ローカルのみのデータから引きたい場合はグループの条件としてローカルであることなどを指定する
            var targetIdentifierList = new List<string>();
            var addressPrefix = AudioAssetPath.GetSePath("");
            var entries = settings.groups.SelectMany(g => g.entries).ToArray();
            foreach (var entry in entries)
            {
                if (!entry.address.StartsWith(addressPrefix))
                {
                    continue;
                }

                var identifier =
                    entry.address.Replace(addressPrefix, "");
                targetIdentifierList.Add(identifier);
            }

            return targetIdentifierList.ToArray();
        }

        IReadOnlyCollection<string> IUIEditorSoundEffectManifestLoader.Load()
        {
            return CreateFromSettings(AddressableAssetSettingsDefaultObject.Settings);
        }
    }
}
