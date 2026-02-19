using System;
using System.Collections.Generic;
using System.IO;
using System.Reflection;
using UnityEditor;
using UnityEngine;
using GLOW.Scenes.InGame.Presentation.Field;
using Spine.Unity;

namespace GLOW.Editor.CompositeUnitAsset
{
    /// <summary>
    /// unit_chara_xxx_00000.prefabをテンプレートにして
    /// 指定されたSkeletonDataAssetを持つユニットプレハブを生成する
    /// TODO: 常駐エフェクト等に対応できていない
    ///
    /// </summary>
    public static class UnitPrefabGenerator
    {
        public static void GenerateUnitPrefabWithOutline(string assetKey, string releaseKey)
        {
            if (string.IsNullOrEmpty(releaseKey))
            {
                Debug.LogError("リリースキーが設定されていません");
                return;
            }

            // テンプレートプレハブのパス
            var templatePath = "Assets/GLOW/Scripts/Editor/CompositeUnitAsset/AssetTemplate/unit_chara_xxx_00000.prefab";

            // コピー先のディレクトリとファイルパス
            var targetDirectory = $"Assets/GLOW/AssetBundles/unit_sd_prefab/unit_sd_prefab!{releaseKey}";
            var targetPath = $"{targetDirectory}/unit_{assetKey}.prefab";

            if(!Directory.Exists(targetDirectory))
            {
                CreateTargetDirectory(targetDirectory);
            }

            if (!CopyTemplatePrefab(templatePath, targetPath))
            {
                return;
            }

            var prefab = LoadPrefab(targetPath);
            if (prefab == null)
            {
                return;
            }

            // プレハブ編集モードに入る
            var prefabContents = PrefabUtility.LoadPrefabContents(targetPath);

            try
            {
                var skeletonDataAsset = LoadSkeletonDataAsset(assetKey);
                if (skeletonDataAsset == null)
                {
                    return;
                }

                var skeletonObject = CreateSkeletonObject(assetKey, prefabContents.transform, skeletonDataAsset);
                var outlineObject = CreateOutlineObject(skeletonObject.transform, assetKey);

                SetupUnitImageReferences(prefabContents, skeletonObject, outlineObject);

                SavePrefab(prefabContents, targetPath);

                Debug.Log($"ユニットプレハブが正常に生成されました: {targetPath}");
            }
            finally
            {
                PrefabUtility.UnloadPrefabContents(prefabContents);
            }
        }

        static void CreateTargetDirectory(string targetDirectory)
        {
            if (!Directory.Exists(targetDirectory))
            {
                Directory.CreateDirectory(targetDirectory);
            }
        }

        static bool CopyTemplatePrefab(string templatePath, string targetPath)
        {
            if (!AssetDatabase.CopyAsset(templatePath, targetPath))
            {
                Debug.LogError($"テンプレートプレハブのコピーに失敗しました: {templatePath} -> {targetPath}");
                return false;
            }

            AssetDatabase.Refresh();
            return true;
        }

        static GameObject LoadPrefab(string targetPath)
        {
            var prefab = AssetDatabase.LoadAssetAtPath<GameObject>(targetPath);
            if (prefab == null)
            {
                Debug.LogError($"プレハブの読み込みに失敗しました: {targetPath}");
                return null;
            }

            return prefab;
        }

        static SkeletonDataAsset LoadSkeletonDataAsset(string assetKey)
        {
            var skeletonDataPath = $"Assets/GLOW/Graphics/Characters/{assetKey}/Spine/{assetKey}_SkeletonData.asset";
            var skeletonDataAsset = AssetDatabase.LoadAssetAtPath<SkeletonDataAsset>(skeletonDataPath);

            if (skeletonDataAsset == null)
            {
                Debug.LogError($"SkeletonDataAssetが見つかりません: {skeletonDataPath}");
                return null;
            }

            return skeletonDataAsset;
        }

        static GameObject CreateSkeletonObject(string assetKey, Transform parentTransform, SkeletonDataAsset skeletonDataAsset)
        {
            var skeletonObject = new GameObject($"Spine GameObject ({assetKey})");
            skeletonObject.transform.SetParent(parentTransform);

            var skeletonAnimation = skeletonObject.AddComponent<SkeletonAnimation>();

            // SkeletonDataAssetを設定（publicプロパティを使用）
            skeletonAnimation.skeletonDataAsset = skeletonDataAsset;

            // 初期化を実行
            skeletonAnimation.Initialize(false);

            return skeletonObject;
        }

        static GameObject CreateOutlineObject(Transform parentTransform, string assetKey)
        {
            var outlineObject = new GameObject("Outline");
            outlineObject.transform.SetParent(parentTransform);

            // RenderExistingUnitMeshが必要とするコンポーネントを追加
            outlineObject.AddComponent<MeshRenderer>();
            outlineObject.AddComponent<MeshFilter>();

            var renderExistingUnitMesh = outlineObject.AddComponent<RenderExistingUnitMesh>();
            SetOutlineMaterial(renderExistingUnitMesh, assetKey);

            return outlineObject;
        }

        static void SetupUnitImageReferences(GameObject prefabRoot, GameObject skeletonObject, GameObject outlineObject)
        {
            var unitImage = prefabRoot.GetComponent<UnitImage>();
            if (unitImage == null)
            {
                Debug.LogWarning("プレハブルートにUnitImageコンポーネントが見つかりません。");
                return;
            }

            // リフレクションでprivateフィールドに値を設定
            var unitImageType = typeof(UnitImage);

            // SkeletonAnimationオブジェクトの参照設定
            var skeletonAnimationField = unitImageType.GetField("_skeletonAnimation",
                BindingFlags.NonPublic | BindingFlags.Instance);
            if (skeletonAnimationField != null)
            {
                var skeletonAnimation = skeletonObject.GetComponent<SkeletonAnimation>();
                skeletonAnimationField.SetValue(unitImage, skeletonAnimation);
            }
            else
            {
                Debug.LogWarning("UnitImageのprivateフィールド '_skeletonAnimation' が見つかりません。値の設定ができませんでした。");
            }

            // MeshRendererの参照設定
            var meshRendererField = unitImageType.GetField("_meshRenderer",
                BindingFlags.NonPublic | BindingFlags.Instance);
            if(meshRendererField != null)
            {
                var meshRenderer = skeletonObject.GetComponent<MeshRenderer>();
                meshRendererField.SetValue(unitImage, meshRenderer);
            }
            else
            {
                Debug.LogWarning("UnitImageのprivateフィールド '_meshRenderer' が見つかりません。値の設定ができませんでした。");
            }

            // OutlineMeshRendererの参照設定
            var outlineMeshRendererField = unitImageType.GetField("_outlineMeshRenderer",
                BindingFlags.NonPublic | BindingFlags.Instance);
            if (outlineMeshRendererField != null)
            {
                var outlineMeshRenderer = outlineObject.GetComponent<MeshRenderer>();
                outlineMeshRendererField.SetValue(unitImage, outlineMeshRenderer);
            }
            else
            {
                Debug.LogWarning("UnitImageのprivateフィールド '_outlineMeshRenderer' が見つかりません。値の設定ができませんでした。");
            }

            Debug.Log("UnitImageコンポーネントへの参照設定が完了しました。");
        }

        static void SetOutlineMaterial(RenderExistingUnitMesh renderExistingUnitMesh, string assetKey)
        {
            var outlineMaterialPath = $"Assets/GLOW/Graphics/Characters/{assetKey}/Spine/{assetKey}_MaterialOutline.mat";
            var outlineMaterial = AssetDatabase.LoadAssetAtPath<Material>(outlineMaterialPath);

            if (outlineMaterial == null)
            {
                Debug.LogWarning($"アウトラインマテリアルが見つかりません: {outlineMaterialPath}");
                return;
            }

            var renderExistingUnitMeshType = typeof(RenderExistingUnitMesh);

            // ReplacementMaterialを設定（リフレクションを使用）
            var replacementMaterialField = renderExistingUnitMeshType.GetField("_replacementMaterial",
                BindingFlags.NonPublic | BindingFlags.Instance);
            if (replacementMaterialField != null)
            {
                replacementMaterialField.SetValue(renderExistingUnitMesh, outlineMaterial);
            }
            else
            {
                Debug.LogWarning("RenderExistingUnitMeshのprivateフィールド '_replacementMaterial' が見つかりません。値の設定ができませんでした。");
            }

            // ReplacementMaterialPairsを空の配列に設定して、不要な値が入らないようにする
            var replacementMaterialPairsField = renderExistingUnitMeshType.GetField("_replacementMaterialPairs",
                BindingFlags.NonPublic | BindingFlags.Instance);
            if (replacementMaterialPairsField != null)
            {
                replacementMaterialPairsField.SetValue(renderExistingUnitMesh, Array.Empty<RenderExistingUnitMesh.MaterialReplacement>());
            }
            else
            {
                Debug.LogWarning("RenderExistingUnitMeshのprivateフィールド '_replacementMaterialPairs' が見つかりません。値の設定ができませんでした。");
            }
        }

        static void SavePrefab(GameObject prefabContents, string targetPath)
        {
            PrefabUtility.SaveAsPrefabAsset(prefabContents, targetPath);
            AssetDatabase.SaveAssets();
            AssetDatabase.Refresh();
        }
    }
}
