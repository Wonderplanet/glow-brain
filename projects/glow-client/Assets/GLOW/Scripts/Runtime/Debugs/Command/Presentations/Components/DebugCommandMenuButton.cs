using System;
using UnityEngine;
using UnityEngine.EventSystems;
using UnityEngine.UI;

namespace GLOW.Debugs.Command.Presentations.Components
{
    public class DebugCommandMenuButton : UIBehaviour
    {
        [SerializeField] Button _button;
        [SerializeField] Text _labelText;

        public string Text
        {
            set => _labelText.text = value;
        }

        public Action OnTapped { get; set; }

        protected override void Awake()
        {
            base.Awake();

            _button.onClick.AddListener(() => OnTapped?.Invoke());
        }
    }
}
