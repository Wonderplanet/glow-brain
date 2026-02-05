using System;
using System.Linq;
using Cysharp.Text;
using UIKit;
using UnityEngine;
using UnityEngine.UI;

#if GLOW_DEBUG
using System.Collections.Generic;
using GLOW.Debugs.Command.Presentations.Components;
#endif //GLOW_DEBUG

namespace GLOW.Debugs.Command.Presentations.Views
{
    public sealed class DebugCommandView : UIView
    {
#if GLOW_DEBUG
        [Header("環境情報")]
        [SerializeField] Text _applicationTimeText;
        [SerializeField] Text _applicationEnvText;
        [Header("メニュー")]
        [SerializeField] RectTransform _menuContentRectTransform;
        [SerializeField] DebugCommandMenuButton _menuButtonPrefab;
        [SerializeField] DebugCommandMenuToggleButton _menuToggleButtonPrefab;
        [SerializeField] DebugCommandTextBox _menuTextBoxPrefab;
        [SerializeField] DebugCommandInputToggle _menuInputTogglePrefab;
        [SerializeField] DebugCommandDropdownInputToggle _menuDropdownInputTogglePrefab;
        [SerializeField] DebugCommandStateButton _menuStateButtonPrefab;

        public string ApplicationTimeText
        {
            set => _applicationTimeText.text = value;
        }
        public string ApplicationEnvText
        {
            set => _applicationEnvText.text = value;
        }

        public void ClearMenu()
        {
            foreach (Transform child in _menuContentRectTransform)
            {
                Destroy(child.gameObject);
            }
        }

        public void AddButton(string text, Action onTapped)
        {
            var button = Instantiate(_menuButtonPrefab, _menuContentRectTransform);
            button.Text = text;
            button.OnTapped = onTapped;
        }

        public void AddToggleButton(string text, bool isOn, Action<bool> onTapped)
        {
            var button = Instantiate(_menuToggleButtonPrefab, _menuContentRectTransform);
            button.Text = text;
            button.OnTapped = onTapped;
            button.SetToggle(isOn);
        }

        public void AddTextBox(string text, string defaultInputFieldText, Action<string> onEndEdit)
        {
            var textBox = Instantiate(_menuTextBoxPrefab, _menuContentRectTransform);
            textBox.Text = text;
            textBox.DefaultInputFieldText = defaultInputFieldText;
            textBox.OnEndEdit = onEndEdit;
        }

        public void AddInputToggle(string text, string defaultInputFieldText, bool isOn, Action<bool> onTapped, Action<string> onEndEdit)
        {
            var inputToggle = Instantiate(_menuInputTogglePrefab, _menuContentRectTransform);
            inputToggle.LabelText = text;
            inputToggle.InputFieldText = defaultInputFieldText;
            inputToggle.OnTapped = onTapped;
            inputToggle.OnEndEdit = onEndEdit;
            inputToggle.SetToggle(isOn);
        }

        public void AddDropdownInputToggle(string text, List<(bool, string)> defaultValue, Action<List<(bool ,string)>> onChanged)
        {
            var dropdownInputToggle = Instantiate(_menuDropdownInputTogglePrefab, _menuContentRectTransform);
            dropdownInputToggle.DropdownLabel = text;
            dropdownInputToggle.InitializeFromDefaultValue(defaultValue);
            dropdownInputToggle.Text = ">" + text;
            dropdownInputToggle.OnValueChanged = onChanged;
        }

        public void AddStateButton(string text, string[] states, string defaultValue, Action<string> onChanged)
        {
            var stateButton = Instantiate(_menuStateButtonPrefab, _menuContentRectTransform);
            stateButton.LabelTextTemp = text;
            stateButton.States = states.ToList();
            stateButton.CurrentValue = defaultValue;
            stateButton.OnChangeState = onChanged;
        }

#endif //GLOW_DEBUG
    }
}
