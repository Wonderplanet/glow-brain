using GLOW.Core.Presentation.Views.UIAnimator;
using UnityEditor;
using UnityEditor.SceneManagement;
using UnityEngine;

namespace GLOW.Editor.UI
{
    [CustomEditor(typeof(UIAnimator))]
    public class UIAnimatorEditor : UnityEditor.Editor
    {
        static UIAnimator _editingAnimator;

        public override void OnInspectorGUI()
        {
            DrawDefaultInspector();
            var animator = (UIAnimator)target;
            bool isPrefabMode = PrefabStageUtility.GetCurrentPrefabStage() != null;
            bool isPlaying = Application.isPlaying;

            EditorGUILayout.Space();
            if (isPrefabMode)
            {
                GUI.enabled = false;
                GUILayout.Button("テスト再生");
                GUI.enabled = true;
                EditorGUILayout.HelpBox("Prefabモード中は再生できません", MessageType.Info);
            }
            else if (!isPlaying)
            {
                bool isEditorPlaying = (_editingAnimator == animator) && animator.IsEditorAnimating();
                
                Color prevColor = GUI.backgroundColor;
                if (isEditorPlaying)
                {
                    GUI.backgroundColor = Color.red;
                }

                string btnLabel = isEditorPlaying ? "再生中（クリックで停止＆リセット）" : "テスト再生";
                if (GUILayout.Button(btnLabel))
                {
                    if (isEditorPlaying)
                    {
                        animator.StopAnimation();
                        _editingAnimator = null;
                        EditorApplication.update -= EditorUpdate;
                    }
                    else
                    {
                        animator.PlayAnimation();
                        _editingAnimator = animator;
                        EditorApplication.update -= EditorUpdate;
                        EditorApplication.update += EditorUpdate;
                    }
                }
                GUI.backgroundColor = prevColor;
                if (isEditorPlaying)
                {
                    EditorGUILayout.HelpBox("再生中", MessageType.Info);
                }
            }
        }

        static void EditorUpdate()
        {
            if (_editingAnimator == null)
            {
                EditorApplication.update -= EditorUpdate; 
                return;
            }
            
            _editingAnimator.EditorManualUpdate();
            
            SceneView.RepaintAll();
            UnityEditorInternal.InternalEditorUtility.RepaintAllViews();
            
            if (!_editingAnimator.IsEditorAnimating())
            {
                _editingAnimator = null;
                EditorApplication.update -= EditorUpdate;
            }
        }
    }
}
