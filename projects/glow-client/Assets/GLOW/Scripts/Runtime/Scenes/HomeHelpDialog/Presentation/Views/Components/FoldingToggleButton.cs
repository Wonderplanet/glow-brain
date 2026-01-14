using System;
using GLOW.Core.Presentation.Components;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Scenes.HomeHelpDialog.Presentation.Views.Components
{
    public class FoldingToggleButton : UIObject
    {
        [SerializeField] UIToggleableComponent _toggleableComponent;
        [SerializeField] Button _button;

        public bool IsToggleOn
        {
            get => _toggleableComponent.IsToggleOn;
            set => _toggleableComponent.IsToggleOn = value;
        }

        public bool Interactable
        {
            get => _button.interactable;
            set => _button.interactable = value;
        }

        public Action<bool> OnToggleAction { get; set; }

        public UIToggleableComponent UIToggleableComponent => _toggleableComponent;

        protected override void Awake()
        {
            base.Awake();
            _button.onClick.AddListener(OnToggleButton);
        }

        void OnToggleButton()
        {
            IsToggleOn = !IsToggleOn;
            OnToggleAction?.Invoke(IsToggleOn);
        }
    }
}
