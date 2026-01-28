using System.Collections.Generic;
using System.Linq;
using UnityEditor;
using UnityEditor.U2D;
using UnityEngine;
using UnityEngine.U2D;

namespace WPFramework.TextureControl
{
    internal static class MenuSpriteAtlasCompression
    {
        [MenuItem(Constants.SpriteAtlasCompressionMenuRootPath + "/Uncompress")]
        public static void MenuExecuteUncompress()
        {
            FindAndUpdateCompressionTargets(TextureImporterCompression.Uncompressed);
        }

        [MenuItem(Constants.SpriteAtlasCompressionMenuRootPath + "/Low")]
        public static void MenuExecuteLow()
        {
            FindAndUpdateCompressionTargets(TextureImporterCompression.CompressedLQ);
        }

        [MenuItem(Constants.SpriteAtlasCompressionMenuRootPath + "/Normal")]
        public static void MenuExecuteNormal()
        {
            FindAndUpdateCompressionTargets(TextureImporterCompression.Compressed);
        }

        [MenuItem(Constants.SpriteAtlasCompressionMenuRootPath + "/High")]
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
            var spriteAtlasAssetPaths = FindSpriteAtlasAssetsFromSelection();
            var assetPaths = new List<string>();
            foreach (var spriteAtlasAssetPath in spriteAtlasAssetPaths)
            {
                var spriteAtlas = AssetDatabase.LoadAssetAtPath<SpriteAtlas>(spriteAtlasAssetPath);
                var packables = spriteAtlas.GetPackables();
                Debug.Log($"{spriteAtlasAssetPath} contains {packables.Length} packables");
                foreach (var packable in packables)
                {
                    var packablePath = AssetDatabase.GetAssetPath(packable);
                    assetPaths.AddRange(TextureFinder.FindTextureAssets(packablePath));
                }
            }
            return assetPaths.ToArray();
        }

        static string[] FindSpriteAtlasAssetsFromSelection()
        {
            var assetPaths = new List<string>();
            foreach (var guid in Selection.assetGUIDs)
            {
                var assetPath = AssetDatabase.GUIDToAssetPath(guid);
                if (AssetDatabase.IsValidFolder(assetPath))
                {
                    var paths =
                        AssetDatabase.FindAssets("t:SpriteAtlas", new[] { assetPath })
                            .Select(AssetDatabase.GUIDToAssetPath)
                            .ToArray();
                    assetPaths.AddRange(paths);
                }
                else
                {
                    // NOTE: テクスチャアセットだった場合だけ処理する
                    if (AssetDatabase.GetMainAssetTypeAtPath(assetPath) != typeof(SpriteAtlas))
                    {
                        continue;
                    }

                    assetPaths.Add(assetPath);
                }
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
