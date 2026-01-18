using GLOW.Core.Presentation.Components;
using UnityEditor;
using UnityEngine;

namespace GLOW.Editor.UI
{
    [CustomEditor(typeof(ChildScaler))]
    public class ChildScalerEditor : UnityEditor.Editor
    {
        public override void OnInspectorGUI()
        {
            DrawDefaultInspector();
            serializedObject.Update();

            var scaler = (ChildScaler)target;

            if (scaler.SettingPreset != null)
            {
                EditorGUILayout.Space();
                EditorGUILayout.LabelField("▼ ScriptableObject 操作", EditorStyles.boldLabel);

                if (GUILayout.Button("設定を反映（Scriptable → 本体）"))
                {
                    scaler.ApplySettings();
                    EditorUtility.SetDirty(scaler);
                }

                if (GUILayout.Button("設定を保存（本体 → Scriptable）"))
                {
                    scaler.SaveSettings();
                    EditorUtility.SetDirty(scaler.SettingPreset);
                }
                
                if (GUILayout.Button("再生"))
                {
                    scaler.Play();
                }
            }
            else
            {
                EditorGUILayout.HelpBox("ScriptableObjectが優先設定になります。", MessageType.Info);
            }
        }
    }
}