using System;
using System.Collections.Generic;
using System.Text;
using UnityEditor;
using UnityEditor.SceneManagement;
using UnityEngine;
using UnityEngine.Localization.Components;

namespace WPFramework.Localization
{
    internal class UnityLocalizationFindMissingLocalizeStringEvent : EditorWindow
    {
        class FindData
        {
            public GameObject Prefab;
            public GameObject[] Components;
        }

        IReadOnlyList<FindData> _findData;
        Vector2 _scrollPosition;

        [MenuItem("Tools/WonderPlanet/Localization/Find Missing LocalizeString")]
        public static void Find()
        {
            try
            {
                var guids = AssetDatabase.FindAssets("t:prefab", new string[] { "Assets" });
                var findData = new List<FindData>();
                for (var i = 0; i < guids.Length; i++)
                {
                    var path = AssetDatabase.GUIDToAssetPath(guids[i]);

                    EditorUtility.DisplayProgressBar("Find Missing LocalizeString", path, (float)i / guids.Length);

                    var prefab = AssetDatabase.LoadAssetAtPath<GameObject>(path);
                    var localizeStringEvents = prefab.GetComponentsInChildren<LocalizeStringEvent>(true);
                    var components = new List<GameObject>();
                    foreach (var localizeStringEvent in localizeStringEvents)
                    {
                        if (localizeStringEvent.StringReference.IsEmpty)
                        {
                            components.Add(localizeStringEvent.gameObject);
                            continue;
                        }

                        var localizedString = localizeStringEvent.StringReference.GetLocalizedString();
                        if (!IsDefaultNoTranslationMessage(localizedString))
                        {
                            continue;
                        }
                        components.Add(localizeStringEvent.gameObject);
                    }

                    if (components.Count > 0)
                    {
                        findData.Add(new FindData
                        {
                            Prefab = prefab,
                            Components = components.ToArray()
                        });
                    }
                }
                EditorUtility.ClearProgressBar();

                if (findData.Count == 0)
                {
                    EditorUtility.DisplayDialog("Find Missing LocalizeString", "No missing LocalizeStringEvent", "OK");
                    return;
                }

                var window = CreateInstance<UnityLocalizationFindMissingLocalizeStringEvent>();
                window.titleContent = new GUIContent("Find Missing LocalizeStringEvent");
                window.Show(findData);
            }
            finally
            {
                EditorUtility.ClearProgressBar();
            }
        }

        static bool IsDefaultNoTranslationMessage(string message)
        {
            // NOTE: LocalizationにStringを問い合わせた結果情報が見つからなかった場合のデフォルトメッセージの一部であるかを確認
            //       k_DefaultNoTranslationMessage で定義されており場合によってはカスタム可能なものとなるため要確認
            return message.StartsWith("No translation found for");
        }

        static string GetObjectPath(GameObject active)
        {
            var obj = active;
            var builder = new StringBuilder(obj.transform.name);
            var current = obj.transform.parent;

            while (current != null)
            {
                builder.Insert(0, current.name + "/");
                current = current.parent;
            }

            return builder.ToString();
        }

        void Show(IReadOnlyList<FindData> findData)
        {
            _findData = findData;
            Show();
        }

        void OnGUI()
        {
            using var scrollViewScope = new GUILayout.ScrollViewScope(_scrollPosition);
            _scrollPosition = scrollViewScope.scrollPosition;

            foreach (var data in _findData)
            {
                using (new GUILayout.VerticalScope("Box"))
                {
                    EditorGUILayout.ObjectField(data.Prefab, typeof(GameObject), false);
                    GUILayout.Box("プレハブモードて対象のプレハブを開くことにより下にリストアップされている選択をすることで対象にジャンプすることができます。");
                    EditorGUI.indentLevel++;
                    foreach (var component in data.Components)
                    {
                        using (new GUILayout.HorizontalScope())
                        {
                            var stage = PrefabStageUtility.GetCurrentPrefabStage();
                            if (GUILayout.Button("選択"))
                            {
                                var root = stage.prefabContentsRoot;
                                var path = GetObjectPath(component);
                                var targetPath = path.Substring(path.IndexOf("/", StringComparison.Ordinal) + 1);
                                // NOTE: パスに Root オブジェクト が含まれているので削除
                                var target = root.transform.Find(targetPath);
                                if (target)
                                {
                                    Selection.activeGameObject = target.gameObject;
                                }
                            }

                            EditorGUILayout.LabelField(GetObjectPath(component), EditorStyles.label);
                        }
                    }
                    EditorGUI.indentLevel--;
                }
            }
        }
    }
}
