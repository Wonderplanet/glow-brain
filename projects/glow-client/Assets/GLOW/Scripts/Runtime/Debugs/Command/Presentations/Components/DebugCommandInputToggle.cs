using System;
using UnityEngine;
using UnityEngine.UI;
using UnityEngine.EventSystems;

namespace GLOW.Debugs.Command.Presentations.Components
{
    public class DebugCommandInputToggle : UIBehaviour
    {
        [SerializeField] Button _button;
        [SerializeField] Text _labelText;
        [SerializeField] GameObject _toggleOnGameobject;
        [SerializeField] GameObject _toggleOffGameobject;
        [SerializeField] InputField _inputField;

        bool _isToggleOn = true;

        public bool IsToggleOn
        {
            get => _isToggleOn;
            set => SetToggle(value);
        }

        public string LabelText
        {
            set => _labelText.text = value;
        }

        public string InputFieldText
        {
            set => _inputField.text = value;
            get => _inputField.text;
        }

        public Action<bool> OnTapped { get; set; }
        public Action<string> OnEndEdit { get; set; }

        protected override void Awake()
        {
            base.Awake();

            _button.onClick.AddListener(() =>
            {
                SetToggle(!_isToggleOn);
                OnTapped?.Invoke(_isToggleOn);
            });

            _inputField.onEndEdit.AddListener(delegate
            {
                OnEndEdit?.Invoke(_inputField.text);
            });
        }

        public void SetToggle(bool isOn)
        {
            _isToggleOn = isOn;

            _toggleOnGameobject.SetActive(isOn);
            _toggleOffGameobject.SetActive(!isOn);
        }
    }
}

