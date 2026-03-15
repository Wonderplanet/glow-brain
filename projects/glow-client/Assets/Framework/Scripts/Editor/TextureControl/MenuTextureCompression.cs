using System.Collections.Generic;
using UnityEditor;

namespace WPFramework.TextureControl
{
    public static class MenuTextureCompression
    {
        [MenuItem(Constants.TextureCompressionMenuRootPath + "/Uncompress")]
        public static void MenuExecuteUncompress()
        {
            FindAndUpdateCompressionTargets(TextureImporterCompression.Uncompressed);
        }

        [MenuItem(Constants.TextureCompressionMenuRootPath + "/Low")]
        public static void MenuExecuteLow()
        {
            FindAndUpdateCompressionTargets(TextureImporterCompression.CompressedLQ);
        }

        [MenuItem(Constants.TextureCompressionMenuRootPath + "/Normal")]
        public static void MenuExecuteNormal()
        {
            FindAndUpdateCompressionTargets(TextureImporterCompression.Compressed);
        }

        [MenuItem(Constants.TextureCompressionMenuRootPath + "/High")]
        public static void MenuExecuteHigh()
        {
            FindAndUpdateCompressionTargets(TextureImporterCompression.CompressedHQ);
        }

        static void FindAndUpdateCompressionTargets(TextureImporterCompression compression)
        {
            var assetPaths = FindTextureAssetsFromSelection();
            // NOTE: テクスチャの圧縮設定を変更する確認を行う
            if (!ConfirmExecuteChangeCompression(compression, assetPaths))
            {
                return;
            }
            TextureCompressionChanger.ChangeCompression(assetPaths, compression);
        }

        static string[] FindTextureAssetsFromSelection()
        {
            var assetPaths = new List<string>();
            foreach (var guid in Selection.assetGUIDs)
            {
                var assetPath = AssetDatabase.GUIDToAssetPath(guid);
                assetPaths.AddRange(TextureFinder.FindTextureAssets(assetPath));
            }

            return assetPaths.ToArray();
        }

        static bool ConfirmExecuteChangeCompression(TextureImporterCompression newCompression, string[] targetAssetPaths)
        {
            return EditorUtility.DisplayDialog(
                "Compression設定変更確認",
                $"Compression設定を{newCompression}に変更しますか？: {targetAssetPaths.Length} assets",
                "Yes",
                "No");
        }
    }
}
