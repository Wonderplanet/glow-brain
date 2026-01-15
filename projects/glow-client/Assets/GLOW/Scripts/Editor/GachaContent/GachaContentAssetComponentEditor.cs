using GLOW.Scenes.GachaContent.Presentation.Views;
using UnityEditor;
using UnityEngine;

namespace GLOW.Editor.GachaContent
{
    class GachaContentAssetPickupAreaValidationResult
    {
        public bool HasEmptyString { get; }
        public bool HasNullGameObject { get; }
        public int EmptyCount { get; }
        public int NullObjectCount { get; }

        public GachaContentAssetPickupAreaValidationResult(
            bool hasEmptyString,
            bool hasNullGameObject,
            int emptyCount,
            int nullObjectCount)
        {
            HasEmptyString = hasEmptyString;
            HasNullGameObject = hasNullGameObject;
            EmptyCount = emptyCount;
            NullObjectCount = nullObjectCount;
        }
    }

    /// <summary>
    /// GachaContentAssetComponentのInspector拡張
    /// ピックアップエリア情報の検証（空文字チェック、null GameObjectチェック）
    /// </summary>
    [CustomEditor(typeof(GachaContentAssetComponent))]
    public class GachaContentAssetComponentEditor : UnityEditor.Editor
    {
        public override void OnInspectorGUI()
        {
            // デフォルトのInspector表示
            DrawDefaultInspector();

            EditorGUILayout.Space(10);

            // 検証処理の実行
            serializedObject.Update();
            var pickupAreaProp = serializedObject.FindProperty("_pickupAreaInformations");

            if (pickupAreaProp == null || !pickupAreaProp.isArray)
            {
                return;
            }

            // 各要素をチェック
            var validationResult = ValidatePickupAreaInformation(pickupAreaProp);

            // 検証結果の表示
            DisplayValidationResult(pickupAreaProp, validationResult);
        }

        GachaContentAssetPickupAreaValidationResult ValidatePickupAreaInformation(SerializedProperty pickupAreaProp)
        {
            bool hasEmptyString = false;
            bool hasNullGameObject = false;
            int emptyCount = 0;
            int nullObjectCount = 0;

            for (int i = 0; i < pickupAreaProp.arraySize; i++)
            {
                var element = pickupAreaProp.GetArrayElementAtIndex(i);
                var pickupGameObject = element.FindPropertyRelative("pickUpGameObject");
                var pickupMstUnitId = element.FindPropertyRelative("pickupMstUnitId");

                // 空文字チェック（nullまたは空文字列）
                if (pickupMstUnitId != null && string.IsNullOrEmpty(pickupMstUnitId.stringValue))
                {
                    hasEmptyString = true;
                    emptyCount++;
                }

                // GameObjectのnullチェック
                if (pickupGameObject != null && pickupGameObject.objectReferenceValue == null)
                {
                    hasNullGameObject = true;
                    nullObjectCount++;
                }
            }

            return new GachaContentAssetPickupAreaValidationResult(hasEmptyString, hasNullGameObject, emptyCount, nullObjectCount);
        }

        void DisplayValidationResult(
            SerializedProperty pickupAreaProp,
            GachaContentAssetPickupAreaValidationResult validationResult)
        {
            EditorGUILayout.BeginVertical(EditorStyles.helpBox);
            EditorGUILayout.LabelField("■ ピックアップエリア情報の検証", EditorStyles.boldLabel);

            if (pickupAreaProp.arraySize == 0)
            {
                EditorGUILayout.HelpBox("✅ ピックアップなし（ガシャ紹介アニメーションのみ）", MessageType.Info);
            }
            else if (validationResult.HasEmptyString || validationResult.HasNullGameObject)
            {
                // エラーがある場合
                if (validationResult.HasEmptyString)
                {
                    EditorGUILayout.HelpBox(
                        $"❌ pickupMstUnitIdに空文字が含まれています ({validationResult.EmptyCount}件)\n正しいIDを設定してください",
                        MessageType.Error);
                }

                if (validationResult.HasNullGameObject)
                {
                    EditorGUILayout.HelpBox(
                        $"❌ pickUpGameObjectがnullの要素があります ({validationResult.NullObjectCount}件)\nGameObjectを設定してください",
                        MessageType.Warning);
                }

                // 詳細リストを表示
                EditorGUILayout.Space(5);
                EditorGUILayout.LabelField("詳細:", EditorStyles.miniBoldLabel);

                for (int i = 0; i < pickupAreaProp.arraySize; i++)
                {
                    var element = pickupAreaProp.GetArrayElementAtIndex(i);
                    var pickupGameObject = element.FindPropertyRelative("pickUpGameObject");
                    var pickupMstUnitId = element.FindPropertyRelative("pickupMstUnitId");

                    bool isEmptyString = string.IsNullOrEmpty(pickupMstUnitId.stringValue);
                    bool isNullObject = pickupGameObject.objectReferenceValue == null;

                    if (isEmptyString || isNullObject)
                    {
                        EditorGUILayout.BeginHorizontal();

                        // インデックス表示
                        GUILayout.Label($"[{i}]", GUILayout.Width(30));

                        // 問題の種類を表示
                        if (isEmptyString)
                        {
                            GUILayout.Label("❌ ID空文字", GUILayout.Width(100));
                        }

                        if (isNullObject)
                        {
                            GUILayout.Label("❌ GameObject null", GUILayout.Width(130));
                        }

                        EditorGUILayout.EndHorizontal();
                    }
                }
            }
            else
            {
                // 問題なし
                EditorGUILayout.HelpBox(
                    $"✅ すべてのピックアップエリア情報が正しく設定されています ({pickupAreaProp.arraySize}件)",
                    MessageType.Info);
            }

            EditorGUILayout.EndVertical();
        }
    }
}
