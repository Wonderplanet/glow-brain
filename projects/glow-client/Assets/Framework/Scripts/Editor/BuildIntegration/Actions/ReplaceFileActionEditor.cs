using System.IO;
using BuildIntegration;
using UnityEditor;
using UnityEngine;

namespace WPFramework.BuildActions
{
    [CustomEditor(typeof(ReplaceFileAction))]
    public class ReplaceFileActionEditor : Editor
    {
        public override void OnInspectorGUI()
        {
            base.OnInspectorGUI();

            var action = target as ReplaceFileAction;
            if (action == null)
            {
                return;
            }

            if (!File.Exists(action.SourceFilePath))
            {
                EditorGUILayout.HelpBox($"Source file is not found. Path: {action.SourceFilePath}", MessageType.Error);
            }

            if (action.IncludeMetaInMove)
            {
                if (!File.Exists(action.SourceMetaFilePath))
                {
                    EditorGUILayout.HelpBox($"Source meta file is not found. Path: {action.SourceFilePath}.meta", MessageType.Error);
                }
            }

            if (!File.Exists(action.DestinationFilePath))
            {
                EditorGUILayout.HelpBox($"Destination file is not found. Path: {action.DestinationFilePath}", MessageType.Info);
            }
            else
            {
                EditorGUILayout.HelpBox($"Destination file is found. Path: {action.DestinationFilePath}", MessageType.Info);
            }

            if (action.IncludeMetaInMove)
            {
                if (!File.Exists(action.DestinationMetaFilePath))
                {
                    EditorGUILayout.HelpBox($"Destination meta file is not found. Path: {action.DestinationFilePath}.meta", MessageType.Info);
                }
                else
                {
                    EditorGUILayout.HelpBox($"Destination meta file is found. Path: {action.DestinationFilePath}.meta", MessageType.Info);
                }
            }

            if (GUILayout.Button("Replace"))
            {
                // NOTE: プロファイルは使わないのでnullを渡す
                action.ExecuteAction<BuildProfile>(null, null);
            }
        }
    }
}
