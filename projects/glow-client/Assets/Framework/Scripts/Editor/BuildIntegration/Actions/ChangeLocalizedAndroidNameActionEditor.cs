using BuildIntegration;
using UnityEditor;
using UnityEngine;

namespace WPFramework.BuildActions
{
    [CustomEditor(typeof(ChangeLocalizedAndroidNameAction))]
    public class ChangeLocalizedAndroidNameActionEditor : Editor
    {
        public override void OnInspectorGUI()
        {
            base.OnInspectorGUI();

            var action = target as ChangeLocalizedAndroidNameAction;
            if (action == null)
            {
                return;
            }

            if (GUILayout.Button("Replace"))
            {
                // NOTE: プロファイルは使わないのでnullを渡す
                action.ExecuteAction<BuildProfile>(null, null);
            }
        }
    }
}
