using TMPro;
using UnityEditor;
using UnityEngine;

namespace WPFramework.Font
{
    [InitializeOnLoad]
    public static class TMProDynamicFontPlayModeAutoCleaner
    {
        static TMProDynamicFontPlayModeAutoCleaner()
        {
            EditorApplication.playModeStateChanged += OnPlayModeStateChanged;
        }

        static void OnPlayModeStateChanged(PlayModeStateChange state)
        {
            if (state != PlayModeStateChange.ExitingPlayMode)
            {
                return;
            }

            Debug.Log("TMProDynamicFontCleaner: TMP_FontAssetの変更をクリアします");

            var tmpFontAssets = Resources.FindObjectsOfTypeAll<TMP_FontAsset>();
            foreach (var tmpFontAsset in tmpFontAssets)
            {
                if (!tmpFontAsset || tmpFontAsset.atlasPopulationMode != AtlasPopulationMode.Dynamic)
                {
                    continue;
                }

                // NOTE: プレイで動的に追加されたフォントアセット情報をクリアする
                tmpFontAsset.ClearFontAssetData();
                Debug.Log("TMProDynamicFontCleaner: ClearFontAssetData " + tmpFontAsset.name);
            }
        }
    }
}
