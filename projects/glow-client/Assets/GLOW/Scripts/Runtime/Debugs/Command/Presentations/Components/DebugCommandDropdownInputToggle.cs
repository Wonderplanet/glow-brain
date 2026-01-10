using System;
using System.Collections.Generic;
using UnityEngine;
using UnityEngine.EventSystems;
using UnityEngine.Serialization;
using UnityEngine.UI;
using Button = UnityEngine.UI.Button;

namespace GLOW.Debugs.Command.Presentations.Components
{
    // もう少し汎用的なものにできるかも
    public class DebugCommandDropdownInputToggle : UIBehaviour
    {
        [SerializeField] Button _button;
        [SerializeField] Text _labelText;
        [SerializeField] Button _addContentButton;
        [SerializeField] Button _removeContentButton;
        [SerializeField] DebugCommandInputToggle _menuInputTogglePrefab;

        List<DebugCommandInputToggle> _inputToggles = new List<DebugCommandInputToggle>();
        bool _isOpen = false;

        public string DropdownLabel = string.Empty;
        public string Text
        {
            set => _labelText.text = value;
        }

        public Action<List<(bool, string)>> OnValueChanged;

        protected override void Awake()
        {
            _button.onClick.AddListener(() =>
            {
                _isOpen = !_isOpen;
                SetDropdownOpen(_isOpen);
            });

            _addContentButton.onClick.AddListener(() =>
            {
                AddInputToggle();
            });

            _removeContentButton.onClick.AddListener(() =>
            {
                RemoveInputToggle();
            });
        }

        public void InitializeFromDefaultValue(List<(bool, string)> defaultValue)
        {
            if (defaultValue.Count <= 0) return;

            foreach (var (isOn, text) in defaultValue)
            {
                AddInputToggle(isOn, text);
            }
        }

        void SetDropdownOpen(bool open)
        {
            Text = open ? "v " + DropdownLabel : "> " + DropdownLabel;
            foreach (var inputToggle in _inputToggles)
            {
                inputToggle.gameObject.SetActive(open);
            }
        }

        void AddInputToggle(bool isOn = false, string text = null)
        {
            var inputToggle = Instantiate(_menuInputTogglePrefab, transform.parent);
            inputToggle.transform.SetSiblingIndex(transform.GetSiblingIndex() + _inputToggles.Count + 1);
            inputToggle.LabelText = "  - Value";
            inputToggle.IsToggleOn = isOn;
            inputToggle.InputFieldText = string.IsNullOrEmpty(text) ? "" : text;
            inputToggle.OnTapped = (isOn) => { NotifyValueChanged(); };
            inputToggle.OnEndEdit = (text) => { NotifyValueChanged(); };
            _inputToggles.Add(inputToggle);

            if (_isOpen) return;

            _isOpen = true;
            SetDropdownOpen(_isOpen);

            NotifyValueChanged();
        }

        void RemoveInputToggle()
        {
            if (_inputToggles.Count <= 0) return;

            var lastIndex = _inputToggles.Count - 1;
            if (_isOpen && lastIndex == 0)
            {
                _isOpen = false;
                SetDropdownOpen(_isOpen);
            }
            var inputToggle = _inputToggles[lastIndex];
            _inputToggles.RemoveAt(lastIndex);
            Destroy(inputToggle.gameObject);

            NotifyValueChanged();
        }

        void NotifyValueChanged()
        {
            var values = new List<(bool, string)>();
            foreach (var inputToggle in _inputToggles)
            {
                values.Add((inputToggle.IsToggleOn, inputToggle.InputFieldText));
            }

            OnValueChanged?.Invoke(values);
        }
    }
}
