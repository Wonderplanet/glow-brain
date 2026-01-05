using System.Collections.Generic;
using System.Linq;
using UnityEditor;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Editor.ImageMissingChecker
{
    public class ImageMissingChecker
    {
        const string TargetPropertyName = "m_Sprite";
        const string ProgressBarTitle = "Checking image miggin data";

        public class MissingImageData
        {
            public string PrefabName { get; }
            public string AttachedObjectName { get; }
            public string AttachedObjectHierarchy { get; }
            public string AssetPath { get; }
            public MissingImageData(string prefabName, string attachedObjectName, string hierarchy, string assetPath)
            {
                PrefabName = prefabName;
                AttachedObjectName = attachedObjectName;
                AttachedObjectHierarchy = hierarchy;
                AssetPath = assetPath;
            }

        }
        public static void CheckImageMissing(string targetFolder)
        {
            Debug.Log("<color=green>==Checking image miggin data==</color>");
            string[] guids = AssetDatabase.FindAssets("t:prefab", new [] { targetFolder });

            try
            {
                var missingImageDatas = guids.ToList().SelectMany(guid =>
                {
                    string assetPath = AssetDatabase.GUIDToAssetPath(guid);

                    var gameObject = (GameObject)AssetDatabase.LoadAssetAtPath(assetPath, typeof(GameObject));

                    return gameObject.GetComponentsInChildren<Image>()
                    .SelectMany(image =>
                    {
                        return GenerateMissingDatas(gameObject.name, assetPath, image);
                    });
                })
                .ToList();
                var groupedMissingImages = missingImageDatas.GroupBy(m => m.PrefabName);
                ShowResults(groupedMissingImages, missingImageDatas.Count);
                Debug.Log("<color=green>==Complete image miggin data==</color>");
            }
            finally
            {
                EditorUtility.ClearProgressBar();
            }
        }

        public static IReadOnlyList<MissingImageData> GenerateMissingDatas(string gameObjectName, string assetPath, Image image)
        {
            var invalidDatas = new List<MissingImageData>();
            if (CheckComponentInTargetPropertyName(image, TargetPropertyName))
            {
                var imageGameObject = image.gameObject;

                var missingObj = new MissingImageData(
                    prefabName: gameObjectName,
                    attachedObjectName: imageGameObject.name,
                    hierarchy: GetHierarchyPath(imageGameObject),
                    assetPath: assetPath
                    );
                invalidDatas.Add(missingObj);
            }
            return invalidDatas;
        }
        public static string GetHierarchyPath(GameObject gameObject)
        {
            string path = gameObject.transform.name;
            var parent = gameObject.transform.parent;
            while (parent)
            {
                path = $"{parent.name}/{path}";
                parent = parent.parent;
            }
            return path;
        }

        public static bool CheckComponentInAll(Image image)
        {
            var so = new SerializedObject(image);
            var sp = so.GetIterator();

            while (sp.NextVisible(true))
            {
                if (sp.propertyType != SerializedPropertyType.ObjectReference) continue;
                if (sp.objectReferenceValue != null) continue;
                if (!sp.hasChildren) continue;
                var fileId = sp.FindPropertyRelative("m_FileID");
                if (fileId == null) continue;
                if (fileId.intValue == 0) continue;

                return true;
            }
            return false;
        }
        public static bool CheckComponentInTargetPropertyName(Image image, string targetPropertyName)
        {
            var so = new SerializedObject(image);
            var property = so.FindProperty(targetPropertyName);
            if (property.propertyType != SerializedPropertyType.ObjectReference) return false;
            if (property.objectReferenceValue != null) return false;
            if (!property.hasChildren) return false;
            var fileId = property.FindPropertyRelative("m_FileID");
            if (fileId == null) return false;
            if (fileId.intValue == 0) return false;

            return true;
        }

        public static void ShowResults(IEnumerable<IGrouping<string, MissingImageData>> groupedMissingImages, int missingCount)
        {
            Debug.Log($"{missingCount}件、Image Missingが発見されました");
            foreach (var group in groupedMissingImages)
            {
                Debug.Log("=============");
                Debug.Log("プレハブ名: " + group.Key);
                Debug.Log("アセットパス: " + group.First().AssetPath);
                foreach (var item in group)
                {
                    // Debug.Log("オブジェクト名: " + item.AttachedObjectName);
                    Debug.Log("対象ヒエラルキー: " + item.AttachedObjectHierarchy);
                }
            }
        }
    }
}
