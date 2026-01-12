using System.IO;
using System.Text.RegularExpressions;
using UnityEditor;
using UnityEngine;

namespace GLOW.Editor.ImageMissingChecker
{
    public class ImageMissingCheckerWindow : EditorWindow
    {
        const string TargetPath = "";
        [SerializeField] string _targetPath = "";

        [MenuItem("Window/ImageMissingCheckerWindow", false, 305)]
        static void ShowWindow()
        {
            GetWindow<ImageMissingCheckerWindow>("ImageMissingCheckerWindow");
        }
        void OnGUI()
        {
            var so = new SerializedObject(this);
            so.Update();

            EditorGUILayout.LabelField("ImageのMissing発掘するやーつ");
            EditorGUILayout.Space();
            EditorGUILayout.LabelField("フォルダ指定 (ex: Assets/AssetBundles)");
            EditorGUILayout.LabelField("    tips: フォルダ右クリック > Copy Pathでパスがコピーできます");
            EditorGUILayout.PropertyField(so.FindProperty("_targetPath"), true);
            so.ApplyModifiedProperties();


            if (GUILayout.Button("検索"))
            {

                if (string.IsNullOrEmpty(_targetPath))
                {
                    EditorUtility.DisplayDialog("指定パスの空行を発見しました", "指定パスに空行は設定できません\n指定パス一覧で空行が無いように調整後、再度お試しください", "確認");
                    return;
                }
                if (CheckExistsTargetPath(_targetPath))
                {
                    string message = "";
                    message += "フォルダが見つかりませんでした。\nパスの記述確認後、再度お試しください\n";
                    message += _targetPath;
                    EditorUtility.DisplayDialog("パスが見つかりません", message, "確認");
                    return;
                }

                //ここにチェックロジックを入れる
                ImageMissingChecker.CheckImageMissing(_targetPath);
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
