using GLOW.Core.Presentation.Components;
using GLOW.Scenes.InGame.Presentation.Components;
using GLOW.Scenes.InGame.Presentation.Field;
using UnityEditor;
using UnityEngine;


namespace GLOW.Editor.CompositeUnitAsset
{
    public class TimelineAnimationComponentInjector : UnityEditor.Editor
    {
        public static void SetupTimelineAnimation(string assetKey)
        {
            var assetPath = new UnitEffectAssetPath(assetKey);
            SetBattleEffectView(assetPath);
            SetTimelineMangaEffectComponent(assetPath);

            SetCutInBackground(assetPath.CutInSpCutInBackground);
            SetCutInChaEF(assetPath.CutInSpCutInChaEf);
        }

        static void SetBattleEffectView(UnitEffectAssetPath assetPath)
        {
            AddBattleEffectView(assetPath.AtEf);
            AddBattleEffectView(assetPath.AtEfMir);
            AddBattleEffectView(assetPath.SpEf);
            AddBattleEffectView(assetPath.SpEfMir);
            AddBattleEffectView(assetPath.SpEfFollow);
            AddBattleEffectView(assetPath.SpEfNotFollow);
        }

        static void SetTimelineMangaEffectComponent(UnitEffectAssetPath assetPath)
        {
            AddTimelineMangaEffectComponent(assetPath.AtOf);
            AddTimelineMangaEffectComponent(assetPath.AtOfMir);
            AddTimelineMangaEffectComponent(assetPath.SpOf);
            AddTimelineMangaEffectComponent(assetPath.SpOfMir);
        }

        static void SetCutInBackground(string assetPath)
        {
            var prefab = AssetDatabase.LoadAssetAtPath<GameObject>(assetPath);
            if (prefab == null) return;
            var prefabInstance = (GameObject)PrefabUtility.InstantiatePrefab(prefab);

            var timelineAnimation = GetOrAddComponent<TimelineAnimation>(prefabInstance);
            PrefabUtility.SaveAsPrefabAsset(prefabInstance, assetPath);
            DestroyImmediate(prefabInstance);
            Debug.Log($"[CompositeUnitAssetEditor] SetCutInBackground:{assetPath}");
        }

        static void SetCutInChaEF(string assetPath)
        {
            var prefab = AssetDatabase.LoadAssetAtPath<GameObject>(assetPath);
            if (prefab == null) return;
            var prefabInstance = (GameObject)PrefabUtility.InstantiatePrefab(prefab);

            var timelineAnimation = GetOrAddComponent<TimelineAnimation>(prefabInstance);
            var cutInUnitLayer = GetOrAddComponent<CutInUnitLayer>(prefabInstance);

            SetObjectReference(cutInUnitLayer, "_timelineAnimation", timelineAnimation);

            var unitRoot = FindChildByName(prefabInstance.transform, "UnitRoot");

            SetObjectReference(cutInUnitLayer, "_unitRoot", unitRoot);

            PrefabUtility.SaveAsPrefabAsset(prefabInstance, assetPath);
            DestroyImmediate(prefabInstance);
            Debug.Log($"[CompositeUnitAssetEditor] SetCutInChaEF:{assetPath}");
        }

        // 指定したパスのプレハブにTimelineAnimationとBattleEffectViewを追加して、
        // BattleEffectViewの参照にTimelineAnimationを設定する
        static void AddBattleEffectView(string assetPath)
        {
            // プレハブロード
            var prefab = AssetDatabase.LoadAssetAtPath<GameObject>(assetPath);
            if (prefab == null) return;
            var prefabInstance = (GameObject)PrefabUtility.InstantiatePrefab(prefab);

            // TimelineAnimationとBattleEffectViewを取得
            var timelineAnimation = GetOrAddComponent<TimelineAnimation>(prefabInstance);
            var battleEffectView = GetOrAddComponent<BattleEffectView>(prefabInstance);

            // BattleEffectViewにTimelineAnimationを設定
            SetObjectReference(battleEffectView, "_timelineAnimation", timelineAnimation);

            // プレハブ保存
            PrefabUtility.SaveAsPrefabAsset(prefabInstance, assetPath);
            DestroyImmediate(prefabInstance);
            Debug.Log($"[CompositeUnitAssetEditor] AddBattleEffectView:{assetPath}");

        }

        // 指定したパスのプレハブにTimelineAnimationとTimelineMangaEffectComponentを追加して、
        // TimelineMangaEffectComponentの参照にTimelineAnimationを設定する
        static void AddTimelineMangaEffectComponent(string assetPath)
        {
            // プレハブロード
            var prefab = AssetDatabase.LoadAssetAtPath<GameObject>(assetPath);
            if (prefab == null) return;
            var prefabInstance = (GameObject)PrefabUtility.InstantiatePrefab(prefab);

            // TimelineAnimationとTimelineMangaEffectComponentを取得
            var timelineAnimation = GetOrAddComponent<TimelineAnimation>(prefabInstance);
            var mangaEffectComponent = GetOrAddComponent<TimelineMangaEffectComponent>(prefabInstance);

            // TimelineMangaEffectComponentにTimelineAnimationを設定
            SetObjectReference(mangaEffectComponent, "_timelineAnimation", timelineAnimation);

            PrefabUtility.SaveAsPrefabAsset(prefabInstance, assetPath);
            DestroyImmediate(prefabInstance);
            Debug.Log($"[CompositeUnitAssetEditor] AddTimelineMangaEffectComponent:{assetPath}");
        }

        static T GetOrAddComponent<T>(GameObject gameObject) where T : MonoBehaviour
        {
            if (!gameObject.TryGetComponent<T>(out var component))
            {
                component = gameObject.AddComponent<T>();
            }

            return component;
        }

        static void SetObjectReference(Object obj,string propertyName, Object referenceObj)
        {
            var serializedObject = new SerializedObject(obj);
            var timelineAnimationProperty = serializedObject.FindProperty(propertyName);
            timelineAnimationProperty.objectReferenceValue = referenceObj;
            serializedObject.ApplyModifiedProperties();
        }

        static Transform FindChildByName(Transform parent, string name)
        {
            foreach (Transform child in parent)
            {
                if (child.name == name)
                {
                    return child;
                }
                var result = FindChildByName(child, name);
                if (result != null)
                {
                    return result;
                }
            }
            return null;
        }
    }
}
