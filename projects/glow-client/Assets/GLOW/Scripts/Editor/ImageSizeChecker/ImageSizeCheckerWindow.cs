using System.IO;
using System.Text.RegularExpressions;
using UnityEditor;
using UnityEngine;

namespace GLOW.Editor.ImageSizeChecker
{
    public enum ImageSize
    {
        Size128 = 128,
        Size256 = 256,
        Size512 = 512,
        Size1024 = 1024,
        Size2048 = 2048,
        Size4096 = 4096,
    }
    public class ImageSizeCheckerWindow : EditorWindow
    {
        [SerializeField] string _targetPath = "";
        [SerializeField] ImageSize _targetSize = ImageSize.Size512;

        SerializedObject _serializedObject;

        [MenuItem("GLOW/ImageSizeCheckerWindow", false, 305)]
        static void ShowWindow()
        {
            GetWindow<ImageSizeCheckerWindow>("ImageSizeCheckerWindow");
        }
        void OnEnable()
        {
            _serializedObject = new SerializedObject(this);
        }
        void OnGUI()
        {
            _serializedObject.Update();

            EditorGUILayout.LabelField("Imageサイズチェッカー");
            EditorGUILayout.Space();
            EditorGUILayout.LabelField("フォルダ指定 (ex: Assets/AssetBundles)");
            EditorGUILayout.LabelField("    tips: フォルダ右クリック > Copy Pathでパスがコピーできます");
            EditorGUILayout.PropertyField(_serializedObject.FindProperty("_targetPath"), true);
            EditorGUILayout.PropertyField(_serializedObject.FindProperty("_targetSize"), true);
            _serializedObject.ApplyModifiedProperties();


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

                ImageSizeChecker.CheckImageSize(_targetPath, (int)_targetSize);
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
