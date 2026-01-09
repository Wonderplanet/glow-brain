using System.Collections.Generic;
using System.Linq;
using UnityEditor;
using UnityEngine;

namespace GLOW.FindReferencesInProject
{
    public class ImageNoUseChecker
    {
        public static void CheckNoUseImages(string prefabFolderPath, string imageFolderPath)
        {
            var prefabDependencyPaths = GetPrefabDependencyPaths(prefabFolderPath);
            Debug.Log("==検索プレハブ数: " + prefabDependencyPaths.Count);

            var imagePaths = GetImagePaths(imageFolderPath);
            Debug.Log("==検索Image数: " + imagePaths.Count);

            var result = imagePaths
                .Where(iPath => !prefabDependencyPaths.Contains(iPath))
                .ToList();

            // 一覧させる
            Debug.Log("==未参照Imageパス一覧 件数: " + result.Count);
            foreach (var r in result)
            {
                Debug.Log(r);
            }
        }

        // プレハブが参照しているアセットのパスを一覧取得
        static List<string> GetPrefabDependencyPaths(string prefabFolderPath)
        {
            string[] guids = AssetDatabase.FindAssets("t:prefab", new [] { prefabFolderPath });

            var a = guids.ToList().SelectMany(guid =>
                {
                    string targetPath = AssetDatabase.GUIDToAssetPath(guid);
                    var result = new List<string>();
                    foreach (var dependency in AssetDatabase.GetDependencies(targetPath, true))
                    {
                        result.Add(dependency);
                    }

                    return result;
                })
                .Distinct();
            return a.ToList();
        }

        // Imageフォルダ内の画像パスを一覧取得
        static List<string> GetImagePaths(string imageFolderPath)
        {
            string[] guids = AssetDatabase.FindAssets("t:texture2D", new[] { imageFolderPath });

            var a = guids.ToList().Select(guid =>
                {
                    string targetPath = AssetDatabase.GUIDToAssetPath(guid);
                    return targetPath;
                })
                .Distinct();
            return a.ToList();
        }

    }
}
