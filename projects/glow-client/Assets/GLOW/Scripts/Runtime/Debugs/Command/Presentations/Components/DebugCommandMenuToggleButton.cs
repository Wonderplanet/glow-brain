using System;
using UnityEngine;
using UnityEngine.EventSystems;
using UnityEngine.UI;

namespace GLOW.Debugs.Command.Presentations.Components
{
    public class DebugCommandMenuToggleButton : UIBehaviour
    {
        [SerializeField] Button _button;
        [SerializeField] Text _labelText;
        [SerializeField] GameObject _toggleOnGameobject;
        [SerializeField] GameObject _toggleOffGameobject;

        bool _isToggleOn;

        public string Text
        {
            set => _labelText.text = value;
        }

        public Action<bool> OnTapped { get; set; }

        protected override void Awake()
        {
            base.Awake();

            _button.onClick.AddListener(() =>
            {
                SetToggle(!_isToggleOn);
                OnTapped?.Invoke(_isToggleOn);
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
