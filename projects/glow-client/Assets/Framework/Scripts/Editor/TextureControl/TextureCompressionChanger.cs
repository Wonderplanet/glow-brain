using UnityEditor;
using UnityEngine;

namespace WPFramework.TextureControl
{
    internal static class TextureCompressionChanger
    {
        public static void ChangeCompression(string[] targetAssetPaths, TextureImporterCompression newCompression)
        {
            // NOTE: テクスチャの圧縮設定を変更する
            try
            {
                // NOTE: エディット結果をまとめて反映させるためエディットモードへ変更する
                AssetDatabase.StartAssetEditing();

                for (var i = 0; i < targetAssetPaths.Length; i++)
                {
                    var assetPath = targetAssetPaths[i];
                    EditorUtility.DisplayProgressBar(
                        "Compression設定を変更中",
                        $"{i + 1} / {targetAssetPaths.Length}",
                        (float)i / targetAssetPaths.Length);

                    if (!TextureCompressionChanger.SetCompression(assetPath, newCompression))
                    {
                        Debug.LogWarning($"Failed to change compression settings: {assetPath}");
                    }
                }
            }
            finally
            {
                // NOTE: エディットモードを終了させる
                AssetDatabase.StopAssetEditing();

                EditorUtility.ClearProgressBar();
            }
        }

        static bool SetCompression(string assetPath, TextureImporterCompression newCompression)
        {
            var importer = AssetImporter.GetAtPath(assetPath) as TextureImporter;
            if (importer == null)
            {
                return false;
            }

            importer.textureCompression = newCompression;
            AssetDatabase.ImportAsset(assetPath);
            return true;
        }
    }
}
