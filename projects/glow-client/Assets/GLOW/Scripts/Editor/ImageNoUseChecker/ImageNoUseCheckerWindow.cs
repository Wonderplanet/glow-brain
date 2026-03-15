using System.IO;
using System.Text.RegularExpressions;
using GLOW.Editor.ImageMissingChecker;
using UnityEditor;
using UnityEngine;
using UnityEngine.Serialization;

namespace GLOW.FindReferencesInProject
{
    public class ImageNoUseCheckerWindow : EditorWindow
    {
        [SerializeField] string _prefabFolderPath = "";
        [SerializeField] string _imageFolderPath = "";

        [MenuItem("GLOW/FindSupport/ImageNoUseCheckerWindow", false, 305)]
        static void ShowWindow()
        {
            GetWindow<ImageNoUseCheckerWindow>("ImageNoUseCheckerWindow");
        }
        void OnGUI()
        {
            var so = new SerializedObject(this);
            so.Update();

            EditorGUILayout.LabelField("使ってないpngを発掘するやーつ");
            EditorGUILayout.Space();
            EditorGUILayout.LabelField("プレハブフォルダ指定 (ex: Assets/GLOW/Prefabs)");
            EditorGUILayout.LabelField("    tips: フォルダ右クリック > Copy Pathでパスがコピーできます");
            EditorGUILayout.PropertyField(so.FindProperty("_prefabFolderPath"), true);
            EditorGUILayout.Space();
            EditorGUILayout.LabelField("画像フォルダ指定 (ex: Assets/GLOW/Graphics/UI)");
            EditorGUILayout.LabelField("    tips: フォルダ右クリック > Copy Pathでパスがコピーできます");
            EditorGUILayout.PropertyField(so.FindProperty("_imageFolderPath"), true);
            EditorGUILayout.Space();
            so.ApplyModifiedProperties();

            if (GUILayout.Button("検索"))
            {
                if (string.IsNullOrEmpty(_prefabFolderPath) || string.IsNullOrEmpty(_imageFolderPath))
                {
                    EditorUtility.DisplayDialog("指定パスの空行を発見しました", "指定パスに空行は設定できません\n指定パス一覧で空行が無いように調整後、再度お試しください", "確認");
                    return;
                }
                if (CheckExistsTargetPath(_prefabFolderPath) || CheckExistsTargetPath(_imageFolderPath))
                {
                    string message = "";
                    message += "フォルダが見つかりませんでした。\nパスの記述確認後、再度お試しください\n";
                    message += _prefabFolderPath;
                    EditorUtility.DisplayDialog("パスが見つかりません", message, "確認");
                    return;
                }

                //ここにチェックロジックを入れる
                ImageNoUseChecker.CheckNoUseImages(_prefabFolderPath, _imageFolderPath);
            }

            bool CheckExistsTargetPath(string path)
            {
                var regex = new Regex(Regex.Escape("Assets"));
                var replacePath = regex.Replace(path, "", 1);
                return !Directory.Exists(Application.dataPath + replacePath);
            }
        }

    }
}
