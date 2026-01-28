using System;
using System.Collections.Generic;
using System.Linq;
using UnityEditor;
using UnityEngine;

namespace WPFramework.TextureControl
{
    internal static class TextureFinder
    {
        public static bool IsTextureAsset(string assetPath)
        {
            return AssetDatabase.GetMainAssetTypeAtPath(assetPath) == typeof(Texture2D) ||
                   AssetDatabase.GetMainAssetTypeAtPath(assetPath) == typeof(Texture);
        }

        public static string[] FindTextureAssetsFromDirectory(string folderPath)
        {
            var guids =
                AssetDatabase.FindAssets("t:Texture", new[] {folderPath});
            return guids.Select(AssetDatabase.GUIDToAssetPath).Where(IsTextureAsset).ToArray();
        }

        public static string[] FindTextureAssets(string assetPath)
        {
            var assetPaths = new List<string>();
            if (AssetDatabase.IsValidFolder(assetPath))
            {
                var paths = FindTextureAssetsFromDirectory(assetPath);
                assetPaths.AddRange(paths);
            }
            else
            {
                // NOTE: テクスチャアセットだった場合だけ処理する
                if (!IsTextureAsset(assetPath))
                {
                    return Array.Empty<string>();
                }

                assetPaths.Add(assetPath);
            }

            return assetPaths.ToArray();
        }
    }
}
