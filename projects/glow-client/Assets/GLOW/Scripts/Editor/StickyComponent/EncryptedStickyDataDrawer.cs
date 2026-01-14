using GLOW.Core.Modules.StickyComponent;
using UnityEditor;
using UnityEngine;

namespace GLOW.Editor.StickyComponent
{
    [CustomPropertyDrawer(typeof(EncryptedStickyData))]
    public class EncryptedStickyDataDrawer : PropertyDrawer
    {
        const float MinTextAreaHeight = 100f;
        const float MaxTextAreaHeight = 500f;
        
        public override void OnGUI(Rect position, SerializedProperty property, GUIContent label)
        {
            EditorGUI.BeginProperty(position, label, property);
            
            var encryptedTextProperty = property.FindPropertyRelative("_encryptedText");
            
            var decryptedText = StickyEncryptionUtility.DecryptTextAES(encryptedTextProperty.stringValue);
            
            var textAreaHeight = CalculateTextAreaHeight(decryptedText);
            
            var rect = new Rect(position.x, position.y, position.width, EditorGUIUtility.singleLineHeight);
            EditorGUI.LabelField(rect, "Sticky Text", EditorStyles.boldLabel);
            
            rect.y += EditorGUIUtility.singleLineHeight;
            rect.height = textAreaHeight;
            
            EditorGUI.BeginChangeCheck();
            var newText = EditorGUI.TextArea(rect, decryptedText);
            if (EditorGUI.EndChangeCheck())
            {
                encryptedTextProperty.stringValue = StickyEncryptionUtility.EncryptTextAES(newText);
            }
            
            EditorGUI.EndProperty();
        }
        
        public override float GetPropertyHeight(SerializedProperty property, GUIContent label)
        {
            var encryptedTextProperty = property.FindPropertyRelative("_encryptedText");
            var decryptedText = StickyEncryptionUtility.DecryptTextAES(encryptedTextProperty.stringValue);
            var textAreaHeight = CalculateTextAreaHeight(decryptedText);
            return EditorGUIUtility.singleLineHeight + textAreaHeight + EditorGUIUtility.standardVerticalSpacing;
        }
        
        float CalculateTextAreaHeight(string text)
        {
            if (string.IsNullOrEmpty(text)) return MinTextAreaHeight;
            
            var content = new GUIContent(text);
            var textAreaStyle = EditorStyles.textArea;
            var height = textAreaStyle.CalcHeight(content, EditorGUIUtility.currentViewWidth);
            
            return Mathf.Clamp(height, MinTextAreaHeight, MaxTextAreaHeight);
        }
        
    }
}