using UnityEditor;
using UnityEngine;

namespace WonderPlanet
{
    public class UnityPackageExporterWindow : EditorWindow
    {
        [SerializeField] string _targetPath = "";

        [MenuItem("GLOW/UnityPackageExporterWindow", false)]
        static void ShowWindow()
        {
            GetWindow<UnityPackageExporterWindow>("UnityPackageExporterWindow");
        }

        void OnGUI()
        {
            var so = new SerializedObject(this);
            so.Update();

            EditorGUILayout.LabelField("UnityPackage書き出しツール");
            EditorGUILayout.Space();
            EditorGUILayout.LabelField("フォルダ指定 (ex: Assets/Graphics/Characters/[:作業フォルダ])");
            EditorGUILayout.LabelField("    tips: フォルダ右クリック > Copy Pathでパスがコピーできます");
            EditorGUILayout.PropertyField(so.FindProperty("_targetPath"), true);
            so.ApplyModifiedProperties();


            if (GUILayout.Button("生成"))
            {

                if (string.IsNullOrEmpty(_targetPath))
                {
                    EditorUtility.DisplayDialog("指定パスの空行を発見しました", "指定パスに空行は設定できません\n指定パス一覧で空行が無いように調整後、再度お試しください",
                        "確認");
                    return;
                }

                UnityPackageExporter.Export(_targetPath);
            }

            EditorGUILayout.Space();
            EditorGUILayout.LabelField($"書き出し先: 本プロジェクト/{UnityPackageExporter.ExportPath}");
        }
    }
}
