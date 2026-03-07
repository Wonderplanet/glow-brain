using GLOW.Core.Domain.Constants;
using GLOW.Core.Presentation.Components;
using UIKit;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Scenes.UnitSortAndFilterDialog.Presentation.Components.FilterItem
{
    /// <summary> 各フィルタ項目が持つ基本部分 </summary>
    public class FilterItem : UIComponent
    {
        [SerializeField] UIToggleableComponent _toggleableComponent;
        [SerializeField] Button _button;

        public bool IsToggleOn
        {
            get => _toggleableComponent.IsToggleOn;
            set => _toggleableComponent.IsToggleOn = value;
        }

        public UIToggleableComponent UIToggleableComponent => _toggleableComponent;

        protected override void Awake()
        {
            base.Awake();
            _button.onClick.AddListener(OnToggleButton);
        }

        void OnToggleButton()
        {
            IsToggleOn = !IsToggleOn;
        }
    }
}
