using System.Linq;
using UnityEditor;
using UnityEngine;
using WonderPlanet.UnityStandard.Extension;
using WPFramework.UIEditor;

namespace WPFramework.Presentation.Components
{
    [CustomEditor(typeof(UIButtonSound))]
    public class UIButtonSoundEditor : Editor
    {
        const string SoundPropertyPath = "_soundIdentifier";

        public override void OnInspectorGUI()
        {
            serializedObject.Update();

            // NOTE: UISoundEffectManifestからリストを作成して選択される
            //       シリアライズ自体はstringで行われる
            var soundOption = UIEditorSoundEffectManifest.Get();
            var property = serializedObject.FindProperty(SoundPropertyPath);
            var currentId = property.stringValue;
            var currentIndex = soundOption.IndexOf(currentId);
            var updatedIndex = EditorGUILayout.Popup("Sound Identifier", currentIndex, soundOption.ToArray());

            if (updatedIndex != currentIndex)
            {
                property.stringValue = soundOption.ElementAt(updatedIndex);
            }

            serializedObject.ApplyModifiedProperties();

            if (currentIndex == -1)
            {
                EditorGUILayout.HelpBox("Sound Identifier is not found in manifest.", MessageType.Error);
            }

            if (GUILayout.Button("Refresh Identifier List"))
            {
                // NOTE: キャッシュされている場合があるので明示的にクリアする
                UIEditorSoundEffectManifest.ClearCache();
            }
        }
    }
}
