using System;
using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Presentation.Components;
using UIKit;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Scenes.UnitSortAndFilterDialog.Presentation.Components.FilterCell
{
    public class ToggleAllSelectCancelButtonCell : UIComponent
    {
        [SerializeField] Button _allCancelButton;
        [SerializeField] Button _allSelectButton;
        [SerializeField] List<UIToggleableComponent> _toggleableComponents;

        public Action OnAllSelect;
        public Action OnAllCancel;

        protected override void Awake()
        {
            base.Awake();
            _allSelectButton.onClick.AddListener(OnAllSelectButton);
            _allCancelButton.onClick.AddListener(OnAllCancelButton);
        }

#if UNITY_EDITOR
        protected override void Reset()
        {
            _toggleableComponents = gameObject.GetComponentsInChildren<UIToggleableComponent>().ToList();
        }
#endif

        public void SetToggleComponent(List<UIToggleableComponent> setToggles)
        {
            _toggleableComponents = setToggles;
        }

        public void OnAllSelectButton()
        {
            foreach (var toggleable in _toggleableComponents)
            {
                toggleable.IsToggleOn = true;
            }

            OnAllSelect?.Invoke();
        }

        public void OnAllCancelButton()
        {
            foreach (var toggleable in _toggleableComponents)
            {
                toggleable.IsToggleOn = false;
            }

            OnAllCancel?.Invoke();
        }
    }
}
