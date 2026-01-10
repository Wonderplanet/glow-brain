using System;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.ViewModels;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Core.Presentation.Components
{
    public class PlayerResourceIconButtonComponent : UIObject
    {
        [SerializeField] PlayerResourceIconComponent _iconComponent;

        [SerializeField] Button _iconButton;

        public PlayerResourceIconViewModel IconViewModel { get; private set; } = PlayerResourceIconViewModel.Empty;
        public Action AdditionalButtonEvent { get; set; }

        public void Setup(PlayerResourceIconViewModel viewModel, Action onTapped = null)
        {
            IconViewModel = viewModel;
            _iconComponent.Setup(viewModel);
            _iconButton.onClick.RemoveAllListeners();
            _iconButton.onClick.AddListener(() =>
            {
                onTapped?.Invoke();
                AdditionalButtonEvent?.Invoke();
            });
        }

        public void SetAmount(PlayerResourceAmount amount)
        {
            _iconComponent.SetAmount(amount);
        }
    }
}
