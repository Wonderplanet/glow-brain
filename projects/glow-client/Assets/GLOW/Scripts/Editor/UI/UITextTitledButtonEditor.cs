using GLOW.Core.Presentation.Components;
using TMPro;
using UnityEditor;
using UnityEditor.UI;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Editor.UI
{
    [CustomEditor(typeof(UITextButton), true)]
    [CanEditMultipleObjects]
    public class UITextTitledButtonEditor : ButtonEditor
    {
        SerializedProperty _titleTextProperty;
        SerializedProperty _titleColorTransitionProperty;
        SerializedProperty _titleDisableTintColorProperty;
        SerializedProperty _titleDisableSwapColorProperty;
        SerializedProperty _optionalGraphicColorTransitionProperty;
        SerializedProperty _optionalGraphicDisableTintColorProperty;

        protected override void OnEnable()
        {
            base.OnEnable();
            _titleTextProperty = serializedObject.FindProperty("_titleText");
            _titleColorTransitionProperty = serializedObject.FindProperty("_titleColorTransition");
            _titleDisableTintColorProperty = serializedObject.FindProperty("_titleDisableTintColor");
            _optionalGraphicColorTransitionProperty = serializedObject.FindProperty("_optionalGraphicColorTransition");
            _optionalGraphicDisableTintColorProperty = serializedObject.FindProperty("_optionalGraphicDisableTintColor");
        }

        public override void OnInspectorGUI()
        {
            base.OnInspectorGUI();

            serializedObject.Update();
            EditorGUILayout.PropertyField(_titleTextProperty);
            EditorGUILayout.PropertyField(_titleColorTransitionProperty);
            if (_titleColorTransitionProperty.enumValueIndex == (int)UITextButton.TitleColorTransition.ColorTint)
            {
                EditorGUILayout.PropertyField(_titleDisableTintColorProperty);
            }
            EditorGUILayout.PropertyField(_optionalGraphicColorTransitionProperty);
            if (_optionalGraphicColorTransitionProperty.enumValueIndex == (int)UITextButton.OptionalGraphicColorTransition.ColorTint)
            {
                EditorGUILayout.PropertyField(_optionalGraphicDisableTintColorProperty);
            }
            serializedObject.ApplyModifiedProperties();
        }

        static void SetDefaultColorTransitionValues(Selectable slider)
        {
            ColorBlock colors = slider.colors;
            colors.highlightedColor = new Color(0.882f, 0.882f, 0.882f);
            colors.pressedColor = new Color(0.698f, 0.698f, 0.698f);
            colors.disabledColor = new Color(0.521f, 0.521f, 0.521f);
        }

        [MenuItem("GameObject/UI/GLOW/UITextTitled Button", false, 0)]
        static void Create()
        {
            GameObject buttonRoot = new GameObject("Button", typeof(RectTransform));

            GameObject childText = new GameObject("TextMeshPro");
            GameObjectUtility.SetParentAndAlign(childText, buttonRoot);

            Image image = buttonRoot.AddComponent<Image>();
            image.sprite = AssetDatabase.GetBuiltinExtraResource<Sprite>("UI/Skin/UISprite.psd");
            image.type = Image.Type.Sliced;
            image.color = new Color(1f, 1f, 1f, 1f);

            TextMeshProUGUI text = childText.AddComponent<TextMeshProUGUI>();
            text.text = "Button";
            text.alignment = TextAlignmentOptions.Center;
            text.color = new Color(50f / 255f, 50f / 255f, 50f / 255f, 1f);

            childText.AddComponent<UIText>();

            Button bt = buttonRoot.AddComponent<UITextButton>();
            SetDefaultColorTransitionValues(bt);

            RectTransform textRectTransform = childText.GetComponent<RectTransform>();
            textRectTransform.anchorMin = Vector2.zero;
            textRectTransform.anchorMax = Vector2.one;
            textRectTransform.sizeDelta = Vector2.zero;

            buttonRoot.transform.SetParent(Selection.activeTransform, false);
        }

        [MenuItem("GameObject/UI/GLOW/UITextTitled Button", true)]
        static bool ValidateCreate()
        {
            return Selection.gameObjects.Length == 1;
        }
    }
}
