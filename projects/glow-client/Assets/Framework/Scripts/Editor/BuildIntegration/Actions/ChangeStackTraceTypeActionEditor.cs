using UnityEditor;
using UnityEngine;

namespace WPFramework.BuildActions
{
    [CustomEditor(typeof(ChangeStackTraceTypeAction))]
    public class ChangeStackTraceTypeActionEditor : Editor
    {
        SerializedProperty _logSettingsProperty;

        public void OnEnable()
        {
            _logSettingsProperty = serializedObject.FindProperty("_logSettings");
        }

        public override void OnInspectorGUI()
        {
            // NOTE: 更新
            serializedObject.Update();

            foreach (SerializedProperty property in _logSettingsProperty)
            {
                var logType = property.FindPropertyRelative("logType");
                var stackTraceLogType = property.FindPropertyRelative("stackTraceLogType");

                using (new GUILayout.HorizontalScope())
                {
                    EditorGUILayout.LabelField(logType.enumDisplayNames[logType.enumValueIndex]);
                    stackTraceLogType.enumValueIndex = EditorGUILayout.Popup(stackTraceLogType.enumValueIndex, stackTraceLogType.enumDisplayNames);
                }
            }

            // NOTE: 保存
            serializedObject.ApplyModifiedProperties();

            if (GUILayout.Button("PlayerSettingsに設定"))
            {
                var action = target as ChangeStackTraceTypeAction;
                action?.SetStackTraceLogType();
            }

            if (GUILayout.Button("リセット"))
            {
                var action = target as ChangeStackTraceTypeAction;
                action?.ResetStackTraceLogType();
            }

            EditorGUILayout.HelpBox("StackTraceLogTypeを設定すると、UnityEditorのConsoleに表示されるログのスタックトレースの表示方法を変更できます。\n反映がうまくいかない場合はCmd+Sなどで保存を実行してください。", MessageType.Info);
        }
    }
}
